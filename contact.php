<?php
$pageTitle = "Contact";
require_once 'includes/db.php';
$success = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $message = trim($_POST['message'] ?? '');
    if (!$name || !$email || !$message) {
        $error = "Please fill all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name,email,message) VALUES (?,?,?)");
        $stmt->bind_param("sss", $name, $email, $message);
        $success = $stmt->execute() ? "Your message has been sent. We will be in touch soon." : "Failed to send. Please try again.";
        if ($stmt->execute()) { $name = $email = $message = ''; }
    }
}

require_once 'includes/header.php';
?>

<div class="section">
  <div class="section-label">Get in touch</div>
  <h1 class="section-title">Contact us</h1>
  <p class="section-sub" style="margin-bottom:0">Questions, partnerships, or feedback — we would love to hear from you.</p>

  <div class="contact-layout" style="margin-top:48px;">
    <div class="contact-info">
      <h2>How to reach us</h2>
      <p>We are a small team passionate about reducing food waste. Reach out any time during business hours and we will respond within one working day.</p>
      <div class="contact-item">
        <div class="contact-icon">Loc</div>
        <div class="contact-item-text"><strong>Location</strong><span>Mumbai, Maharashtra, India</span></div>
      </div>
      <div class="contact-item">
        <div class="contact-icon">Hr</div>
        <div class="contact-item-text"><strong>Hours</strong><span>Monday to Saturday, 9 AM to 6 PM</span></div>
      </div>
    </div>

    <div class="form-card" style="max-width:100%; box-shadow:none; border:1px solid var(--border);">
      <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
      <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="POST">
        <div class="form-group">
          <label class="form-label">Your name</label>
          <input type="text" name="name" class="form-control" placeholder="Full name" required value="<?= htmlspecialchars($name ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Email address</label>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" required value="<?= htmlspecialchars($email ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Message</label>
          <textarea name="message" class="form-control" rows="5" placeholder="How can we help?" required><?= htmlspecialchars($message ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">Send message</button>
      </form>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
