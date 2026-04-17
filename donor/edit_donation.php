<?php
$pageTitle = "Edit Donation";
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('donor');

$user = currentUser();
$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM donations WHERE donation_id=? AND donor_id=?");
$stmt->bind_param("ii", $id, $user['id']);
$stmt->execute();
$d = $stmt->get_result()->fetch_assoc();

if (!$d) {
    header("Location: ../donor/my_donations.php");
    exit;
}

$success = '';
$error = '';

function normalizeExpiryDateToDatetime(string $expiryDateRaw): ?string {
  $expiryDateRaw = trim($expiryDateRaw);
  $dt = DateTime::createFromFormat('Y-m-d', $expiryDateRaw);
  if (!$dt || $dt->format('Y-m-d') !== $expiryDateRaw) {
    return null;
  }

  $today = new DateTime('today');
  if ($dt < $today) {
    return null;
  }

  return $dt->format('Y-m-d') . ' 23:59:59';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $food_title     = trim($_POST['food_title']);
    $description    = trim($_POST['description']);
    $quantity       = trim($_POST['quantity']);
    $food_type      = $_POST['food_type'];
    $expiryDateRaw  = $_POST['expiry_time'];
    $pickup_address = trim($_POST['pickup_address']);

    $expiry_time = normalizeExpiryDateToDatetime($expiryDateRaw);

    if (empty($food_title) || empty($quantity) || empty($expiry_time) || empty($pickup_address)) {
        $error = "Please fill all required fields.";
    } elseif (!preg_match('/^\d+$/', $quantity) || (int)$quantity <= 0) {
      $error = "Quantity must be a whole number greater than 0.";
    } else {
        $stmt = $conn->prepare("UPDATE donations SET food_title=?, description=?, quantity=?, food_type=?, expiry_time=?, pickup_address=? WHERE donation_id=? AND donor_id=?");
        $stmt->bind_param("ssssssii", $food_title, $description, $quantity, $food_type, $expiry_time, $pickup_address, $id, $user['id']);
        if ($stmt->execute()) {
            header("Location: ../donor/my_donations.php?updated=1");
            exit;
        } else {
            $error = "Failed to update. Try again.";
        }
    }
}

require_once '../includes/header.php';
?>

<div class="dashboard">
  <div class="dash-header">
    <div>
      <div class="dash-title">Edit Donation</div>
      <div class="dash-subtitle">Update the details for your donation</div>
    </div>
    <a href="my_donations.php" class="btn btn-outline">← Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error">Error: <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="form-card wide" style="max-width:680px; margin:0;">
    <form method="POST" action="">
      <div class="form-group">
        <label class="form-label">Food Title *</label>
        <input type="text" name="food_title" class="form-control" value="<?= htmlspecialchars($d['food_title']) ?>" required>
      </div>

      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($d['description']) ?></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Quantity *</label>
          <input type="number" name="quantity" class="form-control" value="<?= htmlspecialchars($d['quantity']) ?>" required min="1" step="1" inputmode="numeric" title="Enter a whole number greater than 0">
        </div>
        <div class="form-group">
          <label class="form-label">Food Type *</label>
          <select name="food_type" class="form-control" required>
            <option value="veg"     <?= $d['food_type']==='veg'     ? 'selected' : '' ?>>Vegetarian</option>
            <option value="non-veg" <?= $d['food_type']==='non-veg' ? 'selected' : '' ?>>Non-Vegetarian</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Best before date *</label>
          <input type="date" name="expiry_time" class="form-control" required min="<?= date('Y-m-d') ?>"
            value="<?= date('Y-m-d', strtotime($d['expiry_time'])) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Pickup Address *</label>
          <input type="text" name="pickup_address" class="form-control" value="<?= htmlspecialchars($d['pickup_address']) ?>" required>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:13px;">
        Save Changes
      </button>
    </form>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>