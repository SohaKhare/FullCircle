<?php
require_once 'includes/db.php';
require_once 'includes/registration_service.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Intentionally accept GET or POST, since this handler demonstrates $_REQUEST.
$data = fc_registration_extract($_REQUEST);
$_SESSION['reg_old'] = fc_registration_old($data);

$validationError = fc_registration_validate($data);
if ($validationError) {
    $_SESSION['reg_error'] = $validationError;
    fc_registration_redirect_back('request', $data['role'] ?? '');
}

$error = '';
if (!fc_registration_create_user($conn, $data, $error)) {
    $_SESSION['reg_error'] = $error ?: 'Registration failed. Please try again.';
    fc_registration_redirect_back('request', $data['role'] ?? '');
}

unset($_SESSION['reg_old']);
$_SESSION['reg_success'] = 'Account created. You can now sign in.';
fc_registration_redirect_back('request', $data['role'] ?? '');
