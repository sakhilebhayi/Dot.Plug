<?php

namespace Tests\Feature\Plug;

use App\Models\Extension;
use App\Models\ExtensionVersion;
use App\Models\Installation;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExtensionAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function certifiedExtension(string $name = 'Gated Thing'): Extension
    {
        $extension = Extension::create([
            'developer_team_id' => Team::factory()->create()->id,
            'name' => $name,
            'slug' => str($name)->slug()->toString(),
            'category' => 'general',
            'status' => 'certified',
        ]);

        ExtensionVersion::create([
            'extension_id' => $extension->id,
            'version' => '1.0.0',
            'is_current' => true,
        ]);

        return $extension;
    }

    /**
     * A user who belongs to $team with the given Jetstream role and has it
     * as their active team.
     */
    private function memberOf(Team $team, string $role): User
    {
        $user = User::factory()->create();
        $team->users()->attach($user, ['role' => $role]);
        $user->switchTeam($team);

        return $user->fresh();
    }

    private function ownerTeam(): array
    {
        $owner = User::factory()->withPersonalTeam()->create();

        return [$owner, $owner->currentTeam];
    }

    public function test_plain_member_cannot_install(): void
    {
        [, $team] = $this->ownerTeam();
        $editor = $this->memberOf($team, 'editor');
        $extension = $this->certifiedExtension();

        $this->actingAs($editor)
            ->post(route('extensions.install', $extension))
            ->assertForbidden();

        $this->assertDatabaseMissing('installations', ['team_id' => $team->id]);
    }

    public function test_plain_member_cannot_uninstall(): void
    {
        [$owner, $team] = $this->ownerTeam();
        $editor = $this->memberOf($team, 'editor');
        $extension = $this->certifiedExtension();

        $this->actingAs($owner)->post(route('extensions.install', $extension));

        $this->actingAs($editor)
            ->post(route('extensions.uninstall', $extension))
            ->assertForbidden();

        $this->assertDatabaseHas('installations', [
            'team_id' => $team->id,
            'extension_id' => $extension->id,
            'status' => 'active',
        ]);
    }

    public function test_team_admin_can_install_and_uninstall(): void
    {
        [, $team] = $this->ownerTeam();
        $admin = $this->memberOf($team, 'admin');
        $extension = $this->certifiedExtension();

        $this->actingAs($admin)
            ->post(route('extensions.install', $extension))
            ->assertRedirect(route('extensions.show', $extension));
        $this->assertDatabaseHas('installations', ['team_id' => $team->id, 'status' => 'active']);

        $this->actingAs($admin)
            ->post(route('extensions.uninstall', $extension))
            ->assertRedirect(route('extensions.show', $extension));
        $this->assertDatabaseHas('installations', ['team_id' => $team->id, 'status' => 'uninstalled']);
    }

    public function test_team_owner_can_install(): void
    {
        [$owner, $team] = $this->ownerTeam();
        $extension = $this->certifiedExtension();

        $this->actingAs($owner)
            ->post(route('extensions.install', $extension))
            ->assertRedirect(route('extensions.show', $extension));

        $this->assertDatabaseHas('installations', ['team_id' => $team->id, 'status' => 'active']);
    }

    public function test_install_and_uninstall_buttons_are_hidden_from_plain_members(): void
    {
        [$owner, $team] = $this->ownerTeam();
        $editor = $this->memberOf($team, 'editor');
        $extension = $this->certifiedExtension();

        // Not installed: owner sees Install, editor does not.
        $this->actingAs($owner)->get(route('extensions.show', $extension))->assertOk()->assertSee('>Install<', false);
        $this->actingAs($editor)->get(route('extensions.show', $extension))->assertOk()->assertDontSee('>Install<', false);

        // Installed: owner sees Uninstall, editor does not.
        $this->actingAs($owner)->post(route('extensions.install', $extension));
        $this->actingAs($owner)->get(route('extensions.show', $extension))->assertOk()->assertSee('>Uninstall<', false);
        $this->actingAs($editor)->get(route('extensions.show', $extension))->assertOk()->assertDontSee('>Uninstall<', false);
    }

    public function test_team_b_cannot_see_or_remove_team_a_installations(): void
    {
        [$ownerA, $teamA] = $this->ownerTeam();
        [$ownerB] = $this->ownerTeam();
        $extension = $this->certifiedExtension('Team A Tool');

        $this->actingAs($ownerA)->post(route('extensions.install', $extension));
        $this->assertDatabaseHas('installations', ['team_id' => $teamA->id, 'status' => 'active']);

        // Team B (even as owner, so authorization passes) does not see the
        // install on the extension page or the dashboard...
        $this->actingAs($ownerB)->get(route('extensions.show', $extension))
            ->assertOk()
            ->assertViewHas('isInstalled', false);
        $this->actingAs($ownerB)->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('installedCount', 0);

        // ...and cannot remove it.
        $this->actingAs($ownerB)->post(route('extensions.uninstall', $extension));

        $this->assertDatabaseHas('installations', [
            'team_id' => $teamA->id,
            'extension_id' => $extension->id,
            'status' => 'active',
        ]);

        // The Installation model itself never returns another team's rows.
        $this->actingAs($ownerB);
        $this->assertSame(0, Installation::count());
        $this->assertNull(Installation::where('team_id', $teamA->id)->first());
    }

    public function test_team_b_installing_does_not_touch_team_a_row(): void
    {
        [$ownerA, $teamA] = $this->ownerTeam();
        [$ownerB, $teamB] = $this->ownerTeam();
        $extension = $this->certifiedExtension('Shared Tool');

        $this->actingAs($ownerA)->post(route('extensions.install', $extension));
        $this->actingAs($ownerB)->post(route('extensions.install', $extension));
        $this->actingAs($ownerB)->post(route('extensions.uninstall', $extension));

        $this->assertDatabaseHas('installations', ['team_id' => $teamA->id, 'status' => 'active']);
        $this->assertDatabaseHas('installations', ['team_id' => $teamB->id, 'status' => 'uninstalled']);
        $this->assertSame(2, Installation::withoutGlobalScopes()->count());
    }

    public function test_plain_member_cannot_publish(): void
    {
        [, $team] = $this->ownerTeam();
        $editor = $this->memberOf($team, 'editor');

        $this->actingAs($editor)->get(route('extensions.create'))->assertForbidden();

        $this->actingAs($editor)
            ->post(route('extensions.store'), ['name' => 'Rogue Listing', 'category' => 'general'])
            ->assertForbidden();

        $this->assertDatabaseMissing('extensions', ['name' => 'Rogue Listing']);
    }

    public function test_team_admin_can_publish_but_listing_is_always_a_draft(): void
    {
        [, $team] = $this->ownerTeam();
        $admin = $this->memberOf($team, 'admin');

        $this->actingAs($admin)->get(route('extensions.create'))->assertOk();

        // A hand-crafted `status` in the request must be ignored: only a
        // platform operator may certify, and there is no such pipeline here.
        $this->actingAs($admin)
            ->post(route('extensions.store'), [
                'name' => 'Honest Listing',
                'category' => 'general',
                'status' => 'certified',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('extensions', [
            'name' => 'Honest Listing',
            'developer_team_id' => $team->id,
            'status' => 'draft',
        ]);
    }

    public function test_publish_buttons_are_hidden_from_plain_members(): void
    {
        [$owner, $team] = $this->ownerTeam();
        $editor = $this->memberOf($team, 'editor');

        $this->actingAs($owner)->get(route('extensions.index'))->assertOk()->assertSee('Publish an extension');
        $this->actingAs($owner)->get(route('dashboard'))->assertOk()->assertSee('Publish new');

        $this->actingAs($editor)->get(route('extensions.index'))->assertOk()->assertDontSee('Publish an extension');
        $this->actingAs($editor)->get(route('dashboard'))->assertOk()->assertDontSee('Publish new');
    }
}
