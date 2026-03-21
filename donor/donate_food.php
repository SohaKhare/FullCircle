<?php
$pageTitle = "Donate Food";
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('donor');
$user = currentUser();
$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['food_title']     ?? '');
    $desc    = trim($_POST['description']    ?? '');
    $qty     = trim($_POST['quantity']       ?? '');
    $type    = $_POST['food_type']           ?? 'veg';
    $expiry  = $_POST['expiry_time']         ?? '';
    $address = trim($_POST['pickup_address'] ?? '');

    if (!$title || !$qty || !$expiry || !$address) {
        $error = "Please fill all required fields.";
    } else {
        $stmt = $conn->prepare("INSERT INTO donations (donor_id,food_title,description,quantity,food_type,expiry_time,pickup_address) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("issssss", $user['id'], $title, $desc, $qty, $type, $expiry, $address);
        if ($stmt->execute()) {
            $success = "Donation posted. NGOs can now request it.";
        } else {
            $error = "Failed to post donation. Please try again.";
        }
    }
}

require_once '../includes/header.php';
?>

<div class="dashboard">
  <div class="dash-header">
    <div>
      <div class="dash-title">Post a donation</div>
      <div class="dash-sub">List surplus food for registered NGOs to request.</div>
    </div>
    <a href="my_donations.php" class="btn btn-outline">View my donations</a>
  </div>

  <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

  <div class="form-card wide" style="margin:0; box-shadow:none;">
    <form method="POST">
      <div class="form-group">
        <label class="form-label">Food title <span style="color:#dc2626">*</span></label>
        <input type="text" name="food_title" class="form-control" placeholder="e.g. Cooked rice and dal, Bread loaves" required>
      </div>
      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2" placeholder="Any relevant details — allergens, packaging, freshness"></textarea>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Quantity <span style="color:#dc2626">*</span></label>
          <input type="text" name="quantity" class="form-control" placeholder="e.g. 20 meals, 5 kg, 30 pieces" required>
        </div>
        <div class="form-group">
          <label class="form-label">Food type</label>
          <select name="food_type" class="form-control">
            <option value="veg">Vegetarian</option>
            <option value="non-veg">Non-vegetarian</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Expires / best before <span style="color:#dc2626">*</span></label>
          <input type="datetime-local" name="expiry_time" class="form-control" required min="<?= date('Y-m-d\TH:i') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Pickup address <span style="color:#dc2626">*</span></label>
          <input type="text" name="pickup_address" class="form-control" placeholder="Full address for collection" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-lg">Post donation</button>
    </form>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
