<?php
require_once 'config.php';
requireLogin();

$u  = currentUser();
$db = db();

$sessions = $db->prepare(
    'SELECT s.*,
            vol.full_name AS volunteer_name,
            vol.photo AS volunteer_photo,
            stu.full_name AS student_name,
            stu.photo AS student_photo,
            sr.category
     FROM sessions s
     JOIN users vol ON vol.id = s.volunteer_id
     JOIN users stu ON stu.id = s.student_id
     JOIN support_requests sr ON sr.id = s.request_id
     WHERE (s.student_id = ? OR s.volunteer_id = ?)
       AND s.scheduled_datetime >= NOW()
     ORDER BY s.scheduled_datetime ASC'
);
$sessions->execute([$u['id'], $u['id']]);
$upcoming = $sessions->fetchAll();

$catLabels = [
    'academic' => 'Academic Support',
    'technology' => 'Technology Support',
    'new_student' => 'New Student Support',
    'general' => 'General Support',
];

$pageTitle  = 'Upcoming Sessions';
$currentNav = 'sessions';
include 'includes/header.php';
?>

<div class="page-header">
    <h1>Upcoming Sessions</h1>
    <p>View support sessions booked for you as a student or volunteer.</p>
</div>

<?php if ($upcoming): ?>
<?php foreach ($upcoming as $session):
    $isVolunteer = $session['volunteer_id'] == $u['id'];
    $otherName = $isVolunteer ? $session['student_name'] : $session['volunteer_name'];
    $otherPhoto = $isVolunteer ? $session['student_photo'] : $session['volunteer_photo'];
?>
<div class="session-card">
    <div class="card-row">
        <div style="flex:1;">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:8px;">
                <img src="<?= e($otherPhoto ?? '') ?>"
                     alt="<?= e($otherName) ?>"
                     style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid var(--border);"
                     onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($otherName) ?>&background=1F3864&color=fff&size=80'">
                <div>
                    <strong style="font-family:Nunito,sans-serif;"><?= e($otherName) ?></strong>
                    <span class="text-muted" style="font-size:.78rem;margin-left:6px;">
                        <?= $isVolunteer ? '(Student)' : '(Volunteer)' ?>
                    </span>
                </div>
                <span class="badge badge-blue">
                    <?= e($catLabels[$session['category']] ?? $session['category']) ?>
                </span>
            </div>
            <div class="card-meta">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
                <?= e(date('D, d M Y', strtotime($session['scheduled_datetime']))) ?>
                &nbsp;·&nbsp;
                <?= e(date('g:i A', strtotime($session['scheduled_datetime']))) ?>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
<?php else: ?>
<div class="card card-body" style="text-align:center;padding:48px;">
    <h3 style="margin-bottom:8px;">No upcoming sessions</h3>
    <p class="text-muted">A session will appear here after a volunteer accepts and schedules a request.</p>
    <a href="requests.php" class="btn btn-primary mt-3">Submit Support Request</a>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
