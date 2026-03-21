<?php
$pageTitle = "Home";
require_once 'includes/db.php';
require_once 'includes/header.php';

$total_donations = $conn->query("SELECT COUNT(*) FROM donations")->fetch_row()[0];
$total_ngos      = $conn->query("SELECT COUNT(*) FROM users WHERE role='ngo'")->fetch_row()[0];
$completed       = $conn->query("SELECT COUNT(*) FROM donations WHERE status='completed'")->fetch_row()[0];
?>

<section class="hero">
  <div class="hero-inner">
    <div class="hero-eyebrow">Food Donation Network</div>
    <h1>Surplus food, <em>purposefully</em><br>redirected.</h1>
    <p class="hero-sub">FullCircle connects donors with verified NGOs to ensure surplus food reaches those who need it — with minimal friction and full transparency.</p>
    <div class="hero-btns">
      <a href="<?= isset($_SESSION['role']) && $_SESSION['role']==='donor' ? $base.'donor/donate_food.php' : $base.'register.php?role=donor' ?>" class="btn btn-hero-light btn-lg">Donate Food</a>
      <a href="<?= isset($_SESSION['role']) && $_SESSION['role']==='ngo'   ? $base.'ngo/request_food.php'  : $base.'register.php?role=ngo' ?>"   class="btn btn-hero-ghost btn-lg">Request Food</a>
    </div>
  </div>
</section>

<div class="stats-strip">
  <div class="stats-inner">
    <div><div class="stat-num" data-count="<?= $total_donations ?>"><?= $total_donations ?></div><div class="stat-label">Donations listed</div></div>
    <div><div class="stat-num" data-count="<?= $total_ngos      ?>"><?= $total_ngos      ?></div><div class="stat-label">NGOs registered</div></div>
    <div><div class="stat-num" data-count="<?= $completed       ?>"><?= $completed       ?></div><div class="stat-label">Donations completed</div></div>
  </div>
</div>

<section class="section">
  <div class="section-label">How it works</div>
  <h2 class="section-title">Three steps from surplus to served</h2>
  <p class="section-sub">Designed to be as frictionless as possible for both donors and NGOs.</p>
  <div class="steps-grid">
    <div class="step-item fade-up">
      <div class="step-num">01</div>
      <h3>Donor lists food</h3>
      <p>Restaurants, households, or organisations post available food with quantity, type, and pickup window.</p>
    </div>
    <div class="step-item fade-up">
      <div class="step-num">02</div>
      <h3>NGO requests it</h3>
      <p>Registered NGOs browse available donations and submit a collection request in seconds.</p>
    </div>
    <div class="step-item fade-up">
      <div class="step-num">03</div>
      <h3>Pickup confirmed</h3>
      <p>The donor approves the request, coordinates pickup, and marks it complete when delivered.</p>
    </div>
  </div>
</section>

<?php
$recent = $conn->query("
  SELECT d.*, u.name AS donor_name
  FROM donations d JOIN users u ON d.donor_id = u.user_id
  WHERE d.status = 'available'
  ORDER BY d.created_at DESC LIMIT 6
");
if ($recent->num_rows > 0):
?>
<section class="section" style="padding-top:0">
  <div class="section-label">Full transparency</div>
  <h2 class="section-title">Find detailed donations</h2>
  <p class="section-sub">Sample Donations:</p>
  <div class="cards-grid">
    <?php while ($d = $recent->fetch_assoc()): ?>
    <div class="card fade-up">
      <span class="card-tag tag-<?= $d['food_type'] === 'veg' ? 'veg' : 'nonveg' ?>"><?= ucfirst($d['food_type']) ?></span>
      <div class="card-title"><?= htmlspecialchars($d['food_title']) ?></div>
      <div class="card-meta">
        <div class="meta-row"><span class="meta-key">Quantity</span><span class="meta-val"><?= htmlspecialchars($d['quantity']) ?></span></div>
        <div class="meta-row"><span class="meta-key">Expires</span><span class="meta-val"><?= date('d M, g:i A', strtotime($d['expiry_time'])) ?></span></div>
        <div class="meta-row"><span class="meta-key">Pickup</span><span class="meta-val"><?= htmlspecialchars(mb_strimwidth($d['pickup_address'],0,45,'...')) ?></span></div>
        <div class="meta-row"><span class="meta-key">Donor</span><span class="meta-val"><?= htmlspecialchars($d['donor_name']) ?></span></div>
      </div>
      <div class="card-actions">
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'ngo'): ?>
          <a href="<?= $base ?>ngo/request_food.php?request=<?= $d['donation_id'] ?>" class="btn btn-primary btn-sm">Request</a>
        <?php else: ?>
          <a href="<?= $base ?>register.php?role=ngo" class="btn btn-outline btn-sm">Sign in to request</a>
        <?php endif; ?>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
</section>
<?php endif; ?>

<div class="cta-band">
  <h2>Ready to make a difference?</h2>
  <p>Join donors and NGOs already using FullCircle to reduce waste and feed communities across the city.</p>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
    <a href="<?= $base ?>register.php?role=donor" class="btn btn-primary btn-lg">Register as Donor</a>
    <a href="<?= $base ?>register.php?role=ngo"   class="btn btn-outline  btn-lg">Register as NGO</a>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
