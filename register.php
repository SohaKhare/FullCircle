<?php
$pageTitle = "Register";
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$error      = '';
$success    = '';
$prefill    = $_GET['role'] ?? '';
$old        = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name']     = trim($_POST['name']     ?? '');
    $old['email']    = trim($_POST['email']    ?? '');
    $old['phone']    = trim($_POST['phone']    ?? '');
    $old['role']     = $_POST['role']          ?? '';
    $old['address']  = trim($_POST['address']  ?? '');
    $old['ngo_name'] = trim($_POST['ngo_name'] ?? '');
    $password        = $_POST['password']      ?? '';
    $confirm         = $_POST['confirm_password'] ?? '';

    // Server-side validation
    if (!$old['name'] || !$old['email'] || !$password || !$old['role']) {
        $error = "Please fill all required fields.";
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($old['phone'] && !preg_match('/^\+?[\d\s\-\(\)]{10,15}$/', $old['phone'])) {
        $error = "Phone number must be 10 to 13 digits.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif ($old['role'] === 'ngo' && !$old['ngo_name']) {
        $error = "Organisation name is required.";
    } else {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $old['email']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "This email is already registered.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name,email,phone,password,role,address,ngo_name) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param("sssssss", $old['name'], $old['email'], $old['phone'], $hashed, $old['role'], $old['address'], $old['ngo_name']);
            if ($stmt->execute()) {
                $success = "Account created. You can now sign in.";
                $old = [];
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="form-page" style="align-items:flex-start; padding-top:48px;">
  <div class="form-card wide">
    <h1 class="form-heading">Create account</h1>
    <p class="form-sub">Join FullCircle as a food donor or an NGO.</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success"><?= $success ?> <a href="login.php">Sign in</a></div>
    <?php endif; ?>

    <form id="register-form" method="POST" action="" novalidate>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="reg-name">Full name <span style="color:#dc2626">*</span></label>
          <input id="reg-name" type="text" name="name" class="form-control"
            placeholder="Your full name" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="reg-phone">Phone number</label>
          <input id="reg-phone" type="tel" name="phone" class="form-control"
            placeholder="e.g. 98765 43210" value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
          <span id="hint-phone" class="field-hint"></span>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="reg-email">Email address <span style="color:#dc2626">*</span></label>
        <input id="reg-email" type="email" name="email" class="form-control"
          placeholder="you@example.com" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
        <span id="hint-email" class="field-hint"></span>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="reg-password">Password <span style="color:#dc2626">*</span></label>
          <input id="reg-password" type="password" name="password" class="form-control"
            placeholder="Min. 6 characters" required>
          <div id="pw-strength-meter" class="pw-strength">
            <div class="pw-strength-bar"><div id="pw-strength-fill" class="pw-strength-fill"></div></div>
            <span id="pw-strength-label" class="pw-strength-label"></span>
          </div>
          <span id="hint-password" class="field-hint"></span>
        </div>
        <div class="form-group">
          <label class="form-label" for="reg-confirm">Confirm password <span style="color:#dc2626">*</span></label>
          <input id="reg-confirm" type="password" name="confirm_password" class="form-control"
            placeholder="Repeat password" required>
          <span id="hint-confirm" class="field-hint"></span>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="role">I am registering as <span style="color:#dc2626">*</span></label>
        <select id="role" name="role" class="form-control" onchange="toggleNGOField()" required>
          <option value="">Select role</option>
          <option value="donor" <?= ($old['role'] ?? $prefill) === 'donor' ? 'selected' : '' ?>>Food Donor</option>
          <option value="ngo"   <?= ($old['role'] ?? $prefill) === 'ngo'   ? 'selected' : '' ?>>NGO / Organisation</option>
        </select>
      </div>

      <div class="form-group" id="ngo-field"
        style="display:<?= ($old['role'] ?? $prefill) === 'ngo' ? 'block' : 'none' ?>">
        <label class="form-label" for="reg-ngo">Organisation name <span style="color:#dc2626">*</span></label>
        <input id="reg-ngo" type="text" name="ngo_name" class="form-control"
          placeholder="Name of your NGO" value="<?= htmlspecialchars($old['ngo_name'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label class="form-label" for="reg-address">Address</label>
        <textarea id="reg-address" name="address" class="form-control"
          rows="2" placeholder="City / area"><?= htmlspecialchars($old['address'] ?? '') ?></textarea>
      </div>

      <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">Create account</button>
    </form>

    <p class="form-footer">Already have an account? <a href="login.php">Sign in</a></p>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
