<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertViewIs('dashboard')
            ->assertSee('Dashboard')
            ->assertSee('Installed Extensions')
            ->assertSee('Published Extensions');
    }

    /**
     * Regression test: a user can reach protected routes with no active
     * team (e.g. after leaving/being removed from their last remaining
     * team, which nulls out current_team_id without signing them out).
     * There is no team-context middleware in this app to intercept that
     * case upstream, so the dashboard route itself must guard against a
     * null currentTeam instead of dereferencing ->id on it.
     */
    public function test_authenticated_user_with_no_team_is_redirected_to_team_creation(): void
    {
        $user = User::factory()->create(['current_team_id' => null]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('teams.create'));
    }
}
