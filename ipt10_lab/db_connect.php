<?php
declare(strict_types=1);

// Database configuration settings
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db   = 'ip10_lab';

// TODO(1): enable strict error reporting BEFORE connecting.
// This tells mysqli to throw exceptions instead of silent warnings on database errors.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // TODO(2): open the connection and store it in $conn.
    // Connect to the MySQL database using the mysqli object constructor.
    $conn = new mysqli($host, $user, $pass, $db);

    // TODO(3): agree the character set immediately after connecting.
    // Set the character set to utf8mb4 for full UTF-8 support (including special characters and emojis).
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // Log the actual technical error to the server error log for debugging
    error_log('DB connect failed: ' . $e->getMessage());
    die('Database unavailable');
}
