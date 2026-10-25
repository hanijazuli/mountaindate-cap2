<?php
session_start();
require 'config.php';


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    header('Location: login.php?msg=login_first');
    exit;
}

$message = '';
if (isset($_GET['status']) && $_GET['status'] == 'success') {
    $message = "✨ Surat ceritamu berhasil terkirim ke awan!";
}


try {
    $stmt = $pdo->query('SELECT * FROM stories ORDER BY id DESC');
    $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    $users_map = [];
    $u_stmt = $pdo->query('SELECT id, username FROM users');
    while ($r = $u_stmt->fetch(PDO::FETCH_ASSOC)) {
        $users_map[$r['id']] = $r['username'];
    }
} catch (PDOException $e) {
    echo "<div style='background:#f43f5e; color:#fff; padding:15px; font-weight:bold; margin:20px; border-radius:10px;'>
            ⚠️ GAGAL BACA DATABASE: " . htmlspecialchars($e->getMessage()) . "
          </div>";
    $stories = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kumpulan Surat Pendaki - MountainDate</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,800;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body style="background-color: #0b132b; color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; padding: 0;">

    <?php 
        if (file_exists('includes/navbar.php')) {
            include 'includes/navbar.php'; 
        } else {
            include 'navbar.php'; 
        }
    ?>

    <div class="page-wrapper">
        
        <?php if($message): ?>
            <p style="color: #4ade80; font-weight: 600; margin-bottom: 25px; font-size: 0.9rem; background: rgba(74, 222, 128, 0.1); padding: 12px 18px; border-radius: 10px; border: 1px solid rgba(74,222,128,0.3); text-align: left;">
                <?= $message ?>
            </p>
        <?php endif; ?>

        <div class="header-section">
            <div>
                <h1 style="font-family: 'Playfair Display', serif; font-size: 2.2rem; color: #ffffff; margin: 0 0 6px 0; font-weight: 700;">
                    📫 Kumpulan Surat Pendaki
                </h1>
                <p style="color: #94a3b8; font-style: italic; margin: 0; font-size: 0.92rem;">
                    Buka amplop rahasia di bawah ini untuk membaca cerita suka duka dari pendaki lain.
                </p>
            </div>
            <a href="stories.php" class="btn-write-new">
                ✍️ Tulis Surat Baru
            </a>
        </div>

        <?php if(empty($stories)): ?>
            <div style="background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(255, 255, 255, 0.08); border-left: 4px solid #f43f5e; border-radius: 12px; padding: 22px; color: #94a3b8; text-align: left;">
                <p style="margin: 0; font-size: 0.92rem;">🍃 Belum ada surat cerita yang dikirimkan ke database.</p>
            </div>
        <?php else: ?>
            <div class="envelope-grid">
                <?php foreach($stories as $s): ?>
                    <?php 
                        $penulis = isset($users_map[$s['id_user']]) ? $users_map[$s['id_user']] : 'Pendaki Misterius';
                        $nama_g = isset($s['nama_gunung']) && !empty($s['nama_gunung']) ? $s['nama_gunung'] : 'Pendakian Umum';
                        $tgl = isset($s['created_at']) ? date('d M Y', strtotime($s['created_at'])) : date('d M Y');
                    ?>
                    <div class="envelope-card">
                        <div class="wax-seal">✉️</div>
                        <button class="btn-open-letter" 
                                data-judul="<?= htmlspecialchars($s['judul'] ?? 'Tanpa Judul', ENT_QUOTES) ?>" 
                                data-gunung="<?= htmlspecialchars($nama_g, ENT_QUOTES) ?>" 
                                data-user="<?= htmlspecialchars($penulis, ENT_QUOTES) ?>" 
                                data-tanggal="<?= htmlspecialchars($tgl, ENT_QUOTES) ?>" 
                                data-isi="<?= htmlspecialchars($s['isi'] ?? '', ENT_QUOTES) ?>" 
                                onclick="showModal(this)">
                            📜 Buka & Baca Surat
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

    <!-- MODAL POPUP BACA SURAT -->
    <div id="letterModal" class="modal-overlay">
        <div class="letter-modal">
            <button onclick="closeModal()" style="position: absolute; top: 18px; right: 20px; background: none; border: none; color: #f43f5e; font-size: 1.4rem; cursor: pointer; font-weight: 700;">✕</button>
            <h2 id="modalTitle" style="font-family: 'Playfair Display', serif; color: #38bdf8; margin-top: 0; margin-bottom: 8px;"></h2>
            <p id="modalMeta" style="font-size: 0.82rem; color: #94a3b8; border-bottom: 1px dashed rgba(255,255,255,0.15); padding-bottom: 12px; margin-bottom: 20px;"></p>
            <div id="modalContent" style="line-height: 1.7; color: #cbd5e1; font-size: 0.95rem; white-space: pre-line;"></div>
        </div>
    </div>

    <?php 
        if (file_exists('includes/footer.php')) {
            include 'includes/footer.php'; 
        } else {
            include 'footer.php'; 
        }
    ?>

    <script>
        function showModal(btn) {
            const judul = btn.getAttribute('data-judul');
            const gunung = btn.getAttribute('data-gunung');
            const user = btn.getAttribute('data-user');
            const tanggal = btn.getAttribute('data-tanggal');
            const isi = btn.getAttribute('data-isi');

            document.getElementById('modalTitle').innerText = judul;
            document.getElementById('modalMeta').innerText = '🏔️ Gunung: ' + gunung + ' | ✍️ Penulis: ' + user + ' | 📅 ' + tanggal;
            document.getElementById('modalContent').innerText = isi;
            document.getElementById('letterModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('letterModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('letterModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>

</body>
</html>