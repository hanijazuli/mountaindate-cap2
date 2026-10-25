<?php
session_start();
require 'config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($email) && !empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
          
            $stmt = $pdo->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
            $stmt->execute([$username, $email, $hashed_password]);

            header('Location: login.php?msg=register_success');
            exit;
        } catch (PDOException $e) {
            $message = "Username atau Email sudah terdaftar!";
        }
    } else {
        $message = "Semua kolom wajib diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Pendaki - MountainDate</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=2.0">
</head>
<body style="background-color: #0b132b; color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif;">

    <?php include 'includes/navbar.php'; ?>

    <div class="auth-container">
        <h2 style="margin-top:0; color:#fff; font-size:1.6rem; text-align:center;">✨ Buat Akun Baru</h2>
        <p style="text-align:center; color:#94a3b8; font-size:0.88rem; margin-bottom:24px;">Bergabung bersama komunitas pendaki MountainDate</p>

        <?php if ($message): ?>
            <div class="alert-msg"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-input" placeholder="Pilih username" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-input" placeholder="Masukkan email aktif" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-input" placeholder="Buat password aman" required>
            </div>
            <button type="submit" class="btn-submit">Daftar Akun</button>
        </form>

        <p style="text-align:center; font-size:0.85rem; color:#94a3b8; margin-top:20px;">
            Sudah punya akun? <a href="login.php" style="color:#38bdf8; text-decoration:none; font-weight:600;">Login di sini</a>
        </p>
    </div>

</body>
</html>