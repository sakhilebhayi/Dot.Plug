<?php

namespace Tests\Feature;

use App\Models\Extension;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExtensionCertificationNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_transitioning_status_to_certified_notifies_every_developer_team_member(): void
    {
        $owner = User::factory()->withPersonalTeam()->create();
        $publisherTeam = $owner->currentTeam;

        $secondMember = User::factory()->create();
        $publisherTeam->users()->attach($secondMember, ['role' => 'admin']);

        $extension = Extension::create([
            'developer_team_id' => $publisherTeam->id,
            'name' => 'Test Extension',
            'slug' => 'test-extension',
            'category' => 'general',
            'status' => 'draft',
        ]);

        $extension->update(['status' => 'certified']);

        $this->assertSame(1, $owner->fresh()->unreadNotifications()->count());
        $this->assertSame(1, $secondMember->fresh()->unreadNotifications()->count());
    }

    public function test_creating_an_extension_as_already_certified_does_not_fire_the_notification(): void
    {
        $publisherTeam = Team::factory()->create();
        $owner = $publisherTeam->owner;

        Extension::create([
            'developer_team_id' => $publisherTeam->id,
            'name' => 'Pre-Certified Extension',
            'slug' => 'pre-certified-extension',
            'category' => 'general',
            'status' => 'certified',
        ]);

        $this->assertSame(0, $owner->fresh()->unreadNotifications()->count());
    }

    public function test_updating_an_already_certified_extension_without_changing_status_does_not_refire(): void
    {
        $publisherTeam = Team::factory()->create();
        $owner = $publisherTeam->owner;

        $extension = Extension::create([
            'developer_team_id' => $publisherTeam->id,
            'name' => 'Steady Extension',
            'slug' => 'steady-extension',
            'category' => 'general',
            'status' => 'draft',
        ]);

        $extension->update(['status' => 'certified']);
        $this->assertSame(1, $owner->fresh()->unreadNotifications()->count());

        $extension->update(['tagline' => 'Now with a tagline.']);
        $this->assertSame(1, $owner->fresh()->unreadNotifications()->count());
    }

    public function test_transitioning_status_to_decertified_does_not_fire_this_notification(): void
    {
        $publisherTeam = Team::factory()->create();
        $owner = $publisherTeam->owner;

        $extension = Extension::create([
            'developer_team_id' => $publisherTeam->id,
            'name' => 'Draft Extension',
            'slug' => 'draft-extension',
            'category' => 'general',
            'status' => 'draft',
        ]);

        $extension->update(['status' => 'decertified']);

        $this->assertSame(0, $owner->fresh()->unreadNotifications()->count());
    }

    public function test_a_member_of_a_different_team_is_not_notified(): void
    {
        $publisherTeam = Team::factory()->create();
        $outsider = User::factory()->withPersonalTeam()->create();

        $extension = Extension::create([
            'developer_team_id' => $publisherTeam->id,
            'name' => 'Another Extension',
            'slug' => 'another-extension',
            'category' => 'general',
            'status' => 'draft',
        ]);

        $extension->update(['status' => 'certified']);

        $this->assertSame(0, $outsider->fresh()->unreadNotifications()->count());
    }
}
