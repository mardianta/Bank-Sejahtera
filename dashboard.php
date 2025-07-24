<?php require_once 'templates/header.php'; ?>

<?php
// Lindungi halaman ini, hanya untuk pengguna yang sudah login
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Query untuk menghitung total saldo
$stmt = $pdo->prepare("
    SELECT 
        (SELECT COALESCE(SUM(jumlah), 0) FROM transactions WHERE user_id = ? AND tipe = 'pemasukan') - 
        (SELECT COALESCE(SUM(jumlah), 0) FROM transactions WHERE user_id = ? AND tipe = 'pengeluaran') 
    AS total_saldo
");
$stmt->execute([$user_id, $user_id]);
$saldo = $stmt->fetch(PDO::FETCH_ASSOC)['total_saldo'];
?>

<h2>Dashboard</h2>
<p>Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['user_nama']); ?></strong>!</p>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger"><?php echo $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>

<div class="summary">
    <h3>Total Saldo Anda Saat Ini:</h3>
    <p class="saldo"><?php echo format_rupiah($saldo); ?></p>
</div>

<a href="tambah_transaksi.php" class="button">Tambah Transaksi Baru</a>

<?php require_once 'templates/footer.php'; ?>