# Extension Certified Notification Wiring Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fire `App\Notifications\ExtensionCertifiedNotification` automatically, for every member
of an extension's developer team, whenever the extension's `status` transitions into `certified`.

**Architecture:** A `static::updated()` listener added to `App\Models\Extension::booted()` —
following the exact shape already proven in the sibling Dot.Notify platform's own codebase
(`NotifyChannel::booted()`'s `ChannelDegradedNotification`): fire only on update, only on the
transition into the target value, never on initial `create()`.

**Tech Stack:** Laravel (per repo's existing conventions), PHPUnit, Jetstream Teams.

## Global Constraints

- The listener fires only when `$extension->wasChanged('status') && $extension->status ===
  'certified'` — never on `Extension::create()`, even one created with `status: 'certified'`
  already set.
- Recipients are every member of the developer team (`Team::allUsers()`) — not a subset, not an
  admin-only gate (this is good news for the whole publisher team, not an approval request).
- Certification itself remains a manual, hand-set `status` flag — this plan does not add any way
  to certify an extension; it only makes the *notification* automatic once certification happens
  by whatever means (today: a manual database edit).
- `ExtensionCertifiedNotification` itself is not modified — only its class docblock.
- No mail/broadcast channels — `via()` stays `['database']` only.

---

## Task 1: `Extension::booted()` listener + docblock cleanup

**Files:**
- Modify: `app/Models/Extension.php`
- Modify: `app/Notifications/ExtensionCertifiedNotification.php` (docblock only)
- Test: `tests/Feature/ExtensionCertificationNotificationTest.php`

**Interfaces:**
- Consumes: `App\Notifications\ExtensionCertifiedNotification::__construct(Extension $extension)`
  (existing, unmodified), `Team::allUsers()` (Jetstream-provided).
- Produces: no new public interface — `Extension`'s existing `update()`/`save()` calls now have
  this side effect when the transition condition is met.

- [ ] **Step 1: Write the failing tests**

```php
<?php

namespace Tests\Feature;

use App\Models\Extension;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExtensionCertificationNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_transitioning_status_to_certified_notifies_every_developer_team_member(): void
    {
        $owner = User::factory()->withPersonalTeam()->create();
        $publisherTeam = $owner->currentTeam;

        $secondMember = User::factory()->create();
        $publisherTeam->users()->attach($secondMember, ['role' => 'admin']);

        $extension = Extension::create([
            'developer_team_id' => $publisherTeam->id,
            'name' => 'Test Extension',
            'slug' => 'test-extension',
            'category' => 'general',
            'status' => 'draft',
        ]);

        $extension->update(['status' => 'certified']);

        $this->assertSame(1, $owner->fresh()->unreadNotifications()->count());
        $this->assertSame(1, $secondMember->fresh()->unreadNotifications()->count());
    }

    public function test_creating_an_extension_as_already_certified_does_not_fire_the_notification(): void
    {
        $publisherTeam = Team::factory()->create();
        $owner = $publisherTeam->owner;

        Extension::create([
            'developer_team_id' => $publisherTeam->id,
            'name' => 'Pre-Certified Extension',
            'slug' => 'pre-certified-extension',
            'category' => 'general',
            'status' => 'certified',
        ]);

        $this->assertSame(0, $owner->fresh()->unreadNotifications()->count());
    }

    public function test_updating_an_already_certified_extension_without_changing_status_does_not_refire(): void
    {
        $publisherTeam = Team::factory()->create();
        $owner = $publisherTeam->owner;

        $extension = Extension::create([
            'developer_team_id' => $publisherTeam->id,
            'name' => 'Steady Extension',
            'slug' => 'steady-extension',
            'category' => 'general',
            'status' => 'draft',
        ]);

        $extension->update(['status' => 'certified']);
        $this->assertSame(1, $owner->fresh()->unreadNotifications()->count());

        $extension->update(['tagline' => 'Now with a tagline.']);
        $this->assertSame(1, $owner->fresh()->unreadNotifications()->count());
    }

    public function test_transitioning_status_to_decertified_does_not_fire_this_notification(): void
    {
        $publisherTeam = Team::factory()->create();
        $owner = $publisherTeam->owner;

        $extension = Extension::create([
            'developer_team_id' => $publisherTeam->id,
            'name' => 'Draft Extension',
            'slug' => 'draft-extension',
            'category' => 'general',
            'status' => 'draft',
        ]);

        $extension->update(['status' => 'decertified']);

        $this->assertSame(0, $owner->fresh()->unreadNotifications()->count());
    }

    public function test_a_member_of_a_different_team_is_not_notified(): void
    {
        $publisherTeam = Team::factory()->create();
        $outsider = User::factory()->withPersonalTeam()->create();

        $extension = Extension::create([
            'developer_team_id' => $publisherTeam->id,
            'name' => 'Another Extension',
            'slug' => 'another-extension',
            'category' => 'general',
            'status' => 'draft',
        ]);

        $extension->update(['status' => 'certified']);

        $this->assertSame(0, $outsider->fresh()->unreadNotifications()->count());
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --compact tests/Feature/ExtensionCertificationNotificationTest.php`
Expected: FAIL — `unreadNotifications()->count()` is 0 in the first test (nothing fires yet), so
`assertSame(1, ...)` fails.

- [ ] **Step 3: Add the `booted()` listener to `Extension`**

Read `app/Models/Extension.php` first, then add these imports at the top:

```php
use App\Notifications\ExtensionCertifiedNotification;
use Illuminate\Support\Facades\Notification;
```

Add this method to the class, before `developerTeam()`:

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

- [ ] **Step 4: Update the notification's docblock**

In `app/Notifications/ExtensionCertifiedNotification.php`, replace the class docblock:

```php
/**
 * In-app (database channel only) notification for a marketplace listing's
 * certification status changing. Fired automatically for every member of
 * the extension's developer team by App\Models\Extension::booted(), which
 * fires only when `status` transitions into "certified" via an update —
 * never on initial creation. Certification itself remains a manual,
 * hand-set flag (wiki.md §3, §7 roadmap) — only this notification is
 * automatic; nothing here changes how or when an extension gets certified.
 */
```

- [ ] **Step 5: Run tests to verify they pass**

Run: `php artisan test --compact tests/Feature/ExtensionCertificationNotificationTest.php`
Expected: 5 passed.

- [ ] **Step 6: Re-run the existing `NotificationBellTest` to confirm no regression**

Run: `php artisan test --compact tests/Feature/NotificationBellTest.php`
Expected: all 3 still passing — both of its tests create the `Extension` with `status: 'certified'`
already set, so the new `updated()`-only listener does not fire during their setup, matching the
spec's explicit prediction.

- [ ] **Step 7: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Models/Extension.php app/Notifications/ExtensionCertifiedNotification.php tests/Feature/ExtensionCertificationNotificationTest.php
git commit -m "feat(plug): auto-fire ExtensionCertifiedNotification on status transition to certified"
```

---

## Task 2: Full regression

**Files:** none (verification only).

- [ ] **Step 1: Run the full test suite**

```bash
php artisan test --compact
```

Expected: all tests pass, including every pre-existing suite (`NotificationBellTest`, and any
`ExtensionTest`/`ExtensionPolicyTest`-style suites already in the repo) alongside the new suite
from this plan.

- [ ] **Step 2: Run Pint across the whole diff one final time**

```bash
vendor/bin/pint --dirty --format agent
```

If it reformats anything, `git add -A` and amend or add a small formatting commit.

- [ ] **Step 3: Report**

Summarize the final test count and confirm this plan's scope (Task 1) is fully implemented against
`docs/superpowers/specs/2026-08-09-extension-certified-notification-wiring-design.md`. No commits
in this task beyond an optional Pint formatting fix — this is verification-only.
