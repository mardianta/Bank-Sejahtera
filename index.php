<?php
require_once __DIR__ . '/core/init.php';

// Jika pengguna sudah login, arahkan ke dashboard
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit();
} else {
    // Jika belum login, arahkan ke halaman login
    header('Location: login.php');
    exit();
}