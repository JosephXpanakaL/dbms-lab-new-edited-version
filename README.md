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
- New car entries can optionally include a JPG, PNG or WEBP photo (maximum 5 MB). The image path is stored with the car and displayed in rental reports.
- Existing customer, car and rental tables, IDs, fields, joins and foreign-key relationships are retained; `image_path` is an additional nullable car field.

## Database update
- Fresh installation: import the updated `carrental_db.sql`.
- Existing installation: run `upgrade_car_images.sql` once in phpMyAdmin.
- Ensure the PHP server can write to the `uploads/` directory. For hosting, use persistent storage for uploaded images.
