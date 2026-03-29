<?php
require_once 'includes/db.php';
require_once 'includes/registration_service.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $_SESSION['reg_error'] = 'Invalid request method.';
    fc_registration_redirect_back('get', $_GET['role'] ?? '');
}

// NOTE: GET is not recommended for passwords; implemented to match assignment requirements.
$data = fc_registration_extract($_GET);
$_SESSION['reg_old'] = fc_registration_old($data);

$validationError = fc_registration_validate($data);
if ($validationError) {
    $_SESSION['reg_error'] = $validationError;
    fc_registration_redirect_back('get', $data['role'] ?? '');
}

$error = '';
if (!fc_registration_create_user($conn, $data, $error)) {
    $_SESSION['reg_error'] = $error ?: 'Registration failed. Please try again.';
    fc_registration_redirect_back('get', $data['role'] ?? '');
}

unset($_SESSION['reg_old']);
$_SESSION['reg_success'] = 'Account created. You can now sign in.';
fc_registration_redirect_back('get', $data['role'] ?? '');
