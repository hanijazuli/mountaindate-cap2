<?php
session_start();
require 'config.php';

// 1. Cek Login & Role Admin 
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$check_admin = $pdo->prepare('SELECT role FROM user WHERE id = ?');
$check_admin->execute([$_SESSION['user_id']]);
$user_role = $check_admin->fetchColumn();

if ($user_role !== 'admin') {
    header('Location: index.php');
    exit;
}

// 2. Proses Penghapusan Data
if (isset($_GET['id'])) {
    $id_gunung = (int) $_GET['id'];

    try {
        
        $stmt_del_exp = $pdo->prepare('DELETE FROM pengalaman WHERE id_gunung = ?');
        $stmt_del_exp->execute([$id_gunung]);

        
        $stmt_get = $pdo->prepare('SELECT image_url FROM gunung WHERE id = ?');
        $stmt_get->execute([$id_gunung]);
        $gunung = $stmt_get->fetch(PDO::FETCH_ASSOC);

        if ($gunung) {
            
            if (!empty($gunung['image_url'])) {
                $image_path = './images/' . $gunung['image_url'];
                if (file_exists($image_path)) {
                    @unlink($image_path);
                }
            }

          
            $stmt_del = $pdo->prepare('DELETE FROM gunung WHERE id = ?');
            $stmt_del->execute([$id_gunung]);
        }
    } catch (PDOException $e) {
       
        die("Gagal menghapus data: " . $e->getMessage());
    }
}

// Kembalikan langsung ke halaman utama
header('Location: index.php#mountains');
exit;