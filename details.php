<?php
session_start();
require 'config.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php?msg=silakan_login_terlebih_dahulu');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];

$stmt = $pdo->prepare("
    SELECT g.*, COALESCE(AVG(p.rating_bintang), 0) AS avg_rating, COUNT(p.id) AS total_reviews 
    FROM gunung g 
    LEFT JOIN pengalaman p ON g.id = p.id_gunung 
    WHERE g.id = ? 
    GROUP BY g.id
");
$stmt->execute([$id]);
$mountain = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mountain) {
    die("Data gunung tidak ditemukan!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (isset($_SESSION['user_id'])) {
        $rating = (int)$_POST['rating_bintang'];
        $cerita = trim($_POST['cerita_perjalanan']);
        $user_id = $_SESSION['user_id'];

        if (!empty($cerita) && $rating >= 1 && $rating <= 5) {
            $stmt_rev = $pdo->prepare("INSERT INTO pengalaman (id_gunung, id_user, rating_bintang, cerita_perjalanan, tanggal) VALUES (?, ?, ?, ?, NOW())");
            $stmt_rev->execute([$id, $user_id, $rating, $cerita]);
            header("Location: details.php?id=" . $id);
            exit;
        }
    }
}

$stmt_revs = $pdo->prepare("
    SELECT p.*, COALESCE(u.username, 'Pendaki Anonim') AS username, u.id AS user_id_pembuat 
    FROM pengalaman p 
    LEFT JOIN users u ON p.id_user = u.id 
    WHERE p.id_gunung = ? 
    ORDER BY p.id DESC
");
$stmt_revs->execute([$id]);
$reviews = $stmt_revs->fetchAll(PDO::FETCH_ASSOC);

$jalur_via_clean = preg_replace('/^via\s+/i', '', trim($mountain['jalur_via'] ?? ''));
$user_login = $_SESSION['username'] ?? '';
$is_admin   = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($mountain['nama']) ?> - Details | MountainDate</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="style.css">
</head>
<body style="background-color: #0b132b; color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif;">

    <?php 
        if (file_exists('includes/navbar.php')) { include 'includes/navbar.php'; } 
        else if (file_exists('navbar.php')) { include 'navbar.php'; }
    ?>

    <div class="article-main-container">
        
        <div class="top-action-bar">
            <a href="index.php#mountains" class="btn-back-link">← Kembali ke Katalog</a>
            <?php if ($is_admin): ?>
                <a href="edit_gunung.php?id=<?= $mountain['id'] ?>" class="btn-admin-edit">✏️ Edit Admin</a>
            <?php endif; ?>
        </div>

        <div class="hero-photo-wrapper">
            <img src="images/<?= htmlspecialchars($mountain['image_url']) ?>" 
                 onerror="this.src='https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?q=80&w=1200'" 
                 alt="Foto <?= htmlspecialchars($mountain['nama']) ?>">
        </div>

        <div class="hero-meta-bar">
            <h1><?= htmlspecialchars($mountain['nama']) ?></h1>
            
            <div class="meta-item">
                📍 Via: <span><?= htmlspecialchars($jalur_via_clean ?: '-') ?></span>
            </div>
            <div class="meta-item">
                ⛰️ Ketinggian: <span><?= htmlspecialchars($mountain['mdpl'] ?? 0) ?> MDPL</span>
            </div>
            <div class="meta-item">
                💪 Kesulitan: <span><?= htmlspecialchars($mountain['tingkat_kesulitan'] ?? 'Easy') ?></span>
            </div>
            <div class="meta-item" style="color: #f59e0b;">
                ★ Rating: <span><?= number_format($mountain['avg_rating'], 1) ?> (<?= $mountain['total_reviews'] ?> ulasan)</span>
            </div>
        </div>

        <div class="article-content-grid">
            <div class="article-column">
                <h3>📖 Deskripsi Gunung</h3>
                <p>
                    <?= !empty($mountain['deskripsi']) ? nl2br(htmlspecialchars($mountain['deskripsi'])) : 'Belum ada deskripsi yang ditambahkan untuk gunung ini.' ?>
                </p>
            </div>

            <div class="article-column">
                <h3>🗺️ Info Jalur & Rute</h3>
                <p>
                    <?= !empty($mountain['info_rute']) ? nl2br(htmlspecialchars($mountain['info_rute'])) : 'Belum ada catatan rute spesifik untuk jalur ini.' ?>
                </p>
            </div>
        </div>

        <div class="comments-container">
            <h3>💬 Ulasan & Pengalaman Pendaki (<?= count($reviews) ?>)</h3>

            <?php if (isset($_SESSION['user_id'])): ?>
                <form method="POST" action="">
                    <label style="display:block; margin-bottom:8px; font-weight:600; color:#cbd5e1;">Rating Bintang:</label>
                    <select name="rating_bintang" class="form-control" style="color: #ffffff; background-color: rgba(15, 23, 42, 0.9); max-width: 250px;" required>
                        <option value="5">❤️ (5/5) - Mahadasyat</option>
                        <option value="4">😊 (4/5) - Cakep Parah</option>
                        <option value="3">💔 (3/5) - Lumayanlah</option>
                        <option value="2">🤢 (2/5) - B Aja</option>
                        <option value="1">🤮 (1/5) - Aku Menyesal</option>
                    </select>
                    
                    <label style="display:block; margin-bottom:8px; font-weight:600; color:#cbd5e1;">Pengalaman Anda:</label>
                    <textarea name="cerita_perjalanan" rows="3" class="form-control" placeholder="Tuliskan ulasan kamu di sini..." required></textarea>

                    <button type="submit" name="submit_review" class="btn-submit">🚀 Kirim Ulasan</button>
                </form>
            <?php else: ?>
                <p style="color: #94a3b8; font-size: 0.9rem; margin-bottom: 24px;">Silakan <a href="login.php" style="color: #38bdf8; font-weight: 700;">Login</a> untuk menulis ulasan.</p>
            <?php endif; ?>

            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $rev): ?>
                    <div class="review-card">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <strong style="color: #fff; font-size: 0.9rem;">👤 <?= htmlspecialchars($rev['username']) ?></strong>
                            <span style="color: #f59e0b; font-size: 0.85rem;"><?= str_repeat('⭐', $rev['rating_bintang']) ?></span>
                        </div>
                        <p style="color: #cbd5e1; font-size: 0.88rem; margin-bottom: 8px;"><?= htmlspecialchars($rev['cerita_perjalanan']) ?></p>

                        <?php if ($user_login === $rev['username'] || $is_admin): ?>
                            <a href="hapus_komentar.php?id=<?= $rev['id'] ?>&id_gunung=<?= $id ?>" 
                               onclick="return confirm('Yakin ingin menghapus komentar ini?');" 
                               style="color: #f43f5e; font-size: 0.8rem; text-decoration: none; display: inline-block; font-weight: 600;">
                                🗑️ Hapus Komentar
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #94a3b8; font-style: italic; font-size: 0.88rem;">Belum ada ulasan untuk gunung ini.</p>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>