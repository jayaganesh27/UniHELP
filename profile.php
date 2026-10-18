<?php
require_once 'config.php';
requireLogin();

$u    = currentUser();
$db   = db();

/* ── Save profile ────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    verifyCsrf();
    $name  = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone']     ?? '');
    $photo = null;
    $error = null;

    if (!empty($_FILES['photo']['name'])) {
        if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $error = 'The profile photo could not be uploaded.';
        } elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            $error = 'The profile photo must be 2 MB or smaller.';
        } else {
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['photo']['tmp_name']);
            $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            if (!isset($extensions[$mime])) {
                $error = 'Please upload a JPG, PNG, or WebP image.';
            } else {
                if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0755, true)) {
                    $error = 'The upload folder could not be created.';
                } else {
                    $filename = 'profile-' . $u['id'] . '-' . bin2hex(random_bytes(6)) . '.' . $extensions[$mime];
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], UPLOAD_DIR . $filename)) {
                        $photo = UPLOAD_URL . $filename;
                    } else {
                        $error = 'The profile photo could not be saved.';
                    }
                }
            }
        }
    }

    if (!$name) {
        $error = 'Full name is required.';
    }

    if ($error) {
        setFlash('error', $error);
    } else {
        $sql = 'UPDATE users SET full_name = ?, phone = ?';
        $params = [$name, $phone ?: null];
        if ($photo) {
            $sql .= ', photo = ?';
            $params[] = $photo;
        }
        $sql .= ' WHERE id = ?';
        $params[] = $u['id'];
        $db->prepare($sql)->execute($params);
        $_SESSION['user_name'] = $name;
        setFlash('success', 'Profile updated successfully.');
    }
    redirect('profile.php');
}

// Load current data
$userData = $db->prepare('SELECT * FROM users WHERE id = ?');
$userData->execute([$u['id']]);
$userData = $userData->fetch();

$pageTitle  = 'My Profile';
$currentNav = 'profile';
include 'includes/header.php';
?>
<meta name="csrf" content="<?= e(csrfToken()) ?>">

<div class="page-header">
    <h1>My Profile</h1>
    <p>Manage your personal information.</p>
</div>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:24px;align-items:start;" class="profile-grid">

    <!-- Left: photo card -->
    <div class="card card-body" style="text-align:center;">
        <div class="profile-photo-wrap" style="display:inline-block;margin-bottom:16px;">
            <img src="<?= e($userData['photo'] ?? 'assets/default-avatar.png') ?>"
                 alt="Profile photo"
                 class="profile-photo"
                 onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($userData['full_name']) ?>&background=1F3864&color=fff&size=200'">
        </div>
        <h3 style="font-family:Nunito,sans-serif;font-weight:800;margin-bottom:4px;"><?= e($userData['full_name']) ?></h3>
        <p class="text-muted" style="font-size:.85rem;">ID: <?= e($userData['student_id']) ?></p>
        <span class="badge badge-blue" style="margin-top:8px;"><?= $userData['is_volunteer'] ? 'Volunteer' : 'Student' ?></span>


    </div>

    <!-- Right: edit form -->
    <div class="card">
        <div class="card-header">Personal Information</div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf"         value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="save_profile" value="1">

                <div class="row">
                    <div class="col-half">
                        <div class="form-group">
                            <label>Full Name <span class="req">*</span></label>
                            <input class="form-control" type="text" name="full_name"
                                   value="<?= e($userData['full_name']) ?>" required>
                        </div>
                    </div>
                    <div class="col-half">
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input class="form-control" type="tel" name="phone"
                                   placeholder="+60 12-xxx xxxx"
                                   value="<?= e($userData['phone'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-full">
                        <div class="form-group">
                            <label>Profile Photo</label>
                            <input class="form-control" type="file" name="photo"
                                   accept="image/jpeg,image/png,image/webp">
                            <div class="form-hint">JPG, PNG, or WebP. Maximum 2 MB.</div>
                        </div>
                    </div>
                    <div class="col-full">
                        <div class="form-group">
                            <label>University Email</label>
                            <input class="form-control" type="email"
                                   value="<?= e($userData['email']) ?>" readonly>
                        </div>
                    </div>
                    <div class="col-half">
                        <div class="form-group">
                            <label>Student ID</label>
                            <input class="form-control" value="<?= e($userData['student_id']) ?>" readonly>
                        </div>
                    </div>
                    <div class="col-half">
                        <div class="form-group">
                            <label>Role</label>
                            <input class="form-control" value="<?= e(ucfirst($userData['role'])) ?>" readonly>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>
</div>

<style>
@media (max-width:768px) {
    .profile-grid { grid-template-columns: 1fr !important; }
}
</style>

<?php include 'includes/footer.php'; ?>
