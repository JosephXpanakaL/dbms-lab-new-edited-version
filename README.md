# Car Rental System - DBMS Mini Project

Question: 17  
Database: carrental_db

## Files
- db.php: Connects PHP to MySQL.
- rental.php: Displays the rental form (dropdowns for existing members, unified section for adding new customer and car with auto-ascending IDs, shared auto-capitalized Licence/Registration No, auto car selection, and double-booking check) and inserts into the tables.
- report.php: Displays all rental reports or individual customer reports with date-range and search filters using INNER JOIN.
- carrental_db.sql: Creates the database, tables and sample records.

## How to run
1. Start Apache and MySQL in XAMPP.
2. Open phpMyAdmin and import carrental_db.sql.
3. Copy the dbms-lab-17 folder into C:/xampp/htdocs/.
4. Open http://localhost/dbms-lab-17/rental.php.


## DriveLux interface upgrade
- Responsive car-rental interface with a car-led homepage, subtle 3D hover treatment and refreshed booking/report pages.
- Every new car entry requires a JPG, PNG or WEBP photo (maximum 5 MB). The image path is stored with the car and displayed in rental reports.
- Existing customer, car and rental tables, IDs, fields, joins and foreign-key relationships are retained; `image_path` is an additional nullable car field.

## Database update
- Fresh installation: import the updated `carrental_db.sql`.
- Existing installation: run `upgrade_car_images.sql` once in phpMyAdmin.
- Ensure the PHP server can write to the `uploads/` directory. For hosting, use persistent storage for uploaded images.

## Deploy online with Railway

This is a PHP + MySQL application, so deploy it as a PHP/Apache container rather than as a static site.

1. Merge the `ui-redesign` pull request into `main`.
2. In Railway, create a project and add a MySQL service.
3. Add a service from this GitHub repository and select the `main` branch. Railway will build from the included `Dockerfile`.
4. In the web service variables, map the MySQL service values:
   - `MYSQLHOST` = `${{MySQL.MYSQLHOST}}`
   - `MYSQLPORT` = `${{MySQL.MYSQLPORT}}`
   - `MYSQLUSER` = `${{MySQL.MYSQLUSER}}`
   - `MYSQLPASSWORD` = `${{MySQL.MYSQLPASSWORD}}`
   - `MYSQLDATABASE` = `${{MySQL.MYSQLDATABASE}}`
   Replace `MySQL` with the exact name of your Railway database service if it differs.
5. Import the updated `carrental_db.sql` into the Railway MySQL database. If migrating an existing database, run `upgrade_car_images.sql` once instead.
6. Attach a Railway volume to the web service at `/var/www/html/uploads` so uploaded car photos persist across deployments.
7. Generate a public domain for the web service and open it to test bookings and reports.

Keep database credentials in Railway variables; do not commit them to GitHub. The included `db.php` keeps the XAMPP defaults for local use and reads Railway's MySQL environment variables when deployed.
