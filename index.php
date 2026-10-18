<?php
require_once 'config.php';

// Already logged in → redirect
if (!empty($_SESSION['user_id']) && !empty($_SESSION['2fa_verified'])) {
    redirect('dashboard.php');
}

$errors = [];
$step   = $_SESSION['2fa_pending_user'] ?? null ? 'verify' : 'login';

/* ── Handle login form ──────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    verifyCsrf();
    $email    = trim($_POST['email']    ?? '');
    $password =       $_POST['password'] ?? '';

    if (!$email || !$password) {
        $errors[] = 'Please enter your email and password.';
    } else {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Store partial session; require 2FA
            $_SESSION['2fa_pending_user'] = $user['id'];
            $code = generate2FACode($user['id']);
            if (!send2FACodeEmail($user['email'], $code) && SHOW_OTP_IF_EMAIL_FAILS) {
                $_SESSION['demo_code'] = $code;
            } else {
                unset($_SESSION['demo_code']);
            }

            $step = 'verify';
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}

/* ── Resend 2FA code ────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend'])) {
    verifyCsrf();
    $pendingId = $_SESSION['2fa_pending_user'] ?? null;

    if (!$pendingId) {
        $errors[] = 'Session expired. Please log in again.';
        $step = 'login';
    } else {
        $stmt = db()->prepare('SELECT email FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$pendingId]);
        $email = $stmt->fetchColumn();

        if (!$email) {
            $errors[] = 'Account not found. Please log in again.';
            $step = 'login';
        } else {
            db()->prepare('UPDATE two_factor_codes SET used = 1 WHERE user_id = ? AND used = 0')
                ->execute([$pendingId]);
            $code = generate2FACode($pendingId);
            if (!send2FACodeEmail($email, $code) && SHOW_OTP_IF_EMAIL_FAILS) {
                $_SESSION['demo_code'] = $code;
            } else {
                unset($_SESSION['demo_code']);
            }
            $step = 'verify';
        }
    }
}

/* ── Handle 2FA verification ────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify'])) {
    verifyCsrf();
    $pendingId = $_SESSION['2fa_pending_user'] ?? null;
    $code      = trim($_POST['otp_code'] ?? '');

    if (!$pendingId) {
        $errors[] = 'Session expired. Please log in again.';
        $step = 'login';
    } elseif (strlen($code) !== 6 || !ctype_digit($code)) {
        $errors[] = 'Please enter a valid 6-digit code.';
        $step = 'verify';
    } else {
        $stmt = db()->prepare(
            'SELECT id FROM two_factor_codes
             WHERE user_id = ? AND code = ? AND used = 0 AND expires_at > NOW()
             LIMIT 1'
        );
        $stmt->execute([$pendingId, $code]);
        $row = $stmt->fetch();

        if ($row) {
            // Mark code used
            db()->prepare('UPDATE two_factor_codes SET used = 1 WHERE id = ?')->execute([$row['id']]);

            // Load user into session
            $user = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
            $user->execute([$pendingId]);
            $u = $user->fetch();

            $_SESSION['user_id']      = $u['id'];
            $_SESSION['user_name']    = $u['full_name'];
            $_SESSION['user_email']   = $u['email'];
            $_SESSION['user_role']    = $u['role'];
            $_SESSION['is_volunteer'] = (bool) $u['is_volunteer'];
            $_SESSION['2fa_verified'] = true;
            session_regenerate_id(true);
            unset($_SESSION['2fa_pending_user'], $_SESSION['demo_code']);

            redirect('dashboard.php');
        } else {
            $errors[] = 'Invalid or expired code. Please try again.';
            $step = 'verify';
        }
    }
}

$pageTitle = 'Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — UniHELP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <meta name="csrf" content="<?= e(csrfToken()) ?>">
</head>
<body>

<div class="auth-wrap">
    <div class="auth-box">
        <div class="auth-logo">
            <div class="auth-logo-icon">🎓</div>
            <h1>UniHELP</h1>
            <p>Peer support for every student</p>
        </div>

        <?php if ($errors): ?>
        <div class="flash flash-error" style="border-radius:10px;margin-bottom:16px;">
            <?= e(implode(' ', $errors)) ?>
        </div>
        <?php endif; ?>

        <?php if ($step === 'login'): ?>
        <!-- ── Login form ─────────────────────────────── -->
        <div class="card auth-card">
            <h2 style="font-size:1.25rem;font-weight:800;margin-bottom:4px;color:#0f172a;">Welcome back</h2>
            <p class="text-muted" style="font-size:.875rem;margin-bottom:22px;">Sign in to your account</p>

            <form method="POST">
                <input type="hidden" name="csrf"  value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="login" value="1">

                <div class="form-group">
                    <label for="email">University Email <span class="req">*</span></label>
                    <input class="form-control" type="email" id="email" name="email"
                           placeholder="student@university.edu"
                           value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
                </div>

                <div class="form-group">
                    <label for="pwd">Password <span class="req">*</span></label>
                    <input class="form-control" type="password" id="pwd" name="password"
                           placeholder="Your password" required>
                    <div style="text-align:right;margin-top:5px;">
                        <a href="#" style="font-size:.75rem;">Forgot password?</a>
                    </div>
                </div>

                <button type="submit" class="btn btn-navy btn-block btn-lg" style="margin-top:8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M14 12H3"/></svg>
                    Login
                </button>
            </form>

            <p class="text-muted" style="text-align:center;font-size:.875rem;margin-top:18px;">
                New student? <a href="register.php" style="font-weight:700;">Create account</a>
            </p>
        </div>

        <?php else: ?>
        <!-- ── 2FA Verify form ────────────────────────── -->
        <div class="card auth-card">
            <div style="text-align:center;margin-bottom:20px;">
                <div style="width:52px;height:52px;border-radius:50%;background:#eff6ff;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/><path d="M12 18h.01"/></svg>
                </div>
                <h2 style="font-size:1.15rem;font-weight:800;color:#0f172a;margin-bottom:4px;">Two-Factor Verification</h2>
                <p class="text-muted" style="font-size:.85rem;">Enter the 6-digit code sent to your email.</p>
                <?php if (!empty($_SESSION['demo_code'])): ?>
                <p style="margin-top:8px;padding:8px 14px;background:#fefce8;border:1px solid #fde68a;border-radius:8px;font-size:.8rem;color:#78350f;">
                    🔑 <strong>Demo code:</strong> <?= e($_SESSION['demo_code']) ?>
                </p>
                <?php endif; ?>
            </div>

            <form method="POST" id="otpForm">
                <input type="hidden" name="csrf"      value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="verify"    value="1">
                <input type="hidden" name="otp_code"  id="otpValue">

                <div class="otp-group">
                    <?php for ($i = 0; $i < 6; $i++): ?>
                    <input type="text" class="otp-input" inputmode="numeric"
                           maxlength="1" autocomplete="one-time-code"
                           <?= $i === 0 ? 'autofocus' : '' ?>>
                    <?php endfor; ?>
                </div>

                <button type="submit" class="btn btn-success btn-block btn-lg">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
                    Verify &amp; Continue
                </button>
            </form>

            <form method="POST" style="text-align:center;margin-top:14px;">
                <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="resend" value="1">
                <span class="text-muted" style="font-size:.8rem;">Didn't receive a code?</span>
                <button type="submit" style="border:0;background:none;color:var(--primary);font-weight:700;cursor:pointer;">Resend</button>
            </form>
            <p style="text-align:center;margin-top:8px;">
                <a href="index.php" style="font-size:.78rem;color:var(--muted);">← Back to login</a>
            </p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="assets/app.js"></script>
</body>
</html>
