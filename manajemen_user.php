<?php require_once 'templates/header.php'; ?>

<?php
// Hanya admin yang bisa mengakses halaman ini
authorize_role(['admin']);

$stmt = $pdo->query("SELECT id, nama_lengkap, email, role FROM users ORDER BY id ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Manajemen Pengguna</h2>

<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-success"><?php echo $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?></div>
<?php endif; ?>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nama Lengkap</th>
            <th>Email</th>
            <th>Role</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($users) > 0): ?>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo $user['nama_lengkap']; ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo ucfirst($user['role']); ?></td>
                    <td>
                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn-edit">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align:center;">Tidak ada data pengguna.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once 'templates/footer.php'; ?>