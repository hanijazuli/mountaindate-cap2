<?php
session_start();
require 'config.php';

// PROTEKSI: Cek apakah user sudah login dan role-nya admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

//  PROSES TAMBAH GUNUNG + BANYAK FOTO GALERI SEKALIGUS

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_mountain'])) {
    $nama = trim($_POST['nama']);
    $mdpl = (int)$_POST['mdpl'];
    $via = trim($_POST['via']);
    $info_rute = trim($_POST['info_rute']);
    $tingkat_kesulitan = $_POST['tingkat_kesulitan'];
    $deskripsi = trim($_POST['deskripsi']);

    // 1. Upload Cover Image Utama
    $cover_name = '';
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $tmp_cover = $_FILES['cover_image']['tmp_name'];
        $cover_name = time() . '_cover_' . basename($_FILES['cover_image']['name']);
        
        if (!is_dir('images')) {
            mkdir('images', 0777, true);
        }
        move_uploaded_file($tmp_cover, 'images/' . $cover_name);
    }

     if (!empty($nama)) {
    $stmt = $pdo->prepare("INSERT INTO gunung (nama, mdpl, jalur_via, info_rute, tingkat_kesulitan, deskripsi, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nama, $mdpl, $via, $info_rute, $tingkat_kesulitan, $deskripsi, $cover_name]);
    
    // Ambil ID gunung yang baru saja dibuat
    $id_gunung_baru = $pdo->lastInsertId();
   

        // 2. Upload Banyak Foto Galeri (Multiple Upload)
        if (isset($_FILES['gallery_photos'])) {
            $gallery_files = $_FILES['gallery_photos'];
            $target_dir = 'uploads/gallery/';

            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

           
            for ($i = 0; $i < count($gallery_files['name']); $i++) {
                if ($gallery_files['error'][$i] === UPLOAD_ERR_OK) {
                    $tmp_gal = $gallery_files['tmp_name'][$i];
                    $gal_file_name = time() . '_' . $i . '_' . basename($gallery_files['name'][$i]);

                    if (move_uploaded_file($tmp_gal, $target_dir . $gal_file_name)) {
                        // Simpan ke tabel galeri_gunung
                        $stmt_gal = $pdo->prepare("INSERT INTO galeri_gunung (id_gunung, nama_file) VALUES (?, ?)");
                        $stmt_gal->execute([$id_gunung_baru, $gal_file_name]);
                    }
                }
            }
        }

        $message = "Gunung baru beserta foto galerinya berhasil disimpan!";
    } else {
        $error = "Nama gunung wajib diisi.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>[Admin Panel] Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
        <link rel="stylesheet" href="style.css?v=1.0">
</head>
<body>

    <div class="container">
        <div class="header">
            <h1>[Admin Panel] Dashboard</h1>
            <div class="nav-links">
                <a href="index.php" class="btn-link btn-web">Lihat Web →</a>
                <a href="logout.php" class="btn-link btn-logout">Logout</a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert-success">✓ <?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert-error">✕ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- FORM TAMBAH GUNUNG + GALERI SEKALIGUS -->
        <div class="card">
            <h2 class="card-title">⛰️ Add New Mountain Card</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Mountain Name</label>
                    <input type="text" name="nama" class="form-control" placeholder="e.g., Mount Rinjani" required>
                </div>

                <div class="form-group">
                    <label>Elevation (MDPL)</label>
                    <input type="number" name="mdpl" class="form-control" placeholder="e.g., 3726">
                </div>

                <div class="form-group">
                    <label>Hiking Trail / Via</label>
                    <input type="text" name="via" class="form-control" placeholder="e.g., Via Selo / Via Sembalun">
                </div>

                <div class="form-group">
                    <label>Info Rute / Jalur</label>
                    <input type="text" name="info_rute" class="form-control" placeholder="contoh : Via Sembalun">
                </div>

                <div class="form-group">
                    <label>Difficulty Level</label>
                    <select name="tingkat_kesulitan" class="form-control">
                        <option value="Easy">Easy</option>
                        <option value="Medium">Medium</option>
                        <option value="Hard">Hard</option>
                        <option value="Extreme">Extreme</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Cover Image (Foto Sampul Utama)</label>
                    <input type="file" name="cover_image" class="form-control" accept="image/*">
                </div>

                
                <div class="form-group">
                    <label>Gallery Images (Bisa pilih beberapa foto sekaligus)</label>
                    <input type="file" name="gallery_photos[]" class="form-control" accept="image/*" multiple>
                    <small style="color: #64748b; margin-top: 4px; display: block;">Tahan tombol <b>Ctrl</b> (Windows) atau <b>Cmd</b> (Mac) saat memilih file untuk mengunggah lebih dari 1 foto.</small>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="deskripsi" rows="4" class="form-control" placeholder="Brief or detailed summary of the mountain..."></textarea>
                </div>


                <button type="submit" name="add_mountain" class="btn-submit">Simpan Gunung & Foto Galeri</button>
            </form>
        </div>
    </div>

</body>
</html>