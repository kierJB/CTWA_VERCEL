<?php
// includes/session_handler.php
// Stores PHP sessions in MySQL so login works across Vercel serverless instances.

class DbSessionHandler implements SessionHandlerInterface
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $now = time();
        $stmt = mysqli_prepare($this->conn, 'SELECT data FROM php_sessions WHERE id = ? AND expires > ?');
        mysqli_stmt_bind_param($stmt, 'si', $id, $now);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $data);
        $found = mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        return $found ? (string) $data : '';
    }

    public function write(string $id, string $data): bool
    {
        $expires = time() + (int) ini_get('session.gc_maxlifetime');
        $stmt = mysqli_prepare($this->conn, 'REPLACE INTO php_sessions (id, data, expires) VALUES (?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'ssi', $id, $data, $expires);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return (bool) $ok;
    }

    public function destroy(string $id): bool
    {
        $stmt = mysqli_prepare($this->conn, 'DELETE FROM php_sessions WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 's', $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return (bool) $ok;
    }

    public function gc(int $max_lifetime): int|false
    {
        $now = time();
        $stmt = mysqli_prepare($this->conn, 'DELETE FROM php_sessions WHERE expires < ?');
        mysqli_stmt_bind_param($stmt, 'i', $now);
        mysqli_stmt_execute($stmt);
        $n = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        return $n;
    }
}
