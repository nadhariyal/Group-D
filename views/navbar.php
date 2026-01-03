<nav style="background: #333; color: #fff; padding: 15px; display: flex; justify-content: space-between; align-items: center; border-radius: 4px; margin-bottom: 20px;">
    <div style="font-weight: bold; font-size: 1.2em;">InventaFlow</div>
    <div>
        <?php if (isset($_SESSION['role'])): ?>
            <span style="margin-right: 15px;">Role: <strong><?php echo $_SESSION['role']; ?></strong></span>
            <a href="logout.php" style="color: #ff4d4d; text-decoration: none; font-weight: bold;">Logout</a>
        <?php else: ?>
            <a href="login.php" style="color: #fff; text-decoration: none; margin-right: 10px;">Login</a>
            <a href="register.php" style="color: #fff; text-decoration: none;">Register</a>
        <?php endif; ?>
    </div>
</nav>