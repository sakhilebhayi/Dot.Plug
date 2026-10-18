<?php

namespace Tests\Feature\Plug;

use App\Models\Extension;
use App\Models\ExtensionVersion;
use App\Models\Installation;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Jetstream;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Role by action. InfoDot has a read-only "viewer" company role, so this app has
 * one too: a viewer browses the marketplace and the dashboard like an editor does,
 * but cannot install, uninstall, publish, edit or delete anything. Install,
 * uninstall and publishing stay owner/admin only.
 *
 * The "outsider" is somebody who does not belong to the team at all, even when
 * their active team is forced to point at it.
 */
class ViewerRoleMatrixTest extends TestCase
{
    use RefreshDatabase;

    private const ROLES = ['owner', 'admin', 'editor', 'viewer', 'outsider'];

    private Team $team;

    /** @var array<string, User> */
    private array $users = [];

    protected function setUp(): void
    {
        parent::setUp();

        $owner = User::factory()->withPersonalTeam()->create();
        $this->team = $owner->currentTeam;
        $this->users['owner'] = $owner;

        foreach (['admin', 'editor', 'viewer'] as $role) {
            $user = User::factory()->create();
            $this->team->users()->attach($user, ['role' => $role]);
            $user->switchTeam($this->team);
            $this->users[$role] = $user->fresh();
        }

        // Not a member. Their active team points at the target team anyway.
        $outsider = User::factory()->withPersonalTeam()->create();
        $outsider->forceFill(['current_team_id' => $this->team->id])->save();
        $this->users['outsider'] = $outsider->fresh();
    }

    private function certified(): Extension
    {
        $extension = Extension::create([
            'developer_team_id' => Team::factory()->create()->id,
            'name' => 'Matrix Listing',
            'slug' => 'matrix-listing',
            'category' => 'general',
            'status' => 'certified',
        ]);

        ExtensionVersion::create(['extension_id' => $extension->id, 'version' => '1.0.0', 'is_current' => true]);

        return $extension;
    }

    /** A draft this team publishes itself. */
    private function ownDraft(): Extension
    {
        return Extension::create([
            'developer_team_id' => $this->team->id,
            'name' => 'Our Draft',
            'slug' => 'our-draft',
            'category' => 'general',
            'status' => 'draft',
        ]);
    }

    public function test_jetstream_has_a_read_only_viewer_role(): void
    {
        $viewer = Jetstream::findRole('viewer');

        $this->assertNotNull($viewer);
        $this->assertSame(['read'], $viewer->permissions);
        $this->assertSame(['admin', 'editor', 'viewer'], array_keys(Jetstream::$roles));
    }

    /**
     * @return array<string, array{string, list<string>}>
     */
    public static function writes(): array
    {
        $managers = ['owner', 'admin'];

        return [
            'install' => ['install', $managers],
            'uninstall' => ['uninstall', $managers],
            'publish' => ['create', $managers],
            'update own listing' => ['update', $managers],
            'delete own listing' => ['delete', $managers],
        ];
    }

    /**
     * @param  list<string>  $allowed
     */
    #[DataProvider('writes')]
    public function test_policy_matrix(string $ability, array $allowed): void
    {
        $extension = in_array($ability, ['update', 'delete'], true) ? $this->ownDraft() : $this->certified();

        foreach (self::ROLES as $role) {
            $user = $this->users[$role];
            $args = $ability === 'create' ? [Extension::class] : [$extension];

            $this->assertSame(
                in_array($role, $allowed, true),
                $user->can($ability, $args),
                "{$role} / {$ability}"
            );
        }
    }

    public function test_every_member_can_browse_and_only_outsiders_cannot_open_the_drafts(): void
    {
        $draft = $this->ownDraft();
        $certified = $this->certified();

        foreach (self::ROLES as $role) {
            $user = $this->users[$role];
            $member = $role !== 'outsider';

            $this->assertTrue($user->can('viewAny', Extension::class), "{$role} viewAny");
            $this->assertTrue($user->can('view', $certified), "{$role} view certified");
            $this->assertSame($member, $user->can('view', $draft), "{$role} view draft");
        }
    }

    /**
     * @return array<string, array{string, string, list<string>}>
     */
    public static function http(): array
    {
        $managers = ['owner', 'admin'];
        $members = ['owner', 'admin', 'editor', 'viewer'];

        return [
            'marketplace' => ['get', 'index', $members],
            'dashboard' => ['get', 'dashboard', $members],
            'detail' => ['get', 'show', $members],
            'publish form' => ['get', 'create', $managers],
            'publish' => ['post', 'store', $managers],
            'install' => ['post', 'install', $managers],
            'uninstall' => ['post', 'uninstall', $managers],
        ];
    }

    /**
     * @param  list<string>  $allowed
     */
    #[DataProvider('http')]
    public function test_http_matrix(string $method, string $action, array $allowed): void
    {
        $certified = $this->certified();

        foreach (self::ROLES as $role) {
            // Each role starts from the same state.
            Installation::query()->delete();
            Extension::where('slug', 'like', 'published-%')->delete();
            if ($action === 'uninstall') {
                Installation::create([
                    'team_id' => $this->team->id, 'extension_id' => $certified->id,
                    'extension_version_id' => $certified->versions()->first()->id,
                    'status' => 'active', 'installed_at' => now(),
                ]);
            }

            $user = $this->users[$role];
            $request = $this->actingAs($user);

            $response = match ($action) {
                'index' => $request->get(route('extensions.index')),
                'dashboard' => $request->get(route('dashboard')),
                'show' => $request->get(route('extensions.show', $certified)),
                'create' => $request->get(route('extensions.create')),
                'store' => $request->post(route('extensions.store'), ['name' => 'Published By '.$role, 'category' => 'general']),
                'install' => $request->post(route('extensions.install', $certified)),
                'uninstall' => $request->post(route('extensions.uninstall', $certified)),
            };

            $this->flushSession();

            if (in_array($role, $allowed, true)) {
                $this->assertContains($response->getStatusCode(), [200, 302], "{$role} {$action} should be allowed");
                $this->assertNotSame(403, $response->getStatusCode());
            } elseif ($role === 'outsider' && in_array($action, ['index', 'dashboard', 'show'], true)) {
                // Reading is not team-gated for anybody signed in: certified listings are public.
                $this->assertContains($response->getStatusCode(), [200, 302], "{$role} {$action}");
            } else {
                $response->assertForbidden();
            }
        }
    }

    public function test_a_denied_write_changes_nothing(): void
    {
        $certified = $this->certified();

        $this->actingAs($this->users['viewer'])->post(route('extensions.install', $certified))->assertForbidden();
        $this->assertSame(0, Installation::count());

        $this->actingAs($this->users['viewer'])->post(route('extensions.store'), ['name' => 'Sneaky', 'category' => 'general'])->assertForbidden();
        $this->assertSame(0, Extension::where('name', 'Sneaky')->count());

        Installation::create([
            'team_id' => $this->team->id, 'extension_id' => $certified->id,
            'extension_version_id' => $certified->versions()->first()->id, 'status' => 'active', 'installed_at' => now(),
        ]);
        $this->actingAs($this->users['viewer'])->post(route('extensions.uninstall', $certified))->assertForbidden();
        $this->assertSame('active', Installation::first()->status);
    }

    public function test_a_viewer_sees_the_listing_but_no_install_or_publish_controls(): void
    {
        $certified = $this->certified();

        $this->actingAs($this->users['viewer'])->get(route('extensions.show', $certified))
            ->assertOk()
            ->assertSee('Matrix Listing')
            ->assertDontSee(route('extensions.install', $certified), false)
            ->assertDontSee('>Install<', false);

        $this->actingAs($this->users['viewer'])->get(route('extensions.index'))
            ->assertOk()
            ->assertDontSee(route('extensions.create'), false);

        $this->actingAs($this->users['viewer'])->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('extensions.create'), false);

        // The same pages do show the controls to an admin, so the absence above means something.
        $this->actingAs($this->users['admin'])->get(route('extensions.show', $certified))
            ->assertSee(route('extensions.install', $certified), false);
        $this->actingAs($this->users['admin'])->get(route('extensions.index'))
            ->assertSee(route('extensions.create'), false);
    }

    public function test_a_viewer_sees_the_uninstall_control_for_nobody_else_only_managers_do(): void
    {
        $certified = $this->certified();
        Installation::create([
            'team_id' => $this->team->id, 'extension_id' => $certified->id,
            'extension_version_id' => $certified->versions()->first()->id, 'status' => 'active', 'installed_at' => now(),
        ]);

        $this->actingAs($this->users['viewer'])->get(route('extensions.show', $certified))
            ->assertOk()->assertDontSee(route('extensions.uninstall', $certified), false);
        $this->actingAs($this->users['editor'])->get(route('extensions.show', $certified))
            ->assertOk()->assertDontSee(route('extensions.uninstall', $certified), false);
        $this->actingAs($this->users['owner'])->get(route('extensions.show', $certified))
            ->assertOk()->assertSee(route('extensions.uninstall', $certified), false);
    }
}
