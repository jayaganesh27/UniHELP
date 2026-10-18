<?php
// $pageTitle should be set before including this file
$user  = currentUser();
$flash = getFlash();
$csrf  = csrfToken();
$nav   = $currentNav ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? APP_NAME) ?> — <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/style.css">
</head>
<body>

<?php if (!empty($user['id'])): ?>
<!-- ── Navbar ─────────────────────────────────────────────── -->
<nav class="navbar">
    <div class="container navbar-inner">
        <a href="dashboard.php" class="navbar-brand">
            <div class="navbar-logo">U</div>
            UniHELP
        </a>
        <button class="navbar-toggle" id="navToggle" aria-label="Toggle menu">&#9776;</button>
        <div class="navbar-links" id="navLinks">
            <a href="dashboard.php"          class="nav-link <?= $nav==='dashboard' ?'active':'' ?>">⊞ Dashboard</a>
            <a href="requests.php"           class="nav-link <?= $nav==='requests'  ?'active':'' ?>">📋 Requests</a>
            <a href="sessions.php"           class="nav-link <?= $nav==='sessions'  ?'active':'' ?>">📅 Upcoming Sessions</a>
            <?php if ($user['is_volunteer']): ?>
            <a href="volunteer_dashboard.php" class="nav-link <?= $nav==='vol_dash' ?'active':'' ?>">🙋 My Queue</a>
            <?php endif; ?>
            <a href="profile.php"            class="nav-link <?= $nav==='profile'   ?'active':'' ?>">👤 Profile</a>
            <a href="logout.php"             class="nav-link nav-logout">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                Logout
            </a>
        </div>
    </div>
</nav>
<?php endif; ?>

<!-- ── Flash message ─────────────────────────────────────── -->
<?php if ($flash): ?>
<div class="flash flash-<?= e($flash['type']) ?>" id="flashMsg">
    <?= e($flash['msg']) ?>
    <button class="flash-close" onclick="this.parentElement.remove()">×</button>
</div>
<?php endif; ?>

<main class="main-content">
