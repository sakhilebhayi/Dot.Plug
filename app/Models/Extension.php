<?php

namespace App\Models;

use App\Notifications\ExtensionCertifiedNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Notification;

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

    protected static function booted(): void
    {
        static::updated(function (self $extension) {
            if ($extension->wasChanged('status') && $extension->status === 'certified' && $extension->developerTeam) {
                Notification::send($extension->developerTeam->allUsers(), new ExtensionCertifiedNotification($extension));
            }
        });
    }

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
