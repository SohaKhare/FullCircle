<?php

function fc_registration_extract(array $src): array {
    return [
        'name'             => trim($src['name'] ?? ''),
        'email'            => trim($src['email'] ?? ''),
        'phone'            => trim($src['phone'] ?? ''),
        'role'             => $src['role'] ?? '',
        'address'          => trim($src['address'] ?? ''),
        'ngo_name'         => trim($src['ngo_name'] ?? ''),
        'password'         => $src['password'] ?? '',
        'confirm_password' => $src['confirm_password'] ?? '',
    ];
}

function fc_registration_old(array $data): array {
    return [
        'name'     => $data['name'] ?? '',
        'email'    => $data['email'] ?? '',
        'phone'    => $data['phone'] ?? '',
        'role'     => $data['role'] ?? '',
        'address'  => $data['address'] ?? '',
        'ngo_name' => $data['ngo_name'] ?? '',
    ];
}

function fc_registration_validate(array $data): string {
    $name     = $data['name'] ?? '';
    $email    = $data['email'] ?? '';
    $phone    = $data['phone'] ?? '';
    $role     = $data['role'] ?? '';
    $ngoName  = $data['ngo_name'] ?? '';
    $password = $data['password'] ?? '';
    $confirm  = $data['confirm_password'] ?? '';

    if (!$name || !$email || !$password || !$role) {
        return 'Please fill all required fields.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Please enter a valid email address.';
    }
    if ($phone && !preg_match('/^\+?[\d\s\-\(\)]{10,15}$/', $phone)) {
        return 'Phone number must be 10 to 13 digits.';
    }
    if (strlen($password) < 6) {
        return 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm) {
        return 'Passwords do not match.';
    }
    if ($role === 'ngo' && !$ngoName) {
        return 'Organisation name is required.';
    }

    return '';
}

function fc_registration_create_user(mysqli $conn, array $data, string &$error): bool {
    $error = '';

    $stmt = $conn->prepare('SELECT user_id FROM users WHERE email = ?');
    if (!$stmt) {
        $error = 'Registration failed. Please try again.';
        return false;
    }
    $stmt->bind_param('s', $data['email']);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $error = 'This email is already registered.';
        return false;
    }

    $hashed = password_hash($data['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO users (name,email,phone,password,role,address,ngo_name) VALUES (?,?,?,?,?,?,?)');
    if (!$stmt) {
        $error = 'Registration failed. Please try again.';
        return false;
    }

    $stmt->bind_param(
        'sssssss',
        $data['name'],
        $data['email'],
        $data['phone'],
        $hashed,
        $data['role'],
        $data['address'],
        $data['ngo_name']
    );

    if (!$stmt->execute()) {
        $error = 'Registration failed. Please try again.';
        return false;
    }

    return true;
}

function fc_registration_redirect_back(string $handler, string $role = ''): void {
    $handler = $handler ?: 'post';
    $role = $role ? urlencode($role) : '';
    $qs = 'handler=' . urlencode($handler);
    if ($role) {
        $qs .= '&role=' . $role;
    }

    header('Location: register.php?' . $qs);
    exit;
}
