<?php
session_start();
require 'config.php';

// 1. Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}


$id_komentar = $_GET['id'] ?? null;
$id_gunung   = $_GET['id_gunung'] ?? null;

if (!$id_komentar) {
    header("Location: index.php");
    exit();
}

try {
   
    $stmt = $pdo->prepare("
        SELECT p.*, u.username AS pembuat_username 
        FROM pengalaman p 
        LEFT JOIN users u ON p.id_user = u.id 
        WHERE p.id = :id
    ");
    $stmt->execute(['id' => $id_komentar]);
    $komentar = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($komentar) {
        $user_login = $_SESSION['username'];
        $is_admin   = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

       
        if ($komentar['pembuat_username'] === $user_login || $is_admin) {
            
            // Hapus komentar
            $deleteStmt = $pdo->prepare("DELETE FROM pengalaman WHERE id = :id");
            $deleteStmt->execute(['id' => $id_komentar]);

           g
            header("Location: details.php?id=" . $id_gunung);
            exit();

        } else {
           
            echo "<script>alert('Anda tidak memiliki izin untuk menghapus komentar ini!'); window.history.back();</script>";
            exit();
        }
    } else {
        header("Location: index.php");
        exit();
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>