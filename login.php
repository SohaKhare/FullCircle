<?php
$pageTitle = "Sign in";
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = "Please enter your email and password.";
    } else {
        $stmt = $conn->prepare("SELECT user_id, name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ($row && password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['name']    = $row['name'];
            $_SESSION['role']    = $row['role'];
            $_SESSION['email']   = $email;
            header("Location: " . ($row['role'] === 'donor' ? 'donor/my_donations.php' : 'ngo/request_food.php'));
            exit;
        } else {
            $error = "Incorrect email or password.";
        }
    }
}

require_once 'includes/header.php';
?>

<div class="form-page">
  <div class="form-card">
    <h1 class="form-heading">Sign in</h1>
    <p class="form-sub">Welcome back to FullCircle.</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error']) && $_GET['error'] === 'unauthorized'): ?>
      <div class="alert alert-error">You do not have access to that page.</div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group">
        <label class="form-label" for="email">Email address</label>
        <input id="email" type="email" name="email" class="form-control"
          placeholder="you@example.com" value="<?= htmlspecialchars($email) ?>" required autofocus>
      </div>
      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input id="password" type="password" name="password" class="form-control"
          placeholder="Your password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">Sign in</button>
    </form>

    <p class="form-footer">No account? <a href="register.php">Register here</a></p>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
