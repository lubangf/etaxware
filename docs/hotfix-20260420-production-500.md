# eTaxWare Production Hotfix - 2026-04-20

## Summary
This hotfix addresses production 500 errors observed after git deployment.

Reported symptom:
- Internal Server Error
- Unable to open [vendor/bcosca/fatfree-core/base.php:3305]

## Root Cause
The layout template used raw F3 include tags for variables that can be empty during some request/error paths:
- pageheader
- pagecontent
- pagescripts

When any of these are empty, F3 attempts to open an empty path and throws:
- Unable to open ... base.php:3305

On PHP 8.2, additional runtime warnings can also become fatal depending on error handling:
- Optional parameter declared before required parameter
- Creation of dynamic property is deprecated

## Code Changes
1. Guarded optional includes in layout template:
- public/Layout.htm

2. Updated function signatures for PHP 8.2 compatibility:
- util/v1/Utilities.php
- util/v2/Utilities.php
- util/v3/Utilities.php

3. Declared emailUrl property to avoid dynamic property deprecation:
- util/v1/Utilities.php
- util/v2/Utilities.php
- util/v3/Utilities.php

## Deployment Commands (Production)
Run from project root.

1. Fetch and update branch
- git fetch --all --prune
- git checkout main
- git pull origin main

2. Optional: verify exact hotfix files
- git log --name-only --oneline -n 1

3. If dependencies changed in your release process
- composer install --no-dev --optimize-autoloader

4. Restart web service
For XAMPP Apache on Windows (pick one):
- Use XAMPP Control Panel: Stop Apache, Start Apache
- Or service command from elevated shell if installed as service:
  - net stop Apache2.4
  - net start Apache2.4

## Post-Deploy Verification
1. Open home and login routes:
- /etaxware/
- /etaxware/login

2. Confirm absence of error in logs:
- error.log: no new "Unable to open" entries
- apache error log: no new fatal entries for layout include paths

3. Functional smoke test:
- Login
- Open dashboard/home
- Open one module page (invoice or product)

## Rollback
If required:
- git checkout <previous-known-good-commit>
- restart Apache

## Notes
- Keep existing environment/config differences untouched during this hotfix.
- If new PHP 8.2 notices appear, treat them as separate hardening tasks and patch incrementally.
