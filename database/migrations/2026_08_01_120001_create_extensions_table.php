<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extensions listed in the Dot.Plug marketplace. Each extension is owned by
 * a publisher team (see wiki.md §4 — "Publisher" maps to the ecosystem's
 * standard Jetstream Team, the same team-as-tenant pattern used across
 * every Dot platform). MVP: no capability-grant engine, no certification
 * pipeline, no sandbox/runtime layer yet — `status` is a simple lifecycle
 * flag standing in for the full certification workflow described in the
 * wiki's roadmap.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('developer_team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('category')->default('general');
            $table->string('status')->default('draft'); // draft | certified | decertified
            $table->string('icon')->nullable(); // material-symbols icon name for MVP listing UI
            $table->timestamps();

            $table->index(['status', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extensions');
    }
};
