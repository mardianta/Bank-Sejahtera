<?php require_once __DIR__ . '/../core/init.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Sejahtera</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header>
    <h1><a href="dashboard.php">Bank Sejahtera</a></h1>
    <nav>
        <ul class="nav-links">
            <?php if (is_logged_in()): ?>
                <?php
                    // Ambil data user untuk foto profil
                    $stmt_user = $pdo->prepare("SELECT nama_lengkap, foto_profil FROM users WHERE id = ?");
                    $stmt_user->execute([$_SESSION['user_id']]);
                    $current_user = $stmt_user->fetch(PDO::FETCH_ASSOC);
                ?>
                <li><a href="dashboard.php">Dashboard</a></li>
                <?php if ($_SESSION['user_role'] === 'nasabah'): ?>
                    <li><a href="tambah_transaksi.php">Tambah Transaksi</a></li>
                <?php endif; ?>
                
                <li><a href="riwayat.php">Riwayat Transaksi</a></li>
                
                <li><a href="profil.php">Profil Saya</a></li>

                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <li><a href="manajemen_user.php">Manajemen User</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            <?php endif; ?>
        </ul>
        <?php if (is_logged_in() && isset($current_user)): ?>
            <?php display_profile_picture($current_user); ?>
        <?php endif; ?>
    </nav>
</header>

<main>