<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Installation extends Model
{
    use HasFactory;

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
