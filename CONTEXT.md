# eTaxWare Context

## What This Codebase Does

etaxware is a PHP web platform for electronic tax operations, document lifecycle management, ERP integration, and compliance workflows.

This codebase implements the Uganda Revenue Authority (URA) EFRIS API and is used as an abridged ERP to demonstrate practical EFRIS integration flows.

It also includes direct QuickBooks Online (QBO) integration, with bi-directional pull/push flows between QBO and EFRIS.

From the implementation, the core capabilities include:

- Tax document workflows: invoices, credit notes, debit notes, purchase orders
- Master data management: products, customers, suppliers, branches, currencies, commodity codes
- User and access control: authentication, role/permission groups, session management
- Operational tooling: notifications, event tracking, audit logs, reporting exports
- ERP integration: especially QuickBooks Online (QBO)
- Tax platform integration: EFRIS-oriented operations and dictionary synchronization

The codebase currently mixes product naming and historical context (e.g., eTaxWare + EFRIS WebApp references), but runtime behavior and routing are centered around etaxware.

## End-to-End Ecosystem Architecture

This ecosystem is a multi-entry integration platform with two primary runtime applications in the same workspace:

- etaxware: browser-facing web platform, internal operations console, and direct integration runtime (including QBO integration paths).
- etaxware-api: ERP-adapter API runtime that exposes ERP-friendly endpoints and translates ERP events into eTaxWare and EFRIS workflows.

Both applications share one operational database (`etaxware`), which acts as the state backbone for users, settings, permissions, dictionaries, staging records, and tax document lifecycle data.

### Architecture Layers

1. Client and channel layer

- Browser users interact with etaxware UI routes for operations, setup, monitoring, and document workflows.
- ERP systems integrate through etaxware-api endpoints (normally tested via Postman and ERP connectors).
- EFRIS is the external tax platform endpoint for submission, query, and synchronization.

1. Application runtime layer

- etaxware runtime:
  - Fat-Free MVC stack (`app/controller`, `app/model`, `app/view`)
  - shared service layer in `util/v3/Utilities.php`
  - API surface in `api/e-taxware/v5` (active by autoload)
- etaxware-api runtime:
  - Fat-Free API-focused stack with adapter-specific Api classes
  - adapter-specific utility implementations where available
  - active adapter selected by `AUTOLOAD` in `../etaxware-api/config/config.ini`

1. Domain and integration service layer

- Document orchestration: invoice, credit note, debit note, purchase order lifecycles.
- Master data orchestration: products, buyers/customers, suppliers, branches, currency, commodity and tax dictionaries.
- Compliance services: EFRIS login/session, taxpayer validation, dictionary sync, upload/query/cancel operations.
- Operational controls: permissions, notifications, event logs, audit logs, reporting/export.

1. Data and persistence layer

- Shared MySQL schema for both repos.
- Mapper-model pattern (`DB\\SQL\\Mapper`) for table-level persistence.
- Dynamic settings from `tblsettings` drive behavior in both runtimes.

1. Observability and operations layer

- App logs (`app.log`), utility/integration logs (`util.log`), API logs (`api.log`), and framework/global error logs.
- SQL and PowerShell scripts for migration, maintenance, import/update jobs, and environment operations.

### End-to-End Flow Patterns

1. Browser-driven document flow (etaxware)

- User performs create/edit/approve actions in UI.
- Controller validates permissions and payload shape.
- Utility service composes EFRIS payload from DB state.
- EFRIS response is normalized and written back to local tables.
- UI reload reflects local persisted state and external status.

1. ERP-driven API flow (etaxware-api)

- ERP posts payload to adapter endpoint (for example invoice upload).
- `beforeroute()` performs gate checks (API key, version, organisation TIN, ERP user, permissions).
- Adapter Api method coordinates transformation and delegates to utility logic.
- Local DB is updated and downstream EFRIS interaction is executed as needed.
- Structured response is returned to ERP caller.

1. Sync and round-trip flow

- Scheduled/manual sync fetches dictionaries and tax documents from EFRIS.
- Incoming records are reconciled into local canonical tables.
- Subsequent UI and API flows reuse synchronized local state.

### Currently Integrated ERP Landscape

The repository is designed for one-active-adapter-at-a-time operation, but supports multiple ERP families as switchable integrations.

Currently active adapter in configuration snapshot:

- FTS v11 (via `../etaxware-api/config/config.ini` AUTOLOAD).

Direct integration path currently documented in etaxware runtime:

- QuickBooks Online (QBO) bi-directional integration flows are implemented directly in etaxware.

Available ERP adapter families in etaxware-api:

- Agilis
- D365BC
- D365FO
- FTS
- msdynamics
- Odoo
- QBD
- QBPOS
- Rrq
- SAPB1
- Tally

Version maturity is uneven by family (for example Tally has the deepest version ladder), and utility implementation coverage is also family-dependent. Adapter switching should therefore include a route-method and utility-coverage validation pass before production activation.

### Practical Operating Model

- Use browser testing for etaxware UI workflows and permission/session behavior.
- Use Postman (and ERP clients) for etaxware-api endpoint contract validation.
- Treat both tracks as one integrated system test when validating end-to-end tax document behavior.

## Runtime Entry and Boot Sequence

Main application bootstrap is in index.php.

Boot flow:

1. Load Fat-Free Framework core from vendor
2. Load config/config.ini
3. Load config/routes.ini
4. Load Composer autoload
5. Start Session()
6. Register global ONERROR handler
7. Run F3 router

Global error behavior:

- Clears F3 cache
- Writes error details to error.log
- Clears output buffers
- Renders Error.htm

## Framework and Architectural Pattern

This is an MVC-style Fat-Free Framework application.

Top-level structure:

- app/controller: request handlers and orchestration
- app/model: DB mappers (table-level data access)
- app/view: F3 HTML templates
- api/e-taxware: versioned API classes and endpoints
- util/v3: shared business/service logic (large Utilities class)
- config: global settings + routing
- public: static assets and main layout template
- scripts: SQL and PowerShell operational scripts
- docs: runtime storage and operational folders

Scale snapshot from current tree:

- controllers: 32 files
- models: 76 files
- views/templates: 74 files
- API versions present: v1 through v7

## Active Autoload and Version Wiring

From config/config.ini:

- AUTOLOAD=app/controller/|app/model/|util/v3/|api/e-taxware/v5/

Important implication:

- Although api/e-taxware has v1-v7 folders, the autoloaded API class resolved by routes is currently from api/e-taxware/v5/Api.php unless explicitly referenced otherwise.
- Utility code in use is util/v3/Utilities.php.

## Configuration Model

Primary configuration file: config/config.ini.

Observed globals:

- DEBUG=0
- UI=app/view/|public/|public/js/
- CACHE=true
- ESCAPE=false
- dbserver/dbuser/dbpwd (MySQL DSN + credentials)
- app metadata fields (developer/org/longname)

Note:

- Many operational settings are not hardcoded in config.ini and are loaded dynamically from tblsettings (non-sensitive rows) in both MainController and Utilities constructors.

## Request Lifecycle and Session Handling

MainController defines the base lifecycle for most web controllers.

In beforeroute:

- Enforces login session for protected routes
- Enforces inactivity timeout using SESSION.lastActivityDate and MAXINACTIVITYTIME
- If expired, resets user online flag in tblusers and clears SESSION/CACHE
- Loads current user from tblusers
- Loads in-app notifications via Utilities.getnotifications
- Builds effective permissions map from tblpermissiondetails using user permission group
- Exposes settings and flags to view layer:

  - vatRegistered
  - platformMode
  - efrisMode
  - integratedErp

In afterroute:

- Updates SESSION.lastActivityDate
- Persists lastActivityDate to tblusers

AuthenticationController overrides login behavior and provides:

- authenticate
- logout
- resetaccount
- resetpassword

Authentication implementation uses:

- password_verify for password checks
- online/offline markers in tblusers
- cache/session cleanup on logout and account reset

## Authorization and UI Gating

Permissions are data-driven:

- Permission codes and values are loaded from tblpermissiondetails
- Values are stored in f3 variable userpermissions
- Templates conditionally render menus/actions based on userpermissions flags (for example VIEWINVOICES, VIEWPRODUCTS, VIEWCREDITNOTES)

This pattern creates a two-layer gate:

- server-side checks in controllers
- client/navigation checks in templates

## Routing Surface

Routing is centralized in config/routes.ini with a large set of GET/POST endpoints.

Functional route families found:

- base/home/dashboard/search
- authentication and user management
- role and branch management
- administration and synchronization actions
- API actions (sendmail/import/update/renew token)
- settings and reports
- events and notifications
- currencies and rates
- products and stock operations (stock in/out/transfer/query/opening stock)
- commodity code lookup
- invoices and ERP sync
- credit/debit note workflows
- taxpayer query
- customers/suppliers
- purchase orders

Observed route style details:

- Many endpoints have both plain and /etaxware/ prefixed forms for compatibility.
- Some endpoints use dynamic path segments (for example /viewinvoice/@id).
- Duplicate route declaration exists for sendmail.
- `POST /recyclelogs` is intentionally POST-only (no GET) and is triggered from a CSRF-protected Administration quick action.

## API Layer Details

API namespace path: api/e-taxware.

Observed characteristics in v5 Api.php:

- Uses PHPMailer (SMTP), QuickBooks SDK facades, and DataService
- Contains email dispatch operations and business/API orchestration
- Holds app setting state, caller context, permissions, and integration flags
- Uses JSON payloads in several endpoints

API versions present (v1-v7) indicate incremental evolution, and current autoload wiring targets v7 runtime classes.

## Utility Layer Details

Core business helper class: util/v3/Utilities.php.

This class is extensive and central to business operations. Notable function groups include:

- Notification and messaging:

  - sendemailnotification
  - sendemailnotification_v2
  - getnotifications
  - createinappnotification

- Audit:

  - createauditlog
  - createerpauditlog

- Document lifecycle:

  - create/update invoice
  - create/update credit note
  - create debit note
  - upload/download invoice/credit/debit notes

- ERP/EFRIS synchronization:

  - syncproducts, syncbranches
  - syncefrisinvoices, syncefrisdebitnotes, syncefriscreditnotes
  - syncdictionaries, synchscodelist, syncefrisexcisedutylist
  - efrislogin, querytaxpayer

- Inventory operations:

  - stockin, stockout, transferproductstock, batchstockin
  - stock adjustment/transfer logging

- Master data operations:

  - create/update customer
  - create/update supplier
  - upload/update/fetch product

- Finance logic:

  - tax rate resolution functions
  - discount application logic

Constructor behavior:

- Creates independent DB connection
- Loads non-sensitive app settings from tblsettings
- Initializes API user context from APIUSERID
- Evaluates VAT registration flag from table lookups

## Data Access Layer Pattern

Models in app/model are mostly thin DB\SQL\Mapper wrappers.

Typical model pattern:

- __construct maps to a specific table
- all/getByID/getByCode style selectors
- add/edit/delete methods driven by copyFrom('POST') or mapper load/update/erase

Representative tables visible from models and SQL usage:

- tblusers, tblroles, tblpermissions, tblpermissiondetails
- tblsettings, tblstatuses, tblbranches
- tblinvoices, tblcreditnotes, tbldebitnotes, tblpurchaseorders
- tblproductdetails, tblcustomers, tblsuppliers
- tblnotifications, tblevents, tbleventnotifications
- and many reference dictionary tables

## UI and Frontend Stack

Template rendering uses F3 templates under app/view plus a shell layout in public/Layout.htm.

Frontend stack observed:

- Bootstrap 3.x
- AdminLTE styling and skinning
- jQuery
- DataTables
- Select2
- Datepicker/timepicker plugins

Navigation and sections are role/permission aware and include:

- Home, Dashboard
- Product and stock modules
- Sales modules (invoice/credit/debit)
- Master data and administration
- Reporting and settings

Login experience includes:

- sign-in form
- account reset modal
- password reset modal

QBO OAuth flow support is present in view scripts (popup-based code flow).

## Reporting and Exports

ReportController provides:

- report group retrieval
- report metadata retrieval
- report execution

Export behavior includes:

- Spreadsheet export logic
- PDF generation path via PhpSpreadsheet + Mpdf writer pipeline
- temp file generation and cleanup under tmp paths

## Integration and Automation Assets

scripts/api contains operational PowerShell automations for:

- importErpInvoices
- importErpSalesReceipts
- importPurchaseOrders
- updateErpInvoices
- renewAccessToken

scripts/db contains SQL baselines and incremental patches, including:

- etaxware.sql baseline
- backup/cleanup scripts
- EFRIS enhancement scripts
- QBO automation update scripts
- feature-specific fixes (credit memo, excise duty, buyer type, etc.)

## Logging Strategy

Observed log files in root:

- app.log and app-trace.log (plus dated/archived variants)
- util.log and util-trace.log (plus dated/archived variants)
- api.log and api-trace.log (plus dated/archived variants)
- error log files

Log writers:

- MainController writes through SmartLogger to app.log (operational) and app-trace.log (high-volume trace)
- Utilities writes through SmartLogger to util.log (operational) and util-trace.log (high-volume trace)
- Active API runtime writes through SmartLogger to api.log (operational) and api-trace.log (high-volume trace)
- Global ONERROR writes to error.log

Operational note:

- Administration `recyclelogs()` rotates both operational and trace files for app/api scopes and reports rotated/missing/failed counts in the UI alert.

Logging is verbose and used for step-level diagnostics of controller, API, and utility operations while preserving cleaner operational logs.

## Docs Folder Role

docs appears to be an operational storage tree, not only static docs.

Observed subtrees include:

- docs/admin/incoming
- docs/admin/reports
- docs/logs/qb
- docs/certs (archived cert bundle)
- docs/temeru/reports

This suggests runtime artifacts, imports, logs, and generated reports may be staged here.

## Dependencies (Composer)

From composer.json:

- bcosca/fatfree-core
- ikkez/f3-fal
- ikkez/f3-sheet
- phpoffice/phpspreadsheet
- mpdf/mpdf
- phpmailer/phpmailer
- quickbooks/v3-php-sdk

## Notable Implementation Observations

These are practical findings worth preserving for maintainers:

1. README is minimal

- README.md currently contains only a brief EFRIS WebApp line and does not fully describe setup/runbook.

1. Naming drift exists

- Code comments and metadata include eTaxWare, EFRIS WebApp, and historical org names.

1. Runtime API selection is configuration-driven

- v5 API is active through AUTOLOAD despite multiple API versions existing.

1. Route map is broad and includes compatibility aliases

- /etaxware-prefixed duplicate routes are common.

1. A few route definitions should be reviewed

- Example: one payment route references paymentController with different casing.
- Example: debit note ERP download/fetch routes point to CreditnoteController in current routes file.

These items are not necessarily fatal at runtime in all environments, but they are high-value checkpoints during refactoring.

## How to Extend Safely

Recommended extension workflow in this codebase:

1. Define data requirements

- Add/adjust table and dictionary scripts in scripts/db.

1. Add model methods

- Extend mapper models in app/model for new selectors and writes.

1. Add service logic

- Place shared business/integration logic in util/v3/Utilities.php (or a newer utility version if introducing one).

1. Add controller endpoints

- Implement action methods in app/controller.
- Enforce permission checks consistently.

1. Register routes

- Add endpoint mappings in config/routes.ini.
- Consider whether both plain and /etaxware alias routes are required.

1. Wire view and frontend assets

- Add/modify templates in app/view and JS in public/js.

1. Validate logs and operational scripts

- Ensure app.log/util.log traces are useful for support teams.
- Update scripts/api PowerShell automations if API contracts change.

## Suggested Follow-Up Docs

To make onboarding and support faster, the next high-impact docs to add are:

- Environment setup and first-run checklist
- DB migration strategy (which SQL files to run and in what order)
- API contract reference (request/response samples)
- Controller-to-model-to-table map
- Production operations runbook (logs, backups, scheduled scripts)

## Cross-Repo Profile: etaxware-api

This workspace also contains a second runtime application at `../etaxware-api` that operates as an ERP-facing adapter layer for eTaxWare.

### Testing Workflow (Operational)

- Use Postman to test `etaxware-api` route-level behavior (request/response contracts, adapter-specific API operations).
- Use browser-based testing to validate `etaxware` web workflows (UI flows, controller/view behavior, session-driven interactions).
- Keep route/API validation and UI validation as two coordinated tracks when verifying end-to-end changes across both repos.

### Purpose in the Overall Integration

- etaxware-api exposes ERP-friendly endpoints (`/uploadinvoice`, `/uploadcreditnote`, `/uploaddebitnote`, stock operations, taxpayer checks) and translates those requests into eTaxWare/EFRIS workflows.
- It shares the same MySQL schema (`dbname=etaxware`) as the main etaxware web app.
- In practice, this means etaxware-api and etaxware are two entry points over one operational database and settings model.

### Runtime Boot and Wiring

From `../etaxware-api/index.php`, boot flow mirrors the main app pattern:

1. Load Fat-Free core.
1. Load `config/config.ini`.
1. Load `config/routes.ini`.
1. Start session.
1. Register global `ONERROR` logger/renderer.
1. Run router.

From `../etaxware-api/config/config.ini`:

- `AUTOLOAD=app/controller/|app/model/|util/FTS/v11/|api/FTS/v11/`
- Active runtime adapter in this repo is currently FTS v11.
- DB target is the same shared database used by etaxware.

### Route Surface (Current)

From `../etaxware-api/config/routes.ini`, key endpoint families include:

- Product and stock: `uploadproduct`, `stockin`, `stockout`, `batchstockin`, `batchstockout`, `stocktransfer`, `fetchproduct`, `uploadcommoditycode`
- Invoice lifecycle: `uploadinvoice`, `queryinvoice`, `printinvoice`
- Credit/debit lifecycle: `uploadcreditnote`, `querycreditnote`, `voidcreditnote`, `uploaddebitnote`, `querydebitnote`
- Validation and dictionaries: `validatetin`, `checktaxpayer`, `currencyquery`, `loadrateunits`
- Master data and services: `uploadcustomer`, `uploadsupplier`, `uploadimportedservice`
- Utility: `sendmail`

### Endpoint-to-Permission Map (Active FTS v11)

Verified against `../etaxware-api/api/FTS/v11/Api.php` by matching route target method to in-method `$permission` constant.

| Route | Target | Permission constant |
| --- | --- | --- |
| `/sendmail` | `Api->sendmail` | `SENDEMAIL` |
| `/validatetin` | `Api->validatetin` | `QUERYTAXPAYER` |
| `/checktaxpayer` | `Api->checktaxpayer` | `QUERYTAXPAYER` |
| `/currencyquery` | `Api->currencyquery` | `FETCHCURRENCYRATES` |
| `/stockin` | `Api->stockin` | `STOCKIN` |
| `/batchstockin` | `Api->batchstockin` | `STOCKIN` |
| `/stockout` | `Api->stockout` | `STOCKOUT` |
| `/batchstockout` | `Api->batchstockout` | `STOCKOUT` |
| `/fetchproduct` | `Api->fetchproduct` | `FETCHPRODUCT` |
| `/uploadproduct` | `Api->uploadproduct` | `UPLOADPRODUCT` |
| `/stocktransfer` | `Api->stocktransfer` | `TRANSFERPRODUCTSTOCK` |
| `/uploadinvoice` | `Api->uploadinvoice` | `UPLOADINVOICE` |
| `/queryinvoice` | `Api->queryinvoice` | `DOWNLOADINVOICE` |
| `/uploadcreditnote` | `Api->uploadcreditnote` | `UPLOADCREDITNOTE` |
| `/uploaddebitnote` | `Api->uploaddebitnote` | `UPLOADDEBITNOTE` |
| `/voidcreditnote` | `Api->voidcreditnote` | `CANCELCREDITNOTE` |

Routes currently present in `../etaxware-api/config/routes.ini` but corresponding method definitions were not found in the active `../etaxware-api/api/FTS/v11/Api.php` class during this pass:

- `/printinvoice` -> `Api->printinvoice`
- `/uploadcommoditycode` -> `Api->uploadcommoditycode`
- `/querycreditnote` -> `Api->querycreditnote`
- `/querydebitnote` -> `Api->querydebitnote`
- `/uploadcustomer` -> `Api->uploadcustomer`
- `/uploadsupplier` -> `Api->uploadsupplier`
- `/uploadimportedservice` -> `Api->uploadimportedservice`

Maintenance note:

- Treat the unresolved set as a runtime-risk checklist and verify whether those endpoints are intentionally served from another adapter/version or are stale route declarations.

### Active API Class Behavior (FTS v11)

From `../etaxware-api/api/FTS/v11/Api.php`:

- `beforeroute()` acts as a strong gate before all operations:
  - captures request metadata and raw JSON body
  - requires and validates `ORGTIN` against configured seller TIN
  - validates API key (`tblapikeys`: active + not expired)
  - enforces client `VERSION == APPVERSION`
  - loads API-key permission group into `$permissions`
  - resolves ERP user and loads user permission group into `$userpermissions`
  - evaluates VAT registration flag from `tbltaxtypes`
  - updates API key `lastaccessdt`
- Endpoint methods then apply per-operation permission checks against `$userpermissions` (for example `UPLOADINVOICE`, `UPLOADCREDITNOTE`, `UPLOADDEBITNOTE`, `STOCKIN`, `DOWNLOADINVOICE`, etc.).
- Error behavior follows structured response code/message payloads and verbose operational logging (`api.log`).

### Active Utility Class Behavior (FTS v11)

From `../etaxware-api/util/FTS/v11/Utilities.php` constructor path:

- Builds a DB connection directly from F3 globals.
- Loads non-sensitive settings from `tblsettings` into a key/value map.
- Resolves API/system user context from configured `APIUSERID`.
- Computes VAT registration status by checking configured VAT tax type in `tbltaxtypes`.
- Initializes utility logging (`util.log`) and shared helper state used across API workflows.

### Adapter Matrix Snapshot

Adapter families currently present under `../etaxware-api/api/`:

- `Agilis`, `D365BC`, `D365FO`, `FTS`, `msdynamics`, `Odoo`, `QBD`, `QBPOS`, `Quickbooks`, `Rrq`, `SAPB1`, `Tally`

Count snapshot:

- 47 `Api.php` adapter/version entry files currently exist under `../etaxware-api/api/**`.

Operational implication:

- Multiple ERP integrations are versioned in-repo, but active runtime behavior is controlled by `AUTOLOAD` configuration, currently pointing to `FTS/v11`.

### Multi-ERP Profile (All Adapter Families)

Important architecture note:

- Only one ERP adapter family/version is active at runtime, selected through `AUTOLOAD` in `../etaxware-api/config/config.ini`.
- The other adapter families remain available in-repo as switchable implementations.

#### API Family and Version Spread

Current `Api.php` distribution under `../etaxware-api/api/`:

| Family | Versions present | Api file count |
| --- | --- | --- |
| Agilis | v3 | 1 |
| D365BC | v1, v2, v3 | 3 |
| D365FO | v1, v2, v3 | 3 |
| FTS | v3, v4, v5, v6, v7, v8, v9, v10, v11 | 9 |
| msdynamics | v2 | 1 |
| Odoo | v1, v2 | 2 |
| QBD | v2, v3 | 2 |
| QBPOS | v1, v2 | 2 |
| Rrq | v2, v3, v4, v11, v12, v13 | 6 |
| SAPB1 | v1, v2 | 2 |
| Tally | v1 through v16 | 16 |

Additional observation:

- `../etaxware-api/api/Quickbooks/` exists but currently has no `Api.php` entry file.

#### Utility Coverage by Family

`Utilities.php` coverage under `../etaxware-api/util/` is not uniform:

| Family | Utility versions present |
| --- | --- |
| D365BC | v1, v2, v3 |
| D365FO | v1 |
| FTS | v1 through v7, v11 |
| SAPB1 | v1 |
| Tally | v1, v2, v3 |
| core | base (`util/Utilities.php`) |

Families with API adapters but no family-specific utility folder in this repository snapshot:

- Agilis
- msdynamics
- Odoo
- QBD
- QBPOS
- Rrq

#### Latest Adapter Complexity Snapshot

Latest detected adapter per family:

| Family | Latest Api | Approx. lines | Function count | `beforeroute()` |
| --- | --- | --- | --- | --- |
| Agilis | v3 | 5338 | 23 | Yes |
| D365BC | v3 | 6005 | 22 | Yes |
| D365FO | v3 | 5217 | 22 | Yes |
| FTS | v11 | 6225 | 23 | Yes |
| msdynamics | v2 | 5580 | 24 | Yes |
| Odoo | v2 | 5150 | 22 | Yes |
| QBD | v3 | 7473 | 26 | Yes |
| QBPOS | v2 | 5563 | 24 | Yes |
| Rrq | v13 | 6115 | 24 | Yes |
| SAPB1 | v2 | 5597 | 22 | Yes |
| Tally | v16 | 6548 | 21 | Yes |

Interpretation:

- All latest adapters implement a common request lifecycle hook (`beforeroute()`), indicating shared gatekeeping pattern across ERP families.
- QBD and Tally latest adapters are among the heaviest by file size/function count, suggesting broader custom behavior for those ERP integrations.

#### Route-Method Coverage on Latest Adapters

Compared against route targets declared in `../etaxware-api/config/routes.ini`, latest adapter methods show partial but not identical surface coverage.

Highlights from latest-family checks:

- D365BC v3 / D365FO v3 / SAPB1 v2 / Rrq v13 commonly miss methods for `uploaddebitnote`, `querydebitnote`, `querycreditnote`, `printinvoice`, `uploadcommoditycode`, and some master-data/imported-service uploads.
- Active FTS v11 now includes `uploaddebitnote`, but route-surface gaps still remain for `querydebitnote`, `querycreditnote`, `printinvoice`, `uploadcommoditycode`, and some master-data/imported-service uploads.
- Tally v16 and Agilis v3 include `uploaddebitnote` but still miss `querydebitnote`, `querycreditnote`, `printinvoice`, and customer/supplier/imported-service uploads in the checked route-target set.
- QBD v3 and QBPOS v2 expose a comparatively wider route-target method surface in latest versions, but still omit at least some query/print methods.

Operational guidance:

- When switching active ERP via `AUTOLOAD`, validate route-to-method alignment for that exact family/version before go-live.
- Treat route declarations as shared intent and family Api classes as actual implemented capability.

## Follow-Up Approach

Use this approach for every new change so implementation and documentation stay aligned.

1. Define the change clearly

- What is changing.
- Why it is needed.
- Which module is affected (UI, controller, utility, API, config, DB).

1. Identify impact before editing

- List touched files.
- Check for related dependencies (routes, settings, templates, scripts).
- Confirm whether backward compatibility is required.

1. Implement minimal safe change

- Change only what is necessary.
- Preserve existing behavior unless intentionally changed.
- Keep security-sensitive details out of rendered UI.

1. Validate immediately

- Check diagnostics for touched files.
- Verify the primary flow manually (or via available scripts).
- Confirm no accidental regressions in related modules.

1. Document completion in Update Tracker

- Add one timestamped entry.
- Include summary and impacted file paths.
- Mention any important follow-up tasks or caveats.

Tracker entry template:

- YYYY-MM-DD HH:MM:SS +/-TZ - [change summary]. Files: [file1], [file2], [file3]. Notes: [optional caveat/follow-up].

## Local GUI Test Credentials

Use these local credentials for GUI sanity checks in this development environment:

- Username: admin
- Password: admin

## Business Rules

Use this section as the source-of-truth for implemented business behavior that must be preserved during refactors.

Rule template:

- BR-MODULE-NNN: rule statement.
- Scope: where it applies.
- Enforcement: controller/view/js/db behavior.
- Updated: YYYY-MM-DD.

### Invoice Buyer Rules

- BR-INV-001: For buyer types other than B2B (for example B2C, Foreigner, B2G), Buyer TIN is optional when entering buyer details manually on invoice Buyer Info.
- Scope: Invoice edit/view buyer flow.
- Enforcement: `InvoiceController->edit()` validation allows blank TIN for non-B2B types; `public/js/invoice.js` does not raise empty-TIN validation error for non-B2B; `EditInvoice.htm` TIN field pattern allows blank.
- Updated: 2026-04-12.

- BR-INV-002: For Buyer Type B2B (`buyertype=0`), Buyer TIN is mandatory and must be exactly 10 characters when no buyer template is selected.
- Scope: Invoice edit/view buyer flow.
- Enforcement: `InvoiceController->edit()` blocks save for missing TIN/legal name only in B2B; `public/js/invoice.js` shows required-TIN message only in B2B; TIN format check remains 10 chars when provided/required.
- Updated: 2026-04-12.

- BR-INV-003: Invoice buyer reload must hydrate from `tblbuyers` first because `tblinvoices.buyerid` references buyer records created/edited in buyer save flow; fallback to `tblcustomers` is for legacy/template-linked records only.
- Scope: Invoice view/edit page load and post-save reload.
- Enforcement: `InvoiceController->view()` and `InvoiceController->edit()` hydration blocks load `buyers` first, then fallback to `customers` when buyer mapper is dry.
- Updated: 2026-04-12.

### Invoice Discount Rules

- BR-INV-004: Invoice goods discount persistence is percentage-driven; backend derives `discountflag` from `discountpercentage` and does not rely on posted discount flag for final save state.
- Scope: Invoice add/edit good save path.
- Enforcement: `InvoiceController->edit()` computes `POST.discountflag` as `1` only when `add/editdiscountpercentage != 0`, otherwise `2`.
- Updated: 2026-04-14.

- BR-INV-005: Discount amounts on goods are stored as negative values (`discounttotal`) and uploaded to EFRIS as `discountTotal`; `discountTaxRate` is only emitted when discount is active.
- Scope: Invoice good calculation and EFRIS upload payload mapping.
- Enforcement: `InvoiceController->edit()` computes negative `POST.discounttotal`; `Utilities->uploadinvoice()` maps `discountTotal` from goods and conditionally emits `discountTaxRate` when `discountflag != 2`.
- Updated: 2026-04-14.

- BR-INV-006: EFRIS invoice sync/download must restore discount fields (`discountflag`, `discounttotal`, `discounttaxrate`) into `tblgooddetails` and derive `discountpercentage` from returned `discountTotal` and `total` when possible.
- Scope: Invoice import/sync from EFRIS (`downloadinvoice` and `syncefrisinvoices`).
- Enforcement: `InvoiceController` goods import blocks parse `discountFlag`/`discountTotal`/`discountTaxRate`, normalize flag values, and compute `discountpercentage = abs(discountTotal)/total*100` when `total > 0`.
- Updated: 2026-04-14.

### Product Excise Rules

- BR-PROD-001: Product excise duty persistence is canonical on `tblproductdetails.exciseDutyCode`; `excisedutylist` is legacy read-fallback only and must not be mirror-written by new save/sync logic.
- Scope: Product create/edit/sync flows and product upload payload mapping.
- Enforcement: `ProductController` write paths set `POST.exciseDutyCode` and SQL updates persist `exciseDutyCode` only; `Utilities->uploadproduct()` maps payload `exciseDutyCode` with fallback reads from legacy `excisedutylist` for historical rows.
- Updated: 2026-04-12.

- BR-PROD-002: When a user selects/enters an excise duty code on Product create/edit, backend save must auto-derive and persist `exciseDutyName` and `exciseRate` from `tblexcisedutylist`.
- Scope: Product create/edit save path.
- Enforcement: `ProductController` resolves selected `exciseDutyCode` against `tblexcisedutylist` (`goodService` -> `exciseDutyName`, `rateText` -> `exciseRate`) before mapper save.
- Updated: 2026-04-12.

## Update Tracker

Use this section as a running changelog for implementation updates.

Tracker policy:

- Every completed code/config/documentation change must be logged here.
- Each entry must include timestamp, summary of change, and impacted file paths.

- 2026-04-26 18:45:00 +03:00 - Refactored Administration settings governance lock logic into named non-editable policy groups (`runtime_core`, `logging`, `integration_endpoints`, `filesystem_paths`) to improve maintainability without changing effective edit behavior. Files: app/controller/AdministrationController.php, CONTEXT.md.

- 2026-04-26 19:05:00 +03:00 - Hardened Administration Settings UX by hiding raw setting codes from the end-user table and edit modal while preserving internal code usage for save requests; added inline maintainer comments in view and JS for intent clarity. Files: app/view/Administration.htm, public/js/administration.js, CONTEXT.md.

- 2026-04-26 11:12:00 +03:00 - Updated documentation sections to reflect trace-log split and security hardening: documented POST-only CSRF-protected `/recyclelogs`, corrected active API runtime note to v7 autoload target, and expanded logging strategy with SmartLogger operational-vs-trace files (`app/api/util` + `*-trace`) plus recycle count behavior. Files: CONTEXT.md.

- 2026-04-26 11:00:00 +03:00 - Introduced trace-log split concept (ported from etaxware-api approach) into active etaxware runtime: added shared SmartLogger in util/v3, switched MainController app logger to app.log/app-trace.log, switched Utilities v3 logger to util.log/util-trace.log, switched active API v7 logger to api.log/api-trace.log, and extended Administration recycle-logs flow to rotate trace files for both app and api scopes. Files: util/v3/SmartLogger.php, app/controller/MainController.php, util/v3/Utilities.php, api/e-taxware/v7/Api.php, app/controller/AdministrationController.php, CONTEXT.md.

- 2026-04-26 10:35:00 +03:00 - Hardened Administration log recycle flow to prevent state-changing GET calls and improve operation integrity: changed route to POST-only, replaced quick-action anchor with CSRF-protected POST form, added controller CSRF validation + method guard, refactored recycle logic into helper-based per-log processing, and changed final alert/notification to include rotated/missing/failed counts so partial failures are no longer reported as unconditional success. Files: config/routes.ini, app/view/Administration.htm, app/controller/AdministrationController.php, CONTEXT.md.

- 2026-04-11 17:19:53 +03:00 - Added timestamped tracker in this file for ongoing updates.
- 2026-04-11 17:19:53 +03:00 - Created Utilities v3 by cloning util/v2 into util/v3.
- 2026-04-11 17:19:53 +03:00 - Updated AUTOLOAD in config/config.ini from util/v2 to util/v3.
- 2026-04-11 17:19:53 +03:00 - Updated util/v3/Utilities.php header metadata (path/date/version).
- 2026-04-11 17:27:13 +03:00 - Clarified project purpose: URA EFRIS API implementation used as an abridged ERP demo.
- 2026-04-11 17:29:21 +03:00 - Added direct QBO integration note: bi-directional push/pull between QBO and EFRIS.
- 2026-04-11 17:36:24 +03:00 - Hardened DB secret handling: removed plaintext dbpwd from config and now resolve password from environment variable ETAXWARE_DB_PASSWORD.
- 2026-04-11 17:39:32 +03:00 - Updated DB secret strategy to admin-editable base64 config value (dbpwd_b64), with env override and legacy fallback.
- 2026-04-11 17:47:47 +03:00 - Updated homepage footer year to dynamic runtime year ({{date('Y')}}) instead of static FINYEAR setting.
- 2026-04-11 17:52:15 +03:00 - Hardened error handling UX: ONERROR now fully handles errors to prevent raw technical details from rendering to users.
- 2026-04-11 17:58:42 +03:00 - Added tracker governance rule requiring all future changes to be recorded with timestamp and impacted files. Files: CONTEXT.md.
- 2026-04-11 18:00:11 +03:00 - Documented a reusable follow-up approach and tracker template for ongoing maintenance. Files: CONTEXT.md.
- 2026-04-11 18:31:22 +03:00 - Fixed Product fetch/edit handling for blank export and piece-unit fields: normalized `fetchproduct()` updates to avoid persisting empty-string codes (`piecemeasureunit`, `hscode`, `customsmeasureunit`) and prevented blank Select2 preselection on Edit Product when those values are empty. Files: app/controller/ProductController.php, app/view/EditProductFooter.htm.
- 2026-04-11 18:47:56 +03:00 - Implemented one-time in-app cleanup during `fetchproduct()` to normalize historical blank strings to NULL for `piecemeasureunit`, `hscode`, and `customsmeasureunit`. Also added dated inline comments at each changed code block (controller normalization and UI prefill guards) to preserve change intent/auditability. Approach: (1) normalize historical data once per fetch path, (2) normalize incoming fetch payload writes, (3) prevent frontend blank-option preselection. Files: app/controller/ProductController.php, app/view/EditProductFooter.htm, CONTEXT.md.
- 2026-04-11 18:55:55 +03:00 - Follow-up fix for remaining blank Select2 display state: normalized literal `NULL`/`null` string values to SQL NULL in the one-time cleanup query, and added client-side value sanitizer in Edit Product to treat blank/`NULL` hidden-span values as empty before prefill. Also added fallback label behavior to display code when lookup name is missing, preventing blank-selected UI chips. Approach: (1) data normalization for legacy `NULL` strings, (2) UI normalization at prefill boundary, (3) resilient display fallback to code. Files: app/controller/ProductController.php, app/view/EditProductFooter.htm, CONTEXT.md.
- 2026-04-11 19:07:14 +03:00 - Performed wider Select2 prefill hardening sweep across modules: updated Edit Product prefill for `measureunit` and `commoditycategory`, updated Edit Credit Note and Edit Debit Note original invoice prefill, and updated Product Other Units edit modal prefill. Added normalization for blank/`NULL` strings and fallback labels to code where name text is missing to prevent blank-selected Select2 states. Approach: (1) normalize raw hidden/modal values, (2) gate prefill on non-empty normalized ids, (3) fallback display label to id/code. Files: app/view/EditProductFooter.htm, app/view/EditCreditnoteFooter.htm, app/view/EditDebitnoteFooter.htm, public/js/product.js, CONTEXT.md.
- 2026-04-11 19:13:27 +03:00 - Hardened Product excise-duty end-to-end flow (display/edit/persist/upload/fetch-sync): (1) create flow now enforces excise consistency by clearing `excisedutylist` when `hasexcisetax=102` and only persisting duty code when applicable, (2) fetch/update sync now maps EFRIS `exciseDutyCode` into `tblproductdetails.excisedutylist` in both `fetchproduct()` and post-upload refresh update path, (3) EFRIS upload/update payload now sources duty code from persisted `excisedutylist` (not non-existent camelCase property) and clears payload duty code when excise flag is No. Approach: enforce one source-of-truth column and conditional payload emission. Files: app/controller/ProductController.php, util/v3/Utilities.php, CONTEXT.md.
- 2026-04-11 19:17:13 +03:00 - Added login-page password visibility toggle (`Show password`) to improve usability without changing authentication flow. Approach: lightweight checkbox toggle that switches `#password` input type between `password` and `text` client-side only. Files: app/view/Login.htm, CONTEXT.md.
- 2026-04-11 19:23:11 +03:00 - Added matching password visibility toggle to Reset Account modal for UX consistency with login form. Approach: lightweight checkbox toggle that switches `#resetpassword` input type between `password` and `text` client-side only. Files: app/view/Login.htm, CONTEXT.md.
- 2026-04-11 19:33:53 +03:00 - Fixed Product create-flow failure for excise-duty product setup caused by empty-string decimals (`Incorrect decimal value: '' for packagescaledvaluecustoms`). Approach: corrected posted field mapping (`productcustomsunitprice`, `productcustomsscaledvalue`) and normalized optional numeric customs fields (`customsunitprice`, `packagescaledvaluecustoms`, `customsscaledvalue`, `weight`) to `null` when empty before ORM save. Files: app/controller/ProductController.php, CONTEXT.md.
- 2026-04-11 19:38:15 +03:00 - Enhanced Product create/edit UX by converting `productcurrency` to Select2 dropdown in shared form template. Approach: apply `select2` class and full-width style to existing currency `<select>` so existing global Select2 initialization handles it with no controller logic changes. Files: app/view/EditProduct.htm, CONTEXT.md.
- 2026-04-11 19:44:49 +03:00 - Updated Product currency field to behave like commodity category (AJAX Select2 search): added `searchcurrencies` endpoint in ProductController, added routes (`/searchcurrencies` and `/etaxware/searchcurrencies`), switched currency field to empty Select2 control, added hidden currency code/name spans for edit prefill, and added JS Select2 config + prefill guards/fallback label handling. Approach: same pattern as commodity category (`minimumInputLength`, remote search, preselected option injection on edit). Files: app/controller/ProductController.php, config/routes.ini, app/view/EditProduct.htm, app/view/EditProductFooter.htm, public/js/product.js, CONTEXT.md.
- 2026-04-11 20:14:33 +03:00 - Added excise-to-piece safeguard and enforcement for Product create/edit/upload: when `hasexcisetax=101` (Yes), system now enforces populated excise/piece fields (`excisedutylist`, `havepieceunit=101`, `piecemeasureunit`, `pieceunitprice`, `packagescaledvalue`, `piecescaledvalue`). Implemented frontend guard to auto-force `Have Piece Units` to Yes, enable/require piece fields, and block submit when incomplete; implemented backend guard in ProductController to block save/upload with clear validation messages; also corrected create-flow `havepieceunit` defaulting logic so posted value is respected instead of always forcing No. Files: app/controller/ProductController.php, public/js/product.js, CONTEXT.md.
- 2026-04-11 20:24:18 +03:00 - Completed excise/piece round-trip wiring in ProductController fetch/upload persistence paths: (1) confirmed upload/update payload already includes `havePieceUnit`, `pieceMeasureUnit`, `pieceUnitPrice`, `packageScaledValue`, `pieceScaledValue`; (2) patched post-upload refresh update block to persist those fields from EFRIS response; (3) patched `fetchproduct()` update block to persist numeric piece fields in addition to existing `havepieceunit`/`piecemeasureunit`; (4) patched `syncproducts()` insert/update paths to map and persist piece-unit fields from fetched records. Approach: conditional SQL mapping (`NULL` when blank/NULL-string, preserve existing when key absent). Files: app/controller/ProductController.php, CONTEXT.md.
- 2026-04-11 20:37:52 +03:00 - Added persistent local DB-schema context by restoring MySQL dump structure into a SQLite mirror for fast schema lookups during development/debugging. Generated converter script and artifacts: `scripts/db/build_sqlite_schema.py`, `scripts/db/etaxware_schema.sqlite`, `scripts/db/etaxware_schema_sqlite.sql`, `scripts/db/etaxware_schema_summary.txt`, and module-grouped compact reference `scripts/db/etaxware_schema_reference.md`. Result: 117 tables converted and key Product/Excise tables confirmed (`tblproductdetails`, `tblotherunits`, `tblexcisedutylist`, `tblrateunits`). Files: scripts/db/build_sqlite_schema.py, scripts/db/etaxware_schema.sqlite, scripts/db/etaxware_schema_sqlite.sql, scripts/db/etaxware_schema_summary.txt, scripts/db/etaxware_schema_reference.md, CONTEXT.md.
- 2026-04-11 20:45:21 +03:00 - Updated login and reset-account password visibility UX from checkbox labels to inline eye-icon buttons next to password inputs for cleaner aesthetics. Replaced checkbox toggles with Bootstrap input-group icon buttons and updated JS from checkbox `change` handlers to button `click` handlers that toggle input type and swap eye-open/eye-close icons plus accessibility labels. Files: app/view/Login.htm, CONTEXT.md.
- 2026-04-11 20:53:06 +03:00 - Fixed login-page eye-icon password input notch/misalignment by scoping legacy signin field-corner CSS to direct-child inputs only, so input-group based password controls are no longer affected by old `input[type=password]` styling. Approach: changed selectors from `.form-signin input[...]` to `.form-signin > input[...]` in signin stylesheet. Files: public/css/signin.css, CONTEXT.md.
- 2026-04-11 20:58:14 +03:00 - Adjusted password visibility eye icon placement for aesthetics by moving icon controls outside password boxes (side-by-side row) on both Login and Reset Account forms. Approach: replaced input-group embedding with `password-field-row` flex layout (`input` + separate icon button), preserving existing toggle JS behavior and accessibility labels. Files: app/view/Login.htm, CONTEXT.md.
- 2026-04-11 21:04:12 +03:00 - Fixed false excise-piece validation failure on save (`Have Piece Units must be Yes...`) when piece-unit selector is disabled in UI and omitted from POST. Logs showed `havepieceunit after:` blank while Excise=Yes. Backend now force-sets `POST.havepieceunit='101'` whenever `POST.hasexcisetax='101'` in both edit and create flows before validation. Files: app/controller/ProductController.php, CONTEXT.md.
- 2026-04-11 21:18:42 +03:00 - Compared `etaxware-api/util/FTS/v7/Utilities.php` against current `etaxware/util/v3/Utilities.php` and aligned Product EFRIS payload generation with safe v7 deltas: (1) `unitPrice` fallback changed from empty string to `'1'` in both upload/update payloads, (2) removed integer rounding from `packageScaledValue`/`pieceScaledValue` in update payload to preserve submitted precision, and (3) suspended `commodityGoodsExtendEntity` in update payload to match v7 temporary behavior. Scope intentionally limited to product payload blocks (`uploadproduct`, `updateproduct`) as high-impact/no-API-contract-change updates. Files: util/v3/Utilities.php, CONTEXT.md.
- 2026-04-11 21:31:14 +03:00 - Completed broad utilities sync pass from `etaxware-api/util/FTS/v7/Utilities.php` into `etaxware/util/v3/Utilities.php`: replaced all shared function blocks (35 common functions) and appended functions missing in v3 (37 functions) to align v3 utility surface with v7. Post-sync, restored legacy controller compatibility for product operations by adding ID-based compatibility handling in `uploadproduct`/`fetchproduct` and reintroducing `updateproduct($userid, $id)` behavior via delegation to upload flow, because ProductController still invokes ID-based signatures. Validation: no diagnostics errors in `util/v3/Utilities.php` and `app/controller/ProductController.php`. Files: util/v3/Utilities.php, CONTEXT.md.
- 2026-04-11 21:36:58 +03:00 - Clarified utility sync scope in tracker: the 21:18:42 entry was a targeted product-only delta before the full sync; the 21:31:14 sync is broader and includes non-product functions (invoice upload/download/query flows, credit/debit note flows, stock flows, taxpayer/query helpers, dictionary/sync helpers). This note is added to prevent misreading the update stream as product-only. Files: CONTEXT.md.
- 2026-04-11 21:42:17 +03:00 - Added invoice field-audit logging to improve visibility of create/upload invoice payload changes in runtime logs. New logs now print (1) createinvoice field summary and (2) uploadinvoice payload audit including buyer flags and first-good fields (`hsCode`, `vatProjectId`, `pieceQty`, `pieceMeasureUnit`, `totalWeight`) before base64 encoding. Files: util/v3/Utilities.php, CONTEXT.md.
- 2026-04-12 08:55:00 +03:00 - Documented Home direct-link UX implementation and tracking updates: added maintainers' inline comments in Home search JS (local route discovery, direct-link rendering intent), added Home footer note for JS cache-busting version flag, and validated the direct-link flow (search -> select -> Go to link -> target page navigation). Files: public/js/home.js, app/view/HomeFooter.htm, CONTEXT.md.
- 2026-04-12 09:04:00 +03:00 - Performed a follow-up inline-commentary sweep for recent Home UX changes: annotated the Home template direct-link result container and added event-handler intent comments for select/clear behaviors in Home search JS. Files: app/view/Home.htm, public/js/home.js, CONTEXT.md.
- 2026-04-12 09:22:00 +03:00 - Updated invoice Buyer Info validation so B2C can be saved without TIN: backend now requires Legal Name for B2C and requires TIN only for non-B2C when no buyer template is selected; frontend TIN validation now skips empty TIN errors for B2C and enforces 10-digit rule only when needed. Also cache-busted invoice.js includes to prevent stale browser validation scripts. Files: app/controller/InvoiceController.php, app/view/EditInvoice.htm, public/js/invoice.js, app/view/EditInvoiceFooter.htm, app/view/InvoiceFooter.htm, CONTEXT.md.
- 2026-04-12 09:33:00 +03:00 - Fixed invoice Buyer Info reload issue where page reopened with empty buyer fields after save: invoice view/edit hydration now loads buyer details from tblbuyers first (source of `tblinvoices.buyerid`) with fallback to tblcustomers for legacy/template-linked records. Added inline maintainers' comments at each hydration block. Files: app/controller/InvoiceController.php, CONTEXT.md.
- 2026-04-12 09:41:00 +03:00 - Verified end-to-end buyer behavior on invoice id 32 after fixes: (1) B2C save allows blank TIN with legal name, (2) buyer data persists in tblbuyers (`buyerid=7`, `type=1`, `tin=''`, updated legal name), and (3) Edit Invoice reload now shows populated Buyer Info instead of blank fields due to corrected tblbuyers-first hydration. Inline comments confirmed on all newly changed validation/hydration/cache-busting blocks. Files: app/controller/InvoiceController.php, app/view/EditInvoice.htm, public/js/invoice.js, app/view/EditInvoiceFooter.htm, app/view/InvoiceFooter.htm, CONTEXT.md.
- 2026-04-12 09:49:00 +03:00 - Added dedicated Business Rules section to context for long-term behavior tracking and future refactor safety, including invoice buyer rules for B2C optional TIN, non-B2C mandatory TIN, and tblbuyers-first buyer hydration with tblcustomers fallback. Files: CONTEXT.md.
- 2026-04-12 09:56:00 +03:00 - Refined invoice buyer TIN business rule per requirement: TIN is now mandatory only for B2B (`buyertype=0`) and optional for all other buyer types. Updated both backend validation message/logic and frontend blur validation rule, and synchronized Business Rules documentation. Files: app/controller/InvoiceController.php, public/js/invoice.js, CONTEXT.md.
- 2026-04-12 10:07:00 +03:00 - Updated Invoice Buyer Delivery Terms dropdown to commodity-category-style AJAX Select2 behavior (search-as-you-type with code-name labels): added `searchdeliveryterms` endpoint in `InvoiceController`, registered routes (`/searchdeliveryterms` and `/etaxware/searchdeliveryterms`), converted Buyer Info delivery terms select to Select2 AJAX control, added hidden prefill source (`buyerdeliveryTermsCode`) and footer prefill wiring, and bumped invoice.js cache-busting version to load changes immediately. Files: app/controller/InvoiceController.php, config/routes.ini, app/view/EditInvoice.htm, app/view/EditInvoiceFooter.htm, app/view/InvoiceFooter.htm, public/js/invoice.js, CONTEXT.md.
- 2026-04-12 10:24:00 +03:00 - Fixed Invoice Goods Add/Edit modal item search typing issue in real browser usage by avoiding double Select2 initialization on `#additem/#edititem` and adding modal-scoped Select2 config (`dropdownParent`) for both controls so focus/keyboard input remains inside the active modal. Also bumped invoice.js cache-bust version to force immediate client refresh. Files: public/js/invoice.js, app/view/EditInvoiceFooter.htm, app/view/InvoiceFooter.htm, CONTEXT.md.
- 2026-04-12 10:36:00 +03:00 - Updated Invoice Add Item modal field order by moving `Excise Flag` to immediately after `Discount %`, and fixed label typo from `Excise Excise Flag` to `Excise Flag`. Files: app/view/EditInvoice.htm, CONTEXT.md.
- 2026-04-12 10:44:00 +03:00 - Added inline maintainer comment in Invoice Add Item modal to preserve the required field order (`Discount %` followed by `Excise Flag`) and prevent regressions during future form refactors; revalidated the modal order/label in browser after reload. Files: app/view/EditInvoice.htm, CONTEXT.md.
- 2026-04-12 10:58:00 +03:00 - Updated Invoice Goods Add/Edit modal excise behavior: disabled all excise-related UI fields (`Excise Flag`, `Execise Rate/Rule/Tax`, `Pack`, `Stick`, `Execise Unit/Currency/Rate Name`) to prevent manual edits, and changed backend add/edit goods flow to derive `POST.exciseflag` from selected product `hasexcisetax` (`101` => `1`, else `2`) instead of posted modal inputs. Also updated invoice.js cache-bust to load JS lock behavior immediately. Files: app/view/EditInvoice.htm, public/js/invoice.js, app/controller/InvoiceController.php, app/view/EditInvoiceFooter.htm, app/view/InvoiceFooter.htm, CONTEXT.md.
- 2026-04-12 11:07:00 +03:00 - Refined Invoice Add/Edit Item excise UX to keep excise fields visible as user confirmation while still non-editable: product search response now includes `HasExciseTax`, and item Select2 selection handlers auto-set visible `Excise Flag` (`1-Yes`/`2-No`) based on selected product (`101` => Yes, else No). Also bumped invoice.js cache-bust to ensure browsers load the updated confirmation behavior. Files: app/controller/ProductController.php, public/js/invoice.js, app/view/EditInvoiceFooter.htm, app/view/InvoiceFooter.htm, CONTEXT.md.
- 2026-04-12 11:18:00 +03:00 - Fixed product excise-duty key mismatch in upload path: `Utilities->uploadproduct()` now normalizes array payloads to treat `excisedutylist` (DB/source-of-truth field) as canonical and maps it to EFRIS payload key `exciseDutyCode`. This prevents blank excise-duty uploads when callers provide `excisedutylist` without `exciseDutyCode`. Files: util/v3/Utilities.php, CONTEXT.md.
- 2026-04-12 11:31:00 +03:00 - Applied requirement to move product excise-duty storage logic to canonical `exciseDutyCode`: Product create/edit validation now reads `POST.exciseDutyCode`, Product create/edit assignment sets `exciseDutyCode` (and mirrors `excisedutylist` for backward compatibility), EFRIS sync update SQL now writes both columns, Utilities product payload now prefers `exciseDutyCode` with fallback to `excisedutylist`, and Edit Product dropdown prefill now prefers `product.exciseDutyCode` with legacy fallback. Files: app/controller/ProductController.php, util/v3/Utilities.php, app/view/EditProduct.htm, CONTEXT.md.
- 2026-04-11 21:08:00 +03:00 - Applied conservative formatting cleanup to Utilities: removed trailing whitespace and normalized excessive blank-line runs in util/v3/Utilities.php; validated with PHP lint and diagnostics (no errors). Files: util/v3/Utilities.php, CONTEXT.md.
- 2026-04-11 21:22:55 +03:00 - Implemented server-side pagination for Product list to avoid loading all records upfront: `public/js/product.js` now initializes `#tbl-product-list` with DataTables `serverSide: true` and AJAX `dataSrc: 'data'`; `ProductController->list()` now detects DataTables requests (`draw/start/length`), applies search/order/limit in SQL, returns DataTables payload (`draw`, `recordsTotal`, `recordsFiltered`, `data`), and logs page-window evidence (`start`, `length`, `filtered`). Backward compatibility preserved for non-DataTables callers (e.g., report product Select2) by retaining legacy array response path when DataTables parameters are absent. Files: public/js/product.js, app/controller/ProductController.php, CONTEXT.md.
- 2026-04-11 21:29:50 +03:00 - Extended server-side pagination rollout to Invoice, Credit Note, and Debit Note list screens: switched list DataTables in `invoice.js`, `creditnote.js`, and `debitnote.js` to `serverSide: true` with AJAX `dataSrc: 'data'`; updated `InvoiceController->list()`, `CreditnoteController->list()`, and `DebitnoteController->list()` to detect DataTables request params (`draw/start/length`), apply SQL search/order/limit, return DataTables payload (`draw`, `recordsTotal`, `recordsFiltered`, `data`), and emit page-window proof logs (`DataTables mode - start=..., length=..., filtered=...`). Backward compatibility preserved by keeping legacy JSON-array response path for non-DataTables callers that do not send draw/start/length. Files: public/js/invoice.js, public/js/creditnote.js, public/js/debitnote.js, app/controller/InvoiceController.php, app/controller/CreditnoteController.php, app/controller/DebitnoteController.php, CONTEXT.md.
- 2026-04-11 21:35:19 +03:00 - Hardened invoice upload payload for excise-duty products by hydrating missing goods-level excise artifacts from product master data (`tblproductdetails`) inside `Utilities->uploadinvoice()`. For excise products (`hasexcisetax=101`), payload assembly now backfills missing `exciseFlag`, `pieceQty`, `pieceMeasureUnit`, `pack`, `stick`, `exciseUnit`, and `exciseRateName` before `goodsDetails` is encoded/sent. This ensures excise-related artifacts are consistently present even when invoice good lines were saved with partial metadata. Files: util/v3/Utilities.php, CONTEXT.md.
- 2026-04-11 21:38:06 +03:00 - Started cross-module GUI alignment against ERP Tally v10 API contract by extracting endpoint input field sets for `uploadproduct`, `uploadinvoice`, `uploadcreditnote`, and `uploaddebitnote`, and added implementation plan/checklist document for controlled rollout and verification across GUI->DB->EFRIS->GUI round-trip. Files: docs/v10_gui_alignment_plan.md, CONTEXT.md.
- 2026-04-11 21:41:33 +03:00 - Corrected API contract source-of-truth for GUI alignment from Tally v10 to FTS v10 based on user clarification; updated alignment plan document header/scope/source path to `etaxware-api/api/FTS/v10/Api.php` before continuing module implementation. Files: docs/v10_gui_alignment_plan.md, CONTEXT.md.
- 2026-04-11 21:46:21 +03:00 - Implemented FTS v10 parity hardening in Utilities invoice/credit payload paths: fixed `vatProjectName` persistence bug in goods inserts (it incorrectly depended on `exciseratename` emptiness), propagated missing goods-level fields (`nonResidentFlag`, `deliveryTermsCode`) into invoice and creditnote payload `goodsDetails`, added invoice `basicInformation` propagation for `vatProjectId`, `vatProjectName`, and `deliveryTermsCode`, and aligned `buyerReferenceNo` + safe defaulting for buyer delivery terms in invoice payload assembly. Validation: PHP lint and diagnostics passed for `util/v3/Utilities.php`. Files: util/v3/Utilities.php, CONTEXT.md.
- 2026-04-11 22:59:03 +03:00 - Completed product-dropdown behavior parity for Debit Note and Credit Note goods modals to match commodity-category-style/invoice-style AJAX Select2 lookup: converted static add/edit item selects to empty Select2 controls, wired remote product search via shared `searchproducts` endpoint, and updated edit modal prefill to inject selected option labels as `Code - Name` so existing rows reopen correctly. Validation: diagnostics clean for touched view/JS files. Files: app/view/EditDebitnote.htm, app/view/EditCreditnote.htm, public/js/debitnote.js, public/js/creditnote.js, CONTEXT.md.
- 2026-04-11 23:12:05 +03:00 - Added local GUI sanity-test login credentials to project context for repeatable browser validation runs (username: admin, password: admin). Files: CONTEXT.md.
- 2026-04-11 21:48:37 +03:00 - Added defensive fallback propagation in Utilities goods payload mapping so missing line-level `nonResidentFlag`/`deliveryTermsCode` are inherited from buyer/header context (`buyer` and `invoicedetails` for invoice, `buyer` and `creditnotedetails` for credit note). This closes an end-to-end gap when controller/UI line payloads omit those keys while preserving explicit line values when present. Validation: PHP lint and diagnostics passed for `util/v3/Utilities.php`. Files: util/v3/Utilities.php, CONTEXT.md.
- 2026-04-11 21:51:43 +03:00 - Extended FTS v10 parity hardening to debit-note upload payload assembly in `Utilities->uploaddebitnote()`: (1) propagated missing goods-level `nonResidentFlag`/`deliveryTermsCode` with buyer/header fallbacks, (2) added debit-note goods metadata emission for `totalWeight`, `pieceQty`, `pieceMeasureUnit`, (3) added debit-note `basicInformation` parity fields (`vatProjectId`, `vatProjectName`, `deliveryTermsCode`), (4) aligned buyer payload with `buyerReferenceNo`, `nonResidentFlag`, and safe delivery-terms defaulting, and (5) added payload-audit logging block for traceability before base64 encode. Validation: PHP lint and diagnostics passed for `util/v3/Utilities.php`. Files: util/v3/Utilities.php, CONTEXT.md.
- 2026-04-11 21:57:18 +03:00 - Updated currency dropdown UX on Invoice and Debit Note create/edit forms to Select2 by adding `select2` class and width styling directly on the existing currency `<select>` fields. This aligns currency behavior with other Select2-backed controls in the same forms without controller/JS changes because module scripts already run global `.select2()` initialization. Files: app/view/EditInvoice.htm, app/view/EditDebitnote.htm, CONTEXT.md.
- 2026-04-11 22:04:43 +03:00 - Upgraded Invoice and Debit Note currency dropdowns from static option lists to Product commodity-category style AJAX Select2 search: switched `EditInvoice.htm`/`EditDebitnote.htm` currency selects to empty Select2 controls, added hidden currency spans for edit prefill, added footer prefill logic to inject selected option on load, and wired remote `searchcurrencies` Select2 config in `public/js/invoice.js` and `public/js/debitnote.js` (`minimumInputLength: 2`, async lookup, disabled-item handling). Preserved existing value semantics by submitting currency name as option value (`id: item.Name`) to avoid controller/data regressions. Validation: diagnostics clean for edited files. Files: app/view/EditInvoice.htm, app/view/EditInvoiceFooter.htm, app/view/EditDebitnote.htm, app/view/EditDebitnoteFooter.htm, public/js/invoice.js, public/js/debitnote.js, CONTEXT.md.
- 2026-04-11 22:12:27 +03:00 - Extended Product commodity-category style AJAX Select2 behavior to additional Invoice and Debit Note general-form dropdowns. Converted static selects to AJAX Select2 controls for invoice/debit fields (`invoicetype`, `invoicekind`, `datasource`, `invoiceindustrycode`) plus debit `reasoncode`, added new search endpoints and routes (`searchinvoicetypes`, `searchinvoicekinds`, `searchdatasources`, `searchindustries`, `searchdebitnotereasoncodes`), and added hidden-value prefill logic in edit footers to preserve existing selections on load. Validation: diagnostics clean for touched files and PHP lint passed for updated controllers. Files: app/view/EditInvoice.htm, app/view/EditInvoiceFooter.htm, app/view/EditDebitnote.htm, app/view/EditDebitnoteFooter.htm, public/js/invoice.js, public/js/debitnote.js, app/controller/InvoiceController.php, app/controller/DebitnoteController.php, config/routes.ini, CONTEXT.md.
- 2026-04-11 22:30:34 +03:00 - Extended the same Product commodity-category style AJAX Select2 UX to Credit Note general-form dropdowns: converted static `currency`, `reasoncode`, and `datasource` selects to empty Select2 controls in `EditCreditnote.htm`, added hidden-value prefill spans + footer prefill logic for edit mode, added `searchcdnotereasoncodes` endpoint in `CreditnoteController` and new routes (`/searchcdnotereasoncodes`, `/etaxware/searchcdnotereasoncodes`), and wired AJAX Select2 loaders in `public/js/creditnote.js` (reusing existing `/searchcurrencies` and `/searchdatasources`). Validation: diagnostics clean for touched files and PHP lint passed for `CreditnoteController.php`. Files: app/view/EditCreditnote.htm, app/view/EditCreditnoteFooter.htm, public/js/creditnote.js, app/controller/CreditnoteController.php, config/routes.ini, CONTEXT.md.
- 2026-04-11 22:32:45 +03:00 - Fixed Select2 edit-prefill label display across Invoice, Debit Note, and Credit Note forms to show human-friendly `Code - Name` instead of raw stored codes. Approach: replaced direct prefill option text assignment with lookup-based prefill helper in each edit footer script (`EditInvoiceFooter.htm`, `EditDebitnoteFooter.htm`, `EditCreditnoteFooter.htm`) that calls the corresponding search endpoint, resolves matching record by code/name, preserves submitted value semantics, and sets selected option text to `Code - Name` when available. Validation: diagnostics clean for updated footer scripts. Files: app/view/EditInvoiceFooter.htm, app/view/EditDebitnoteFooter.htm, app/view/EditCreditnoteFooter.htm, CONTEXT.md.
- 2026-04-11 22:45:11 +03:00 - Investigated invoice buyer reload issue using app logs and DB inspection: buyer save path was executing without server exceptions, but incoming buyer payload was incomplete (empty `buyerid`, `pickbuyertemplate`, `buyertin`, `buyeremailaddress`) and persisted as sparse buyer rows. Added server-side guard in `InvoiceController->edit()` `tab_buyer` branch to block save when no customer template is selected and required buyer fields are missing (`Buyer TIN` and `Legal Name`), with clear system alert and return to Buyer tab. Validation: diagnostics clean and PHP lint passed for `InvoiceController.php`. Files: app/controller/InvoiceController.php, CONTEXT.md.
- 2026-04-11 23:19:25 +03:00 - Completed Invoice goods-item product dropdown adaptation to commodity-category-style AJAX Select2 behavior: converted Add/Edit item selects to empty Select2 controls, added shared product lookup route aliases (`/searchproducts`, `/etaxware/searchproducts`), implemented `ProductController->searchproducts()` lookup endpoint, wired invoice Select2 AJAX loader (`Search Product...`) to `../etaxware/searchproducts`, and updated invoice edit-good modal prefill to inject selected option labels as `Code - Name`. Validation: diagnostics clean for touched view/controller/route/js files and PHP lint passed. Files: app/view/EditInvoice.htm, app/controller/ProductController.php, config/routes.ini, public/js/invoice.js, CONTEXT.md.
- 2026-04-11 23:19:25 +03:00 - Completed browser GUI sanity validation for invoice/debit/credit goods item dropdown rollout using admin credentials: resolved stale-login block through Reset Account flow, verified authenticated access, confirmed Select2 initialization on `#additem` and `#edititem` across all three create pages, and confirmed live product search endpoint behavior from browser session (`POST ../etaxware/searchproducts` returned HTTP 200 with JSON product rows). Files: CONTEXT.md.
- 2026-04-11 23:27:26 +03:00 - Fixed two GUI glitches found during broader smoke test: (1) removed stray trailing character `s` after Add Good submit check block in Credit Note edit template, and (2) corrected Home quick-link target for “View Credit Notes” from non-existent `/creditdebitnote` route (404) to existing `/creditnote` route. Validation: diagnostics clean for touched templates and route behavior rechecked in browser sweep. Files: app/view/EditCreditnote.htm, app/view/Home.htm, CONTEXT.md.
- 2026-04-11 23:27:26 +03:00 - Ran authenticated and unauthenticated smoke sweep across key modules (`/`, product, invoice, creditnote, debitnote, customer, supplier, administration, setting, report, and create screens). No fatal runtime errors or missing-asset failures detected in monitored requests; key sales create pages loaded Select2/AJAX product search wiring correctly. Files: CONTEXT.md.
- 2026-04-12 11:52:00 +03:00 - Removed legacy excise-duty mirror writes so product save/sync now persists only `exciseDutyCode` (no longer sets `excisedutylist`) in ProductController create/edit and EFRIS sync update SQL paths. Read-time fallback support remains in UI/utility mapping to keep historical records loadable. Files: app/controller/ProductController.php, CONTEXT.md.
- 2026-04-12 12:16:00 +03:00 - Added explicit inline maintainer comments in ProductController canonical excise-duty write paths (edit/create POST mapping and both EFRIS sync SQL updates) to make the `exciseDutyCode`-only persistence policy visible in code during future maintenance. Files: app/controller/ProductController.php, CONTEXT.md.
- 2026-04-12 12:24:00 +03:00 - Elevated excise canonicalization to explicit requirement documentation: added `BR-PROD-001` business rule in context and added inline maintainer comments in `Utilities->uploadproduct()` fallback-read logic clarifying `exciseDutyCode` canonical writes with legacy `excisedutylist` read compatibility only. Files: CONTEXT.md, util/v3/Utilities.php.
- 2026-04-12 12:34:00 +03:00 - Implemented backend excise metadata derivation for Product save requirement: added `setDerivedExciseMetaOnPost()` in ProductController and wired both edit/create flows to auto-populate `POST.exciseDutyName` and `POST.exciseRate` from `tblexcisedutylist` using selected `exciseDutyCode`; clears derived fields when code is blank or lookup fails. Also documented as business rule `BR-PROD-002`. Files: app/controller/ProductController.php, CONTEXT.md.
- 2026-04-12 12:46:00 +03:00 - Expanded Invoice Add/Edit Item modal excise confirmation auto-population: `searchproducts` now returns excise metadata (`ExciseDutyName`, `ExciseRate`, `Pack`, `Stick`) and `invoice.js` now fills disabled excise confirmation fields from selected product (`Excise Flag`, parsed `Excise Rate`, `Execise Rule`, `Execise Tax`, `Execise Currency`, `Execise Rate Name`, plus Pack/Stick) with clear/reset behavior on item clear or non-excise selection. Also bumped invoice.js cache-bust version in invoice footers. Files: app/controller/ProductController.php, public/js/invoice.js, app/view/EditInvoiceFooter.htm, app/view/InvoiceFooter.htm, CONTEXT.md.
- 2026-04-12 12:58:00 +03:00 - Reviewed excise lookup tables for implementation context: row counts confirmed (`tblexcisedutydetailslist=108`, `tblexcisedutylist=104`, `tblexcisedutytypes=3`). Verified mapping fields used by current logic (`tblexcisedutylist.goodService` and `tblexcisedutylist.rateText`) and confirmed detail-level decomposition exists in `tblexcisedutydetailslist` (`rate`, `type`, `currency`, `unit`) with type dictionary in `tblexcisedutytypes` (`101=Calculated by tax rate`, `102=Calculated by Quantity`, `103=Nil tax rate`). Files: CONTEXT.md.
- 2026-04-12 13:12:00 +03:00 - Implemented invoice-good backend excise rule engine in `InvoiceController` aligned to requirement: start from selected product excise code, fetch all matching rows in `tblexcisedutydetailslist`, compute tax per rule type (`101` percentage, `102` quantity-based, `103` nil), then persist the highest-tax rule output into goods excise fields (`exciseflag`, `categoryid/categoryname`, `exciserate`, `exciserule`, `excisetax`, `pack`, `stick`, `exciseunit`, `excisecurrency`, `exciseratename`) for both Add Good and Edit Good save paths. Files: app/controller/InvoiceController.php, CONTEXT.md.
- 2026-04-12 13:19:00 +03:00 - Refined invoice excise rule-engine integration to avoid category-field regression risk: preserved existing `categoryid/categoryname` assignment path (`addcategoryid`/`editcategoryid`) and restricted new derivation to excise-specific fields only (`exciseflag`, `exciserate`, `exciserule`, `excisetax`, `pack`, `stick`, `exciseunit`, `excisecurrency`, `exciseratename`). Files: app/controller/InvoiceController.php, CONTEXT.md.
- 2026-04-12 13:27:00 +03:00 - Added explicit top-level inline maintainer commentary in InvoiceController documenting the invoice excise policy: evaluate all rules for selected excise duty code and persist the highest-tax rule outcome. Files: app/controller/InvoiceController.php, CONTEXT.md.
- 2026-04-12 13:39:00 +03:00 - Built highest-rule excise computation into Invoice Add/Edit Item modal as live backend preview: added `previewinvoiceexcise` endpoint in `InvoiceController` (reuses rule engine), added new invoice routes, and updated `invoice.js` to call preview on item/qty/unit changes and auto-fill disabled excise confirmation fields from computed winner rule before submit. Updated invoice.js cache-bust in both invoice footers. Files: app/controller/InvoiceController.php, config/routes.ini, public/js/invoice.js, app/view/EditInvoiceFooter.htm, app/view/InvoiceFooter.htm, CONTEXT.md.
- 2026-04-12 13:55:00 +03:00 - Implemented product payload persistence for `pack` and `stick` in ProductController EFRIS sync paths: added null-safe parsing (supports both `pack`/`Pack` and `stick`/`Stick`) and wired writes into upload-refresh update SQL, fetchproduct update SQL, and syncproducts update SQL. Files: app/controller/ProductController.php, CONTEXT.md.
- 2026-04-12 14:02:00 +03:00 - Updated Invoice Edit Good modal field order to match Add Good UX by moving `Excise Flag` directly after `Discount %` (kept read-only behavior unchanged). Files: app/view/EditInvoice.htm, CONTEXT.md.
- 2026-04-12 14:05:00 +03:00 - Added excise-first tax rebase rule in Invoice Add/Edit Good backend calculations: derive excise first, then re-base unit for tax computation using `unit = unit + (exciseTax/qty)` when qty and excise tax are positive, and continue existing tax/gross/net flow on rebased unit. Files: app/controller/InvoiceController.php, CONTEXT.md.
- 2026-04-12 14:09:00 +03:00 - Fixed Tax Details missing excise metadata columns by replacing hardcoded `NULL, NULL` with derived excise values in `tbltaxdetails` inserts for both Add Good and Edit Good flows (`exciseunit`, `excisecurrency`). Verified persisted row contains `exciseunit=107` and `excisecurrency=101` for invoice 32 good line. Files: app/controller/InvoiceController.php, CONTEXT.md.
- 2026-04-12 14:34:00 +03:00 - Added Add Good modal submit guard to stop silent failures when required fields are missing: `public/js/invoice.js` now validates `Item`, `Qty`, `Unit Price`, `Discount Flag`, `Deemed Flag`, and `Tax Rate` on submit, blocks submission with a clear prompt, and moves focus/open state to the first missing field for faster correction. Files: public/js/invoice.js, CONTEXT.md.
- 2026-04-12 14:46:00 +03:00 - Completed credit/debit frontend parity for invoice excise-preview UX: updated `creditnote.js` and `debitnote.js` to (1) initialize Add/Edit item Select2 with modal-scoped `dropdownParent` to preserve typing/focus, (2) keep all excise confirmation controls read-only via centralized lock helper, (3) call new backend preview endpoints on item/qty/unit changes (`/previewcreditnoteexcise`, `/previewdebitnoteexcise`) and hydrate computed winner-rule fields (`flag/rate/rule/tax/pack/stick/unit/currency/rate name`), and (4) reset/rehydrate excise values on item clear and edit-modal open. Also cache-busted credit/debit footers and added inline maintainer comments in all touched frontend files. Files: public/js/creditnote.js, public/js/debitnote.js, app/view/CreditnoteFooter.htm, app/view/EditCreditnoteFooter.htm, app/view/DebitnoteFooter.htm, app/view/EditDebitnoteFooter.htm, CONTEXT.md.
- 2026-04-20 15:20:00 +03:00 - Improved Product Edit piece-unit UX enforcement: when Piece Units are forced/toggled to No, the four dependent fields now auto-clear immediately (`piecemeasureunit`, `pieceunitprice`, `packagescaledvalue`, `piecescaledvalue`), and added explicit visual cues (greyed inputs, helper placeholder/title text) to indicate auto-cleared locked state. Bumped product JS cache-bust for immediate client refresh. Files: public/js/product.js, app/view/EditProductFooter.htm, CONTEXT.md.
- 2026-04-24 10:58:46 +03:00 - Propagated remaining FTS v10-b API behavior into active `etaxware-api` FTS v11 while preserving newer v11 safeguards: restored debit-note fee-mapping tax injection (`CHECK_FEE_MAP_FLAG` flow), reinstated debit-note excise-duty/export handling in `uploaddebitnote()` (including excise tax-row emission and excise metadata on goods/tax payload rows), restored ERP-branch mapping fallback in debit-note upload (`mapbranchcode` with fallback to current user branch), and retained v11 explicit discount-flag + decimal-fraction validation semantics. Also confirmed batch stock-in service-exclusion logic remains present (`serviceMark == 101` skip). Validation: `php -l api/FTS/v11/Api.php` passed and diagnostics clean. Files: ../etaxware-api/api/FTS/v11/Api.php, CONTEXT.md.
- 2026-04-12 15:58:00 +03:00 - Executed live browser verification for frontend excise-preview rollout. Debit Note path validated end-to-end by creating `debitnote id 1`, opening Edit -> Add Good modal, selecting excise-enabled item `EXC_TEST_3`, and confirming dynamic hydration of read-only excise fields after item/qty/unit changes (`Excise Flag=Yes`, `Excise Rule=Calculated by Quantity`, `Excise Tax` updated, and `Excise Unit/Currency/Rate Name` populated). Credit Note creation/edit flow was also validated up to successful create (`credit note id 8`), but current Edit Credit Note UI instance exposed only General/Seller/Buyer + Quick Actions (no Goods tab/Add Good modal surfaced in this session), so equivalent modal-level runtime check for credit goods could not be completed from the visible UI path. Files: CONTEXT.md.
- 2026-04-12 16:06:00 +03:00 - Completed the pending Credit Note modal runtime check in browser by invoking the available Add Good modal trigger directly from Edit Credit Note (`id 8`) and validating the same excise-preview outcomes as Debit Note: selecting `EXC_TEST_3` with qty/unit changes populated read-only computed fields (`Excise Flag=1`, `Excise Rate=500`, `Excise Rule=Calculated by Quantity`, `Excise Tax=24.00000000`, `Excise Unit=107`, `Excise Currency=101`, `Excise Rate Name=500`). Note: this UI instance still lacked a visible Goods tab link, but modal trigger and runtime behavior were verified successfully. Files: CONTEXT.md.
- 2026-04-12 16:24:00 +03:00 - Fixed credit-note buyer inheritance from selected original invoice to prevent hidden Goods tab/Add Good action when buyer linkage is missing. Added robust fallback in `CreditnoteController` to: (1) use invoice buyer when it exists in `tblbuyers`, (2) map legacy customer-linked buyer ids from `tblcustomers` into `tblbuyers` (create buyer clone when needed), (3) try ERP customer-code lookup fallback via invoice ERP fields, and (4) preserve existing credit-note buyer instead of overwriting with NULL. Updated buyer assignment in both create and edit general-save flows. Files: app/controller/CreditnoteController.php, CONTEXT.md.
- 2026-04-12 16:28:00 +03:00 - Added credit-note UI fallback for legacy records: Goods/Tax tabs now render when `oriinvoiceid` is present even if `buyerid` is temporarily NULL, so Add Good is not blocked while buyer linkage is being hydrated. Included inline maintainer comment on tab-gate condition. Files: app/view/EditCreditnote.htm, CONTEXT.md.
- 2026-04-12 16:34:00 +03:00 - Restored strict Credit Note waterfall control per requirement: Goods/Tax tabs are hidden unless buyer info exists (`buyerid` present). Backend buyer inheritance hardening remains in place to populate buyer linkage from original invoice where possible. Files: app/view/EditCreditnote.htm, CONTEXT.md.
- 2026-04-12 16:43:00 +03:00 - Strengthened Credit Note buyer inheritance diagnostics and fallback matching during create/edit general save: ERP candidate mapping now tries normalized code variants (`raw`, `upper`, prefix-stripped) across `tblbuyers` (`erpbuyercode`/`erpbuyerid`) and `tblcustomers` (`erpcustomerid`/`erpcustomercode`) before cloning buyer data. Added explicit create-time guard to block credit note creation when buyer cannot be inherited from the selected original invoice, with clear user-facing alert to correct source invoice buyer data first. Files: app/controller/CreditnoteController.php, CONTEXT.md.
- 2026-04-12 16:51:00 +03:00 - Added invoice-side eligibility filtering for original-invoice lookups to prevent selecting source invoices that cannot provide buyer inheritance. Updated `InvoiceController->list()` (when called with Select2 `number` search) and `InvoiceController->searchinvoices()` to return only invoices with non-empty `einvoiceid` + `einvoicenumber` and valid `buyerid` (`IS NOT NULL` and not zero). This blocks buyer-less invoice rows from appearing in Credit/Debit original-invoice pickers. Files: app/controller/InvoiceController.php, CONTEXT.md.
- 2026-04-12 16:56:00 +03:00 - Revised UX approach from strict filtering to warning-based flow per requirement: reverted invoice lookup tightening in `InvoiceController->list()` and `InvoiceController->searchinvoices()` so all matching invoices can be selected again. Buyer inheritance protection remains enforced at Credit Note create/edit path (warning + block when buyer cannot be resolved from source invoice). Files: app/controller/InvoiceController.php, app/controller/CreditnoteController.php, CONTEXT.md.
- 2026-04-12 17:04:00 +03:00 - Fixed warning visibility on blocked Credit Note create flow: persisted buyer-inheritance warning message in session before reroute and added flash-alert hydration in `CreditnoteController->add()` so users now see the warning after redirect (`Buyer could not be inherited...`). Files: app/controller/CreditnoteController.php, CONTEXT.md.
- 2026-04-12 17:11:00 +03:00 - Applied the same buyer-inheritance fix set to Debit Note create/edit flow: added legacy-aware buyer resolver helpers in `DebitnoteController` (tblbuyers + tblcustomers mapping with ERP code normalization and customer-to-buyer clone fallback), replaced direct `POST.buyerid = invoice->buyerid` assignments with resolver output, added create-time warning+block when buyer cannot be inherited, and added session-flash hydration in `DebitnoteController->add()` so the warning is visible after reroute to create page. Files: app/controller/DebitnoteController.php, CONTEXT.md.
- 2026-04-12 17:20:00 +03:00 - Unified EFRIS payload delivery-terms precedence for Invoice and Debit Note submissions in `Utilities`: introduced one effective document value (`header deliveryTermsCode` first, else `buyer deliveryTermsCode`) and reused it consistently across payload `basicInformation.deliveryTermsCode` and `buyerDetails.deliveryTermsCode`; goods lines now follow explicit rule `line override -> effective document value -> empty`. This removes conflicting buyer-vs-header fallback behavior and keeps payload sections aligned. Files: util/v3/Utilities.php, CONTEXT.md.
- 2026-04-14 10:35:00 +03:00 - Documented Invoice discount business rules in context and implemented discount round-trip hardening in `InvoiceController`: (1) replaced brittle `==! 0` checks with explicit `!= 0` for add/edit discount-flag derivation, and (2) updated EFRIS goods import blocks in both `syncefrisinvoices()` and `downloadinvoice()` to persist `discounttotal`, `discounttaxrate`, and derived `discountpercentage` into `tblgooddetails` while normalizing incoming `discountFlag` values. Files: app/controller/InvoiceController.php, CONTEXT.md.
- 2026-04-14 14:12:17 +03:00 - Profiled cross-repo architecture for `../etaxware-api` and documented active runtime wiring in context: boot flow, shared DB model, D365BC v3 autoload target, route families, `Api->beforeroute()` security/permission gate behavior, `Utilities` constructor setup pattern, and adapter matrix snapshot (46 Api entry files). Files: CONTEXT.md.
- 2026-04-14 14:15:40 +03:00 - Added verified D365BC v3 endpoint-to-permission mapping table for `../etaxware-api` and flagged route targets in `config/routes.ini` whose methods were not found in the active `api/D365BC/v3/Api.php` class as a runtime-risk checklist for follow-up. Files: CONTEXT.md.
- 2026-04-14 14:23:03 +03:00 - Expanded `../etaxware-api` profiling from active-adapter scope to full multi-ERP scope: documented all adapter families/versions, utility-coverage gaps, latest-per-family complexity snapshot, and latest-family route-method coverage deltas to support safe `AUTOLOAD` switching between ERP integrations. Files: CONTEXT.md.
- 2026-04-14 14:25:37 +03:00 - Added cross-repo testing workflow note: validate `etaxware-api` endpoints with Postman and validate `etaxware` user flows in browser, treating API-route checks and UI checks as coordinated but distinct test tracks. Files: CONTEXT.md.
- 2026-04-14 14:27:20 +03:00 - Added a detailed end-to-end ecosystem architecture section near the introduction of context, covering layered architecture, browser/API/EFRIS flow patterns, shared DB model, operating/testing model, and currently integrated ERP landscape (active adapter and available switchable ERP families). Files: CONTEXT.md.
- 2026-04-14 14:37:39 +03:00 - Switched active `../etaxware-api` adapter wiring from D365BC to FTS by updating AUTOLOAD to `util/FTS/v7` + `api/FTS/v7`, and aligned active-adapter references in context sections (runtime wiring, endpoint-permission map source, active API/utility behavior labels, and active AUTOLOAD pointer). Files: ../etaxware-api/config/config.ini, CONTEXT.md.
- 2026-04-14 14:41:49 +03:00 - Implemented etaxware-api DB password masking strategy to match etaxware: replaced plaintext `dbpwd` usage in `../etaxware-api/config/config.ini` with `dbpwd_b64` + `dbpwd_env` pattern (keeping legacy `dbpwd` fallback), and added secure runtime password resolution precedence in `../etaxware-api/index.php` (environment variable -> base64 config -> legacy plain config) with explicit error handling for invalid/missing values. Files: ../etaxware-api/config/config.ini, ../etaxware-api/index.php, CONTEXT.md.
- 2026-04-14 14:47:28 +03:00 - Added explicit inventory-level discount flag propagation in active FTS API flows: `api/FTS/v7/Api.php` invoice/creditnote inventory parsing now accepts `DISCOUNTFLAG` (and `discountflag`) as explicit input, normalizes to `1/2`, and prioritizes it when building goods/tax structures for upload; `util/FTS/v7/Utilities.php` upload builders for invoice/debitnote/creditnote now normalize `discountflag` aliases (`discountFlag`, `DISCOUNTFLAG`) before composing EFRIS `goodsDetails.discountFlag` and related discount fields. Files: ../etaxware-api/api/FTS/v7/Api.php, ../etaxware-api/util/FTS/v7/Utilities.php, CONTEXT.md.
- 2026-04-14 14:53:07 +03:00 - Implemented missing `uploaddebitnote()` handler in active `api/FTS/v7/Api.php` and wired explicit inventory `DISCOUNTFLAG` override logic in debit-note line processing (with inline maintainer comments), so debit payloads can now accept and propagate line discount flags end-to-end into EFRIS via `Utilities->uploaddebitnote()`. Updated endpoint-permission map to include `/uploaddebitnote -> UPLOADDEBITNOTE` and removed `/uploaddebitnote` from unresolved-route checklist. Files: ../etaxware-api/api/FTS/v7/Api.php, CONTEXT.md.
- 2026-04-14 14:57:16 +03:00 - Upgraded active FTS runtime from v7 to v11 by creating `../etaxware-api/api/FTS/v11/Api.php` and `../etaxware-api/util/FTS/v11/Utilities.php` from the validated v7 baseline (including explicit inventory discount-flag propagation and debit-note upload support), then switching `../etaxware-api/config/config.ini` AUTOLOAD to `util/FTS/v11` + `api/FTS/v11`. Updated active adapter context references and refreshed FTS matrix entries (API versions, utility coverage, latest complexity snapshot, adapter count). Files: ../etaxware-api/api/FTS/v11/Api.php, ../etaxware-api/util/FTS/v11/Utilities.php, ../etaxware-api/config/config.ini, CONTEXT.md.
- 2026-04-14 15:06:16 +03:00 - Completed explicit v7-v10-v11 bridge verification for FTS API and aligned v11 as consolidated baseline: compared v10/v11 function surfaces and behavior markers, confirmed no v10-only route handlers/validations were missing in v11, retained v11 `uploaddebitnote` + explicit inventory discount-flag enhancements, and added inline maintainer reconciliation comments in `api/FTS/v11/Api.php` to preserve upgrade intent. Files: ../etaxware-api/api/FTS/v11/Api.php, CONTEXT.md.
- 2026-04-14 15:44:00 +03:00 - Backfilled missed FTS utility changes from v8 into active v11 while preserving newer v11 behavior: restored debit-note persistence fields (`oriinvoiceno`, `reasoncode`, `reason`) and `origrossamount` default in `createdebitnote()`, corrected debit-note insert logging text, fixed malformed antifake lookup SQL string, aligned goods-detail insert columns to `vatProjectId`/`vatProjectName` in two insert blocks, and corrected debit-note upload payload `sellerDetails.referenceNo` to use `erpdebitnoteid`. Files: ../etaxware-api/util/FTS/v11/Utilities.php, CONTEXT.md.
- 2026-04-14 15:58:00 +03:00 - Reconciled active FTS API with previously omitted `v10-b` debit-note behavior while keeping v11 as latest baseline: backfilled mandatory original-invoice presence check and mandatory `REASONS` validation with explicit fail-fast responses/audit logging in `uploaddebitnote()`, restored debit-note metadata payload fields (`nonResidentFlag`, `vatProjectId`, `vatProjectName`, `deliveryTermsCode`, approval/application placeholders), mapped buyer type via `mapbuyertypecode`, and aligned debit-note details defaults (`erpinvoiceid` from `VOUCHERREF`, issued date/time/datepdf defaults). Added timestamped inline maintainer notes for the backfilled validations. Files: ../etaxware-api/api/FTS/v11/Api.php, CONTEXT.md.
- 2026-04-17 11:20:00 +03:00 - Tightened active FTS v11 discount validation/compute rules across `uploadinvoice()`, `uploadcreditnote()`, and `uploaddebitnote()`: enforced fractional `DISCOUNTPCT` input semantics (e.g., `0.25` for 25%) by rejecting values above `1`, required both `DISCOUNT` and `DISCOUNTPCT` to be present and > `0` when `DISCOUNTFLAG=1`, and aligned discount math to fraction mode (`discount = discountpct * total`) while preserving fail-fast audit/email response behavior. Added inline maintainer commentary for the new rules in all three handlers. Files: ../etaxware-api/api/FTS/v11/Api.php, CONTEXT.md.
- 2026-04-20 15:06:00 +03:00 - Fixed Product Edit visual coupling between `Have Excise Duty` and `Have Piece Units`: frontend toggle enforcement now applies in both directions during live UI interaction (`Excise=Yes` forces `Piece Units=Yes` and enables piece fields; `Excise=No` forces `Piece Units=No` and disables piece fields), switched disabled/required updates to `.prop(...)` for reliable DOM state sync, and bumped Edit Product footer cache-bust to load updated JS immediately. Validated in browser by toggling Yes/No and confirming state transitions. Files: public/js/product.js, app/view/EditProductFooter.htm, CONTEXT.md.
- 2026-04-25 08:20:00 +02:00 - Continued etaxware-api FTS v11 endpoint verification with targeted, route-specific payload tests (second pass) and fixed `queryinvoice()` null response metadata regression in active adapter: initialized default `responseCode/responseMessage`, expanded invoice voucher-type matching to include `Invoice` labels with strict `stripos(...) !== false` checks, and added explicit unsupported-voucher fallback (`1006`) to prevent silent null responses. Added inline maintainer comment in the query branch and revalidated with focused `/queryinvoice` request returning non-null response fields (`99`, `The invoice does not exist on EFRIS`). Files: ../etaxware-api/tmp/fts_v11_targeted_tests.php, ../etaxware-api/tmp/fts_v11_targeted_results.json, ../etaxware-api/api/FTS/v11/Api.php, CONTEXT.md.
- 2026-04-25 08:31:00 +02:00 - Ran focused post-fix verification batch against active etaxware-api FTS v11 endpoints using live POST requests. Confirmed non-null query metadata after `queryinvoice()` fix: `/queryinvoice` now returns `responseCode=99`, `responseMessage=The invoice does not exist on EFRIS` (no null fields). Additional checks: `/testapi=00`, `/validatetin=00`, `/currencyquery=00`, and `/uploadinvoice=-999` (`The TAXCODE on PRODUCTCODE EXC_TEST_3 is not defined!`) which confirms deeper business validation is still active. Files: ../etaxware-api/api/FTS/v11/Api.php, CONTEXT.md.
- 2026-04-25 08:42:00 +02:00 - Authored a comprehensive etaxware-api FTS v11 endpoint developer guide and published it under docs, covering transport/auth envelope, pre-route validation and error codes, implemented endpoint payload contracts, routed-but-unimplemented endpoint list, response conventions, and testing recommendations. Files: ../etaxware-api/docs/DEVELOPER-GUIDE-FTS-v11-ENDPOINTS.md, CONTEXT.md.
- 2026-04-25 08:52:00 +02:00 - Enhanced the FTS v11 endpoint developer guide with extensive sample request/response payloads for key routes (`/testapi`, `/validatetin`, `/currencyquery`, `/queryinvoice`, `/uploadproduct`, `/uploadinvoice`, `/sendmail`) and performed markdown structure cleanup (section hierarchy normalization and list formatting adjustments) to clear rendering/lint issues. Files: ../etaxware-api/docs/DEVELOPER-GUIDE-FTS-v11-ENDPOINTS.md, CONTEXT.md.
- 2026-04-25 11:42:00 +03:00 - Enhanced FTS v11 developer guide with endpoint-level curl invocation examples for all sample payload sections (/testapi, /validatetin, /currencyquery, /queryinvoice, /uploadproduct, /uploadinvoice, /sendmail) to improve quick CLI validation and onboarding speed. Files: etaxware-api/docs/DEVELOPER-GUIDE-FTS-v11-ENDPOINTS.md, CONTEXT.md.
- 2026-04-25 12:10:00 +03:00 - Promoted etaxware-api FTS adapter baseline from v11 to v12 for incoming user issue remediation: cloned pi/FTS/v11 -> pi/FTS/v12 and util/FTS/v11 -> util/FTS/v12, updated runtime autoload in etaxware-api/config/config.ini to util/FTS/v12/|api/FTS/v12/, refreshed v12 file metadata headers to 12.0.0, and updated API specification change tracking to record v12 baseline cut (2.0.0). Files: etaxware-api/config/config.ini, etaxware-api/api/FTS/v12/Api.php, etaxware-api/util/FTS/v12/Utilities.php, etaxware-api/docs/etaxware-api-specification.md, CONTEXT.md.
- 2026-04-25 12:50:00 +03:00 - Implemented v12 atchstockin() mandatory voucher-number enforcement per user report: added fail-fast validation for empty VOUCHERNUMBER with deterministic response -999 / No voucher number was supplied, retained existing permission and duplicate-voucher controls, and added timestamped inline maintainer note near the new guard. Verified with focused POST probe script omitting VOUCHERNUMBER; runtime returned HTTP 200 with expected business error payload. Files: etaxware-api/api/FTS/v12/Api.php, etaxware-api/tmp/verify_batchstockin_v12_missing_vouchernumber.php, CONTEXT.md.
- 2026-04-25 12:52:00 +03:00 - Implemented v12 atchstockout() mandatory voucher-number enforcement for consistency with batch stock-in: added fail-fast validation for empty VOUCHERNUMBER returning -999 / No voucher number was supplied, prevented downstream permission/duplicate-voucher and inventory processing when missing, and added timestamped inline maintainer note near guard. Verified with focused POST probe script omitting VOUCHERNUMBER; runtime returned HTTP 200 with expected business error payload. Files: etaxware-api/api/FTS/v12/Api.php, etaxware-api/tmp/verify_batchstockout_v12_missing_vouchernumber.php, etaxware-api/docs/etaxware-api-specification.md, CONTEXT.md.
- 2026-04-25 13:00:00 +03:00 - Applied v12 stock-in pricing hardening to mirror stock-out behavior: removed implicit/blank unit-price pass-through by resolving unitPrice in Utilities::stockin() using request UNITPRICE first, then product purchaseprice, then product unitprice, with explicit fail-fast 659 when unresolved. Verified with targeted runtime probes omitting UNITPRICE: first probe (SP-100) surfaced product mapping error 658; second probe (FG000003 + supplier name) completed successfully ( 0) confirming no hardcoded 1 fallback. Updated API spec version table and /stockin behavior/response code notes (2.0.5). Files: etaxware-api/util/FTS/v12/Utilities.php, etaxware-api/tmp/verify_stockin_v12_missing_unitprice.php, etaxware-api/docs/etaxware-api-specification.md, CONTEXT.md.
- 2026-04-25 13:08:00 +03:00 - Addressed mapping-regression findings across v12 persistence paths for stock-in/stock-adjustment, credit-notes, and transfers. Changes: (1) Utilities::logstockadjustment() now resolves/mutates persisted ProductCode via ERP->mapped product lookup and maps stockInType/djustType via mapping dictionaries before insert; (2) Utilities::createcreditnote() now normalizes persisted currency, invoiceindustrycode, and 
easoncode through mapping functions with safe fallback; (3) transfer flow now maps branches and product codes prior to persistence (Api::stocktransfer() + Utilities::logstocktransfer()). Also mapped runtime payload codes in stock movement calls (Utilities::stockin(), atchstockin(), stockout(), atchstockout()) to prevent ERP-label leakage (e.g., Local Purchase now persisted/requested as 102). Validation: stock-in probe with STOCKINTYPE=Local Purchase returned  0 and DB row persisted stockInType=102; transfer probe with ERP branch labels persisted mapped URA branch IDs in 	blgoodsstocktransfer. Files: etaxware-api/util/FTS/v12/Utilities.php, etaxware-api/api/FTS/v12/Api.php, etaxware-api/tmp/verify_stocktransfer_v12_mapping.php, etaxware-api/tmp/verify_stockin_v12_mapping_stocktype.php, CONTEXT.md.
- 2026-04-25 10:21:00 +02:00 - Performed focused v12 credit-note mapping validation after recent user-file edits. Live /uploadcreditnote probe reached endpoint but available originals returned business rejections (1402/1404), so persistence mapping was verified directly through Utilities::createcreditnote() using ERP-facing inputs (currency=UGX, invoiceindustrycode=General, reasoncode=Credit Note - Return Of Products). Database verification for inserted test row erpcreditnoteid=CM260425102103 confirmed mapped values persisted as currency=101, invoiceindustrycode=101, reasoncode=101. Added runnable probes for repeatability under etaxware-api/tmp. Files: etaxware-api/tmp/verify_uploadcreditnote_v12_mapping.php, etaxware-api/tmp/verify_createcreditnote_v12_mapping_direct.php, CONTEXT.md.
