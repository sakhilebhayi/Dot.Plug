<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Version records for an extension. MVP scope deliberately excludes
 * versioning diffs / release-artifact storage (see wiki.md roadmap) — this
 * table exists so an Installation can reference "which version" without
 * pretending the full release pipeline exists yet.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extension_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extension_id')->constrained()->cascadeOnDelete();
            $table->string('version'); // e.g. "1.0.0"
            $table->text('changelog')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->unique(['extension_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extension_versions');
    }
};
