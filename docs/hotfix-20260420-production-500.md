# eTaxWare Production Hotfix - 2026-04-20

## Summary
This hotfix addresses production 500 errors observed after git deployment.

Reported symptoms:

- Internal Server Error
- Unable to open [vendor/bcosca/fatfree-core/base.php:3305]

## Root Cause
The layout template used raw F3 include tags for variables that can be empty during some request or error paths:

- pageheader
- pagecontent
- pagescripts

When any of these are empty, F3 attempts to open an empty path and throws:

- Unable to open ... base.php:3305

On PHP 8.2, additional runtime warnings can also become fatal depending on error handling:

- Optional parameter declared before required parameter
- Creation of dynamic property is deprecated

## Code Changes
Guarded optional includes in layout template:

- public/Layout.htm

Updated function signatures for PHP 8.2 compatibility:

- util/v1/Utilities.php
- util/v2/Utilities.php
- util/v3/Utilities.php

Declared emailUrl property to avoid dynamic property deprecation:

- util/v1/Utilities.php
- util/v2/Utilities.php
- util/v3/Utilities.php

## Deployment Commands (Production)
Run from project root.

Fetch and update branch:

```bash
git fetch --all --prune
git checkout main
git pull origin main
```

Optional verification:

```bash
git log --name-only --oneline -n 1
```

If dependencies changed in your release process:

```bash
composer install --no-dev --optimize-autoloader
```

Restart web service (XAMPP Apache on Windows):

- Use XAMPP Control Panel: Stop Apache, then Start Apache.
- Or use service commands from an elevated shell:

```powershell
net stop Apache2.4
net start Apache2.4
```

## Post-Deploy Verification
Open home and login routes:

- /etaxware/
- /etaxware/login

Confirm absence of errors in logs:

- error.log has no new "Unable to open" entries.
- Apache error log has no new fatal entries for layout include paths.

Functional smoke test:

- Login
- Open dashboard or home
- Open one module page (invoice or product)

## Rollback
If required:

```bash
git checkout PREVIOUS_KNOWN_GOOD_COMMIT
```

Then restart Apache.

## Notes
- Keep existing environment and config differences untouched during this hotfix.
- If new PHP 8.2 notices appear, treat them as separate hardening tasks and patch incrementally.
