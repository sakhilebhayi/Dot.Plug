---
title: Dot.Plug — Platform Wiki
version: 0.4.3
status: mvp-scaffold-unverified
owners: [Plug Platform Lead]
platform-id: dot-plug
last-review: 2026-08-06
---

# Dot.Plug

Purpose: this is Dot.Plug's own knowledge home — owned and maintained by the Dot.Plug team. It describes what this platform is, what it owns, and how it connects to the wider Dot Ecosystem. Dot.Brain never edits this file; it only reads what we choose to publish.

> **Related:** [Dot.Brain's ingested view of this platform](https://github.com/sakhilebhayi/Dot.Brain/blob/main/platforms/dot-plug.md)

---

## 1. What Dot.Plug Is

Dot.Plug is the developer portal and extension framework for the Dot Ecosystem: a marketplace and runtime where third-party developers build, certify, and publish extensions — integrations, connectors, domain add-ons, and vertical tools — that add capability to any Dot platform without requiring changes to that platform's core codebase.

Dot.Plug's job is narrow and specific: own the extension lifecycle (build, certify, grant, run, retire), not the extensions themselves. What an extension does inside its own granted scope is the publisher's business, not ours.

**Status: MVP scaffold, hand-authored and UNVERIFIED.** A real Laravel 12 + Jetstream Teams application now exists in this repository — models, migrations, a marketplace listing/install flow, a seeder, and Feature tests. It was written in an environment with no PHP, Composer, or PostgreSQL available, so **none of it has been executed**: no `composer install`, no `php artisan migrate`, no test run. Treat the code as a careful first draft that has not been proven to boot. Sections 3–5 below now describe what exists in code, marked where it's still just roadmap.

## 2. Design Principle: Govern the Touch, Not the Extension

The line we hold: **the ecosystem governs how an extension is allowed to touch a platform; it does not govern what an extension is.** A developer is free to build anything against a capability grant. The moment an extension wants to publish knowledge into, or consume intelligence from, the ecosystem's shared intelligence layer, it crosses into a governed boundary where the same rules apply to every publisher — no shortcuts for being third-party, and no extra tax either.

This keeps the marketplace an open surface for innovation while keeping the parts of the ecosystem that touch trust, data, and cross-platform intelligence tightly controlled.

## 3. Architecture — Built vs. Planned

**Built (MVP scaffold, this repository, unverified):**

- Jetstream Teams shell: auth (Fortify), team management, profile, API tokens, the ecosystem SSO handoff route (`/auth/ecosystem`), the in-app notification bell — copied from Dot.Billing's already-reviewed Jetstream boilerplate and re-branded, per the ecosystem's shared-shell convention.
- **Marketplace/discovery surface (MVP slice only):** plain controller + Blade CRUD for listing, publishing, viewing, installing, and uninstalling extensions. No search, no moderation queue, no payments.
- Domain models: `Extension`, `ExtensionVersion`, `Installation` (see §4). `Publisher` is *not* a separate table — a publisher is simply a Jetstream `Team` that owns `Extension` rows, consistent with "team is the tenant root" across every Dot platform.
- A `status` column on `Extension` (`draft` / `certified` / `decertified`) stands in for the certification pipeline below — it's a flag an admin would flip by hand today, not a workflow.

**Not built — still planned, per the original architecture blueprint:**

- **Registry service** beyond the basic listings table — no publisher verification, no search/discovery ranking.
- **Capability grant engine** — issuing, scoping, and revoking what an installed extension may touch on a given host platform; re-validation on host-schema change. Nothing in this MVP models a capability grant at all; `Installation` currently just records that a team installed a version, not what it's allowed to touch.
- **Runtime/sandbox layer** — no extension code actually executes; this is a listings-and-installs database, not a runtime yet.
- **Certification pipeline** — `status` is hand-set; there's no review workflow, no automated checks.
- **Anomaly detection.**
- Knowledge Pack publishing to Dot.Brain (§6) — no `observation`/`insight`/`outcome`/`incident` payloads are emitted anywhere in this codebase yet.

## 4. Domain Entities

**Implemented in this MVP (see `database/migrations/2026_08_01_12000*` and `app/Models/`):**

| Entity | Table | Notes |
|---|---|---|
| Publisher | *(is a `Team`)* | A Jetstream team acting as a developer/publisher owns `Extension` rows via `developer_team_id`; the same team can also be an installing org |
| Extension | `extensions` | Belongs to a publisher team; `status` is draft/certified/decertified |
| ExtensionVersion | `extension_versions` | Simple version + changelog record; `is_current` flag; no diffing/artifact storage |
| Installation | `installations` | One installing team's install of one extension + version; `status` active/uninstalled |

**Not yet implemented (from the original blueprint, still planned):**

| Entity | Natural key | Notes |
|---|---|---|
| Capability grant | extension + host platform + scope | What an installed extension may touch, per installing org — no table exists for this yet |
| Marketplace observation | extension-class × window | Adoption/retention/health aggregates — no aggregation job exists |
| Extension outcome | extension + review period | Post-certification behavior vs. declared capabilities |

**Explicitly excluded from our data model (unchanged from the original design):** an extension's internal logic, algorithms, and any per-customer data it processes inside its own scope. We know what an extension is entitled to touch and how it behaves in aggregate — never its internals.

**Deliberately out of MVP scope for now** (per the bounded-MVP pattern used across the ecosystem's first-cut platforms): reviews/ratings and their moderation, payments/monetization, and version-diffing. These stay on the roadmap in §7, not in the current schema.

## 5. Events

No event emission exists in code yet. The planned contracts are unchanged from the original design:

| Event | Trigger | Frequency (planned) |
|---|---|---|
| `extension.certified` / `extension.decertified` | Certification lifecycle transition | low |
| `extension.grant.issued` / `extension.grant.revoked` | Capability grant change | frequent |
| `extension.behavior.anomaly` | Runtime behavior outside declared capabilities | rare — target 0 |

## 6. Connecting to Dot.Brain

Still planned, not implemented. Dot.Plug will participate in the ecosystem as a registered platform (`dot-plug`) that publishes Knowledge Packs about the extension marketplace's health — never about what an extension does inside its own granted scope.

| Payload type | Cadence (planned) | Contains |
|---|---|---|
| `observation` | monthly | marketplace-health and extension-class aggregates |
| `insight` | per finding | capability-pattern findings (e.g. over-broad grants correlating with anomalies) |
| `outcome` | per verified recommendation | recertification and grant-review outcome verification |
| `incident` | per incident | certification failures, capability breaches |

An extension that wants to reach the Brain at all must clear our certification, sign its own packs with its publisher key, and satisfy the *host platform's* manifest rules (strictest applicable rule wins). An extension that never touches the Brain needs nothing beyond its capability grants — that surface stays open by design.

Full manifest, entity/event mapping, tenancy rules, and a worked publish→PR round-trip are maintained on the Brain side at [`platforms/dot-plug.md`](https://github.com/sakhilebhayi/Dot.Brain/blob/main/platforms/dot-plug.md) — that document is Dot.Brain's ingested view and is authoritative for integration mechanics; this wiki is authoritative for what Dot.Plug actually *is*.

## 7. Roadmap

- [x] Stand up the Jetstream Teams application shell (auth, teams, profile, notifications) — MVP scaffold, unverified
- [x] Ship a first-cut marketplace listing/install surface (no search, no moderation, no payments)
- [ ] Verify the scaffold actually boots: `composer install`, `php artisan migrate`, run the test suite (blocked — no PHP/Composer/PostgreSQL in the environment that authored this code)
- [ ] Build the capability grant engine and its host-schema-change re-validation
- [ ] Build the runtime/sandbox layer that enforces granted scope
- [ ] Build the certification pipeline and first anomaly-detection pass (replace the hand-set `status` flag)
- [ ] Add search, reviews (with moderation), and version diffing to the marketplace surface
- [ ] Publish the first `observation` Knowledge Pack (hello-pack per Dot.Brain's onboarding procedure)

## Open Questions

- Publisher trust portability: does a publisher's earned trust apply per extension, or per publisher across their whole portfolio?
- Extension-emitted domain metrics: can a certified extension register metrics in its host platform's namespace, or does it need a publisher-scoped namespace?
- Sandbox technology choice: what isolation model (process, container, WASM) backs the runtime layer, and what does that imply for cross-platform extension portability?
- Whether `Installation` should gain a capability-grant reference now (even as a stub) so the schema doesn't need a breaking change when the grant engine is built.

## Change Log

| Version | Date | Author | Change |
|---|---|---|---|
| 0.4.3 | 2026-08-06 | Platform-loop pass | Redesigned `resources/views/welcome.blade.php`, following the ecosystem's guest-page pattern piloted on Dot.Mines (`d191d10`/`dfc4547`). The prior page was the generic dark-SaaS template the pilot warns against — near-black `#0f0d0a` background, a single bright-orange accent (`#fb923c`, unrelated to the real brand), a centered hero, a 4-equal-icon-card grid. Sampled the real logo (`public/images/logo.png`) directly: a gold circle with a white plug icon, paired with a deep violet/plum chevron and a gold+violet two-tone wordmark — grounded the new palette in those actual colors (`--ink #17121f`, a violet-charcoal distinct from every other platform's ink in this batch; `--violet #6b4c93`, `--gold #f0c33a`, `--paper #f1eef5`) instead of the arbitrary orange. Typography: `Manrope` (display) + `Karla` (body) + `Fira Code` (data/labels) — a developer-portal-appropriate pairing (`Fira Code` nods to the platform's actual subject, a developer marketplace), distinct from every other platform's pairing in this batch, not Inter. Replaced the centered hero and 4-card equal grid with an asymmetric hero and a divided-list feature section (hairline borders, mono tags) covering six real capabilities drawn from wiki.md §3–§4 (marketplace listing, versioned releases, certification state, team-owned publishing, capability-scoped governance, the shared-intelligence-layer trust boundary). Added a new "lifecycle" section — a five-step mono-labelled strip (Build → Certify → Grant → Run → Retire) built directly from wiki.md §1's own stated design principle ("own the extension lifecycle: build, certify, grant, run, retire"), phrased as the governance model the platform owns rather than claiming every stage is already automated (§3 is explicit that the grant engine and runtime/sandbox layer are still roadmap, not shipped) — replacing generic filler with the platform's own real conceptual model without overclaiming implementation completeness. Signature element: a large, quiet line-art plug silhouette approaching two outlined sockets — echoing the logo's own plug icon and the "plug into any Dot platform" tagline, this platform's equivalent of Mines' headframe silhouette. Copy audited against wiki.md §1/§3/§4 only — no fabricated stats, no roadmap items presented as shipped; the hero's status strip lists real certification states (draft/certified/decertified) and real facts (any team can publish, versioned releases), not invented metrics. No dead `href="#"` links — all nav/footer links resolve to real in-page anchors or `route('login')`/`route('register')`, confirmed via rendered-HTML inspection. Nav/mobile-menu rebuilt in vanilla JS instead of Alpine `x-data` (same reasoning as the Dot.Memory/Dot.Notify passes this batch: this repo's `package.json` has no `alpinejs` dependency and `resources/js/app.js` is empty). Motion: single restrained scroll-reveal + `scale(0.97)` press-feedback, `prefers-reduced-motion` respected, no perpetual animation. **Logo legibility, the `dfc4547` lesson**: nav logo `h-14`/`h-[4.5rem]` (56px mobile / 72px sm+), footer `h-11` (44px) — verified via `getBoundingClientRect()` at both breakpoints, not guessed. Verified end-to-end: `npm install && npm run build` clean; local `php artisan serve` preview (session driver overridden to `array` for the preview process only — `.env` untouched) at desktop (864px) and mobile (375px) viewports via the Browser tool; confirmed via computed styles that the scroll-reveal reaches `opacity:1`, text renders at full contrast (`rgb(241,238,245)` on `rgb(23,18,31)`), logo height is 72px/56px at each breakpoint, and no horizontal overflow at 375px. |
| 0.4.2 | 2026-08-04 | Platform-loop pass | **Null-currentTeam robustness pass** (ecosystem-wide follow-up; reference bug/fix: Dot.Mines commit `0cc4362`). Dot.Plug has no team-context middleware at all — unlike Dot.Mines' `EnsureTeamContext`, nothing upstream of a route ever guarantees `Auth::user()->currentTeam` is non-null. A user who leaves (or is removed from) their last remaining team keeps their session but has `current_team_id` nulled out, so every unguarded `->currentTeam->id` dereference downstream was a live null-pointer risk, not a theoretical one. Audited every `app/Livewire/*.php` (just `NotificationBell`, no team dependency — safe), `app/Http/Controllers/**/*.php`, and `routes/web.php` (no `app/Jobs`, `app/Listeners`, or `app/Console/Commands` exist in this repo yet) for unguarded `currentTeam`/`current_team_id` use. Found and fixed six occurrences: (1) `routes/web.php`'s `/dashboard` closure dereferenced `$team->id` immediately after `auth()->user()->currentTeam` with no check — added a null guard that redirects to `route('teams.create')`, matching Jetstream's stock route. (2)–(6) `app/Http/Controllers/Plug/ExtensionController.php`'s `index()`, `show()`, `store()`, `install()`, and `uninstall()` all did the same unguarded dereference — added a private `resolveCurrentTeam(Request $request): ?Team` helper (`$request->user()?->currentTeam`) used by all five; `index()`/`show()` are GET/initial-render paths so they redirect to `teams.create` on a null team, while `store()`/`install()`/`uninstall()` are POST actions reachable only after the page already loaded (can't redirect mid-action) so they `abort(403, 'No active team selected.')`, introducing that exact phrasing as this repo's convention for the pattern (matching Dot.Mines' `ReportController` house style; Dot.Plug had no prior instance of this check to match against). Verified already-safe: `app/Models/Concerns/HasTeamScope.php`'s own global-scope closure already guards with `Auth::check() && Auth::user()->currentTeam` before dereferencing — no change needed there. **Trait rollout spot-check:** found one genuine gap — `Installation` (a `team_id`-owned model, and literally the use case `HasTeamScope`'s own docstring names as the intended target) was missing `use HasTeamScope;` entirely; added it. `Extension`/`ExtensionVersion` are deliberately and correctly excluded per that same docstring (marketplace content, not team-private data) — left alone. Added regression tests: `DashboardTest::test_authenticated_user_with_no_team_is_redirected_to_team_creation`, and `ExtensionMarketplaceTest::test_user_with_no_team_is_redirected_to_team_creation_from_marketplace_index` / `test_user_with_no_team_cannot_install_an_extension`. Full suite re-run against real Postgres (temp DB `dot_plug_test_audit`, dropped after): 59 tests, 52 passed, 0 failed, 7 skipped (pre-existing, config-disabled features, unrelated to this pass). No schema/migration/RLS changes made; this app has no queue-dispatched jobs or event listeners today so the "auth in a non-HTTP context" risk called out in the audit brief doesn't yet apply here — noting it for whoever adds the first one. |
| 0.4.1 | 2026-08-03 | Sakhile Bhayi | Fixed a lingering branding gap: `application-logo.blade.php` (and, where present, `application-mark.blade.php`) still rendered Jetstream's stock placeholder SVG wordmark in the app sidebar/nav and other authenticated-app surfaces, even though the login page's own `authentication-card-logo.blade.php` and the marketing welcome page already used the real logo. These two components render on every authenticated page via Jetstream's own layout, so the placeholder was visible constantly, not just on one screen. Swapped to the real logo file, matching the asset path already used elsewhere in this repo. |
| 0.4.0 | 2026-08-03 | Sakhile Bhayi | Redesigned `resources/views/welcome.blade.php`'s marketing surface: it had shipped as the untouched default Laravel/Jetstream scaffold page, so this pass builds a full custom marketing page from scratch — nav + hero + features + CTA + footer — matching the structural pattern already piloted on `mines`' `welcome.blade.php`. Nav and footer brand marks use the real `public/images/logo.png`. Hero background is a real, licensed Unsplash photo of a blue circuit board (photo by Umberto, @umby, unsplash.com/photos/blue-circuit-board-jXd2FSvcRr8), hotlinked via Unsplash's CDN with a dark gradient overlay tuned for WCAG-adequate text contrast, photographer credited inline as an HTML comment. Copy is drawn only from this wiki's real §1/§3/§4 content (extension marketplace, versioned releases, draft/certified/decertified lifecycle, team-owned publishing) — no fabricated stats or testimonials, and no claims beyond what's actually implemented in this MVP scaffold. The CDN image URL was verified with `curl -sI` before use (HTTP/2 200). |
| 0.1.0 | 2026-08-01 | Plug Platform Lead | Initial wiki: architecture blueprint derived from Dot.Brain's platforms/dot-plug.md, adapted to platform-owned framing. Repository verified to contain no application code at time of writing (LICENSE + README only). |
| 0.3.0 | 2026-08-02 | Sakhile Bhayi | **Executed for real, for the first time.** `composer install`, `migrate` (12 migrations), and the full test suite all ran clean against real PHP 8.5 + PostgreSQL — 56 tests, 49 passed, 7 skipped by config, 0 failed, including all 8 `ExtensionMarketplace` tests (certification gate, team-scoped install/uninstall, draft visibility) that had previously only been reviewed, not run. Also guarded the six shared Jetstream-core migrations per Dot.Brain adr/ADR-0013, so this platform's migrations are safe to run against the shared `infodot` database alongside any other platform's. |
| 0.2.0 | 2026-08-01 | Plug Platform Lead (AI-assisted, unverified) | Hand-authored MVP scaffold: Laravel 12 + Jetstream Teams shell copied and re-branded from Dot.Billing's reviewed boilerplate; `Extension`/`ExtensionVersion`/`Installation` domain models, migrations, controller-based CRUD, dashboard, seeder, and Feature tests added. **Written with no PHP/Composer/PostgreSQL available — nothing in this codebase has been run.** Sections 3–5 rewritten to separate built-vs-planned. Reviews/moderation, payments, capability grants, the certification pipeline, the sandbox/runtime layer, and Knowledge Pack publishing remain unimplemented. |
| 0.2.1 | 2026-08-01 | Plug Platform Lead (AI-assisted, unverified) | **Incremental security pass, IDOR check.** Audited every controller lookup of `Extension`/`ExtensionVersion`/`Installation` by ID for missing ownership/policy checks (the pattern already found this session in Dot.Agents, Dot.Pulse, and Dot.Finance's ReportController). `Installation` lookups in `install()`/`uninstall()` were already correctly scoped to `team_id = currentTeam->id`. Found and fixed one real gap: `ExtensionController::show()` loaded any `Extension` by route-bound ID with no policy check at all — `ExtensionPolicy::view()` returned `true` unconditionally — so any authenticated user of any team could read another team's **draft or decertified** (unpublished/pulled) extension listing, including name, tagline, description, and version changelog, simply by guessing/incrementing the extension ID, even though the marketplace index already correctly filtered those statuses out of browsing. Fixed by scoping `ExtensionPolicy::view()` to allow certified extensions publicly but require developer-team membership for draft/decertified ones, and adding the missing `Gate::authorize('view', $extension)` call in `show()`. Added two regression tests (`test_draft_extension_detail_page_is_not_visible_to_other_teams`, `test_publisher_team_can_view_its_own_draft_extension`) to `tests/Feature/Plug/ExtensionMarketplaceTest.php` — written but unexecuted, per environment constraints. |
