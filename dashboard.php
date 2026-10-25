<?php
session_start();
require 'config.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$search = $_GET['search'] ?? '';

if ($search) {
    $stmt = $pdo->prepare('SELECT * FROM gunung WHERE nama LIKE ?');
    $stmt->execute(["%$search%"]);
} else {
    $stmt = $pdo->query('SELECT * FROM gunung');
}
$mountains = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head><title>Dashboard - Mountain Search</title></head>
<body>
    <header>
        <span>Welcome, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>!</span> | 
        <a href="global-comments.php">Global Discussion</a> | 
        <a href="logout.php">Logout</a>
    </header>
    <hr>
    
    <h2>Find a Mountain</h2>
    <form method="GET">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search mountain name...">
        <button type="submit">Search</button>
    </form>

    <h3>Mountain List</h3>
    <ul>
        <?php foreach ($mountains as $m): ?>
            <li>
                <strong><a href="mountain-detail.php?id=<?= $m['id'] ?>"><?= htmlspecialchars($m['nama']) ?></a></strong> 
                (Difficulty: <?= htmlspecialchars($m['tingkat_kesulitan']) ?>)
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>