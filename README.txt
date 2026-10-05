SPORTS CLUB MANAGEMENT WEBSITE - XAMPP READY
============================================

Technology
----------
Frontend: HTML5, CSS3, JavaScript
Backend: PHP 8+
Database: MySQL only
Server: XAMPP (Apache + MySQL)

Project structure
-----------------
index.html              Landing page
login.html              Student/Coach login
portal.html             Student sport portal
dashboard.html          Student dashboard
coach.html              Coach/Admin dashboard
style.css               Main stylesheet
app.js                  Frontend API logic
backend/                PHP API endpoints
database/               MySQL database script

INSTALLATION
------------
1. Install XAMPP.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Copy the folder named sports_club into:
      C:\xampp\htdocs\sports_club
4. Open phpMyAdmin:
      http://localhost/phpmyadmin/
5. Create/import the database using:
      database/sports_club_database.sql
   The SQL script creates the database sports_club_db automatically.
6. Check backend/config.php. Default XAMPP settings are:
      DB_HOST = localhost
      DB_NAME = sports_club_db
      DB_USER = root
      DB_PASS = (empty)
7. Open:
      http://localhost/sports_club/

LOGIN
-----
Students and coaches can log in with their Student/Coach ID, email, or phone,
plus their password. The sample SQL data uses the demonstration password:
      Password123!

The backend supports the seeded SHA-256 password hashes and automatically
upgrades a successfully verified seeded password to PHP password_hash().

MAIN FEATURES
-------------
Student/Player:
- Login with ID, email, or phone
- Choose/view multiple sports
- Football, Volleyball, Cricket
- Sport positions and sport-specific attributes
- Upcoming practice/testing sessions
- Attendance
- Performance statistics and coach notes
- Selection status and overall score
- Team assignment, position, starter/substitute/reserve status

Coach/Admin:
- Add/edit/delete students
- Add/edit/delete coaches
- View sports and positions
- Create/edit/delete practice/testing sessions
- Record attendance
- Record sport-specific performance
- Record selection score and notes
- Selection rule: score > 55 = Selected; score <= 55 = Not Selected
- Manage the single official team for each sport
- Assign starters, substitutes, and reserves with positions/jersey/formation slot

DATABASE RULES
--------------
- MySQL only
- Exactly three sports: Football, Volleyball, Cricket
- One official team per sport
- Students may be registered for multiple sports
- Positions are stored in the database
- Performance and coach notes are stored in the database
- Student result pages retrieve data from the PHP/MySQL backend

TROUBLESHOOTING
---------------
If login or pages show a database error:
- Make sure Apache and MySQL are running.
- Make sure sports_club_db exists in phpMyAdmin.
- Confirm backend/config.php matches your MySQL username/password.
- Open browser developer tools and check the Network tab for the PHP endpoint
  returning the error.

For a school/local XAMPP setup, no internet connection is required after the
project files and database have been installed.

PATCH NOTES - validation and diagnostics
========================================
- PHP 8.2 is supported.
- backend/config.php has APP_DEBUG=true for local XAMPP troubleshooting. Set it to false before public deployment.
- Database connection failures now return the actual PDO error while APP_DEBUG=true.
- Added validation for sport/student/session/position/team relationships.
- Attendance now refuses students who are not registered for the session sport.
- Performance now requires a matching Practice session and registered student.
- Selection now requires a matching Testing session and registered student.
- Team members now require a student and position belonging to the team's sport.
- Session start/end times are validated and database CHECK constraints are included.
- database/upgrade_validation.sql can be run once against an already-existing database.

QUICK BACKEND TEST
==================
After starting Apache and MySQL in XAMPP, open:
http://localhost/sports_club_xampp_ready/backend/health.php

It should return JSON showing PHP version, database server version, and whether all required tables exist.
If it reports a database connection error, read the "Database details" text while APP_DEBUG=true.


STYLING UPDATE
--------------
The included style.css now uses a flat, boxy sports-management design with green gradients, bold uppercase UI typography, a warm light mode, and an OLED-compatible pure-black dark mode. theme.js adds a persistent light/dark mode toggle using localStorage and follows the system preference on first visit.

UI / READABILITY UPDATE
=======================
- Added student dashboard summary cards for next session, attendance, selection, and performance.
- Added a clearer coach-management workflow and quick table filter.
- Improved responsive behavior for long tables and dashboard cards.
- Added password show/hide controls to password fields.
- Added short section explanations and UI-focused documentation in docs/UI_IMPROVEMENTS.md.

IMPORTANT SCOPE NOTE
====================
This UI revision intentionally leaves the PHP backend and SQL database files unchanged.
Existing API endpoints, payloads, database relationships, validation rules, and business logic remain intact.
