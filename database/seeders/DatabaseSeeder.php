<?php

namespace Database\Seeders;

use App\Models\Extension;
use App\Models\ExtensionVersion;
use App\Models\Installation;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with a test user plus a handful of
     * example extensions across a few publisher teams, so the marketplace
     * and dashboard have something real to render. MVP-scoped: no
     * certification pipeline yet, so all seeded extensions are simply
     * marked "certified" directly.
     */
    public function run(): void
    {
        $user = User::factory()->withPersonalTeam()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        /** @var Team $installingTeam */
        $installingTeam = $user->currentTeam;

        $publisherOne = Team::factory()->create(['name' => 'Northwind Integrations', 'user_id' => $user->id, 'personal_team' => false]);
        $publisherTwo = Team::factory()->create(['name' => 'Bluepeak Labs', 'user_id' => $user->id, 'personal_team' => false]);

        // Attach the test user as a member of both publisher teams too, so
        // "published by this team" has something to show when switching
        // current team in the UI.
        $user->teams()->attach([$publisherOne->id, $publisherTwo->id], ['role' => 'admin']);

        $extensions = [
            [
                'team' => $publisherOne,
                'name' => 'Slack Notify',
                'tagline' => 'Push ecosystem events to a Slack channel in real time.',
                'description' => "Slack Notify subscribes to a host platform's event stream and relays a configurable subset to a Slack channel via incoming webhook. Useful for ops teams who want a live feed of billing, HR, or support events without building a custom integration.",
                'category' => 'integrations',
                'icon' => 'chat',
            ],
            [
                'team' => $publisherOne,
                'name' => 'CSV Exporter',
                'tagline' => 'Scheduled exports of any dataset to CSV, delivered to S3 or email.',
                'description' => 'CSV Exporter runs on a configurable schedule, pulls a scoped dataset from the host platform, and delivers a CSV to either an S3 bucket or a recipient list. Common use: nightly finance exports.',
                'category' => 'automation',
                'icon' => 'file_export',
            ],
            [
                'team' => $publisherTwo,
                'name' => 'Cohort Analytics',
                'tagline' => 'Retention and cohort dashboards layered on top of your platform data.',
                'description' => "Cohort Analytics reads aggregate usage data (within its granted scope) and renders retention curves and cohort breakdowns as an embedded dashboard panel. Never touches per-customer records outside the aggregate it's granted.",
                'category' => 'analytics',
                'icon' => 'monitoring',
            ],
            [
                'team' => $publisherTwo,
                'name' => 'Agri Telemetry Connector',
                'tagline' => 'Ingests field-equipment telemetry into the host platform.',
                'description' => 'Connects to common agricultural equipment telemetry feeds (irrigation controllers, soil sensors) and normalizes readings into the host platform\'s data model. Matches the marketplace-gap noted in Dot.Brain\'s platform doc for agri-equipment connectors.',
                'category' => 'vertical',
                'icon' => 'agriculture',
            ],
        ];

        foreach ($extensions as $data) {
            $extension = Extension::create([
                'developer_team_id' => $data['team']->id,
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'tagline' => $data['tagline'],
                'description' => $data['description'],
                'category' => $data['category'],
                'status' => 'certified',
                'icon' => $data['icon'],
            ]);

            $version = ExtensionVersion::create([
                'extension_id' => $extension->id,
                'version' => '1.0.0',
                'changelog' => 'Initial certified release.',
                'is_current' => true,
            ]);

            if ($data['name'] === 'Slack Notify') {
                Installation::create([
                    'team_id' => $installingTeam->id,
                    'extension_id' => $extension->id,
                    'extension_version_id' => $version->id,
                    'status' => 'active',
                    'installed_at' => now(),
                ]);
            }
        }
    }
}
