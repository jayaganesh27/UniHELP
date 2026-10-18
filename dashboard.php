<?php
require_once 'config.php';
requireLogin();

$u          = currentUser();
$pageTitle  = 'Dashboard';
$currentNav = 'dashboard';

// Fetch stats
$db = db();

$upcoming = $db->prepare(
    'SELECT COUNT(*) FROM sessions
     WHERE (student_id = ? OR volunteer_id = ?) AND scheduled_datetime >= NOW()'
);
$upcoming->execute([$u['id'], $u['id']]);
$upcoming = $upcoming->fetchColumn();

include 'includes/header.php';
?>
<meta name="csrf" content="<?= e(csrfToken()) ?>">

<div class="page-header">
    <h1>Welcome back, <?= e(explode(' ', $u['name'])[0]) ?>! 👋</h1>
    <p>Here's a summary of your UniHELP activity.</p>
</div>

<!-- ── Stat cards ────────────────────────────────────────── -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:#f0fdf4;">📅</div>
        <div>
            <div class="stat-value" style="color:var(--success);"><?= $upcoming ?></div>
            <div class="stat-label">Upcoming Sessions</div>
        </div>
    </div>
</div>

<!-- ── Quick actions ────────────────────────────────────── -->
<div class="card card-body mb-4">
    <h3 style="margin-bottom:16px;">Quick Actions</h3>
    <div style="display:flex;gap:12px;flex-wrap:wrap;">
        <a href="requests.php"  class="btn btn-primary">📋 Submit Request</a>
        <a href="sessions.php"  class="btn btn-outline">📅 My Sessions</a>
        <a href="profile.php"   class="btn btn-outline">👤 Edit Profile</a>
        <?php if ($u['is_volunteer']): ?>
        <a href="volunteer_dashboard.php" class="btn btn-navy">🙋 My Queue</a>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
