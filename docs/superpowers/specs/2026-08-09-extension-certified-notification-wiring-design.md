# Extension Certified Notification Wiring — Design Spec

**Status:** Approved by user, ready for implementation planning.
**Platform:** Dot.Plug
**Date:** 2026-08-09

## Context

`Dot.Brain/platforms/dot-plug.md`'s Autonomy Classification audit (§Level 1, §Level 2,
2026-08-08) confirmed zero automation in this codebase — no `app/Console/Commands`, no
`Schedule::` entries, no `app/Jobs`, no event listeners. It names two candidates in passing:

1. A "most natural starting candidate" — routine marketplace housekeeping (e.g. flagging stale
   draft listings) that deliberately never touches certification state, since "auto-approving or
   auto-publishing untrusted third-party code can never be Level 1" — this platform's `Extension`
   model is genuinely installable third-party code, a real trust boundary.
2. `App\Notifications\ExtensionCertifiedNotification` — fully built (in-app database-channel
   notification) but its own docblock states it is "not yet wired to any automatic trigger... there
   is no certification pipeline yet... dispatch manually."

Presented to the user as an explicit choice between the two. **The user chose to wire the
certification notification** — the smaller, already-grounded fix, matching the precedent set by
Dot.Farms (`HarvestRecordedNotification`) and Dot.HR (`LeaveRequestSubmittedNotification`) earlier
this program.

Verified directly against the real code before proceeding:

- `grep -rn "certified" app/` confirms no code path anywhere ever assigns `status = 'certified'` —
  every certification today is a manual, out-of-band database edit by an operator, per wiki.md §3
  and the audit's own Level 3 findings. This spec does not change that — certification remains a
  manual admin action; only the *notification about* a certification becomes automatic.
- `Extension.php` (this codebase) has no `booted()` method at all today — this is a genuinely new
  addition, not an existing pattern within Dot.Plug itself. The shape it follows is proven
  elsewhere in the ecosystem: `App\Models\NotifyChannel::booted()`, in the *sibling* Dot.Notify
  platform's own separate codebase, registers a `static::updated()` listener that fires a
  notification only when a specific field transitions into a specific value, never on initial
  `create()` — the same shape this spec applies to `Extension` here.
- `tests/Feature/NotificationBellTest.php`'s two existing tests both create the `Extension` with
  `status: 'certified'` already set at creation time (never via a subsequent update) — since the
  new listener only fires on `updated()`, not `created()`, these tests are unaffected. Unlike the
  Farms/HR precedent, no pre-existing test needs a compensating fix here.

## Goal

Fire `ExtensionCertifiedNotification` automatically, for every member of the extension's developer
team, whenever an `Extension`'s `status` transitions into `certified` — regardless of how that
transition happens (a future certification pipeline, a manual `tinker`/database edit, a seeder),
not just from one hardcoded call site.

## Design

### `Extension::booted()`

New `static::updated()` listener on `App\Models\Extension`, mirroring `NotifyChannel::booted()`'s
existing shape in this ecosystem exactly:

```php
protected static function booted(): void
{
    static::updated(function (self $extension) {
        if ($extension->wasChanged('status') && $extension->status === 'certified' && $extension->developerTeam) {
            Notification::send($extension->developerTeam->allUsers(), new ExtensionCertifiedNotification($extension));
        }
    });
}
```

- **Fires on update only, not on create.** An `Extension::create(['status' => 'certified', ...])`
  (as both of `NotificationBellTest`'s existing tests already do) does not fire this — only a
  subsequent transition *into* `certified` via `update()`/`save()` does. This matches
  `wiki.md`'s own framing: certification is something that happens to an existing draft listing,
  not a state a listing is born into.
- **Recipients — every developer-team member**, via `Team::allUsers()` (Jetstream's built-in
  owner+members accessor, the same shape already used for Farms' harvest notification). This is
  good news for the whole publisher team, not an approval request to a subset of it — matching
  `ExtensionCertifiedNotification::toDatabase()`'s own copy ("has been certified and is now listed
  in the marketplace"), addressed to the team as a whole.
- **Guards against a missing team.** `$extension->developerTeam` is checked before sending —
  matching this model's existing defensive style (`developer_team_id` is a required foreign key
  with `cascadeOnDelete()`, so in practice this should always resolve, but the check costs nothing
  and avoids a null-pointer path if a team is ever deleted out from under an extension in a race).

### Docblock cleanup

`app/Notifications/ExtensionCertifiedNotification.php`'s class docblock currently reads:

> Not yet wired to any automatic trigger — there is no certification pipeline yet... dispatch
> manually via `$user->notify(new ExtensionCertifiedNotification($extension))` until one exists.

This becomes stale once the listener ships. Replace it with a note pointing at
`Extension::booted()` as the real trigger, and clarify that certification *itself* is still a
manual admin action (wiki.md §3, §7 roadmap) — only the notification about it is now automatic.

## Out of Scope

- Building any certification pipeline, workflow, or approval UI — `status` stays a hand-set flag;
  this spec only makes the *notification* automatic once that flag is set, by whatever means.
- The stale-draft-listing housekeeping job the audit also named — a separate, larger candidate
  explicitly not chosen this pass (see Context).
- Notifying on transitions into `decertified` — the audit and the notification's own scope are
  both specific to certification; a decertification notification is a different, unbuilt gap not
  named by this spec.
- Mail or broadcast channels — `ExtensionCertifiedNotification::via()` already returns only
  `['database']`; this spec does not change that.

## Testing Notes

- Updating an `Extension`'s `status` from `draft` to `certified` notifies every member of the
  developer team, not just one.
- Creating an `Extension` with `status: 'certified'` set at creation time does **not** fire the
  notification — matches `NotificationBellTest`'s existing setup exactly, confirming no regression
  there.
- Updating an `Extension` in a way that leaves `status` at `certified` (e.g. changing only
  `tagline`) does not re-fire the notification a second time — `wasChanged('status')` guards this.
- Transitioning `status` to any value other than `certified` (e.g. `draft` → `decertified`) does
  not fire this notification.
- A member of a *different* team (not the developer team) is not notified.
