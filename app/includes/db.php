<?php
// includes/db.php  (VERCEL VERSION)
// Creates the MySQL connection used by all pages (supports TLS for cloud DBs).

require_once __DIR__ . '/../config/config.php';

if (DB_HOST === '' || DB_USER === '') {
    if (!headers_sent()) { http_response_code(500); }
    die('Database is not configured. Set DB_HOST, DB_USER, DB_PASS and DB_NAME in Vercel > Settings > Environment Variables, then redeploy.');
}

$conn = mysqli_init();
$flags = 0;
if (DB_SSL) {
    if (DB_SSL_CA !== '') {
        mysqli_ssl_set($conn, null, null, DB_SSL_CA, null, null);
    } else {
        mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
    }
    $flags = MYSQLI_CLIENT_SSL;
}

try {
    mysqli_real_connect($conn, DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT, null, $flags);
} catch (Throwable $e) {
    if (!headers_sent()) { http_response_code(500); }
    die('Database connection failed. Please check the DB_* environment variables.');
}

if (mysqli_connect_errno()) {
    if (!headers_sent()) { http_response_code(500); }
    die('Database connection failed: ' . mysqli_connect_error());
}

// Use UTF-8 for proper character handling.
mysqli_set_charset($conn, 'utf8mb4');

// Table that stores PHP sessions (serverless has no shared disk).
try {
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS php_sessions (
        id VARCHAR(128) NOT NULL PRIMARY KEY,
        data MEDIUMTEXT NOT NULL,
        expires INT UNSIGNED NOT NULL,
        KEY idx_expires (expires)
    )");
} catch (Throwable $e) {
    // Ignore; the SQL import normally creates it.
}

// Ensure an administrator account exists (auto-seed on first run).
try {
    $usersTableCheck = mysqli_query($conn, "SELECT 1 FROM users LIMIT 1");
    if ($usersTableCheck !== false) {
        $countResult = mysqli_query($conn, "SELECT COUNT(*) AS c FROM users");
        $countRow = mysqli_fetch_assoc($countResult);
        if ((int) $countRow['c'] === 0) {
            $adminUser = 'admin';
            $adminPass = password_hash('Admin123!', PASSWORD_DEFAULT);
            $adminRole = 'admin';
            $seedStmt = mysqli_prepare($conn, 'INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
            mysqli_stmt_bind_param($seedStmt, 'sss', $adminUser, $adminPass, $adminRole);
            mysqli_stmt_execute($seedStmt);
            mysqli_stmt_close($seedStmt);
        }
        mysqli_free_result($usersTableCheck);
        mysqli_free_result($countResult);
    }
} catch (Throwable $e) {
    if (!headers_sent()) { http_response_code(500); }
    die('Database tables are missing. Import database/campushub_cloud.sql into your cloud database first.');
}
