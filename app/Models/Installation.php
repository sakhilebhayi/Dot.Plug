<?php

namespace App\Models;

use App\Models\Concerns\HasTeamScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * `installations` rows are exactly the "team's own private data" case
 * HasTeamScope's own docstring calls out (installing team, distinct from
 * the extension's publisher team) — scoped to the current team on every
 * read so a forgotten where('team_id', ...) in a future controller can't
 * leak another team's installation rows.
 */
class Installation extends Model
{
    use HasFactory;
    use HasTeamScope;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'team_id',
        'extension_id',
        'extension_version_id',
        'status',
        'installed_at',
        'uninstalled_at',
    ];

    protected function casts(): array
    {
        return [
            'installed_at' => 'datetime',
            'uninstalled_at' => 'datetime',
        ];
    }

    /**
     * The installing team (distinct from the extension's publisher team).
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function extension(): BelongsTo
    {
        return $this->belongsTo(Extension::class);
    }

    public function extensionVersion(): BelongsTo
    {
        return $this->belongsTo(ExtensionVersion::class);
    }
}
