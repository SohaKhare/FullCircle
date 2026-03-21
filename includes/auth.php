<?php
session_start();

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
