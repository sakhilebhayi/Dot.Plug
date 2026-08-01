<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One installing team's install of one extension (wiki.md §4 —
 * "Installation: extension x platform x org"). `team_id` here is the
 * *installing* org, distinct from `extensions.developer_team_id` (the
 * publisher) — the same team can be both a publisher and an installer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('extension_id')->constrained()->cascadeOnDelete();
            $table->foreignId('extension_version_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('active'); // active | uninstalled
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('uninstalled_at')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'extension_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installations');
    }
};
