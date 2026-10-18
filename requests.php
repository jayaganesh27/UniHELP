<?php
require_once 'config.php';
requireLogin();

$u    = currentUser();
$db   = db();
$step = 'form';  // 'form' | 'results'
$errors = [];

/* ── Handle request submission ───────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    verifyCsrf();
    $category = $_POST['category'] ?? '';
    $desc     = trim($_POST['description'] ?? '');
    $pdt      = $_POST['preferred_datetime'] ?? null;
    $validCategories = ['academic', 'technology', 'new_student', 'general'];

    if (!in_array($category, $validCategories, true)) $errors[] = 'Choose a valid support category.';
    if (strlen($desc) < 10 || strlen($desc) > 500) $errors[] = 'Description must be between 10 and 500 characters.';
    if (!$pdt || strtotime($pdt) <= time()) $errors[] = 'Choose a preferred date and time in the future.';

    if (!$errors) {
        // Save the request
        $stmt = $db->prepare(
            'INSERT INTO support_requests (student_id, category, description, preferred_datetime)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$u['id'], $category, $desc, $pdt]);
        $_SESSION['last_request_id'] = $db->lastInsertId();
        $_SESSION['search_params']   = compact('category');
        $step = 'results';
    }
}

// If coming back to results page
if (isset($_SESSION['search_params']) && isset($_GET['view']) && $_GET['view'] === 'results') {
    $step = 'results';
}

/* ── Load volunteers (for results) ───────────────────────── */
$volunteers = [];
if ($step === 'results') {
    $sql = 'SELECT u.id, u.full_name, u.photo,
                   GROUP_CONCAT(DISTINCT vs.skill_type ORDER BY vs.skill_type SEPARATOR ",") AS skills
            FROM users u
            LEFT JOIN volunteer_skills vs ON vs.user_id = u.id
            WHERE u.is_volunteer = 1 AND u.id != ?';

    $execParams = [$u['id']];

    $sql .= ' GROUP BY u.id ORDER BY u.full_name';

    $stmt = $db->prepare($sql);
    $stmt->execute($execParams);
    $volunteers = $stmt->fetchAll();
}

$pageTitle  = 'Submit Request';
$currentNav = 'requests';
include 'includes/header.php';
?>
<meta name="csrf" content="<?= e(csrfToken()) ?>">

<?php if ($step === 'form'): ?>
<!-- ══ Request Form ════════════════════════════════════════ -->
<div class="page-header">
    <h1>Submit Support Request</h1>
    <p>Submit your request, then browse volunteers using basic filters.</p>
</div>

<div style="max-width:680px;">
<?php if ($errors): ?>
<div class="flash flash-error" style="border-radius:10px;margin-bottom:16px;">
    <?= e(implode(' ', $errors)) ?>
</div>
<?php endif; ?>
<div class="card">
    <div class="card-header">New Support Request</div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="csrf"   value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="search" value="1">

            <div class="form-group">
                <label>Support Category <span class="req">*</span></label>
                <select class="form-control" name="category" required>
                    <option value="">Select a support category…</option>
                    <option value="academic">📚 Academic Support</option>
                    <option value="technology">💻 Technology Support</option>
                    <option value="new_student">🗺 New Student Support</option>
                    <option value="general">💬 General Support</option>
                </select>
            </div>

            <div class="form-group">
                <label>Description <span class="req">*</span></label>
                <textarea class="form-control" name="description"
                          rows="4" maxlength="500" required
                          placeholder="Describe what you need help with in as much detail as possible…"
                          data-maxlength="500" data-counter="descCounter"></textarea>
                <div class="form-hint" style="text-align:right;">
                    <span id="descCounter">0/500</span> characters
                </div>
            </div>

            <div class="row">
                <div class="col-half">
                    <div class="form-group">
                        <label>Preferred Date &amp; Time <span class="req">*</span></label>
                        <input class="form-control" type="datetime-local" name="preferred_datetime"
                               min="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                Search Volunteers
            </button>
        </form>
    </div>
</div>
</div>

<?php else: ?>
<!-- ══ Search Results ══════════════════════════════════════ -->
<div style="display:flex;align-items:center;gap:14px;margin-bottom:24px;">
    <a href="requests.php" class="btn btn-outline btn-sm">← Back</a>
    <div>
        <h1 style="font-family:Nunito,sans-serif;font-size:1.5rem;font-weight:800;margin:0;">
            Volunteer Search Results
        </h1>
        <p class="text-muted" style="font-size:.875rem;margin:4px 0 0;">
            <span id="volCount"><?= count($volunteers) ?></span> volunteer(s) available to browse
        </p>
    </div>
</div>

<!-- Filters -->
<div class="filters-bar" id="filterForm">
    <div class="filter-group">
        <label>Filter by Skill</label>
        <select class="form-control" name="skill" onchange="">
            <option value="">All Skills</option>
            <option value="academic">Academic Support</option>
            <option value="technology">Technology Support</option>
            <option value="new_student">New Student Support</option>
            <option value="general">General Support</option>
        </select>
    </div>
</div>

<?php if ($volunteers): ?>
    <div class="vol-grid">
        <?php foreach ($volunteers as $vol):
            $skills = $vol['skills'] ? implode(', ', array_map(fn($s)=>ucwords(str_replace('_',' ',$s)), explode(',',$vol['skills']))) : '—';
        ?>
        <div class="vol-card" data-skill="<?= e($vol['skills'] ?? '') ?>">

            <div class="vol-header">
                <img src="<?= e($vol['photo'] ?? '') ?>"
                     alt="<?= e($vol['full_name']) ?>"
                     class="vol-avatar"
                     onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($vol['full_name']) ?>&background=1F3864&color=fff&size=100'">
                <div>
                    <div style="font-weight:800;font-family:Nunito,sans-serif;"><?= e($vol['full_name']) ?></div>
                </div>
            </div>

            <div style="font-size:.8rem;color:#475569;display:flex;flex-direction:column;gap:6px;margin-bottom:14px;">
                <span>🎓 <?= e($skills) ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div style="margin-top:28px;padding-top:20px;border-top:1px solid var(--border);">
        <p class="text-muted" style="font-size:.875rem;margin-bottom:12px;">
            Your request is now visible in the volunteer queue. A session will appear after a volunteer accepts it and chooses a date and time.
        </p>
        <a href="dashboard.php" class="btn btn-success btn-lg">Back to Dashboard</a>
    </div>
    <?php else: ?>
    <div class="card card-body" style="text-align:center;padding:48px;">
        <div style="font-size:2.5rem;margin-bottom:12px;">🔍</div>
        <h3>No volunteers found</h3>
        <p class="text-muted">Try broadening your category or check back later.</p>
        <a href="requests.php" class="btn btn-primary mt-3">← Modify Request</a>
    </div>
<?php endif; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
