<?php

namespace Tests\Feature;

use App\Livewire\NotificationBell;
use App\Models\Extension;
use App\Models\ExtensionVersion;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\AssertsResponsiveShell;
use Tests\TestCase;

class ResponsiveShellTest extends TestCase
{
    use AssertsResponsiveShell, RefreshDatabase;

    private function certifiedExtension(string $name): Extension
    {
        $extension = Extension::factory()->create(['name' => $name, 'status' => 'certified']);
        ExtensionVersion::factory()->create(['extension_id' => $extension->id, 'version' => '2.4.1']);

        return $extension;
    }

    public function test_every_signed_in_page_carries_the_drawer_hooks(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $extension = $this->certifiedExtension('Drawer Hooks Ledger Connector');

        foreach ([
            route('dashboard'),
            route('extensions.index'),
            route('extensions.show', $extension),
            route('extensions.create'),
            route('profile.show'),
            route('teams.show', $user->currentTeam),
        ] as $url) {
            $this->assertResponsiveShell($this->actingAs($user)->get($url));
        }
    }

    public function test_the_legacy_jetstream_bar_is_wrapped_so_phones_can_hide_it(): void
    {
        $xpath = $this->assertResponsiveShell($this->actingAs(User::factory()->withPersonalTeam()->create())->get(route('dashboard')));

        $this->assertSame(1, $xpath->query('//div[contains(@class, "shell-legacy-nav")]/nav')->length);
    }

    public function test_the_drawer_reaches_the_dashboard_the_marketplace_and_settings(): void
    {
        $xpath = $this->assertResponsiveShell($this->actingAs(User::factory()->withPersonalTeam()->create())->get(route('extensions.index')));

        foreach (['dashboard', 'extensions.index', 'profile.show'] as $name) {
            $this->assertSame(1, $xpath->query('//aside[@id="app-sidebar"]//a[@href="'.route($name).'"]')->length, $name);
        }
    }

    public function test_the_drawer_footer_has_sign_out_and_a_team_switcher(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $second = Team::factory()->create(['user_id' => $user->id, 'personal_team' => false, 'name' => 'Ledger Integrations Studio']);

        $xpath = $this->assertResponsiveShell($this->actingAs($user)->get(route('dashboard')));

        $footer = '//aside[@id="app-sidebar"]//div[contains(@class, "sidebar-footer")]';
        $this->assertSame(1, $xpath->query($footer.'//form[@action="'.route('logout').'"]//button[@aria-label="Sign out"]')->length);
        $this->assertSame(1, $xpath->query($footer.'//details[contains(@class, "team-switch")]/summary')->length);
        $this->assertSame(1, $xpath->query($footer.'//a[@href="'.route('teams.show', $user->currentTeam).'"]')->length);
        $this->assertSame(2, $xpath->query($footer.'//form[@action="'.route('current-team.update').'"]//input[@name="team_id"]')->length);
        $this->assertSame(1, $xpath->query($footer.'//input[@name="team_id"][@value="'.$second->id.'"]')->length);
    }

    public function test_the_notification_panel_is_a_class_the_phone_layout_can_pin_inside_the_viewport(): void
    {
        Livewire::actingAs(User::factory()->withPersonalTeam()->create())->test(NotificationBell::class)
            ->assertDontSeeHtml('notif-panel')
            ->assertSeeHtml('aria-label="Notifications"')
            ->set('open', true)
            ->assertSeeHtml('class="notif-panel"');
    }
}
