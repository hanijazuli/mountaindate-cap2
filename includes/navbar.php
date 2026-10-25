<div class="navbar">
    <div style="color:white; font-size: 1.4rem; font-weight:bold;">
        <a href="index.php" style="margin-left:0;">Mountain Date</a>
    </div>
    <div>
        <a href="index.php#mountains">Mountains</a>
        <a href="tips.php">Safety & Tips</a>
        <a href="stories.php" class="nav-link">Jurnal Cerita</a>
        
        <?php if (isset($is_admin) && $is_admin): ?>
            <a href="dashboard_admin.php" style="background: #0284c7; padding: 8px 16px; border-radius: 6px; margin-left: 15px;">⚙️ Admin Panel</a>
        <?php endif; ?>

        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="profile.php" style="color: #38bdf8; margin-left: 15px; font-weight: 600;">
                👤 <?= htmlspecialchars($_SESSION['username'] ?? 'Profile') ?>
            </a>
            <a href="logout.php" style="color: #ef4444; margin-left: 10px;">Logout</a>
        <?php else: ?>
            <a href="login.php" style="background: #16a34a; padding: 8px 18px; border-radius:6px; margin-left: 15px;">Login</a>
        <?php endif; ?>
    </div>
</div>