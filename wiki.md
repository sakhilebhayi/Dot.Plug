---
title: Dot.Plug — Platform Wiki
version: 0.1.0
status: draft
owners: [Plug Platform Lead]
platform-id: dot-plug
last-review: 2026-08-01
---

# Dot.Plug

Purpose: this is Dot.Plug's own knowledge home — owned and maintained by the Dot.Plug team. It describes what this platform is, what it will own, and how it will connect to the wider Dot Ecosystem. Dot.Brain never edits this file; it only reads what we choose to publish.

> **Related:** [Dot.Brain's ingested view of this platform](https://github.com/sakhilebhayi/Dot.Brain/blob/main/platforms/dot-plug.md)

---

## 1. What Dot.Plug Is

Dot.Plug is the developer portal and extension framework for the Dot Ecosystem: a marketplace and runtime where third-party developers build, certify, and publish extensions — integrations, connectors, domain add-ons, and vertical tools — that add capability to any Dot platform without requiring changes to that platform's core codebase.

Dot.Plug's job is narrow and specific: own the extension lifecycle (build, certify, grant, run, retire), not the extensions themselves. What an extension does inside its own granted scope is the publisher's business, not ours.

**Status:** early-stage. This repository currently contains only a license and a one-line README; no application code, routes, models, or migrations exist yet. Everything below is architectural intent for what we are building, not a description of shipped behavior. Treat every section as design intent until the change log records an implementation milestone.

## 2. Design Principle: Govern the Touch, Not the Extension

The line we hold: **the ecosystem governs how an extension is allowed to touch a platform; it does not govern what an extension is.** A developer is free to build anything against a capability grant. The moment an extension wants to publish knowledge into, or consume intelligence from, the ecosystem's shared intelligence layer, it crosses into a governed boundary where the same rules apply to every publisher — no shortcuts for being third-party, and no extra tax either.

This keeps the marketplace an open surface for innovation while keeping the parts of the ecosystem that touch trust, data, and cross-platform intelligence tightly controlled.

## 3. Planned Architecture

No implementation exists yet. The intended shape, once building starts:

- **Registry service** — publisher accounts, extension listings, versioning, and the certification workflow.
- **Capability grant engine** — issues, scopes, and revokes what an installed extension may touch on a given host platform; re-validates automatically when a host platform's schema changes.
- **Runtime/sandbox layer** — executes extension logic within its granted scope; the boundary that makes "extension internals are never inspected" enforceable rather than just promised.
- **Certification pipeline** — reviews an extension's declared capabilities against its behavior before it can go live or before recertification.
- **Anomaly detection** — watches installed extensions for runtime behavior outside their declared capability grant.
- **Marketplace/discovery surface** — listing, search, and installation UX for host-platform admins browsing available extensions.

## 4. Domain Entities We Will Own

| Entity | Natural key | Notes |
|---|---|---|
| Publisher | publisher ID | Verified developer or organization; tenant root |
| Extension | publisher + extension ID | Version-attributed |
| Capability grant | extension + host platform + scope | What an installed extension may touch, per installing org |
| Installation | extension × platform × org | A single org's install of an extension |
| Marketplace observation | extension-class × window | Adoption/retention/health aggregates |
| Extension outcome | extension + review period | Post-certification behavior vs. declared capabilities |

**Explicitly excluded from our data model:** an extension's internal logic, algorithms, and any per-customer data it processes inside its own scope. We know what an extension is entitled to touch and how it behaves in aggregate — never its internals.

## 5. Events We Will Emit

| Event | Trigger | Frequency (planned) |
|---|---|---|
| `extension.certified` / `extension.decertified` | Certification lifecycle transition | low |
| `extension.grant.issued` / `extension.grant.revoked` | Capability grant change | frequent |
| `extension.behavior.anomaly` | Runtime behavior outside declared capabilities | rare — target 0 |

These are planned event contracts, not yet implemented or emitted by any running service.

## 6. Connecting to Dot.Brain

Dot.Plug will participate in the ecosystem as a registered platform (`dot-plug`) that publishes Knowledge Packs about the extension marketplace's health — never about what an extension does inside its own granted scope.

| Payload type | Cadence (planned) | Contains |
|---|---|---|
| `observation` | monthly | marketplace-health and extension-class aggregates |
| `insight` | per finding | capability-pattern findings (e.g. over-broad grants correlating with anomalies) |
| `outcome` | per verified recommendation | recertification and grant-review outcome verification |
| `incident` | per incident | certification failures, capability breaches |

An extension that wants to reach the Brain at all must clear our certification, sign its own packs with its publisher key, and satisfy the *host platform's* manifest rules (strictest applicable rule wins). An extension that never touches the Brain needs nothing beyond its capability grants — that surface stays open by design.

Full manifest, entity/event mapping, tenancy rules, and a worked publish→PR round-trip are maintained on the Brain side at [`platforms/dot-plug.md`](https://github.com/sakhilebhayi/Dot.Brain/blob/main/platforms/dot-plug.md) — that document is Dot.Brain's ingested view and is authoritative for integration mechanics; this wiki is authoritative for what Dot.Plug actually *is*.

## 7. Roadmap

- [ ] Stand up the registry service (publisher accounts, extension listings)
- [ ] Build the capability grant engine and its host-schema-change re-validation
- [ ] Build the runtime/sandbox layer that enforces granted scope
- [ ] Build the certification pipeline and first anomaly-detection pass
- [ ] Ship the marketplace/discovery surface (listing, search, install UX)
- [ ] Publish the first `observation` Knowledge Pack (hello-pack per Dot.Brain's onboarding procedure)

## Open Questions

- Publisher trust portability: does a publisher's earned trust apply per extension, or per publisher across their whole portfolio?
- Extension-emitted domain metrics: can a certified extension register metrics in its host platform's namespace, or does it need a publisher-scoped namespace?
- Sandbox technology choice: what isolation model (process, container, WASM) backs the runtime layer, and what does that imply for cross-platform extension portability?

## Change Log

| Version | Date | Author | Change |
|---|---|---|---|
| 0.1.0 | 2026-08-01 | Plug Platform Lead | Initial wiki: architecture blueprint derived from Dot.Brain's platforms/dot-plug.md, adapted to platform-owned framing. Repository verified to contain no application code at time of writing (LICENSE + README only). |
