<?php
session_start();
require 'config.php';


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];
$message = '';
$error = '';


if (!file_exists('uploads/gallery')) {
    mkdir('uploads/gallery', 0777, true);
}

// 1. Ambil Data Gunung Lengkap dari DB berdasarkan ID
$stmt = $pdo->prepare("SELECT * FROM gunung WHERE id = ?");
$stmt->execute([$id]);
$gunung = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$gunung) {
    die("Data gunung tidak ditemukan di database!");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_gunung'])) {
    $nama              = trim($_POST['nama']);
    $jalur_via         = trim($_POST['jalur_via']);
    $mdpl              = (int)$_POST['mdpl'];
    $tingkat_kesulitan = trim($_POST['tingkat_kesulitan']);
    $deskripsi         = trim($_POST['deskripsi'] ?? '');
    $info_rute         = trim($_POST['info_rute'] ?? '');
    
  
    $image_url         = $gunung['image_url'];

   
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileName = time() . '_' . $_FILES['image']['name'];
        if (move_uploaded_file($_FILES['image']['tmp_name'], './images/' . $fileName)) {
            $image_url = $fileName;
        }
    }

    try {
        $stmt_up = $pdo->prepare("
            UPDATE gunung 
            SET nama = ?, 
                jalur_via = ?, 
                mdpl = ?, 
                tingkat_kesulitan = ?, 
                deskripsi = ?, 
                info_rute = ?,
                image_url = ?
            WHERE id = ?
        ");
        
        $stmt_up->execute([
            $nama, 
            $jalur_via, 
            $mdpl, 
            $tingkat_kesulitan, 
            $deskripsi, 
            $info_rute,
            $image_url, 
            $id
        ]);

        $message = "✨ Data gunung & info jalur berhasil diperbarui!";
        
       
        $stmt->execute([$id]);
        $gunung = $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        $error = "Gagal memperbarui data gunung: " . $e->getMessage();
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_galeri'])) {
    if (isset($_FILES['foto_galeri']) && $_FILES['foto_galeri']['error'] === UPLOAD_ERR_OK) {
        $fileName = time() . '_' . basename($_FILES['foto_galeri']['name']);
        $targetPath = 'uploads/gallery/' . $fileName;

        if (move_uploaded_file($_FILES['foto_galeri']['tmp_name'], $targetPath)) {
            $stmt_ins = $pdo->prepare("INSERT INTO galeri_gunung (id_gunung, nama_file) VALUES (?, ?)");
            $stmt_ins->execute([$id, $fileName]);
            $message = "📸 Foto baru berhasil ditambahkan ke galeri!";
        } else {
            $error = "Gagal mengupload foto galeri.";
        }
    } else {
        $error = "Pilih file foto galeri terlebih dahulu!";
    }
}

// 4. PROSES HAPUS FOTO DARI GALERI
if (isset($_GET['action']) && $_GET['action'] === 'delete_galeri' && isset($_GET['foto_id'])) {
    $foto_id = $_GET['foto_id'];
    
    $stmt_f = $pdo->prepare("SELECT nama_file FROM galeri_gunung WHERE id = ?");
    $stmt_f->execute([$foto_id]);
    $foto = $stmt_f->fetch(PDO::FETCH_ASSOC);

    if ($foto) {
        $filePath = 'uploads/gallery/' . $foto['nama_file'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $stmt_del = $pdo->prepare("DELETE FROM galeri_gunung WHERE id = ?");
        $stmt_del->execute([$foto_id]);
        
        header("Location: edit_gunung.php?id=" . $id . "&msg=deleted");
        exit;
    }
}

/
$stmt_galeri = $pdo->prepare("SELECT * FROM galeri_gunung WHERE id_gunung = ? ORDER BY id DESC");
$stmt_galeri->execute([$id]);
$gallery_list = $stmt_galeri->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data & Galeri Gunung - MountainDate</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=2.0">
</head>
<body style="background-color: #0b132b; color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; min-height: 100vh; padding: 40px 20px;">

    <div class="edit-container">
        <!-- TOMBOL KEMBALI LANGSUNG KE INDEX -->
        <a href="index.php" class="btn-top-back">⬅️ Kembali ke Katalog Utama</a>

        <div class="edit-card">
            <span class="admin-badge">🛡️ Control Panel Admin</span>
            <h2 class="edit-title">✏️ Edit Data & Galeri Gunung</h2>

            <?php if ($message || isset($_GET['msg'])): ?>
                <div style="background: rgba(74, 222, 128, 0.15); color: #4ade80; border: 1px solid rgba(74, 222, 128, 0.3); padding: 12px; border-radius: 10px; margin-bottom: 20px; font-weight: 600;">
                    <?= htmlspecialchars($message ? $message : "Foto galeri berhasil dihapus.") ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div style="background: rgba(244, 63, 94, 0.2); color: #f43f5e; border: 1px solid rgba(244, 63, 94, 0.4); padding: 12px; border-radius: 10px; margin-bottom: 20px; font-weight: 600;">
                    ⚠️ <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- FORM 1: DATA UTAMA GUNUNG -->
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="grid-2">
                    <div class="form-group">
                        <label>Nama Gunung:</label>
                        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($gunung['nama'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Jalur Via / Basecamp Utama:</label>
                        <input type="text" name="jalur_via" class="form-control" value="<?= htmlspecialchars($gunung['jalur_via'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label>Ketinggian (MDPL):</label>
                        <input type="number" name="mdpl" class="form-control" value="<?= htmlspecialchars($gunung['mdpl'] ?? 0) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Tingkat Kesulitan:</label>
                        <select name="tingkat_kesulitan" class="form-control" style="color: #ffffff; background: rgba(15, 23, 42, 0.9);">
                            <option value="Easy" <?= ($gunung['tingkat_kesulitan'] ?? '') === 'Easy' ? 'selected' : '' ?>>Easy</option>
                            <option value="Medium" <?= ($gunung['tingkat_kesulitan'] ?? '') === 'Medium' ? 'selected' : '' ?>>Medium</option>
                            <option value="Hard" <?= ($gunung['tingkat_kesulitan'] ?? '') === 'Hard' ? 'selected' : '' ?>>Hard</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Deskripsi Gunung:</label>
                    <textarea name="deskripsi" rows="3" class="form-control"><?= htmlspecialchars($gunung['deskripsi'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Info Jalur / Rute Pendakian:</label>
                    <textarea name="info_rute" rows="4" class="form-control" placeholder="Tulis rute dari Basecamp ke Pos 1, Pos 2, Puncak..."><?= htmlspecialchars($gunung['info_rute'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Ganti Foto Cover Utama (Kosongkan jika tidak diubah):</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>

                <div class="btn-group">
                    <button type="submit" name="update_gunung" value="1" class="btn-submit">💾 Simpan Data Utama</button>
                    <a href="index.php" class="btn-cancel">Batal</a>
                </div>
            </form>

            <!-- FORM 2: KELOLA GALERI FOTO -->
            <div class="section-divider">
                <h3 style="color: #38bdf8; font-size: 1.2rem; margin-bottom: 12px;">🖼️ Kelola Galeri Visual</h3>
            </div>

            <form method="POST" action="" enctype="multipart/form-data" style="background: rgba(15, 23, 42, 0.6); padding: 18px; border-radius: 12px; border: 1px dashed rgba(56, 189, 248, 0.4);">
                <div class="form-group">
                    <label>Tambah Foto Baru ke Galeri Gunung ini:</label>
                    <input type="file" name="foto_galeri" class="form-control" accept="image/*" required>
                </div>
                <button type="submit" name="upload_galeri" value="1" class="btn-submit" style="width: auto; padding: 10px 20px;">📤 Upload Foto Galeri</button>
            </form>

            <!-- DAFTAR FOTO GALERI -->
            <div class="gallery-grid">
                <?php if (!empty($gallery_list)): ?>
                    <?php foreach ($gallery_list as $g): ?>
                        <div class="gallery-card">
                            <img src="uploads/gallery/<?= htmlspecialchars($g['nama_file']) ?>" alt="Foto Galeri">
                            <a href="edit_gunung.php?id=<?= $id ?>&action=delete_galeri&foto_id=<?= $g['id'] ?>" 
                               class="btn-delete-img" 
                               onclick="return confirm('Hapus foto ini dari galeri?');" 
                               title="Hapus Foto">✕</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #94a3b8; font-style: italic; font-size: 0.9rem; grid-column: 1/-1;">Belum ada foto tambahan di galeri ini.</p>
                <?php endif; ?>
            </div>

        </div>
    </div>

</body>
</html>