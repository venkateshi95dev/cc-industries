<!--
Sync Impact Report
==================
Version change: (unversioned template) → 1.0.0
Bump rationale: Initial ratification. All placeholder tokens replaced with concrete,
project-specific principles derived from the repository (Magento 2 / Adobe Commerce
codebase with i95Dev Connect ERP integration, SonarQube + Psalm quality tooling).

Modified principles (template slot → new title):
  - [PRINCIPLE_1_NAME] → I. Extend, Never Modify Core or Vendor Code
  - [PRINCIPLE_2_NAME] → II. Magento Architecture & Coding Standards
  - [PRINCIPLE_3_NAME] → III. ERP Integration Integrity (NON-NEGOTIABLE)
  - [PRINCIPLE_4_NAME] → IV. Security, Payments & Data Protection (NON-NEGOTIABLE)
  - [PRINCIPLE_5_NAME] → V. Verifiable Quality & Testing

Added sections:
  - Platform & Technology Constraints (was [SECTION_2_NAME])
  - Development Workflow & Quality Gates (was [SECTION_3_NAME])
  - Governance (filled)

Removed sections: none

Templates requiring updates:
  - ✅ .specify/templates/plan-template.md — Constitution Check is derived dynamically
       from this file; no structural change required.
  - ✅ .specify/templates/spec-template.md — no mandatory sections added/removed; aligned.
  - ✅ .specify/templates/tasks-template.md — test-optional note and path conventions
       updated to reflect Principle V and Magento module layout.
  - ✅ .specify/templates/checklist-template.md — no principle-specific references; aligned.
  - ✅ .claude/skills/speckit-*/SKILL.md — no outdated principle references found.
  - ✅ README.md — contains only the project title; no principle references to update.

Follow-up TODOs:
  - TODO(CI_GATES): Jenkinsfile currently runs only the SonarQube scan; the phpcs
    (Magento2 standard) stage is commented out and Psalm is not wired into CI. Until
    enabled, these gates are enforced locally/at review (see Development Workflow).
  - TODO(ANALYSIS_SCOPE): sonar-project.properties and psalm.xml cover only
    app/code/I95Dev; extend scope as other first-party namespaces are brought under
    analysis.
-->

# CC Industries Commerce Platform Constitution

## Core Principles

### I. Extend, Never Modify Core or Vendor Code

- Magento core (`vendor/magento/*`) and third-party modules — including those vendored under
  `app/code` (e.g., `Ebizcharge`, `WeltPixel`, `Magedelight`, `Bss`, `IWD`, and any
  Amasty/Aheadworks/Mageworx dependencies) — MUST be treated as read-only.
- Behavior changes MUST be delivered in a first-party module (`Crimson\*`, `I95Dev\*`, or a
  project-approved namespace) using, in order of preference: plugins (around/before/after),
  event observers, layout/UI component XML, `di.xml` virtual types/arguments, and — only as a
  last resort with written justification — class preferences.
- Fixes to vendor behavior MUST live in a dedicated customization module (the existing
  `Crimson\EbizchargeFix` / `Crimson\SitemapFix` pattern), never as edits to vendor files.
- Each first-party module MUST have a single, clear purpose and declare its dependencies in
  `etc/module.xml` (`<sequence>`) and `composer.json`.

**Rationale**: Direct edits to core or vendor code are silently lost on upgrade, block security
patches, and make defects impossible to attribute. Isolated extensions keep the platform
upgradeable and reviewable.

### II. Magento Architecture & Coding Standards

- Code MUST follow the Magento 2 coding standard (`magento/magento-coding-standard`, Magento2
  ruleset) and the project's existing module structure, naming, and patterns.
- Dependencies MUST be obtained via constructor injection. Direct use of
  `ObjectManager::getInstance()` is prohibited in new or modified code (factories, proxies,
  and generated classes are the approved alternatives).
- Business logic MUST be exposed through service contracts / repositories (`Api\` interfaces)
  where other modules or integrations consume it; controllers, observers, and plugins MUST
  stay thin and delegate to services.
- Database changes MUST use declarative schema (`etc/db_schema.xml` with whitelist) and data/
  schema patches; legacy `InstallSchema`/`UpgradeSchema` scripts MUST NOT be added.
- New PHP files MUST declare `strict_types=1` and use typed properties, parameters, and return
  types compatible with the PHP version range declared in the module's `composer.json`.
- Configuration values MUST be read through `ScopeConfigInterface` with store/website scope
  respected; environment-specific values MUST NOT be hard-coded.

**Rationale**: Consistent use of Magento's extension mechanisms keeps a large multi-vendor
codebase (100+ modules) predictable for every developer and for static analysis.

### III. ERP Integration Integrity (NON-NEGOTIABLE)

- Every data flow between Magento and the ERP (i95Dev Connect, including NAV and P21 paths) —
  orders, customers, products, inventory, pricing, tier/price levels, payments, shipments,
  returns — MUST be idempotent: replaying the same message MUST NOT create duplicates or
  corrupt state.
- Sync failures MUST be captured and persisted through the established error and message-queue
  mechanisms (`I95DevConnect\ErrorData`, `I95DevConnect\MessageQueue`) with enough context
  (entity type, entity ID, target/ERP ID, direction, error) to diagnose and retry. Silent
  failure or swallowed exceptions are prohibited.
- Changes to entity mappings, target-ID handling, or message payload structure MUST be
  backward compatible with in-flight queue messages, or ship with a documented migration.
- Customizations MUST hook into i95Dev Connect via its extension points (events, plugins,
  `I95Dev\Customizations`) rather than modifying `I95DevConnect\*` modules (see Principle I).

**Rationale**: Integration defects cause financial and inventory discrepancies between systems
of record that are costly to detect and reconcile; traceability and idempotency are the
primary safeguards.

### IV. Security, Payments & Data Protection (NON-NEGOTIABLE)

- Credentials, API keys, tokens, and payment gateway secrets MUST NOT be committed to the
  repository. They MUST be stored in `app/etc/env.php`, encrypted system configuration
  (`Magento\Config\Model\Config\Backend\Encrypted`), or an approved secret store. Any secret
  found in code MUST be removed and rotated.
- Payment integrations (Ebizcharge, Payware, NMI/Aheadworks, gift card, offline methods) MUST
  NOT log, persist, or transmit raw cardholder data (PAN, CVV, track data) outside the
  gateway's tokenization flow.
- All admin controllers MUST define an `ADMIN_RESOURCE` ACL and an entry in `etc/acl.xml`;
  storefront POST actions MUST be protected by form-key/CSRF validation.
- All user and ERP input MUST be validated; all output in templates MUST be escaped with the
  appropriate `$escaper` method; SQL MUST use bound parameters or the collection/query
  builder APIs.
- Customer, order, and business data MUST be handled as confidential: logs MUST NOT contain
  PII beyond what is necessary for diagnosis, and data from this project MUST NOT be mixed
  with other customers' projects.
- Permissions, integrations, and API tokens MUST follow least privilege.

**Rationale**: The platform processes payments and customer PII for a production storefront;
a single leak creates PCI, legal, and contractual exposure for both the customer and i95Dev.

### V. Verifiable Quality & Testing

- Changes touching ERP sync, pricing, tax, shipping, payment, order, or inventory logic MUST
  include automated tests: unit tests for business logic and, for i95Dev Connect flows,
  integration tests following the existing `Test/Integration/<Entity>/Json` fixture pattern.
- Other changes SHOULD include tests; when automated tests are impractical (e.g., layout/
  theme-only changes), the spec or plan MUST document manual verification steps.
- Code MUST pass the SonarQube quality gate, Psalm static analysis, and phpcs (Magento2
  standard) for in-scope paths before merge, with no new blocker/critical issues.
- Claims of "tested" or "verified" in PRs, specs, or task lists MUST reflect tests that were
  actually run; results MUST NOT be fabricated or assumed.

**Rationale**: Commerce defects surface directly as lost revenue or incorrect orders; objective
gates and honest verification are the only reliable signal of readiness.

## Platform & Technology Constraints

- **Platform**: Magento 2 / Adobe Commerce, custom code under `app/code/<Vendor>/<Module>`.
- **Language**: PHP; each module MUST honor the `php` constraint in its `composer.json`. New
  modules MUST target PHP 8.1+ and remain compatible with the platform's deployed PHP version.
- **Frontend**: Layout XML, PHTML templates, RequireJS/Knockout and LESS per the active theme;
  no new frontend framework or build pipeline without an approved plan.
- **Dependencies**: New Composer packages, third-party extensions, external services, or APIs
  MUST be explicitly identified in the plan with license, maintenance status, and security
  review noted.
- **Performance**: Code MUST NOT load collections or repositories inside loops where batch
  loading is possible; storefront-path changes MUST respect full-page cache (no uncacheable
  blocks without justification) and use indexed attributes for filtering.
- **Upgrade safety**: All changes MUST survive `setup:upgrade`, `setup:di:compile`, and
  `setup:static-content:deploy` in production mode.

## Development Workflow & Quality Gates

1. **Spec-driven flow**: Non-trivial features follow `/speckit-specify` → `/speckit-plan` →
   `/speckit-tasks` → `/speckit-implement`. The plan's Constitution Check MUST pass (or
   violations be justified in Complexity Tracking) before implementation begins.
2. **Branching**: Work happens on feature branches; `main` receives changes only through
   reviewed pull requests.
3. **Code review**: Every PR MUST be reviewed by at least one other developer, who verifies
   compliance with Principles I–V and flags security, data-loss, breaking-change, licensing,
   and performance risks.
4. **CI gates**: The Jenkins pipeline's SonarQube scan MUST succeed. Until phpcs and Psalm are
   enabled in CI (see TODO(CI_GATES)), the author MUST run them locally and state the result in
   the PR.
5. **Deployment readiness**: Changes affecting checkout, payment, or ERP sync MUST be verified
   in a non-production environment with representative data before production release, and
   MUST include a rollback approach (config flag, module disable, or revert plan).

## Governance

- This constitution supersedes other development practices for this repository. Where it
  conflicts with i95Dev organization policy, organization policy prevails and this document
  MUST be amended to match.
- **Amendments**: Proposed via pull request modifying `.specify/memory/constitution.md`,
  including an updated Sync Impact Report and any required template changes, and approved by
  the project technical lead.
- **Versioning** (semantic):
  - MAJOR — removal or backward-incompatible redefinition of a principle or governance rule.
  - MINOR — new principle or section, or materially expanded guidance.
  - PATCH — clarifications, wording, or typo fixes with no semantic change.
- **Compliance review**: Every plan's Constitution Check and every PR review MUST confirm
  adherence. Deviations MUST be justified in the plan's Complexity Tracking table; unjustified
  violations block merge. The constitution SHOULD be reviewed at least once per quarter.
- Runtime development guidance for agents lives in `CLAUDE.md` and the current plan.

**Version**: 1.0.0 | **Ratified**: 2026-10-23 | **Last Amended**: 2026-10-23
