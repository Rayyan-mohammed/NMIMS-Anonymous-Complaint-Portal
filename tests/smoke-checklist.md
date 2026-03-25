# APNM6 Smoke Test Checklist

Run this checklist after major changes.

## 0. Automated Baseline
- Run `php tests/smoke_runner.php`
- Optional: `php tests/smoke_runner.php http://localhost/APNM6/public`
- Optional env overrides:
	- `APNM6_SMOKE_BASE_URL`
	- `APNM6_SMOKE_ADMIN_EMAIL`
	- `APNM6_SMOKE_ADMIN_PASSWORD`
- Confirm output ends with `Smoke runner completed` before doing manual checks.

## 1. Public Complaint Flow
- Open `/APNM6/public/index.php`
- Submit anonymous complaint successfully
- Submit non-anonymous complaint with Name, SAP ID, Year
- Confirm generated reference number appears

## 2. Check Status Flow
- Open `/APNM6/public/check-status/check-status.php`
- Search with valid reference number
- Verify complaint details and updates are visible
- Search with invalid number and verify error message

## 3. Admin Login Security
- Open `/APNM6/public/admin/login.php`
- Login with valid account and verify redirect
- Enter wrong password 5 times and verify temporary lock message

## 4. Dashboard Filters
- In admin dashboard, apply status filter
- Apply complaint type filter
- Use search with reference or SAP ID
- Verify list updates and reset works

## 5. Complaint Actions
- Open a complaint and add update
- Change status from pending to in_progress/resolved
- Verify update appears in timeline
- Verify complaint status history row exists in `complaint_status_history`

## 6. Error Handling
- Trigger a handled validation error (e.g., invalid form input)
- Trigger a CSRF failure by reusing old form token
- Verify user-friendly response is shown and app does not crash
- Verify logs are written in `storage/logs/app.log`
