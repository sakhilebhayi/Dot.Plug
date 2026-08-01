---
title: Dot.Plug — Platform Wiki
version: 0.2.0
status: mvp-scaffold-unverified
owners: [Plug Platform Lead]
platform-id: dot-plug
last-review: 2026-08-01
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
| 0.1.0 | 2026-08-01 | Plug Platform Lead | Initial wiki: architecture blueprint derived from Dot.Brain's platforms/dot-plug.md, adapted to platform-owned framing. Repository verified to contain no application code at time of writing (LICENSE + README only). |
| 0.2.0 | 2026-08-01 | Plug Platform Lead (AI-assisted, unverified) | Hand-authored MVP scaffold: Laravel 12 + Jetstream Teams shell copied and re-branded from Dot.Billing's reviewed boilerplate; `Extension`/`ExtensionVersion`/`Installation` domain models, migrations, controller-based CRUD, dashboard, seeder, and Feature tests added. **Written with no PHP/Composer/PostgreSQL available — nothing in this codebase has been run.** Sections 3–5 rewritten to separate built-vs-planned. Reviews/moderation, payments, capability grants, the certification pipeline, the sandbox/runtime layer, and Knowledge Pack publishing remain unimplemented. |
