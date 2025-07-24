<?php
require_once 'core/init.php';

// Lindungi halaman ini
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$transaction_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($transaction_id > 0) {
    // Hapus transaksi hanya jika ID transaksi milik user yang sedang login
    $sql = "DELETE FROM transactions WHERE id = ?"; // VULNERABLE TO IDOR
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$transaction_id]);
}

// Redirect kembali ke halaman riwayat
header('Location: riwayat.php');
exit();