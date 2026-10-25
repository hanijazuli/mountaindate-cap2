<?php
session_start();
require 'config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// PROTEKSI: Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?msg=login_first');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// 1. AMBIL DATA USER DARI TABEL USERS
$stmt_user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $new_password = $_POST['new_password'] ?? '';

    if (!empty($username) && !empty($email)) {
        try {
            if (!empty($new_password)) {
                // Update username, email, dan password baru
                $hashed_pw = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt_up = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
                $stmt_up->execute([$username, $email, $hashed_pw, $user_id]);
            } else {
                // Update username & email saja
                $stmt_up = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                $stmt_up->execute([$username, $email, $user_id]);
            }

            $_SESSION['username'] = $username;
            $message = "✨ Profil berhasil diperbarui!";
            
            
            $stmt_user->execute([$user_id]);
            $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            $error = "Gagal memperbarui profil: " . $e->getMessage();
        }
    } else {
        $error = "Username dan email tidak boleh kosong!";
    }
}


$my_stories = [];
try {
    $stmt_stories = $pdo->prepare("SELECT * FROM stories WHERE id_user = ? ORDER BY id DESC");
    $stmt_stories->execute([$user_id]);
    $my_stories = $stmt_stories->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    //
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - MountainDate</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,800;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=2.0">
</head>
<body style="background-color: #0b132b; color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif;">

    <?php 
        if (file_exists('includes/navbar.php')) { include 'includes/navbar.php'; } 
        else { include 'navbar.php'; }
    ?>

    <div class="profile-container">

        <?php if ($message): ?>
            <div style="background: rgba(74, 222, 128, 0.15); color: #4ade80; border: 1px solid rgba(74, 222, 128, 0.3); padding: 12px 18px; border-radius: 10px; margin-bottom: 20px; font-weight: 600;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div style="background: rgba(244, 63, 94, 0.15); color: #f43f5e; border: 1px solid rgba(244, 63, 94, 0.3); padding: 12px 18px; border-radius: 10px; margin-bottom: 20px; font-weight: 600;">
                ⚠️ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- KARTU PROFIL USER -->
        <div class="profile-card">
            <div class="profile-header">
                <div class="avatar-circle">
                    <?= strtoupper(substr($user['username'] ?? 'P', 0, 1)) ?>
                </div>
                <div>
                    <h2 style="margin: 0; font-family: 'Playfair Display', serif; color: #ffffff;"><?= htmlspecialchars($user['username'] ?? 'Pendaki') ?></h2>
                    <p style="margin: 4px 0 0 0; color: #94a3b8; font-size: 0.9rem;">
                        Pendaki MountainDate • <?= htmlspecialchars($user['email'] ?? '') ?>
                    </p>
                </div>
            </div>

            <form method="POST" action="profile.php">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px;">
                    <div class="form-group">
                        <label>Username:</label>
                        <input type="text" name="username" class="form-input" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Password Baru (Kosongkan jika tidak ingin diubah):</label>
                    <input type="password" name="new_password" class="form-input" placeholder="Ketik password baru...">
                </div>

                <button type="submit" name="update_profile" value="1" class="btn-update">
                    💾 Simpan Perubahan Profil
                </button>
            </form>
        </div>

        <!-- RIWAYAT SURAT CERITA SAYA -->
        <div class="profile-card">
            <h3 style="margin-top: 0; font-family: 'Playfair Display', serif; color: #38bdf8; margin-bottom: 20px;">
                📜 Riwayat Surat Cerita Saya (<?= count($my_stories) ?>)
            </h3>

            <?php if (empty($my_stories)): ?>
                <p style="color: #94a3b8; font-style: italic; margin: 0;">Kamu belum pernah menulis surat cerita pendakian. Yuk bagikan pengalamanamu di <a href="stories.php" style="color: #38bdf8;">Surat Dari Awan</a>!</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <?php foreach ($my_stories as $s): ?>
                        <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.08); padding: 16px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h4 style="margin: 0 0 6px 0; color: #ffffff;"><?= htmlspecialchars($s['judul']) ?></h4>
                                <p style="margin: 0; color: #94a3b8; font-size: 0.85rem;">
                                    🏔️ <?= htmlspecialchars(!empty($s['nama_gunung']) ? $s['nama_gunung'] : 'Pendakian Umum') ?> | 📅 <?= date('d M Y', strtotime($s['created_at'])) ?>
                                </p>
                            </div>
                            <span class="story-badge">Tersimpan</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <?php 
        if (file_exists('includes/footer.php')) { include 'includes/footer.php'; } 
        else { include 'footer.php'; }
    ?>

</body>
</html>