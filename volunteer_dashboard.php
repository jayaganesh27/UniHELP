<?php
require_once 'config.php';
requireVolunteer();

$u  = currentUser();
$db = db();

// Load incoming open requests for basic manual acceptance
$incoming = $db->prepare(
    'SELECT sr.*, u.full_name AS student_name, u.student_id AS s_id
     FROM support_requests sr
     JOIN users u ON u.id = sr.student_id
     WHERE sr.status = "open"
       AND sr.student_id != ?
     ORDER BY sr.created_at DESC'
);
$incoming->execute([$u['id']]);
$requests = $incoming->fetchAll();

$acceptedStmt = $db->prepare(
    'SELECT COUNT(*) FROM sessions WHERE volunteer_id = ? AND scheduled_datetime >= NOW()'
);
$acceptedStmt->execute([$u['id']]);
$accepted = (int) $acceptedStmt->fetchColumn();

$pending = count($requests);

$pageTitle  = 'Volunteer Dashboard';
$currentNav = 'vol_dash';
include 'includes/header.php';
?>
<meta name="csrf" content="<?= e(csrfToken()) ?>">

<div class="page-header">
    <h1>Volunteer Dashboard</h1>
    <p>Manage incoming student support requests.</p>
</div>

<!-- ── Stat row ──────────────────────────────────────────── -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:#e8edf7;">📋</div>
        <div>
            <div class="stat-value text-navy"><?= $pending ?></div>
            <div class="stat-label">Pending Requests</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f0fdf4;">✅</div>
        <div>
            <div class="stat-value" style="color:var(--success);"><?= $accepted ?></div>
            <div class="stat-label">Upcoming Sessions</div>
        </div>
    </div>
</div>

<!-- ── Incoming requests ─────────────────────────────────── -->
<h2 style="font-family:Nunito,sans-serif;font-size:1.15rem;font-weight:800;margin-bottom:16px;">
    Incoming Requests
</h2>

<?php if ($requests): ?>
<?php foreach ($requests as $r):
    $catLabels = ['academic'=>'Academic Support','technology'=>'Technology Support','new_student'=>'New Student Support','general'=>'General Support'];
    $isUrgent  = strtotime($r['preferred_datetime'] ?? 'tomorrow') < strtotime('+2 days');
?>
<div class="request-card" id="req-<?= $r['id'] ?>">
    <div class="card-row">
        <div style="flex:1;">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:6px;">
                <strong style="font-family:Nunito,sans-serif;"><?= e($r['student_name']) ?></strong>
                <span class="text-muted" style="font-size:.78rem;">(<?= e($r['s_id']) ?>)</span>
                <?php if ($isUrgent && $r['preferred_datetime']): ?>
                <span class="badge badge-red">Urgent</span>
                <?php endif; ?>
                <span class="badge badge-blue"><?= e($catLabels[$r['category']] ?? $r['category']) ?></span>
            </div>
            <p style="font-size:.875rem;color:#475569;margin:0 0 8px;"><?= e(substr($r['description'],0,200)) ?><?= strlen($r['description'])>200?'…':'' ?></p>
            <div class="card-meta">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                Submitted: <?= e(date('D, d M Y g:ia', strtotime($r['created_at']))) ?>
                <?php if ($r['preferred_datetime']): ?>
                · Preferred: <?= e(date('D, d M Y g:ia', strtotime($r['preferred_datetime']))) ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-actions">
            <button class="btn btn-success btn-sm"
                    data-action="accept"
                    data-id="<?= $r['id'] ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
                Accept
            </button>
        </div>
    </div>
</div>
<?php endforeach; ?>
<?php else: ?>
<div class="card card-body" style="text-align:center;padding:52px;">
    <div style="font-size:2.5rem;margin-bottom:14px;">🎉</div>
    <h3 style="margin-bottom:8px;">All caught up!</h3>
    <p class="text-muted">No pending requests right now. Check back soon.</p>
</div>
<?php endif; ?>

<!-- ── Accept modal ──────────────────────────────────────── -->
<div class="modal-overlay" id="acceptModal" style="display:none;">
    <div class="modal-box">
        <div class="modal-title">✅ Schedule Session</div>
        <p class="text-muted" style="font-size:.875rem;margin-bottom:16px;">
            Choose the date and time for this session before accepting.
        </p>
        <div class="form-group">
            <label>Scheduled Date &amp; Time</label>
            <input class="form-control" type="datetime-local" id="scheduleTime"
                   min="<?= date('Y-m-d\TH:i') ?>">
        </div>
        <div class="modal-actions">
            <button class="btn btn-success" onclick="confirmAccept()">Confirm Accept</button>
            <button class="btn btn-outline" onclick="closeModal('acceptModal')">Cancel</button>
        </div>
    </div>
</div>

<script>
// Override the default AJAX accept to show the modal first
let pendingAcceptId = null;

document.querySelectorAll('[data-action="accept"]').forEach(btn => {
    btn.addEventListener('click', e => {
        e.stopImmediatePropagation();
        pendingAcceptId = btn.dataset.id;
        openModal('acceptModal');
    }, true);
});

async function confirmAccept() {
    const scheduledAt = document.getElementById('scheduleTime').value;
    if (!scheduledAt) { alert('Please choose a date and time.'); return; }

    const csrf = document.querySelector('meta[name=csrf]').content;
    const res  = await fetch('ajax/handle_request.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'accept', request_id: pendingAcceptId,
            scheduled_at: scheduledAt, csrf
        }),
    });
    const data = await res.json();
    if (data.ok) {
        closeModal('acceptModal');
        const card = document.getElementById('req-' + pendingAcceptId);
        card.classList.add('accepted');
        card.querySelector('.card-actions').innerHTML =
            '<span class="text-success font-bold">✓ Accepted — Session Booked</span>';
    } else {
        alert(data.error || 'Error');
    }
}
</script>

<?php include 'includes/footer.php'; ?>
