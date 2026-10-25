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

// Cek role admin dari Session
$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

//  (Khusus Admin)
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_tips']) && $is_admin) {
    $id_gunung = !empty($_POST['id_gunung']) ? $_POST['id_gunung'] : NULL;
    $id_tips_category = !empty($_POST['id_tips_category']) ? $_POST['id_tips_category'] : NULL;
    $isi = trim($_POST['isi'] ?? '');

    if (!empty($isi) && !empty($id_tips_category)) {
        try {
            $stmt = $pdo->prepare('INSERT INTO tips (id_gunung, id_tips_category, isi) VALUES (?, ?, ?)');
            $stmt->execute([$id_gunung, $id_tips_category, $isi]);
            header("Location: tips.php?status=success");
            exit;
        } catch (PDOException $e) {
            $message = "Error Insert: " . $e->getMessage();
        }
    } else {
        $message = "Kategori dan isi panduan wajib diisi!";
    }
}

if (isset($_GET['status']) && $_GET['status'] == 'success') {
    $message = "Safety tip berhasil ditambahkan!";
}

// Ambil semua tips dan kelompokkan per gunung
$tips_by_mountain = [];
try {
    $tips_stmt = $pdo->query('
        SELECT t.isi, COALESCE(g.nama, "Panduan Umum & Keselamatan") AS nama_gunung, COALESCE(c.nama, "General") AS kategori 
        FROM tips t 
        LEFT JOIN gunung g ON t.id_gunung = g.id 
        LEFT JOIN tips_category c ON t.id_tips_category = c.id
        ORDER BY t.id_gunung ASC, t.id DESC
    ');
    $raw_tips = $tips_stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($raw_tips as $tip) {
        $mountain_name = $tip['nama_gunung'];
        $tips_by_mountain[$mountain_name][] = $tip;
    }
} catch (PDOException $e) {}

// Ambil data dropdown jika Admin
$mountains = [];
$categories = [];
if ($is_admin) {
    try {
        $mountains = $pdo->query('SELECT id, nama FROM gunung')->fetchAll(PDO::FETCH_ASSOC);
        $categories = $pdo->query('SELECT id, nama FROM tips_category')->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {}
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safety & Tips - MountainDate</title>
    
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
        
        <div class="header-section">
            <h1>🛡️ Safety Guidelines & Hiking Tips</h1>
            <p>
                "Menaklukkan Gunung Berawal Dari Menaklukkan Ego Sendiri."<br>
                Pilih gunung pilihanmu di bawah untuk melihat rincian panduan keselamatan dan tips khusus pendakian.
            </p>
        </div>

        <?php if ($is_admin): ?>
            <div class="admin-box">
                <h3 style="margin-top: 0; color: #38bdf8; font-family: 'Playfair Display', serif; font-size: 1.25rem; margin-bottom: 18px;">
                    ⚡ [Admin Panel] Add New Safety Tip
                </h3>
                
                <?php if($message): ?>
                    <p style="color: #4ade80; font-weight: 600; margin-bottom: 15px; font-size: 0.88rem; background: rgba(74, 222, 128, 0.1); padding: 10px 14px; border-radius: 8px;">
                        <?= $message ?>
                    </p>
                <?php endif; ?>
                
                <form method="POST" action="tips.php">
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; color: #cbd5e1; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Select Mountain:</label>
                        <select name="id_gunung" class="form-input-custom">
                            <option value="">-- 🏔️ Panduan Umum (Semua Gunung) --</option>
                            <?php foreach($mountains as $m): ?>
                                <option value="<?= $m['id'] ?>">⛰️ <?= htmlspecialchars($m['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; color: #cbd5e1; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Select Category:</label>
                        <select name="id_tips_category" required class="form-input-custom">
                            <option value="">-- 🏷️ Choose Category --</option>
                            <?php foreach($categories as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; color: #cbd5e1; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Guidelines Content:</label>
                        <textarea name="isi" rows="4" placeholder="Write safety procedures..." required class="form-input-custom" style="resize: vertical; line-height: 1.5;"></textarea>
                    </div>

                    <button type="submit" name="submit_tips" value="1" style="background: linear-gradient(135deg, #0284c7, #0369a1); color: #ffffff; font-weight: 600; padding: 11px 24px; border-radius: 10px; border: none; cursor: pointer; font-size: 0.88rem;">
                        🚀 Publish Tip
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <div class="content-grid">
            <div>
                <p style="color: #cbd5e1; font-size: 0.92rem; margin-bottom: 18px; font-weight: 600; text-align: left;">
                    Explore safety guides grouped by mountain peaks:
                </p>

                <?php if(empty($tips_by_mountain)): ?>
                    <div style="background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(255, 255, 255, 0.08); border-left: 4px solid #f43f5e; border-radius: 12px; padding: 20px; color: #94a3b8; text-align: left;">
                        <p style="margin: 0; font-size: 0.9rem;">🍃 No safety guidelines available at the moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($tips_by_mountain as $nama_gunung => $list_tips): ?>
                        <div class="accordion-item">
                            <div class="accordion-header" onclick="toggleAccordion(this)">
                                <div class="mountain-title">
                                    <span>⛰️ <?= htmlspecialchars($nama_gunung) ?></span>
                                    <span class="tips-count-badge"><?= count($list_tips) ?> Tips</span>
                                </div>
                                <span class="arrow-icon">▼</span>
                            </div>
                            <div class="accordion-content">
                                <?php foreach($list_tips as $item): ?>
                                    <div class="sub-tip-card">
                                        <span class="badge-cat-sub">
                                            🎒 <?= htmlspecialchars($item['kategori']) ?>
                                        </span>
                                        <p style="margin: 0; line-height: 1.6; color: #e2e8f0; font-size: 0.9rem;">
                                            <?= nl2br(htmlspecialchars($item['isi'])) ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div>
                <div class="letter-card">
                    <div class="letter-stamp">AIRMAIL</div>
                    <h4>✉️ Realita vs Medsos</h4>
                    <p style="font-size: 0.86rem; color: #cbd5e1; line-height: 1.6; margin: 0;">
                        Jangan hanya tergiur foto pemandangan di media sosial. Pahami data teknis, karakter jalur, dan batas kemampuan fisikmu sebelum melangkah.
                    </p>
                </div>

                <div class="letter-card">
                    <div class="letter-stamp">URGENT</div>
                    <h4>📜 Ego vs Keselamatan</h4>
                    <p style="font-size: 0.86rem; color: #cbd5e1; line-height: 1.6; margin: 0;">
                        Puncak tidak akan lari, tapi keselamatanmu adalah yang utama. Menyerah dan memilih turun saat cuaca buruk adalah keputusan yang bijak.
                    </p>
                </div>

                <div class="letter-card">
                    <div class="letter-stamp">NOTICE</div>
                    <h4>📌 Etika di Jalur</h4>
                    <p style="font-size: 0.86rem; color: #cbd5e1; line-height: 1.6; margin: 0; font-style: italic;">
                        "Jangan mengambil apapun selain gambar, jangan meninggalkan apapun selain jejak, dan jangan membunuh apapun selain waktu."
                    </p>
                </div>
            </div>
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
        function toggleAccordion(element) {
            const parent = element.parentElement;
            parent.classList.toggle('active');
        }
    </script>

</body>
</html>