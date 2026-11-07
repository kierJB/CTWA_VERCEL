<?php
// config/config.php  (VERCEL VERSION)
// All secrets come from Vercel Environment Variables - nothing is hard-coded.
// There is intentionally NO localhost/XAMPP fallback, so this copy can never
// touch the XAMPP database.

date_default_timezone_set('Asia/Manila');

function env_or($key, $default = '') {
    $v = getenv($key);
    if ($v === false || $v === '') {
        $v = $_ENV[$key] ?? $_SERVER[$key] ?? '';
    }
    return ($v === '' || $v === false) ? $default : $v;
}

define('DB_HOST', env_or('DB_HOST', ''));
define('DB_PORT', (int) env_or('DB_PORT', '3306'));
define('DB_USER', env_or('DB_USER', ''));
define('DB_PASS', env_or('DB_PASS', ''));
define('DB_NAME', env_or('DB_NAME', 'student_org_system'));
// Set DB_SSL=1 for cloud databases that require TLS (TiDB Cloud, Aiven, etc.)
define('DB_SSL', in_array(strtolower(env_or('DB_SSL', '0')), ['1', 'true', 'yes', 'on'], true));
// Optional path to a CA bundle if you want full certificate verification.
define('DB_SSL_CA', env_or('DB_SSL_CA', ''));

define('SITE_NAME', 'CampusHub');
// The site lives at the domain root on Vercel.
define('BASE_URL', '/');

// Minimum password length for new accounts.
define('MIN_PASSWORD_LENGTH', 8);
