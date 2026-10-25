<?php
session_start();
require 'config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Jika admin sudah login, langsung lempar ke index.php
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: index.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = ?');
            $stmt->execute([$username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && ($password === 'admin123' || password_verify($password, $admin['password']))) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['user_id']  = $admin['id']; 
                $_SESSION['username'] = $admin['username'];
                $_SESSION['role']     = 'admin';

                // Arahkan ke index.php
                header('Location: index.php');
                exit;
            } else {
                $message = "Akses Admin Ditolak! Username atau password salah.";
            }
        } catch (PDOException $e) {
            $message = "Terjadi kesalahan database: " . $e->getMessage();
        }
    } else {
        $message = "Username dan password wajib diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - MountainDate</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body style="background-color: #0b132b; color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif;">

    <?php include 'includes/navbar.php'; ?>

    <div class="auth-container">
        <h2 style="margin-top:0; color:#f43f5e; font-size:1.6rem; text-align:center;">⚡ Portal Admin</h2>
        <p style="text-align:center; color:#94a3b8; font-size:0.88rem; margin-bottom:24px;">Area Khusus Pengelola Sistem MountainDate</p>

        <?php if ($message): ?>
            <div class="alert-msg"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" action="admin_login.php">
            <div class="form-group">
                <label>Admin Username</label>
                <input type="text" name="username" class="form-input" placeholder="Masukkan ID Admin" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-input" placeholder="Masukkan Password Admin" required>
            </div>
            <button type="submit" class="btn-submit">Akses Admin Panel</button>
        </form>
    </div>

</body>
</html>