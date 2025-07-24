<?php require_once 'templates/header.php'; ?>

<?php
// Lindungi halaman ini
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$transaction_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data transaksi dari database
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ?"); // VULNERABLE TO IDOR
$stmt->execute([$transaction_id]);
$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika transaksi tidak ditemukan atau bukan milik user, redirect
if (!$transaction) {
    header('Location: riwayat.php');
    exit();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Proses update data
    $tipe = $_POST['tipe'];
    $jumlah = $_POST['jumlah'];
    $deskripsi = trim($_POST['deskripsi']);
    // Ambil hanya bagian tanggal dari input, karena kita tidak ingin mengubah waktu aslinya
    $tanggal_transaksi = date('Y-m-d', strtotime($_POST['tanggal_transaksi']));

    // Validasi
    if (!in_array($tipe, ['pemasukan', 'pengeluaran'])) $errors[] = "Tipe transaksi tidak valid.";
    if (!is_numeric($jumlah) || $jumlah <= 0) $errors[] = "Jumlah harus berupa angka positif.";
    if (empty($deskripsi)) $errors[] = "Deskripsi wajib diisi.";

    if (empty($errors)) {
        // Gabungkan tanggal yang diupdate dengan waktu original dari transaksi
        $original_time = date('H:i:s', strtotime($transaction['tanggal_transaksi']));
        $tanggal_transaksi_dt = $tanggal_transaksi . ' ' . $original_time;

        $sql = "UPDATE transactions SET tipe = ?, jumlah = ?, deskripsi = ?, tanggal_transaksi = ? WHERE id = ?"; // VULNERABLE TO IDOR
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tipe, $jumlah, $deskripsi, $tanggal_transaksi_dt, $transaction_id]);

        header('Location: riwayat.php');
        exit();
    }
}

// Format tanggal untuk value di input type="date"
$tanggal_value = date('Y-m-d', strtotime($transaction['tanggal_transaksi']));

?>

<h2>Edit Transaksi</h2>

<?php if (!empty($errors)): ?><div class="alert alert-danger"><?php echo $errors[0]; ?></div><?php endif; ?>

<form action="edit_transaksi.php?id=<?php echo $transaction_id; ?>" method="POST">
    <div class="form-group">
        <label for="tipe">Tipe Transaksi</label>
        <select name="tipe" id="tipe" required>
            <option value="pemasukan" <?php echo ($transaction['tipe'] == 'pemasukan') ? 'selected' : ''; ?>>Pemasukan</option>
            <option value="pengeluaran" <?php echo ($transaction['tipe'] == 'pengeluaran') ? 'selected' : ''; ?>>Pengeluaran</option>
        </select>
    </div>
    <div class="form-group">
        <label for="jumlah">Jumlah (Rp)</label>
        <input type="number" name="jumlah" id="jumlah" value="<?php echo htmlspecialchars($transaction['jumlah']); ?>" required>
    </div>
    <div class="form-group">
        <label for="deskripsi">Deskripsi</label>
        <textarea name="deskripsi" id="deskripsi" rows="4" required><?php echo htmlspecialchars($transaction['deskripsi']); ?></textarea>
    </div>
    <div class="form-group">
        <label for="tanggal_transaksi">Tanggal</label>
        <input type="date" name="tanggal_transaksi" id="tanggal_transaksi" value="<?php echo $tanggal_value; ?>" required>
    </div>
    <button type="submit">Update Transaksi</button>
    <a href="riwayat.php" class="button-secondary">Batal</a>
</form>

<?php require_once 'templates/footer.php'; ?>