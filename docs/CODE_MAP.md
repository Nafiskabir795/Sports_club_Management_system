# Sports Club — File-by-file Code Map

## Frontend
- `index.html` — landing/home page.
- `login.html` — login UI.
- `register.html` — registration UI.
- `student_login.html` / `student_register.html` / `student_portal.html` — student entry pages.
- `dashboard.html` — student dashboard.
- `coach.html` — coach dashboard.
- `admin_login.html` / `admin_register.html` / `admin_portal.html` — coach/admin entry pages.
- `portal.html` — portal/navigation page.

## JavaScript
- `app.js` — API requests, authentication flow, events, forms, DOM updates and dashboard behavior.
- `theme.js` — theme/dark-mode behavior.

## PHP backend
- `backend/config.php` — MySQL/PDO connection, JSON response helper and secure session setup.
- `backend/auth.php` — authentication/account helpers.
- `backend/validation.php` — server-side validation/common error handling.
- `backend/login.php` — login endpoint.
- `backend/register.php` — registration endpoint.
- `backend/logout.php` — logout endpoint.
- `backend/session_check.php` — session/role checking.
- `backend/health.php` — backend/database health check.
- `backend/registration_options.php` — registration options.
- `backend/coach_*.php` — coach operations.
- `backend/student_*.php` — student operations.

## Database
- `database/sports_club_database.sql` — main schema/data setup.
- `database/upgrade_validation.sql` — validation-related database changes.
- `docs/DBMS_REQUIREMENT_QUERIES.sql` — requirement-oriented SQL.

## Styling
- `style.css` — main layout, components, forms, typography, responsive rules and theme variables.