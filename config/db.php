 <?php
// ============================================================
// config/db.php — Database Connection
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // Default XAMPP has no password
define('DB_NAME', 'hams');

function getDB(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die('<p style="color:red;font-family:monospace;">
            Database connection failed: ' . $conn->connect_error . '<br>
            Make sure XAMPP MySQL is running and you have imported the SQL files.
        </p>');
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
?>
