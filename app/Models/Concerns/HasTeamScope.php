<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Dot.Plug is Jetstream Teams-based, so tenancy runs through team_id, not
 * user_id (see Dot.Finance's HasUserScope for the single-user analog of
 * this same idea). Only genuinely team-private data should use this trait:
 * rows that belong to exactly one team and are never meant to be read by
 * another team, e.g. an installing team's own `installations` rows.
 *
 * It is deliberately NOT applied to `Extension`: an extension listing is
 * marketplace content -- once certified it is meant to be visible to every
 * team, not just its publisher (`developer_team_id`) team. Scoping
 * Extension to the current team would break the marketplace itself, so
 * that model keeps its existing Gate::authorize()-based visibility rules
 * (see ExtensionPolicy) instead of a blanket global scope. ExtensionVersion
 * is likewise left alone: it has no team_id column of its own and its
 * visibility already follows its parent Extension.
 *
 * Any model that does use this trait is automatically scoped to the
 * current user's active team on every query, the same way Dot.Mines'
 * HasTeamFilters scopes every tenant-owned model -- the goal is that a
 * forgotten where('team_id', ...) call in a future controller can no
 * longer leak another team's rows, because the model itself never returns
 * unscoped results while a user is authenticated with an active team.
 *
 * Mass-assignment still sets team_id explicitly at create time (see
 * ExtensionController's install()); this scope only governs reads.
 */
trait HasTeamScope
{
    protected static function bootHasTeamScope(): void
    {
        static::addGlobalScope('team', function (Builder $builder): void {
            if (Auth::check() && Auth::user()->currentTeam) {
                $builder->where($builder->getModel()->getTable().'.team_id', Auth::user()->currentTeam->id);
            }
        });
    }
}
