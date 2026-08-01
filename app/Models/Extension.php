<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Extension extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'developer_team_id',
        'name',
        'slug',
        'tagline',
        'description',
        'category',
        'status',
        'icon',
    ];

    /**
     * The publisher (developer) team that owns this listing.
     */
    public function developerTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'developer_team_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ExtensionVersion::class);
    }

    public function currentVersion(): HasMany
    {
        return $this->hasMany(ExtensionVersion::class)->where('is_current', true);
    }

    public function installations(): HasMany
    {
        return $this->hasMany(Installation::class);
    }

    public function isInstalledBy(Team $team): bool
    {
        return $this->installations()
            ->where('team_id', $team->id)
            ->where('status', 'active')
            ->exists();
    }
}
