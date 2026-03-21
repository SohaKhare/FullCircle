<?php
$pageTitle = "My Requests";
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('ngo');

$user = currentUser();
$success = '';

// Cancel Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel') {
    $rid = (int)$_POST['request_id'];
    $conn->query("UPDATE requests SET request_status='rejected' WHERE request_id=$rid AND ngo_id={$user['id']}");
    $success = "Request cancelled.";
}

// Fetch my requests
$requests = $conn->query("
    SELECT r.*, d.food_title, d.quantity, d.food_type, d.expiry_time, d.pickup_address, d.status as don_status,
           u.name as donor_name, u.phone as donor_phone
    FROM requests r
    JOIN donations d ON r.donation_id = d.donation_id
    JOIN users u ON d.donor_id = u.user_id
    WHERE r.ngo_id = {$user['id']}
    ORDER BY r.request_date DESC
");

require_once '../includes/header.php';
?>

<div class="dashboard">
  <div class="dash-header">
    <div>
      <div class="dash-title">My Requests</div>
      <div class="dash-subtitle">Track the status of your food requests</div>
    </div>
    <a href="request_food.php" class="btn btn-primary">+ Request More Food</a>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success"><strong>Success:</strong> <?= $success ?></div>
  <?php endif; ?>

  <?php if ($requests->num_rows === 0): ?>
    <div class="empty-state">
      <div class="icon"></div>
      <h3>No requests yet</h3>
      <p>Browse available donations and request what your community needs.</p>
      <a href="request_food.php" class="btn btn-primary" style="margin-top:16px">Browse Donations</a>
    </div>
  <?php else: ?>
    <div class="donation-grid">
      <?php while($r = $requests->fetch_assoc()): ?>
      <div class="donation-card">
        <span class="card-badge badge-<?= $r['request_status'] ?>">
          <?= ucfirst($r['request_status']) ?>
        </span>
        <div class="card-title"><?= htmlspecialchars($r['food_title']) ?></div>
        <div class="card-meta">
          <div class="card-meta-row"><span class="icon">Qty:</span> <?= htmlspecialchars($r['quantity']) ?></div>
          <div class="card-meta-row"><span class="icon">Expires:</span> <?= date('d M, g:i A', strtotime($r['expiry_time'])) ?></div>
          <div class="card-meta-row"><span class="icon">Pickup:</span> <?= htmlspecialchars($r['pickup_address']) ?></div>
          <div class="card-meta-row"><span class="icon">Donor:</span> <?= htmlspecialchars($r['donor_name']) ?></div>
          <?php if ($r['request_status'] === 'approved'): ?>
          <div class="card-meta-row"><span class="icon">Phone:</span> <?= htmlspecialchars($r['donor_phone']) ?></div>
          <?php endif; ?>
          <div class="card-meta-row"><span class="icon">Requested:</span> <?= date('d M Y', strtotime($r['request_date'])) ?></div>
        </div>

        <?php if ($r['request_status'] === 'approved'): ?>
          <div class="alert alert-success" style="font-size:0.83rem; padding:10px;">
            Approved. Contact the donor to arrange pickup.
          </div>
        <?php endif; ?>

        <?php if ($r['request_status'] === 'pending'): ?>
          <form method="POST">
            <input type="hidden" name="action" value="cancel">
            <input type="hidden" name="request_id" value="<?= $r['request_id'] ?>">
            <button class="btn btn-danger btn-sm" onclick="return confirm('Cancel this request?')">Cancel Request</button>
          </form>
        <?php endif; ?>
      </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
