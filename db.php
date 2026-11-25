<?php
// Local XAMPP defaults are retained; hosted credentials are read from environment variables.
$db_host = getenv("DB_HOST") ?: (getenv("MYSQLHOST") ?: "localhost");
$db_port = (int) (getenv("DB_PORT") ?: (getenv("MYSQLPORT") ?: 3306));
$db_user = getenv("DB_USER") ?: (getenv("MYSQLUSER") ?: "root");
$db_pass = getenv("DB_PASS") ?: (getenv("MYSQLPASSWORD") ?: "");
$db_name = getenv("DB_NAME") ?: (getenv("MYSQLDATABASE") ?: "carrental_db");

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);
if (!$conn) {
    die("Database connection failed. Please check the database environment settings.");
}
mysqli_set_charset($conn, "utf8mb4");
?>
