<?php
session_start();
require 'config.php';


$showcase_mountains = [];
try {
    $stmt = $pdo->query("SELECT id, nama, image_url FROM gunung ORDER BY id DESC LIMIT 6");
    $showcase_mountains = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang di MountainDate</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        html {
            scroll-behavior: smooth;
        }

        body {
            background-color: #0b132b;
            color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
            line-height: 1.6;
        }

       
        nav {
            background: rgba(11, 19, 43, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding: 18px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .logo {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.35rem;
            font-weight: 800;
            color: #38bdf8;
            text-decoration: none;
            letter-spacing: -0.5px;
        }

        .nav-links {
            display: flex;
            gap: 24px;
            align-items: center;
        }

        .nav-links a {
            color: #cbd5e1;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .nav-links a:hover { color: #38bdf8; }

        .btn-login-nav {
            background: #0284c7;
            color: #fff !important;
            padding: 8px 18px;
            border-radius: 8px;
            font-weight: 700 !important;
        }

        /* HERO SECTION DENGAN EFEK ANIMASI ZOOM HALUS */
        .hero-section {
            position: relative;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 0 24px;
            overflow: hidden;
            
            background: linear-gradient(rgba(11, 19, 43, 0.6), rgba(11, 19, 43, 0.8)), 
                        url('https://images.unsplash.com/photo-1534447677768-be436bb09401?q=80&w=1920');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            animation: heroZoom 20s infinite alternate ease-in-out;
        }

        @keyframes heroZoom {
            0% { transform: scale(1); }
            100% { transform: scale(1.1); }
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .quote-text {
            font-family: 'Playfair Display', serif;
            font-size: 2.3rem;
            color: #f8fafc;
            max-width: 820px;
            line-height: 1.5;
            font-weight: 700;
            text-shadow: 0 10px 30px rgba(0,0,0,0.6);
        }

      
        .showcase-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 24px 100px 24px;
            text-align: center;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: #38bdf8;
            margin-bottom: 40px;
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease-out;
        }

        .section-title.show {
            opacity: 1;
            transform: translateY(0);
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 28px;
            margin-bottom: 50px;
        }

     
        .gallery-card {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            height: 280px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            
            opacity: 0;
            transform: translateY(50px) scale(0.95);
            transition: opacity 0.7s ease, transform 0.7s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .gallery-card.show {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .gallery-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(56, 189, 248, 0.25);
        }

        .gallery-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .gallery-card:hover img {
            transform: scale(1.1);
        }

        .gallery-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(11, 19, 43, 0.95) 0%, rgba(11, 19, 43, 0.2) 60%, transparent 100%);
            display: flex;
            align-items: flex-end;
            padding: 24px;
            text-align: left;
        }

        .mountain-name-card {
            font-family: 'Playfair Display', serif;
            font-size: 1.35rem;
            font-weight: 700;
            color: #ffffff;
        }

       
        .cta-bottom-wrapper {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease-out;
        }

        .cta-bottom-wrapper.show {
            opacity: 1;
            transform: translateY(0);
        }

        .btn-cta-bottom {
            background: linear-gradient(135deg, #0284c7, #0369a1);
            color: #ffffff;
            padding: 16px 38px;
            border-radius: 14px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.05rem;
            box-shadow: 0 10px 30px rgba(2, 132, 199, 0.4);
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-cta-bottom:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(56, 189, 248, 0.5);
            background: linear-gradient(135deg, #0369a1, #0284c7);
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav>
        <a href="welcome.php" class="logo">Mountain Date</a>
        <div class="nav-links">
            <a href="index.php">Mountains</a>
            <a href="tips.php">Safety & Tips</a>
            <a href="story_list.php">Jurnal Cerita</a>
            
            <?php if (isset($_SESSION['username'])): ?>
                <span style="color:#38bdf8; font-weight:700;">👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="logout.php" style="color:#f43f5e;">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn-login-nav">Login</a>
            <?php endif; ?>
        </div>
    </nav>

 
    <div class="hero-section">
        <div class="hero-content">
            <h1 class="quote-text">
                "Terdengar langkah kaki yang berbisik lembut, menyuarakan kerinduan dari negeri di atas awan."
            </h1>
        </div>
    </div>

    <!-- SHOWCASE GALERI FOTO & TOMBOL -->
    <div class="showcase-section">
        <h2 class="section-title scroll-reveal">✨ Keindahan Puncak Pilihan</h2>

        <div class="gallery-grid">
            <?php if (!empty($showcase_mountains)): ?>
                <?php foreach ($showcase_mountains as $index => $m): ?>
                    <div class="gallery-card scroll-reveal" style="transition-delay: <?= $index * 150 ?>ms;">
                        <img src="images/<?= htmlspecialchars($m['image_url']) ?>" 
                             onerror="this.src='https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?q=80&w=600'" 
                             alt="<?= htmlspecialchars($m['nama']) ?>">
                        <div class="gallery-overlay">
                            <div class="mountain-name-card">⛰️ <?= htmlspecialchars($m['nama']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="gallery-card scroll-reveal" style="transition-delay: 0ms;">
                    <img src="https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?q=80&w=600" alt="Mountain">
                    <div class="gallery-overlay"><div class="mountain-name-card">⛰️ Mt. Rinjani</div></div>
                </div>
                <div class="gallery-card scroll-reveal" style="transition-delay: 150ms;">
                    <img src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?q=80&w=600" alt="Mountain">
                    <div class="gallery-overlay"><div class="mountain-name-card">⛰️ Mt. Prau</div></div>
                </div>
                <div class="gallery-card scroll-reveal" style="transition-delay: 300ms;">
                    <img src="https://images.unsplash.com/photo-1486870591958-9b9d0d1dda99?q=80&w=600" alt="Mountain">
                    <div class="gallery-overlay"><div class="mountain-name-card">⛰️ Mt. Gede</div></div>
                </div>
            <?php endif; ?>
        </div>

        <div class="cta-bottom-wrapper scroll-reveal">
            <a href="index.php" class="btn-cta-bottom">
                🚀 Mulai Petualangan Kamu
            </a>
        </div>
    </div>

    <!-- JAVASCRIPT UNTUK EFEK SCROLL MUNCUL SATU PER SATU -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const observerOptions = {
                root: null,
                rootMargin: '0px',
                threshold: 0.15
            };

            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('show');
                        observer.unobserve(entry.target); 
                    }
                });
            }, observerOptions);

            const revealElements = document.querySelectorAll('.scroll-reveal');
            revealElements.forEach(el => observer.observe(el));
        });
    </script>

</body>
</html>