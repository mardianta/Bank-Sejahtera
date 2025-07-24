<?php require_once 'templates/header.php'; ?>

<?php
// Lindungi halaman ini, hanya untuk pengguna yang sudah login
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['user_role'] === 'nasabah') {
    // Nasabah hanya melihat transaksinya sendiri
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY tanggal_transaksi DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Admin dan Teller melihat semua transaksi, di-join dengan nama nasabah
    $sql = "SELECT t.*, u.nama_lengkap 
            FROM transactions t 
            JOIN users u ON t.user_id = u.id 
            ORDER BY t.tanggal_transaksi DESC";
    $stmt = $pdo->query($sql);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<h2>Riwayat Transaksi</h2>

<table class="table">
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Deskripsi</th>
            <th>Tipe</th>
            <?php if ($_SESSION['user_role'] !== 'nasabah'): ?>
                <th>Nasabah</th>
            <?php endif; ?>
            <th style="text-align: right;">Jumlah</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($transactions) > 0): ?>
            <?php foreach ($transactions as $trx): ?>
                <tr>
                    <td><?php echo date('d M Y, H:i', strtotime($trx['tanggal_transaksi'])); ?></td>
                    <td><?php echo $trx['deskripsi']; ?></td>
                    <td>
                        <span class="tipe <?php echo $trx['tipe']; ?>">
                            <?php echo ucfirst($trx['tipe']); ?>
                        </span>
                    </td>
                    <?php if ($_SESSION['user_role'] !== 'nasabah'): ?>
                        <td><?php echo htmlspecialchars($trx['nama_lengkap']); ?></td>
                    <?php endif; ?>
                    <td class="<?php echo $trx['tipe'] === 'pemasukan' ? 'text-success' : 'text-danger'; ?>" style="text-align: right;">
                        <?php echo format_rupiah($trx['jumlah']); ?>
                    </td>
                    <td>
                        <?php // Hanya admin atau nasabah pemilik transaksi yang bisa edit/hapus ?>
                        <?php if ($_SESSION['user_role'] === 'admin' || $trx['user_id'] == $_SESSION['user_id']): ?>
                            <a href="edit_transaksi.php?id=<?php echo $trx['id']; ?>" class="btn-edit">Edit</a>
                            <a href="hapus_transaksi.php?id=<?php echo $trx['id']; ?>" class="btn-hapus" onclick="return confirm('Anda yakin ingin menghapus transaksi ini?');">Hapus</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="<?php echo ($_SESSION['user_role'] !== 'nasabah') ? '6' : '5'; ?>" style="text-align:center;">Belum ada transaksi.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once 'templates/footer.php'; ?>