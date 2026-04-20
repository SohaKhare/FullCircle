<?php
$pageTitle = "My Donations";
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('donor');
$user = currentUser();
$success = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $did = (int)$_POST['donation_id'];
        $conn->query("DELETE FROM requests WHERE donation_id=$did");
        $conn->query("DELETE FROM donations WHERE donation_id=$did AND donor_id={$user['id']}");
        $success = "Donation removed.";
    } elseif ($action === 'approve') {
        $rid = (int)$_POST['request_id']; $did = (int)$_POST['donation_id'];
        $conn->query("UPDATE requests SET request_status='approved' WHERE request_id=$rid");
        $conn->query("UPDATE donations SET status='requested' WHERE donation_id=$did");
        $success = "Request approved.";
    } elseif ($action === 'reject') {
        $rid = (int)$_POST['request_id'];
        $conn->query("UPDATE requests SET request_status='rejected' WHERE request_id=$rid");
        $success = "Request declined.";
    } elseif ($action === 'complete') {
        $rid = (int)$_POST['request_id']; $did = (int)$_POST['donation_id'];
        $conn->query("UPDATE requests SET request_status='completed' WHERE request_id=$rid");
        $conn->query("UPDATE donations SET status='completed' WHERE donation_id=$did");
        $success = "Marked as completed.";
    }
}

$donations = $conn->query("SELECT * FROM donations WHERE donor_id={$user['id']} ORDER BY created_at DESC");
require_once '../includes/header.php';
?>

<div class="dashboard">
  <div class="dash-header">
    <div>
      <div class="dash-title">My donations</div>
      <div class="dash-sub">Manage your listings and respond to NGO requests.</div>
    </div>
    <a href="donate_food.php" class="btn btn-primary">Post new donation</a>
  </div>

  <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
  <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <?php if ($donations->num_rows === 0): ?>
    <div class="empty-state">
      <div class="empty-icon">+</div>
      <h3>No donations yet</h3>
      <p>Post your first donation to get started.</p>
      <a href="donate_food.php" class="btn btn-primary" style="margin-top:16px">Post donation</a>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Food</th><th>Quantity</th><th>Type</th><th>Expires</th><th>Status</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($d = $donations->fetch_assoc()):
            $reqs = $conn->query("
              SELECT r.*, u.name AS ngo_name, u.ngo_name AS org_name, u.phone
              FROM requests r JOIN ngos u ON r.ngo_id = u.ngo_id
              WHERE r.donation_id={$d['donation_id']}
              ORDER BY r.request_date DESC
            ");
          ?>
          <tr>
            <td>
              <strong><?= htmlspecialchars($d['food_title']) ?></strong>
              <?php if ($d['description']): ?>
                <br><small style="color:var(--text-muted)"><?= htmlspecialchars(mb_strimwidth($d['description'],0,55,'...')) ?></small>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($d['quantity']) ?></td>
            <td><span class="badge badge-<?= $d['food_type'] === 'veg' ? 'veg' : 'nonveg' ?>"><?= $d['food_type'] ?></span></td>
            <td><?= date('d M, g:i A', strtotime($d['expiry_time'])) ?></td>
            <td><span class="badge badge-<?= $d['status'] ?>"><?= ucfirst($d['status']) ?></span></td>
            <td>
              <div style="display:flex;gap:6px;flex-wrap:wrap;">
                <?php if ($d['status'] === 'available'): ?>
                  <a href="edit_donation.php?id=<?= $d['donation_id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                  <form method="POST" style="display:inline" onsubmit="return confirmDelete('Remove this donation?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="donation_id" value="<?= $d['donation_id'] ?>">
                    <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                  </form>
                <?php endif; ?>
                <?php if ($reqs->num_rows > 0): ?>
                  <button class="btn btn-outline btn-sm" onclick="openModal('req-<?= $d['donation_id'] ?>')">
                    Requests (<?= $reqs->num_rows ?>)
                  </button>
                <?php endif; ?>
              </div>
            </td>
          </tr>

          <?php $reqs->data_seek(0); if ($reqs->num_rows > 0): ?>
          <div class="modal-overlay" id="req-<?= $d['donation_id'] ?>">
            <div class="modal">
              <div class="modal-header">
                <span class="modal-title">Requests for <?= htmlspecialchars($d['food_title']) ?></span>
                <button class="modal-close" onclick="closeModal('req-<?= $d['donation_id'] ?>')">&times;</button>
              </div>
              <?php while ($r = $reqs->fetch_assoc()): ?>
              <div class="request-item">
                <div class="request-org"><?= htmlspecialchars($r['org_name'] ?: $r['ngo_name']) ?></div>
                <div class="request-meta">Phone: <?= htmlspecialchars($r['phone']) ?></div>
                <div class="request-meta">Requested: <?= date('d M Y, g:i A', strtotime($r['request_date'])) ?></div>
                <span class="badge badge-<?= $r['request_status'] ?>"><?= ucfirst($r['request_status']) ?></span>
                <?php if ($r['request_status'] === 'pending'): ?>
                <div class="request-actions">
                  <form method="POST">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="request_id" value="<?= $r['request_id'] ?>">
                    <input type="hidden" name="donation_id" value="<?= $d['donation_id'] ?>">
                    <button class="btn btn-primary btn-sm">Approve</button>
                  </form>
                  <form method="POST">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="request_id" value="<?= $r['request_id'] ?>">
                    <button class="btn btn-danger btn-sm">Decline</button>
                  </form>
                </div>
                <?php elseif ($r['request_status'] === 'approved'): ?>
                <div class="request-actions">
                  <form method="POST">
                    <input type="hidden" name="action" value="complete">
                    <input type="hidden" name="request_id" value="<?= $r['request_id'] ?>">
                    <input type="hidden" name="donation_id" value="<?= $d['donation_id'] ?>">
                    <button class="btn btn-primary btn-sm">Mark complete</button>
                  </form>
                </div>
                <?php endif; ?>
              </div>
              <?php endwhile; ?>
            </div>
          </div>
          <?php endif; ?>

          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>