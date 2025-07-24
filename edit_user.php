<?php require_once 'templates/header.php'; ?>

<?php
// Hanya admin yang bisa mengakses halaman ini
authorize_role(['admin']);

$user_id_to_edit = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data user yang akan diedit
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id_to_edit]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika user tidak ditemukan, redirect
if (!$user) {
    header('Location: manajemen_user.php');
    exit();
}

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Proses update data
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $alamat = trim($_POST['alamat']);
    $no_telepon = trim($_POST['no_telepon']);
    $tanggal_lahir = !empty($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : null;
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi
    if (empty($nama_lengkap)) $errors[] = "Nama lengkap tidak boleh kosong.";
    if (!in_array($role, ['admin', 'teller', 'nasabah'])) $errors[] = "Peran tidak valid.";

    // Variabel untuk update password kondisional
    $password_sql_part = "";
    $params_password = [];

    if (!empty($password)) {
        if ($password === $confirm_password) {
            if (strlen($password) >= 6) {
                $hashed_password = md5($password);
                $password_sql_part = ", password = ?";
                $params_password[] = $hashed_password;
            } else {
                $errors[] = "Password baru minimal harus 6 karakter.";
            }
        } else {
            $errors[] = "Password baru dan konfirmasi password tidak cocok.";
        }
    }

    // Admin tidak bisa mengubah role-nya sendiri menjadi bukan admin jika dia satu-satunya admin
    if ($user['id'] === $_SESSION['user_id'] && $user['role'] === 'admin' && $role !== 'admin') {
        $stmt_admin_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        if ($stmt_admin_count->fetchColumn() <= 1) {
            $errors[] = "Tidak dapat mengubah peran admin terakhir.";
        }
    }

    if (empty($errors)) {
        $sql = "UPDATE users SET nama_lengkap = ?, alamat = ?, no_telepon = ?, tanggal_lahir = ?, role = ? {$password_sql_part} WHERE id = ?";
        $params = [$nama_lengkap, $alamat, $no_telepon, $tanggal_lahir, $role];
        $params = array_merge($params, $params_password, [$user_id_to_edit]);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $_SESSION['flash_message'] = "Profil pengguna berhasil diperbarui!";
        header('Location: manajemen_user.php');
        exit();
    }
}
?>

<h2>Edit Pengguna: <?php echo htmlspecialchars($user['nama_lengkap']); ?></h2>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><?php foreach ($errors as $error) echo "<p>$error</p>"; ?></div>
<?php endif; ?>

<form action="edit_user.php?id=<?php echo $user_id_to_edit; ?>" method="POST">
    <div class="form-group">
        <label for="nama_lengkap">Nama Lengkap</label>
        <input type="text" name="nama_lengkap" id="nama_lengkap" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>
    </div>
    <div class="form-group">
        <label for="email">Email (tidak dapat diubah)</label>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
    </div>
    <div class="form-group">
        <label for="alamat">Alamat</label>
        <textarea name="alamat" id="alamat" rows="3"><?php echo htmlspecialchars($user['alamat'] ?? ''); ?></textarea>
    </div>
    <div class="form-group">
        <label for="no_telepon">Nomor Telepon</label>
        <input type="tel" name="no_telepon" id="no_telepon" value="<?php echo htmlspecialchars($user['no_telepon'] ?? ''); ?>">
    </div>
    <div class="form-group">
        <label for="tanggal_lahir">Tanggal Lahir</label>
        <input type="date" name="tanggal_lahir" id="tanggal_lahir" value="<?php echo htmlspecialchars($user['tanggal_lahir'] ?? ''); ?>">
    </div>
    <div class="form-group">
        <label for="role">Peran (Role)</label>
        <select name="role" id="role" required>
            <option value="nasabah" <?php echo ($user['role'] == 'nasabah') ? 'selected' : ''; ?>>Nasabah</option>
            <option value="teller" <?php echo ($user['role'] == 'teller') ? 'selected' : ''; ?>>Teller</option>
            <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
        </select>
    </div>

    <hr>
    <h4>Ganti Password (Opsional)</h4>
    <p>Kosongkan jika tidak ingin mengubah password.</p>
    <div class="form-group">
        <label for="password">Password Baru</label>
        <input type="password" name="password" id="password" autocomplete="new-password">
    </div>
    <div class="form-group">
        <label for="confirm_password">Konfirmasi Password Baru</label>
        <input type="password" name="confirm_password" id="confirm_password" autocomplete="new-password">
    </div>

    <button type="submit">Simpan Perubahan</button>
    <a href="manajemen_user.php" class="button-secondary">Batal</a>
</form>

<?php require_once 'templates/footer.php'; ?>