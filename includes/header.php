<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$user = [
    'id'   => $_SESSION['user_id'] ?? null,
    'name' => $_SESSION['name']    ?? '',
    'role' => $_SESSION['role']    ?? '',
];
// Derive base path from calling script depth under DOCUMENT_ROOT
// e.g. /fullcircle/donor/donate_food.php → depth 2 → base = ../../  → ../
// Actually: depth below project = slashes in path after docroot minus 1 (for the file itself)
$scriptPath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']);
$depth = max(0, substr_count($scriptPath, '/') - 2);
$base  = str_repeat('../', $depth);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'FullCircle') ?> &mdash; FullCircle</title>
  <link rel="stylesheet" href="<?= $base ?>css/style.css">
</head>
<body>
<nav class="navbar">
  <div class="nav-inner">
    <a href="<?= $base ?>index.php" class="nav-logo">FullCircle</a>
    <ul class="nav-links">
      <li><a href="<?= $base ?>index.php">Home</a></li>
      <?php if ($user['role'] === 'donor'): ?>
        <li><a href="<?= $base ?>donor/donate_food.php">Donate Food</a></li>
        <li><a href="<?= $base ?>donor/my_donations.php">My Donations</a></li>
      <?php elseif ($user['role'] === 'ngo'): ?>
        <li><a href="<?= $base ?>ngo/request_food.php">Available Food</a></li>
        <li><a href="<?= $base ?>ngo/my_requests.php">My Requests</a></li>
      <?php else: ?>
        <li><a href="<?= $base ?>register.php?role=donor">Donate Food</a></li>
        <li><a href="<?= $base ?>register.php?role=ngo">Request Food</a></li>
      <?php endif; ?>
      <li><a href="<?= $base ?>contact.php">Contact</a></li>
    </ul>
    <div class="nav-actions">
      <button class="dark-toggle" onclick="toggleDarkMode()" aria-label="Toggle dark mode"></button>
      <?php if ($user['id']): ?>
        <span class="nav-user"><?= htmlspecialchars($user['name']) ?></span>
        <a href="<?= $base ?>logout.php" class="btn btn-ghost btn-sm">Sign out</a>
      <?php else: ?>
        <a href="<?= $base ?>login.php"    class="btn btn-ghost btn-sm">Sign in</a>
        <a href="<?= $base ?>register.php" class="btn btn-primary btn-sm">Register</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
