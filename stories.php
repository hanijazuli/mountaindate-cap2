<?php
session_start();
require 'config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?msg=login_first');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_story'])) {
    $judul = trim($_POST['judul'] ?? '');
    $nama_gunung = trim($_POST['nama_gunung'] ?? '');
    $isi = trim($_POST['isi'] ?? '');
    $id_user = $_SESSION['user_id'];

    if (!empty($judul) && !empty($isi)) {
        try {
            
            $pdo->exec("CREATE TABLE IF NOT EXISTS `stories` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `id_user` INT NOT NULL,
                `judul` VARCHAR(255) NOT NULL,
                `nama_gunung` VARCHAR(100) NULL,
                `isi` TEXT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            
            try {
                $pdo->exec("ALTER TABLE `stories` ADD COLUMN `nama_gunung` VARCHAR(100) NULL AFTER `judul`");
            } catch (PDOException $ex) {}

       
            $stmt = $pdo->prepare('INSERT INTO stories (id_user, judul, nama_gunung, isi) VALUES (?, ?, ?, ?)');
            $stmt->execute([$id_user, $judul, $nama_gunung, $isi]);

         
            header('Location: story_list.php?status=success');
            exit;

        } catch (PDOException $e) {
            $error = "Gagal simpan ke database: " . $e->getMessage();
        }
    } else {
        $error = "Judul dan isi cerita wajib diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tulis Surat - MountainDate</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,800;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body style="background-color: #0b132b; color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif;">

    <?php 
        if (file_exists('includes/navbar.php')) { include 'includes/navbar.php'; } 
        else { include 'navbar.php'; }
    ?>

    <div style="max-width: 800px; margin: 40px auto 0 auto; text-align: center; padding: 0 20px;">
        <h1 style="font-family: 'Playfair Display', serif; font-size: 2.2rem; margin-bottom: 8px;">📩 Surat Dari Awan (Jurnal Pendaki)</h1>
        <p style="color: #94a3b8; font-style: italic; font-size: 0.92rem;">
            Nangis di trek gara-gara dengkul mau copot, pas nyampe atas malah bilang: Next gunung mana lagi ya?
        </p>
    </div>

    <div class="form-container">
        <h3 style="margin-top: 0; color: #38bdf8; font-family: 'Playfair Display', serif; margin-bottom: 20px;">
            ✍️ Tulis & Kirim Surat Ceritamu
        </h3>

        <?php if ($error): ?>
            <div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="stories.php">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Judul Cerita / Surat:</label>
                    <input type="text" name="judul" class="form-input" placeholder="Contoh: Badai Rindu di Selo Merbabu" required>
                </div>
                <div class="form-group">
                    <label>Nama Gunung:</label>
                    <input type="text" name="nama_gunung" class="form-input" placeholder="Ketik nama gunung (misal: Mt. Prau)">
                </div>
            </div>

            <div class="form-group">
                <label>Isi Cerita Lengkap (Suka & Duka):</label>
                <textarea name="isi" rows="6" class="form-input" style="resize: vertical; line-height: 1.6;" placeholder="Bagikan momen berkesan, tantangan medan, atau pesan emosional perjalananmu..." required></textarea>
            </div>

            <div class="btn-group">
                <button type="submit" name="submit_story" value="1" class="btn-submit">
                    📌 Segel & Kirim Surat
                </button>
                <a href="story_list.php" class="btn-view-stories">
                    📫 Lihat Kumpulan Surat →
                </a>
            </div>
        </form>
    </div>

    <?php 
        if (file_exists('includes/footer.php')) { include 'includes/footer.php'; } 
        else { include 'footer.php'; }
    ?>
</body>
</html>