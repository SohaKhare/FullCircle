<?php
$pageTitle = "Available Food";
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('ngo');

$user = currentUser();
$success = '';
$error = '';

// Handle quick request from URL (homepage link)
if (isset($_GET['request']) && is_numeric($_GET['request'])) {
    $_POST['action'] = 'request';
    $_POST['donation_id'] = (int)$_GET['request'];
}

// ─── Handle Request ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_POST['action'])) {
    $action = $_POST['action'] ?? '';
    if ($action === 'request') {
        $did = (int)$_POST['donation_id'];

        // Check already requested
        $check = $conn->prepare("SELECT request_id FROM requests WHERE donation_id=? AND ngo_id=? AND request_status NOT IN ('rejected')");
        $check->bind_param("ii", $did, $user['id']);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "You have already requested this donation.";
        } else {
            $stmt = $conn->prepare("INSERT INTO requests (donation_id, ngo_id) VALUES (?,?)");
            $stmt->bind_param("ii", $did, $user['id']);
            if ($stmt->execute()) {
                $success = "Request submitted! The donor will review it.";
            } else {
                $error = "Failed to submit request.";
            }
        }
    }
}

// ─── Filters ───
$where = "d.status = 'available'";
$params = [];
$types = '';

$search = trim($_GET['search'] ?? '');
$filter_type = $_GET['type'] ?? '';

if ($search) {
    $where .= " AND (d.food_title LIKE ? OR d.pickup_address LIKE ?)";
    $s = "%$search%";
    $params[] = $s; $params[] = $s;
    $types .= 'ss';
}
if ($filter_type) {
    $where .= " AND d.food_type = ?";
    $params[] = $filter_type;
    $types .= 's';
}

$sql = "SELECT d.*, u.name as donor_name, u.phone as donor_phone
        FROM donations d JOIN users u ON d.donor_id = u.user_id
        WHERE $where ORDER BY d.created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$donations = $stmt->get_result();

// Get my requests
$my_requests = [];
$mr = $conn->query("SELECT donation_id FROM requests WHERE ngo_id={$user['id']} AND request_status NOT IN ('rejected')");
while ($r = $mr->fetch_assoc()) {
    $my_requests[] = $r['donation_id'];
}

require_once '../includes/header.php';
?>

<div class="dashboard">
  <div class="dash-header">
    <div>
      <div class="dash-title">Available Food Donations</div>
      <div class="dash-subtitle">Browse available donations and request what you need</div>
    </div>
    <a href="my_requests.php" class="btn btn-outline">My Requests</a>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success"><strong>Success:</strong> <?= $success ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-error"><strong>Error:</strong> <?= $error ?></div>
  <?php endif; ?>

  <!-- FILTER BAR -->
  <form method="GET" class="filter-bar">
    <div class="filter-group">
      <label class="filter-label">Search</label>
      <input type="text" name="search" class="form-control" placeholder="Food name or location..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="filter-group" style="flex:0; min-width:180px;">
      <label class="filter-label">Food Type</label>
      <select name="type" class="form-control">
        <option value="">All Types</option>
        <option value="veg"     <?= $filter_type==='veg'     ? 'selected' : '' ?>>Veg Only</option>
        <option value="non-veg" <?= $filter_type==='non-veg' ? 'selected' : '' ?>>Non-Veg Only</option>
      </select>
    </div>
    <div style="display:flex; gap:8px; align-items:flex-end;">
      <button type="submit" class="btn btn-primary">Search</button>
      <a href="request_food.php" class="btn btn-outline">Clear</a>
    </div>
  </form>

  <?php if ($donations->num_rows === 0): ?>
    <div class="empty-state">
      <div class="icon"></div>
      <h3>No donations available right now</h3>
      <p>Check back soon — donors are regularly adding new items.</p>
    </div>
  <?php else: ?>
    <div class="donation-grid">
      <?php while($d = $donations->fetch_assoc()):
        $already_requested = in_array($d['donation_id'], $my_requests);
      ?>
      <div class="donation-card"
           data-title="<?= htmlspecialchars($d['food_title']) ?>"
           data-type="<?= $d['food_type'] ?>"
           data-status="<?= $d['status'] ?>">
        <span class="card-badge badge-<?= $d['food_type'] === 'veg' ? 'veg' : 'nonveg' ?>">
          <?= $d['food_type'] === 'veg' ? 'Veg' : 'Non-Veg' ?>
        </span>
        <div class="card-title"><?= htmlspecialchars($d['food_title']) ?></div>

        <?php if ($d['description']): ?>
          <p style="font-size:0.83rem; color:var(--text-muted); margin-bottom:12px;">
            <?= htmlspecialchars(substr($d['description'],0,80)) ?>
          </p>
        <?php endif; ?>

        <div class="card-meta">
          <div class="card-meta-row"><span class="icon">Qty:</span> <?= htmlspecialchars($d['quantity']) ?></div>
          <div class="card-meta-row"><span class="icon">Expires:</span> <?= date('d M, g:i A', strtotime($d['expiry_time'])) ?></div>
          <div class="card-meta-row"><span class="icon">Pickup:</span> <?= htmlspecialchars($d['pickup_address']) ?></div>
          <div class="card-meta-row"><span class="icon">Donor:</span> <?= htmlspecialchars($d['donor_name']) ?></div>
          <?php if ($d['donor_phone']): ?>
          <div class="card-meta-row"><span class="icon">Phone:</span> <?= htmlspecialchars($d['donor_phone']) ?></div>
          <?php endif; ?>
        </div>

        <div class="card-actions">
          <?php if ($already_requested): ?>
            <span class="card-badge badge-pending">Requested</span>
          <?php else: ?>
            <form method="POST">
              <input type="hidden" name="action" value="request">
              <input type="hidden" name="donation_id" value="<?= $d['donation_id'] ?>">
              <button class="btn btn-primary btn-sm" type="submit">Request This</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
