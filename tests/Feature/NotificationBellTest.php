<?php

namespace Tests\Feature;

use App\Livewire\NotificationBell;
use App\Models\Extension;
use App\Models\Team;
use App\Models\User;
use App\Notifications\ExtensionCertifiedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationBellTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_notification_bell_for_authenticated_user(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSeeLivewire('notification-bell');
    }

    public function test_unread_count_reflects_database_notifications(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $publisher = Team::factory()->create();

        $extension = Extension::create([
            'developer_team_id' => $publisher->id,
            'name' => 'Notify Me Extension',
            'slug' => 'notify-me-extension',
            'category' => 'general',
            'status' => 'certified',
        ]);

        $user->notify(new ExtensionCertifiedNotification($extension));

        $this->assertDatabaseCount('notifications', 1);

        Livewire::actingAs($user)
            ->test(NotificationBell::class)
            ->assertSet('open', false)
            ->call('toggle')
            ->assertSet('open', true)
            ->assertSee('Extension certified');

        $this->assertEquals(1, $user->fresh()->unreadNotifications()->count());
    }

    public function test_mark_all_as_read_clears_unread_count(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $publisher = Team::factory()->create();

        $extension = Extension::create([
            'developer_team_id' => $publisher->id,
            'name' => 'Another Extension',
            'slug' => 'another-extension',
            'category' => 'general',
            'status' => 'certified',
        ]);

        $user->notify(new ExtensionCertifiedNotification($extension));

        Livewire::actingAs($user)
            ->test(NotificationBell::class)
            ->call('markAllAsRead');

        $this->assertEquals(0, $user->fresh()->unreadNotifications()->count());
    }
}
