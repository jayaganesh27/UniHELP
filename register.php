<?php
require_once 'config.php';

if (!empty($_SESSION['user_id'])) redirect('dashboard.php');

$errors = [];
$vals   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $vals = [
        'student_id'   => trim($_POST['student_id']   ?? ''),
        'full_name'    => trim($_POST['full_name']     ?? ''),
        'email'        => trim($_POST['email']         ?? ''),
        'password'     => $_POST['password']           ?? '',
        'confirm_pwd'  => $_POST['confirm_password']   ?? '',
    ];

    if (!$vals['student_id'])  $errors[] = 'Student ID is required.';
    if (!$vals['full_name'])   $errors[] = 'Full name is required.';
    if (!filter_var($vals['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid university email.';
    if (strlen($vals['password']) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($vals['password'] !== $vals['confirm_pwd']) $errors[] = 'Passwords do not match.';

    if (!$errors) {
        // Check unique email / student_id
        $chk = db()->prepare('SELECT id FROM users WHERE email = ? OR student_id = ? LIMIT 1');
        $chk->execute([$vals['email'], $vals['student_id']]);
        if ($chk->fetch()) {
            $errors[] = 'That email or Student ID is already registered.';
        } else {
            $hash = password_hash($vals['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = db()->prepare(
                'INSERT INTO users (student_id, full_name, email, password_hash, is_volunteer, role)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $vals['student_id'],
                $vals['full_name'],
                $vals['email'],
                $hash,
                0,
                'student',
            ]);
            setFlash('success', 'Account created! Please log in.');
            redirect('index.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — UniHELP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="auth-wrap">
    <div class="auth-box">
        <div class="auth-logo">
            <div class="auth-logo-icon">🎓</div>
            <h1>Join UniHELP</h1>
            <p>Peer support for every student</p>
        </div>

        <?php if ($errors): ?>
        <div class="flash flash-error" style="border-radius:10px;margin-bottom:16px;">
            <?= e(implode('<br>', $errors)) ?>
        </div>
        <?php endif; ?>

        <div class="card auth-card">
            <h2 style="font-size:1.2rem;font-weight:800;margin-bottom:4px;color:#0f172a;">Create your account</h2>
            <p class="text-muted" style="font-size:.875rem;margin-bottom:22px;">Fill in your details below.</p>

            <form method="POST">
                <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">

                <div class="form-group">
                    <label>Student ID <span class="req">*</span></label>
                    <input class="form-control" type="text" name="student_id"
                           placeholder="e.g. S20241234"
                           value="<?= e($vals['student_id'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>Full Name <span class="req">*</span></label>
                    <input class="form-control" type="text" name="full_name"
                           placeholder="Your full name"
                           value="<?= e($vals['full_name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>University Email <span class="req">*</span></label>
                    <input class="form-control" type="email" name="email"
                           placeholder="student@university.edu"
                           value="<?= e($vals['email'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>Password <span class="req">*</span></label>
                    <input class="form-control" type="password" id="password"
                           name="password" placeholder="At least 8 characters" required>
                    <div class="form-hint">
                        Strength: <strong id="pwStrength"></strong>
                    </div>
                </div>

                <div class="form-group">
                    <label>Confirm Password <span class="req">*</span></label>
                    <input class="form-control" type="password" name="confirm_password"
                           placeholder="Repeat your password" required>
                </div>

                <button type="submit" class="btn btn-navy btn-block btn-lg">
                    Create Account
                </button>
            </form>

            <p class="text-muted" style="text-align:center;font-size:.875rem;margin-top:18px;">
                Already have an account? <a href="index.php" style="font-weight:700;">Log in</a>
            </p>
        </div>
    </div>
</div>

<script src="assets/app.js"></script>
</body>
</html>
