<?php
session_start();
require 'config.php'; 

$error = '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifikasi password
        if ($user && password_verify($password, $user['password'])) {
            
           
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; // Menyimpan status 'admin' atau 'user'

            
            if ($user['role'] === 'admin') {
                header("Location: dashboard_admin.php"); // Halaman khusus admin
                exit();
            } else {
                header("Location: index.php"); // Halaman utama user
                exit();
            }

        } else {
            $error = "Username atau password salah!";
        }
    } else {
        $error = "Semua kolom harus diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MountainDate</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: #0b132b;
            color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .login-card {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 20px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
        }
        h2 {
            font-size: 1.75rem;
            color: #38bdf8;
            margin-bottom: 10px;
            text-align: center;
            font-weight: 800;
        }
        p.subtitle {
            text-align: center;
            color: #94a3b8;
            font-size: 0.9rem;
            margin-bottom: 30px;
        }
        .alert-error {
            background: rgba(244, 63, 94, 0.15);
            border: 1px solid #f43f5e;
            color: #f43f5e;
            padding: 12px;
            border-radius: 10px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: #cbd5e1;
        }
        input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: #fff;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s;
        }
        input:focus {
            border-color: #38bdf8;
        }
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #0284c7, #0369a1);
            color: #fff;
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(2, 132, 199, 0.3);
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #0369a1, #0284c7);
            transform: translateY(-2px);
            box-shadow: 0 15px 25px rgba(56, 189, 248, 0.4);
        }
        .register-link {
            text-align: center;
            margin-top: 25px;
            font-size: 0.85rem;
            color: #94a3b8;
        }
        .register-link a {
            color: #38bdf8;
            text-decoration: none;
            font-weight: 600;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <h2>MountainDate</h2>
        <p class="subtitle">Masuk untuk melanjutkan petualanganmu</p>

        <?php if (!empty($error)): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Username atau Email</label>
                <input type="text" name="username" required placeholder="Masukkan username/email...">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>

            <button type="submit" name="login" class="btn-login">Masuk</button>
        </form>

        <div class="register-link">
            Belum punya akun? <a href="register.php">Daftar di sini</a>
        </div>
    </div>

</body>
</html>