# Feature Specification: i95Dev Complete Feature List

**Feature Branch**: `001-i95dev-feature-list`

**Created**: 2026-10-23

**Status**: Draft

**Input**: User description: "i95dev complue future list" (interpreted as "i95Dev complete feature list")

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Single authoritative list of i95Dev capabilities (Priority: P1)

A project stakeholder (implementation consultant, support engineer, project manager, or CC
Industries business user) needs one complete, current list of every capability the i95Dev ERP
integration provides on the CC Industries storefront: what data is synchronized, in which
direction, which add-ons are installed, and which behaviors were customized for CC Industries.
Today this knowledge is spread across many components and is only discoverable by reading code.

**Why this priority**: Every later activity (support triage, upgrades, scoping change requests,
onboarding new team members) depends on knowing what exists. It delivers value on its own.

**Independent Test**: Give the list to a team member unfamiliar with the project and ask them to
answer 10 representative questions (e.g., "Do shipments sync to the ERP?", "Are tier prices
supported?", "Which customer fields are CC Industries-specific?"). They answer correctly using
only the list.

**Acceptance Scenarios**:

1. **Given** the feature list, **When** a reader looks up a business entity (e.g., Order),
   **Then** they can see whether it syncs, the direction(s), and any related add-ons.
2. **Given** the feature list, **When** a reader looks for CC Industries-specific behavior,
   **Then** every client customization is listed separately from the standard product features.
3. **Given** a capability that is installed, **When** it is compared to the list, **Then** it
   appears in the list (no installed capability is missing).

---

### User Story 2 - Configuration and operations reference (Priority: P2)

An administrator or support engineer needs to know, for each capability, which admin settings
control it, which admin screens and reports exist (queues, summaries, error reports), what
scheduled background activity runs, and which notifications are sent, so they can operate and
troubleshoot the integration without escalating.

**Why this priority**: Most support incidents involve stuck or failed sync records; knowing the
available controls shortens resolution. It depends on the base list (US1) but is a separate slice.

**Independent Test**: For three historical or simulated sync problems, a support engineer can
identify from the list which screen to inspect and which setting or retry control applies.

**Acceptance Scenarios**:

1. **Given** a failed inbound customer record, **When** the engineer consults the list,
   **Then** they find the inbound queue screen, the retry-limit setting, and the error report.
2. **Given** a capability with an on/off setting, **When** the list is consulted, **Then** the
   setting's name and location are given.

---

### User Story 3 - Gap and risk findings (Priority: P3)

The technical lead needs the list to also flag gaps and risks observed while compiling it
(e.g., capabilities without automated tests, customizations that deviate from the project
constitution, security concerns), so they can be scheduled for remediation.

**Why this priority**: Valuable for planning, but the list is useful without it.

**Independent Test**: Each flagged item names the affected capability, the concern, and the
constitution principle it relates to, and can be turned into a backlog item without further
research.

**Acceptance Scenarios**:

1. **Given** a capability with no automated tests, **When** the list is reviewed, **Then** it is
   flagged as a test-coverage gap.
2. **Given** a security concern, **When** it is recorded, **Then** the record describes the issue
   without reproducing any secret value.

---

### Edge Cases

- A component is installed but disabled by configuration: the list MUST show it as installed and
  state that its enabled/disabled status is environment-specific.
- A component exists with minimal or placeholder behavior (e.g., settings/permissions only): the
  list MUST say so rather than implying full functionality.
- The sync direction of a capability cannot be confirmed from available evidence: it MUST be marked
  "to be confirmed", not guessed.
- A CC Industries customization replaces a standard behavior entirely (rather than adding to it):
  the list MUST state that the standard behavior is overridden.
- Other ERP-related integrations exist in the codebase outside i95Dev (e.g., separate P21 and
  "Mach" integrations): these are out of scope (FR-010); the list MAY note that they exist,
  without describing them.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The feature list MUST enumerate every installed i95Dev capability, grouped into:
  core synchronization platform, synchronized business entities, add-ons, administration and
  operations, and CC Industries customizations.
- **FR-002**: For each synchronized business entity the list MUST state the direction(s):
  storefront-to-ERP (outbound), ERP-to-storefront (inbound), or "to be confirmed".
- **FR-003**: For each capability the list MUST state the controlling admin setting(s), if any.
- **FR-004**: The list MUST identify every admin screen, report, scheduled job, command-line
  operation, and notification email provided by the integration.
- **FR-005**: CC Industries-specific customizations MUST be listed separately, naming each custom
  field or behavior and whether it extends or overrides standard behavior.
- **FR-006**: Every entry MUST be traceable to evidence in the codebase (recorded during planning)
  so the list can be re-verified after upgrades.
- **FR-007**: The list MUST include a gaps-and-risks section (US3) and MUST NOT contain
  credentials, keys, tokens, or customer personal data.
- **FR-008**: The list MUST record the date it was verified against the codebase.
- **FR-009**: The deliverable MUST be a reference document only, kept with the project
  documentation. No storefront or admin functionality is built or changed by this feature.
- **FR-010**: Scope MUST be limited to the i95Dev components (the i95Dev Connect product
  components and the CC Industries i95Dev customization component). Other ERP-related
  integrations (P21, "Mach") are out of scope.

### Baseline Feature Inventory (as found 2026-10-23)

Initial inventory compiled from the codebase; it is the starting content for the deliverable and
is to be verified during planning.

**A. Core synchronization platform**

- Inbound message queue (ERP → storefront) and outbound message queue (storefront → ERP),
  each with an admin list view and a summary report.
- Configurable packet (batch) size, retry limit for failed records, and automatic clean-up of
  processed queue data after a configurable number of days.
- Connection to the i95Dev Cloud service: push data, pull data, push response, pull response,
  running on a schedule and also triggerable manually by command line.
- Direct service endpoints for message-queue and reverse-sync processing.
- Cloud settings: CRM/ERP selection, web-service address, instance type, client and subscription
  identification, endpoint code, authentication token, logging switch.
- ERP-specific component for Microsoft Dynamics NAV.

**B. Synchronized business entities (core)**

| Entity | Outbound (to ERP) | Inbound (from ERP) |
|--------|-------------------|--------------------|
| Customer | Yes (on save) | Yes (create/update) |
| Customer address | Yes (on save) | Yes (create/update) |
| Customer group | Yes (on save) | Yes (create/update) |
| Product (simple) | Yes (on save) | Yes, incl. attributes and stock |
| Inventory | To be confirmed | Yes |
| Order (addresses, items, comments, discounts, payment incl. PO and card transactions) | Yes | Yes (create) |
| Invoice | Yes (on save) | Yes (create, incl. invoice-with-shipment) |
| Shipment and tracking | Yes (on save) | Yes (create) |

**C. Add-ons (installed)**

| Add-on | Business capability | Admin setting |
|--------|--------------------|---------------|
| Category | Category sync; assigns products to categories from ERP data | — |
| Configurable Products | Configurable (parent/child) product sync and order-line info | Enable |
| Tier Price | Quantity-based tier pricing from ERP | — |
| Price Level (Price Groups) | Customer price groups applied to cart totals (storefront and admin) | Enable |
| Discount Groups | Customer and item discount groups, discount prices, discount calculation applied at order placement and shown on sales documents | Enable |
| Cancel Order | Order cancellation sync | Enable |
| Order Edit | Synchronization of edited orders | Enable |
| Payment Journal | Payment journal records linked to invoices | — |
| Returns | Returns and return-receive sync with credit memos (uses Commerce returns) | — |
| Split Shipment | Present with permissions/routes only; functional scope to be confirmed | — |
| Shipping Mapping | Map storefront shipping methods to ERP methods; push to cloud | Enable, update on cloud |
| Payment Mapping | Map storefront payment methods to ERP methods; push to cloud | Enable, update on cloud |

**D. Administration and operations**

- Admin menu: Configurations, Add-ons, Reports (inbound/outbound queues and summaries),
  Price Groups, Discount Groups, per-add-on settings.
- General settings: component (ERP type), capture invoice, default customer group, product
  attribute set and attribute group, order sync start date, customer sync, category parent,
  website.
- Error reporting: scheduled error report email covering selected inbound/outbound entities.
- Notifications: e-mail confirmations, order total mismatch notification, subscription emails,
  admin contact details.
- Logging: enable, log type, clean-up days, archive days, maximum log size; scheduled clean-up
  for connector and cloud logs.

**E. CC Industries customizations**

- Inbound customer: discount flag, car generations owned, Corvette years owned, ERP customer ID.
- Inbound product: discount flag, search description, GM part number, Paragon item number, net
  weight, e-commerce lead days and its last-updated date, air-shipment eligibility, shipping
  height/length/width (and possibly further fields — to be completed during verification).
- Category assignment: **overrides** the standard behavior; builds the category path from ERP
  data under the website's root category and merges with existing assignments.
- Admin manual linking: enter the ERP customer ID for a customer, and ERP order ID/status for an
  order (which also marks the matching outbound queue record as synced).

**F. Gaps and risks observed (for US3)**

- A configuration file in the repository contains integration subscription identifiers/keys
  (Constitution IV). Values are not reproduced here; they need removal and rotation.
- The CC Industries customization component does not declare its dependency on the i95Dev
  components it extends (Constitution I/II).
- The two admin linking actions do not declare a dedicated admin permission (to be verified;
  Constitution IV).
- Category override uses full replacement of standard behavior, which the constitution permits
  only with written justification (Constitution I).
- Automated tests exist for only 5 components (Cancel Order, Configurable Products, core server,
  Price Level, Tier Price); none for CC Industries customizations (Constitution V).

### Key Entities

- **Capability**: a named feature; attributes: group, description, direction(s), controlling
  setting(s), status (installed / partial / to be confirmed), evidence reference.
- **Synchronized entity**: business record type exchanged with the ERP (customer, order, …).
- **Customization**: CC Industries-specific change; attributes: affected entity, fields/behavior,
  extends vs. overrides.
- **Finding**: gap or risk; attributes: capability, concern, constitution principle, severity.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% of installed i95Dev components are represented in the list; a reviewer
  spot-checking any component finds it listed.
- **SC-002**: A team member new to the project answers at least 9 of 10 representative capability
  questions correctly using only the list, in under 15 minutes.
- **SC-003**: Every entry has a direction (or "to be confirmed") and a verification date; zero
  entries rely on unverified assumptions presented as fact.
- **SC-004**: Every gap/risk finding can be raised as a backlog item without additional research.
- **SC-005**: The list contains zero secrets or customer personal data.

## Assumptions

- "complue future list" means "complete feature list" of the i95Dev integration for this
  project.
- Audience is internal (i95Dev delivery/support team and CC Industries stakeholders); the list
  is confidential to this project.
- The inventory reflects the code in this repository; the enabled/disabled state in each
  environment is out of scope.
- Scope limited to i95Dev components (FR-010) by default because the request names "i95dev";
  revisit with `/speckit-clarify` if the other ERP integrations are needed.
- No changes to integration behavior are part of this feature; remediation of findings will be
  separate features.
