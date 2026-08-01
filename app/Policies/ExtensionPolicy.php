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

    public function view(User $user, Extension $extension): bool
    {
        return true;
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
