<?php
session_start();
require 'config.php';

// Cek apakah user/admin yang sedang aktif memiliki role 'admin'
$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

// Ambil data pencarian gunung + hitung rata-rata rating dari tabel pengalaman
$search = $_GET['search'] ?? '';

$sql = "SELECT g.*, 
               COALESCE(AVG(p.rating_bintang), 0) AS avg_rating, 
               COUNT(p.id) AS total_reviews 
        FROM gunung g 
        LEFT JOIN pengalaman p ON g.id = p.id_gunung";

if ($search) {
    $sql .= " WHERE g.nama LIKE :search";
}

$sql .= " GROUP BY g.id";

$stmt = $pdo->prepare($sql);
if ($search) {
    $stmt->execute(['search' => "%$search%"]);
} else {
    $stmt->execute();
}
$mountains = $stmt->fetchAll();


$sql_top = "SELECT g.*, 
                   COALESCE(AVG(p.rating_bintang), 0) AS avg_rating, 
                   COUNT(p.id) AS total_reviews 
            FROM gunung g 
            LEFT JOIN pengalaman p ON g.id = p.id_gunung 
            GROUP BY g.id 
            ORDER BY avg_rating DESC, total_reviews DESC 
            LIMIT 3";
$stmt_top = $pdo->query($sql_top);
$top_mountains = $stmt_top->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MountainDate - Catalogue</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,800;1,400&family=Plus+Jakarta+Sans:wght@500;700;800&display=swap" rel="stylesheet">
    
    
    <link rel="stylesheet" href="style.css?v=2.0">
</head>
<body>

    <?php include 'includes/navbar.php'; ?>

    <!-- 1. HERO SECTION -->
    <div class="hero-image">
        <div class="hero-text">
            <h1>"Mari Berkelana Tanpa Rencana. Biarkan Angin dan Semesta yang Akan Jadi Pemandu, Karena Tempat Indah Akan Sia-Sia Jika Tidak ada Kamu Untuk Aku Kagumi disana."</h1>
        </div>
    </div>
    
    <div class="top-navigation-wrapper">
        <a href="welcome.php" class="btn-back-home">
            ← Kembali ke Beranda
        </a>

        <?php if ($is_admin): ?>
            <a href="tambah_gunung.php" class="btn-add-mountain">
                ➕ Tambah Gunung Baru
            </a>
        <?php endif; ?>
    </div>

    <!-- 2. KATALOG GUNUNG UTAMA -->
    <div class="main-container" id="mountains">
        
        <div class="search-container">
            <div class="section-header">
                <h2>Find Your Next Adventure</h2>
                <p>"Nyesel Sih Pas Tanjakan, Tapi Kangen Pas Sampai Rumah"</p>
            </div>

            <form method="GET" action="index.php#mountains" class="search-box">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Type mountain name...">
                <button type="submit">Search</button>
            </form>
        </div>

        <!-- Grid Card Gunung -->
        <div class="grid">
            <?php if (empty($mountains)): ?>
                <p style="color: #94a3b8; grid-column: 1/-1; text-align: center; padding: 40px;">No mountains found matching your search.</p>
            <?php else: ?>
                <?php foreach ($mountains as $m): ?>
                    <div class="card">
                        <div>
                            <img src="images/<?= htmlspecialchars($m['image_url']) ?>" alt="<?= htmlspecialchars($m['nama']) ?>" onerror="this.src='https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?q=80&w=400'">
                            
                            <!-- NAMA GUNUNG -->
                            <h3><?= htmlspecialchars($m['nama']) ?></h3>
                            
                            <!-- 📍 JALUR VIA -->
                            <div style="color: #cbd5e1; font-size: 0.88rem; font-weight: 600; margin-bottom: 8px;">
                                📍 <?= !empty($m['jalur_via']) ? htmlspecialchars($m['jalur_via']) : 'Via -' ?>
                            </div>

                            <!-- RATING BINTANG -->
                            <div style="color: #f59e0b; font-size: 0.95rem; margin-bottom: 10px;">
                                <?php 
                                    $score = round($m['avg_rating'], 1);
                                    $stars = round($score);
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo ($i <= $stars) ? '★' : '☆';
                                    }
                                ?>
                                <span style="color: #94a3b8; font-size: 0.85rem; margin-left: 5px;">
                                    <?= $score > 0 ? $score . ' (' . $m['total_reviews'] . ' ulasan)' : 'Belum ada ulasan' ?>
                                </span>
                            </div>

                            <p style="margin: 0 0 15px 0; font-size: 0.88rem; color: #94a3b8;">
                                ⛰️ <?= htmlspecialchars($m['mdpl'] ?? 0) ?> MDPL | <span class="badge">Difficulty: <?= htmlspecialchars($m['tingkat_kesulitan']) ?></span>
                            </p>
                        </div>

                      
                        <div class="card-actions">
                            <a href="details.php?id=<?= $m['id'] ?>" class="btn-view">View Details →</a>
                            
                            <!-- Tombol Hapus Khusus Admin -->
                            <?php if ($is_admin): ?>
                                <a href="hapus.php?id=<?= $m['id'] ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus gunung <?= htmlspecialchars($m['nama']) ?>?');"
                                   title="Hapus Gunung">
                                    🗑️
                                </a>
                                <a href="edit_gunung.php?id=<?= $m['id'] ?>" class="btn-action-edit" title="Edit Data Gunung">✏️</a>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

       
        <?php if (!empty($top_mountains)): ?>
        <div style="position: relative; margin-top: 100px; padding: 20px 0 60px 0; border-top: 1px solid rgba(255, 255, 255, 0.08); overflow: hidden;">
            
           
            <div class="running-ribbon" style="top: 35%; left: -10%; transform: rotate(-4deg); color: #38bdf8; background: rgba(56, 189, 248, 0.12); padding: 8px 0;">
                MOUNTAINDATE • EXPLORE THE PEAKS • MOUNTAINDATE • EXPLORE THE PEAKS • MOUNTAINDATE • EXPLORE THE PEAKS • MOUNTAINDATE • EXPLORE THE PEAKS •
            </div>

            
            <div class="running-ribbon" style="top: 65%; left: -10%; transform: rotate(-1.5deg); color: #34d399; background: rgba(52, 211, 153, 0.12); padding: 8px 0; animation-direction: reverse;">
                IGNITE YOUR ADVENTURE • MOUNTAINDATE • IGNITE YOUR ADVENTURE • MOUNTAINDATE • IGNITE YOUR ADVENTURE • MOUNTAINDATE •
            </div>

    
            <div style="position: relative; z-index: 2; text-align: center;">
                <h3 style="font-family: 'Playfair Display', serif; color: #ffffff; font-size: 2.2rem; font-weight: 800; margin-bottom: 28px;">
                    Terpopuler
                </h3>

            
                <div class="top-rank-container" style="background: rgba(28, 37, 65, 0.88); backdrop-filter: blur(12px); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 24px; padding: 28px; max-width: 580px; margin: 0 auto; text-align: left; box-shadow: 0 20px 45px rgba(0,0,0,0.6);">
                    
                    <?php $rank = 1; foreach ($top_mountains as $top): ?>
                        <?php if ($rank === 1): ?>
                          
                            <div class="rank-1-card" style="background: linear-gradient(135deg, #0284c7, #0369a1); border-radius: 18px; padding: 22px; display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 20px; color: #ffffff; box-shadow: 0 10px 25px rgba(2, 132, 199, 0.35);">
                                
                                <div style="display: flex; flex-direction: column; justify-content: center; align-self: flex-start;">
                                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                                        <span style="font-size: 1.5rem; font-weight: 800; color: #38bdf8;">01</span>
                                        <h4 style="font-size: 1.3rem; font-weight: 800; color: #ffffff; margin: 0;">
                                            <?= htmlspecialchars($top['nama']) ?>
                                        </h4>
                                    </div>
                                    <span style="font-size: 0.88rem; font-weight: 600; color: rgba(255,255,255,0.9); margin-left: 32px;">
                                        📍 Via <?= htmlspecialchars(preg_replace('/^via\s+/i', '', trim($top['jalur_via'] ?? '-'))) ?>
                                    </span>
                                </div>

                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <img src="images/<?= htmlspecialchars($top['image_url']) ?>" 
                                         onerror="this.src='https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?q=80&w=200'" 
                                         alt="<?= htmlspecialchars($top['nama']) ?>" 
                                         style="width: 80px; height: 105px; border-radius: 12px; object-fit: cover; box-shadow: 0 6px 15px rgba(0,0,0,0.25);">
                                    
                                    <a href="details.php?id=<?= $top['id'] ?>" style="background: rgba(255,255,255,0.2); color: #ffffff; text-decoration: none; padding: 8px 14px; border-radius: 8px; font-weight: 700; font-size: 0.82rem; backdrop-filter: blur(4px); white-space: nowrap; border: 1px solid rgba(255,255,255,0.3);">
                                        Lihat →
                                    </a>
                                </div>

                            </div>
                        <?php else: ?>
                          
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 8px; border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
                                <div style="display: flex; align-items: center; gap: 16px;">
                                    <span style="font-size: 1.1rem; font-weight: 800; color: #38bdf8; width: 25px;">
                                        0<?= $rank ?>
                                    </span>
                                    <h5 style="font-size: 1rem; font-weight: 700; color: #ffffff; margin: 0;">
                                        <?= htmlspecialchars($top['nama']) ?>
                                        <span style="font-size: 0.82rem; font-weight: 500; color: #94a3b8; margin-left: 6px;">
                                            (Via <?= htmlspecialchars(preg_replace('/^via\s+/i', '', trim($top['jalur_via'] ?? '-'))) ?>)
                                        </span>
                                    </h5>
                                </div>

                                <a href="details.php?id=<?= $top['id'] ?>" style="color: #38bdf8; text-decoration: none; font-weight: 700; font-size: 0.85rem;">
                                    Lihat →
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php $rank++; endforeach; ?>

                </div>
            </div>

        </div>
        <?php endif; ?>

        <!-- 4. KUMPULAN SURAT & JURNAL -->
        <div style="margin-top: 50px; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; padding: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div style="max-width: 650px;">
                <h4 style="font-family: 'Playfair Display', serif; color: #fff; font-size: 1.25rem; margin-bottom: 6px;">
                    ✉️ Kumpulan Surat & Jurnal Awan
                </h4>
                <p style="color: #94a3b8; font-size: 0.88rem; line-height: 1.5; margin: 0;">
                    Punya kisah berkesan di atas awan? Tulis pengalaman pendakianmu atau baca kumpulan catatan perjalanan dari teman-teman pendaki lainnya di Jurnal Cerita MountainDate.
                </p>
            </div>
            <a href="story_list.php" style="background: #0284c7; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 0.85rem;">
                Lihat Jurnal Cerita
            </a>
        </div>

    </div>

    <!-- Panggil File Footer -->
    <?php include 'includes/footer.php'; ?>

</body>
</html>