<?php

namespace App\Policies;

use App\Models\Extension;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Governs who can manage an extension *listing* (the publisher side).
 * Installing an extension is a separate concern, authorized inline in
 * ExtensionController against the current team rather than through this
 * policy, since any authenticated team may install a certified extension.
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

    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only members of the publisher (developer) team may update the listing.
     */
    public function update(User $user, Extension $extension): bool
    {
        return $user->belongsToTeam($extension->developerTeam);
    }

    public function delete(User $user, Extension $extension): bool
    {
        return $user->belongsToTeam($extension->developerTeam);
    }
}
