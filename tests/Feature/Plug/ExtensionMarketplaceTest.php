<?php

namespace Tests\Feature\Plug;

use App\Models\Extension;
use App\Models\ExtensionVersion;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExtensionMarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_marketplace(): void
    {
        $this->get(route('extensions.index'))->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_marketplace_listing(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $publisher = Team::factory()->create();

        Extension::create([
            'developer_team_id' => $publisher->id,
            'name' => 'Test Connector',
            'slug' => 'test-connector',
            'tagline' => 'Connects things to other things.',
            'category' => 'connectors',
            'status' => 'certified',
        ]);

        $this->actingAs($user)
            ->get(route('extensions.index'))
            ->assertOk()
            ->assertViewIs('extensions.index')
            ->assertSee('Test Connector');
    }

    public function test_draft_extensions_are_not_listed_in_marketplace(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $publisher = Team::factory()->create();

        Extension::create([
            'developer_team_id' => $publisher->id,
            'name' => 'Unreleased Thing',
            'slug' => 'unreleased-thing',
            'category' => 'general',
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->get(route('extensions.index'))
            ->assertOk()
            ->assertDontSee('Unreleased Thing');
    }

    public function test_team_can_install_a_certified_extension(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $team = $user->currentTeam;
        $publisher = Team::factory()->create();

        $extension = Extension::create([
            'developer_team_id' => $publisher->id,
            'name' => 'Installable Thing',
            'slug' => 'installable-thing',
            'category' => 'general',
            'status' => 'certified',
        ]);

        ExtensionVersion::create([
            'extension_id' => $extension->id,
            'version' => '1.0.0',
            'is_current' => true,
        ]);

        $this->actingAs($user)
            ->post(route('extensions.install', $extension))
            ->assertRedirect(route('extensions.show', $extension));

        $this->assertDatabaseHas('installations', [
            'team_id' => $team->id,
            'extension_id' => $extension->id,
            'status' => 'active',
        ]);
    }

    public function test_team_can_uninstall_a_previously_installed_extension(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $team = $user->currentTeam;
        $publisher = Team::factory()->create();

        $extension = Extension::create([
            'developer_team_id' => $publisher->id,
            'name' => 'Removable Thing',
            'slug' => 'removable-thing',
            'category' => 'general',
            'status' => 'certified',
        ]);

        $this->actingAs($user)->post(route('extensions.install', $extension));
        $this->actingAs($user)->post(route('extensions.uninstall', $extension));

        $this->assertDatabaseHas('installations', [
            'team_id' => $team->id,
            'extension_id' => $extension->id,
            'status' => 'uninstalled',
        ]);
    }

    public function test_draft_extension_detail_page_is_not_visible_to_other_teams(): void
    {
        // Regression test for an IDOR gap: the marketplace index correctly
        // filtered draft listings from browsing, but the show() action had
        // no ownership check at all, so any authenticated user could read
        // another team's unpublished extension by guessing/incrementing the
        // extension ID in the URL.
        $user = User::factory()->withPersonalTeam()->create();
        $publisher = Team::factory()->create();

        $extension = Extension::create([
            'developer_team_id' => $publisher->id,
            'name' => 'Competitor Secret Sauce',
            'slug' => 'competitor-secret-sauce',
            'tagline' => 'Not yet public.',
            'category' => 'general',
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->get(route('extensions.show', $extension))
            ->assertForbidden();
    }

    public function test_publisher_team_can_view_its_own_draft_extension(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $publisher = $user->currentTeam;

        $extension = Extension::create([
            'developer_team_id' => $publisher->id,
            'name' => 'Our Own Draft',
            'slug' => 'our-own-draft',
            'category' => 'general',
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->get(route('extensions.show', $extension))
            ->assertOk()
            ->assertSee('Our Own Draft');
    }

    public function test_uncertified_extension_cannot_be_installed(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $publisher = Team::factory()->create();

        $extension = Extension::create([
            'developer_team_id' => $publisher->id,
            'name' => 'Still Draft',
            'slug' => 'still-draft',
            'category' => 'general',
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->post(route('extensions.install', $extension))
            ->assertForbidden();
    }
}
