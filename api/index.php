<?php
// api/index.php - single entry point for Vercel.
// Routes every request (/login.php, /admin/dashboard.php, ...) to the matching
// PHP file inside /app. NOTE: no functions wrap the require, so $conn stays global.

$root   = dirname(__DIR__);
$appDir = realpath($root . '/app');
$pubDir = realpath($root . '/public');

$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$path = '/' . ltrim($path, '/');

if (strpos($path, '..') !== false || strpos($path, "\0") !== false) {
    http_response_code(400);
    exit('Bad request');
}

// Fallback static file server (normally Vercel serves /public itself).
if (strpos($path, '/assets/') === 0 && $pubDir) {
    $f = realpath($pubDir . $path);
    if ($f && strpos($f, $pubDir . DIRECTORY_SEPARATOR) === 0 && is_file($f)) {
        $types = ['css' => 'text/css', 'js' => 'application/javascript', 'png' => 'image/png',
                  'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'svg' => 'image/svg+xml',
                  'gif' => 'image/gif', 'ico' => 'image/x-icon', 'woff2' => 'font/woff2'];
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
        header('Cache-Control: public, max-age=3600');
        readfile($f);
        exit;
    }
}

// Friendly aliases.
$aliases = [
    '/'         => 'index.php',
    '/admin'    => 'admin/dashboard.php',
    '/admin/'   => 'admin/dashboard.php',
    '/student'  => 'student/dashboard.php',
    '/student/' => 'student/dashboard.php',
];
$rel = $aliases[$path] ?? ltrim($path, '/');

// Never expose internals.
if (preg_match('#^(includes|config)/#', $rel) || substr($rel, -4) !== '.php') {
    http_response_code(404);
    exit('404 Not Found');
}

$file = realpath($appDir . '/' . $rel);
if (!$file || strpos($file, $appDir . DIRECTORY_SEPARATOR) !== 0 || !is_file($file)) {
    http_response_code(404);
    exit('404 Not Found');
}

// Make pages think they were requested directly (used by the sidebar "active" link).
$_SERVER['SCRIPT_NAME']     = '/' . str_replace(DIRECTORY_SEPARATOR, '/', substr($file, strlen($appDir) + 1));
$_SERVER['PHP_SELF']        = $_SERVER['SCRIPT_NAME'];
$_SERVER['SCRIPT_FILENAME'] = $file;

chdir(dirname($file));
require $file;
