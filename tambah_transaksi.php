<?php require_once 'templates/header.php'; ?>

<?php
// Lindungi halaman ini, hanya untuk pengguna yang sudah login
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipe = $_POST['tipe'];
    $jumlah = $_POST['jumlah'];
    $deskripsi = trim($_POST['deskripsi']);
    $tanggal_transaksi = $_POST['tanggal_transaksi'];
    $user_id = $_SESSION['user_id'];

    // Validasi
    if (!in_array($tipe, ['pemasukan', 'pengeluaran'])) $errors[] = "Tipe transaksi tidak valid.";
    if (!is_numeric($jumlah) || $jumlah <= 0) $errors[] = "Jumlah harus berupa angka positif.";
    if (empty($deskripsi)) $errors[] = "Deskripsi wajib diisi.";

    if (empty($errors)) {
        // Gabungkan tanggal dengan waktu saat ini untuk format DATETIME
        $tanggal_transaksi_dt = $tanggal_transaksi . ' ' . date('H:i:s');
        
        $sql = "INSERT INTO transactions (user_id, tipe, jumlah, deskripsi, tanggal_transaksi) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $tipe, $jumlah, $deskripsi, $tanggal_transaksi_dt]);

        header('Location: riwayat.php');
        exit();
    }
}
?>

<h2>Tambah Transaksi Baru</h2>

<?php if (!empty($errors)): ?><div class="alert alert-danger"><?php echo $errors[0]; ?></div><?php endif; ?>

<form action="tambah_transaksi.php" method="POST">
    <div class="form-group">
        <label for="tipe">Tipe Transaksi</label>
        <select name="tipe" id="tipe" required>
            <option value="pemasukan">Pemasukan</option>
            <option value="pengeluaran">Pengeluaran</option>
        </select>
    </div>
    <div class="form-group">
        <label for="jumlah">Jumlah (Rp)</label>
        <input type="number" name="jumlah" id="jumlah" required>
    </div>
    <div class="form-group">
        <label for="deskripsi">Deskripsi</label>
        <textarea name="deskripsi" id="deskripsi" rows="4" required></textarea>
    </div>
    <div class="form-group">
        <label for="tanggal_transaksi">Tanggal</label>
        <input type="date" name="tanggal_transaksi" id="tanggal_transaksi" value="<?php echo date('Y-m-d'); ?>" required>
    </div>
    <button type="submit">Simpan Transaksi</button>
</form>

<?php require_once 'templates/footer.php'; ?>