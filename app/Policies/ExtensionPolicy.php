<?php

namespace App\Policies;

use App\Models\Extension;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Governs who can manage an extension *listing* (the publisher side) and who
 * can change the *current team's* installs.
 *
 * Installs are company-wide for the team, so install/uninstall and publishing
 * are limited to the team owner or a team admin (Jetstream `admin` role;
 * `hasTeamRole` is also true for the owner). Plain members (editor, etc.) can
 * browse but not change what the whole team runs.
 *
 * Certification is deliberately NOT a policy method here: no team, however
 * privileged, can mark a listing `certified`. That is reserved for a platform
 * operator, and no certification pipeline exists in this app yet.
 */
class ExtensionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Certified listings are public marketplace content. Anything not yet
     * certified (draft, or decertified) is the publisher's own unreleased
     * or pulled work product — only members of the publisher (developer)
     * team may view its detail page. Without this check, any authenticated
     * user could read another team's unpublished extension name, tagline,
     * description, and version changelog simply by guessing/incrementing
     * the extension ID in the URL, even though the marketplace index
     * already correctly hides non-certified listings from browsing.
     */
    public function view(User $user, Extension $extension): bool
    {
        if ($extension->status === 'certified') {
            return true;
        }

        return $user->belongsToTeam($extension->developerTeam);
    }

    /**
     * Publishing creates a listing under the user's current team, so it needs
     * the same owner/admin standing as managing that team's installs.
     */
    public function create(User $user): bool
    {
        return $this->isTeamManager($user, $user->currentTeam);
    }

    /**
     * Install a (certified) extension for the user's current team.
     */
    public function install(User $user, Extension $extension): bool
    {
        return $this->isTeamManager($user, $user->currentTeam);
    }

    /**
     * Uninstall an extension from the user's current team.
     */
    public function uninstall(User $user, Extension $extension): bool
    {
        return $this->isTeamManager($user, $user->currentTeam);
    }

    /**
     * Only owners/admins of the publisher (developer) team may update the listing.
     */
    public function update(User $user, Extension $extension): bool
    {
        return $this->isTeamManager($user, $extension->developerTeam);
    }

    public function delete(User $user, Extension $extension): bool
    {
        return $this->isTeamManager($user, $extension->developerTeam);
    }

    private function isTeamManager(User $user, ?Team $team): bool
    {
        return $team !== null
            && $user->belongsToTeam($team)
            && ($user->ownsTeam($team) || $user->hasTeamRole($team, 'admin'));
    }
}
