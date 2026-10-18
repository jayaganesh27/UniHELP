<?php
// ── Database Configuration ────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'unihelp');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHAR', 'utf8mb4');

// ── App Configuration ─────────────────────────────────────────
define('APP_NAME',    'UniHELP');
define('APP_URL',     'http://localhost/unihelp');
define('UPLOAD_DIR',  __DIR__ . '/uploads/');
define('UPLOAD_URL',  APP_URL . '/uploads/');
define('OTP_FROM_EMAIL', 'no-reply@unihelp.local');
define('SHOW_OTP_IF_EMAIL_FAILS', true);

// ── Session start ─────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── PDO connection (singleton) ────────────────────────────────
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHAR;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

// ── Helper: redirect ──────────────────────────────────────────
function redirect(string $path): void {
    header('Location: ' . $path);
    exit;
}

// ── Helper: require login ─────────────────────────────────────
function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        redirect('index.php');
    }
}

// ── Helper: require volunteer role ───────────────────────────
function requireVolunteer(): void {
    requireLogin();
    if (empty($_SESSION['is_volunteer'])) {
        redirect('dashboard.php');
    }
}

// ── Helper: current user shorthand ───────────────────────────
function currentUser(): array {
    return [
        'id'           => $_SESSION['user_id']    ?? null,
        'name'         => $_SESSION['user_name']  ?? '',
        'email'        => $_SESSION['user_email'] ?? '',
        'role'         => $_SESSION['user_role']  ?? 'student',
        'is_volunteer' => $_SESSION['is_volunteer'] ?? false,
    ];
}

// ── Helper: escape for HTML output ───────────────────────────
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ── Helper: flash messages ────────────────────────────────────
function setFlash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// ── Helper: CSRF token ────────────────────────────────────────
function csrfToken(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verifyCsrf(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(403);
        die('Invalid CSRF token.');
    }
}

// ── Helper: generate 2FA code ─────────────────────────────────
function generate2FACode(int $userId): string {
    $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $stmt = db()->prepare(
        'INSERT INTO two_factor_codes (user_id, code, expires_at)
         VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))'
    );
    $stmt->execute([$userId, $code]);
    return $code;
}

// ── Helper: send 2FA code by email ────────────────────────────
function send2FACodeEmail(string $email, string $code): bool {
    $subject = APP_NAME . ' login verification code';
    $message = "Your " . APP_NAME . " verification code is: {$code}\n\n"
             . "This code expires in 10 minutes. If you did not try to log in, ignore this email.";
    $headers = [
        'From: ' . OTP_FROM_EMAIL,
        'Content-Type: text/plain; charset=UTF-8',
    ];

    return @mail($email, $subject, $message, implode("\r\n", $headers));
}
