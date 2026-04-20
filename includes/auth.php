<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

// Remember-me cookie settings
const REMEMBER_ME_COOKIE_NAME = 'remember_me';
const REMEMBER_ME_LIFETIME_DAYS = 14; // adjust as desired

function isHttpsRequest(): bool {
    if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') return true;
    if (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) return true;
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') return true;
    return false;
}

function setRememberMeCookie(string $token, int $expiresTs): void {
    $secure = isHttpsRequest();
    // PHP 7.3+ supports options array
    setcookie(REMEMBER_ME_COOKIE_NAME, $token, [
        'expires'  => $expiresTs,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function clearRememberMeCookie(): void {
    $secure = isHttpsRequest();
    setcookie(REMEMBER_ME_COOKIE_NAME, '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    unset($_COOKIE[REMEMBER_ME_COOKIE_NAME]);
}

function issueRememberMeToken(mysqli $conn, string $principalType, int $principalId): void {
    // Keep one active token per account (simple + safer for small apps)
    $del = $conn->prepare("DELETE FROM remember_tokens WHERE principal_type = ? AND principal_id = ?");
    $del->bind_param("si", $principalType, $principalId);
    $del->execute();

    $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    $tokenHash = hash('sha256', $token);
    $expiresTs = time() + (REMEMBER_ME_LIFETIME_DAYS * 86400);
    $expiresAt = date('Y-m-d H:i:s', $expiresTs);

    $ins = $conn->prepare("INSERT INTO remember_tokens (principal_type, principal_id, token_hash, expires_at) VALUES (?, ?, ?, ?)");
    $ins->bind_param("siss", $principalType, $principalId, $tokenHash, $expiresAt);
    $ins->execute();

    setRememberMeCookie($token, $expiresTs);
}

function forgetRememberMeToken(mysqli $conn): void {
    $token = $_COOKIE[REMEMBER_ME_COOKIE_NAME] ?? '';
    if ($token) {
        $tokenHash = hash('sha256', $token);
        $del = $conn->prepare("DELETE FROM remember_tokens WHERE token_hash = ?");
        $del->bind_param("s", $tokenHash);
        $del->execute();
    }
    clearRememberMeCookie();
}

function restoreSessionFromRememberMeCookie(): void {
    if (!empty($_SESSION['user_id'])) return;
    $token = $_COOKIE[REMEMBER_ME_COOKIE_NAME] ?? '';
    if (!$token) return;

    global $conn;

    // Best-effort cleanup
    $conn->query("DELETE FROM remember_tokens WHERE expires_at < NOW()");

    $tokenHash = hash('sha256', $token);
    $stmt = $conn->prepare(
        "SELECT principal_type, principal_id, expires_at
         FROM remember_tokens
         WHERE token_hash = ? AND expires_at > NOW()
         LIMIT 1"
    );
    $stmt->bind_param("s", $tokenHash);
    $stmt->execute();
    $tok = $stmt->get_result()->fetch_assoc();

    if (!$tok) {
        // Invalid/expired token
        forgetRememberMeToken($conn);
        return;
    }

    $principalType = $tok['principal_type'] ?? '';
    $principalId = (int)($tok['principal_id'] ?? 0);
    if (!$principalId || ($principalType !== 'donor' && $principalType !== 'ngo')) {
        forgetRememberMeToken($conn);
        return;
    }

    if ($principalType === 'donor') {
        $u = $conn->prepare("SELECT donor_id AS id, name, email FROM donors WHERE donor_id = ? LIMIT 1");
    } else {
        $u = $conn->prepare("SELECT ngo_id AS id, name, email FROM ngos WHERE ngo_id = ? LIMIT 1");
    }
    $u->bind_param("i", $principalId);
    $u->execute();
    $row = $u->get_result()->fetch_assoc();

    if (!$row) {
        // Account removed; invalidate token
        forgetRememberMeToken($conn);
        return;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$row['id'];
    $_SESSION['name']    = $row['name'];
    $_SESSION['role']    = $principalType;
    $_SESSION['email']   = $row['email'];

    // Optional: track usage
    $touch = $conn->prepare("UPDATE remember_tokens SET last_used_at = NOW() WHERE token_hash = ?");
    $touch->bind_param("s", $tokenHash);
    $touch->execute();

}

// Run restore logic on every protected page include
restoreSessionFromRememberMeCookie();

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        $depth = max(0, substr_count(str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']), '/') - 2);
        $base  = str_repeat('../', $depth);
        header("Location: {$base}login.php");
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        $depth = max(0, substr_count(str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']), '/') - 2);
        $base  = str_repeat('../', $depth);
        header("Location: {$base}index.php?error=unauthorized");
        exit;
    }
}

function currentUser() {
    return [
        'id'    => $_SESSION['user_id'] ?? null,
        'name'  => $_SESSION['name']    ?? '',
        'role'  => $_SESSION['role']    ?? '',
        'email' => $_SESSION['email']   ?? '',
    ];
}