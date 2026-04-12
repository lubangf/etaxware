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

## API Layer Details

API namespace path: api/e-taxware.

Observed characteristics in v5 Api.php:

- Uses PHPMailer (SMTP), QuickBooks SDK facades, and DataService
- Contains email dispatch operations and business/API orchestration
- Holds app setting state, caller context, permissions, and integration flags
- Uses JSON payloads in several endpoints

API versions present (v1-v7) indicate incremental evolution, but autoload wiring favors v5 in current runtime.

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

- app.log and dated variants
- util.log and dated variants
- error log files

Log writers:

- MainController writes app-level operational traces to app.log
- Utilities writes integration/business traces to util.log
- Global ONERROR writes to error.log

Logging is verbose and used for step-level diagnostics of controller and utility operations.

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

## Update Tracker

Use this section as a running changelog for implementation updates.

Tracker policy:

- Every completed code/config/documentation change must be logged here.
- Each entry must include timestamp, summary of change, and impacted file paths.

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
