<?php require_once 'templates/header.php'; ?>

<?php
// Lindungi halaman ini
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Proses update data diri
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $alamat = trim($_POST['alamat']);
    $no_telepon = trim($_POST['no_telepon']);
    $tanggal_lahir = !empty($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : null;

    // Validasi dasar
    if (empty($nama_lengkap)) $errors[] = "Nama lengkap tidak boleh kosong.";

    // Proses upload foto profil
    $foto_profil_sql_part = "";
    $params_foto = [];

    // Proses ganti password
    $password_sql_part = "";
    $params_password = [];

    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['foto_profil'];
        $file_name = $file['name'];
        $file_tmp_name = $file['tmp_name'];
        $file_size = $file['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        
        // VULNERABLE TO UNRESTRICTED FILE UPLOAD
        // Validasi ekstensi file dihapus untuk tujuan pembelajaran.
        if ($file_size < 2000000) { // Maks 2MB
            // Tentukan direktori tujuan
            $upload_dir = __DIR__ . '/assets/uploads/profiles/';

            // Cek jika direktori tidak ada, maka buat direktorinya
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $new_file_name = $user_id . '-' . time() . '.' . $file_ext;
            $upload_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp_name, $upload_path)) {
                // Hapus foto lama jika ada
                $stmt = $pdo->prepare("SELECT foto_profil FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $old_photo = $stmt->fetchColumn();
                if ($old_photo && file_exists(__DIR__ . '/assets/uploads/profiles/' . $old_photo)) {
                    unlink(__DIR__ . '/assets/uploads/profiles/' . $old_photo);
                }

                $foto_profil_sql_part = ", foto_profil = ?";
                $params_foto[] = $new_file_name;
            } else {
                $errors[] = "Gagal memindahkan file yang diunggah.";
            }
        } else {
            $errors[] = "Ukuran file terlalu besar. Maksimal 2MB.";
        }
    }

    // Logika ganti password
    if (!empty($_POST['current_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_new_password = $_POST['confirm_new_password'];

        $stmt_pass = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt_pass->execute([$user_id]);
        $user_db_password = $stmt_pass->fetchColumn();

        if (md5($current_password) === $user_db_password) {
            if (!empty($new_password)) {
                if ($new_password === $confirm_new_password) {
                    if (strlen($new_password) >= 6) {
                        $hashed_password = md5($new_password);
                        $password_sql_part = ", password = ?";
                        $params_password[] = $hashed_password;
                    } else {
                        $errors[] = "Password baru minimal harus 6 karakter.";
                    }
                } else {
                    $errors[] = "Password baru dan konfirmasi password tidak cocok.";
                }
            } else {
                $errors[] = "Password baru tidak boleh kosong jika ingin mengganti.";
            }
        } else {
            $errors[] = "Password saat ini salah.";
        }
    }

    if (empty($errors)) {
        $sql = "UPDATE users SET nama_lengkap = ?, alamat = ?, no_telepon = ?, tanggal_lahir = ? {$foto_profil_sql_part} {$password_sql_part} WHERE id = ?";
        $params = [$nama_lengkap, $alamat, $no_telepon, $tanggal_lahir];
        $params = array_merge($params, $params_foto, $params_password, [$user_id]);

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Update nama di session jika berubah
        $_SESSION['user_nama'] = $nama_lengkap;
        $success_message = "Profil berhasil diperbarui!";
    }
}

// Ambil data terbaru user untuk ditampilkan di form
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<h2>Profil Saya</h2>
<p>Perbarui data diri dan foto profil Anda di sini.</p>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><?php foreach ($errors as $error) echo "<p>$error</p>"; ?></div>
<?php endif; ?>

<?php if ($success_message): ?>
    <div class="alert alert-success"><?php echo $success_message; ?></div>
<?php endif; ?>

<form action="profil.php" method="POST" enctype="multipart/form-data">
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
        <label for="foto_profil">Ganti Foto Profil</label>
        <input type="file" name="foto_profil" id="foto_profil">
    </div>

    <hr>
    <h4>Ganti Password</h4>
    <p>Kosongkan jika tidak ingin mengubah password.</p>
    <div class="form-group">
        <label for="current_password">Password Saat Ini</label>
        <input type="password" name="current_password" id="current_password" autocomplete="current-password">
    </div>
    <div class="form-group">
        <label for="new_password">Password Baru</label>
        <input type="password" name="new_password" id="new_password" autocomplete="new-password">
    </div>
    <div class="form-group">
        <label for="confirm_new_password">Konfirmasi Password Baru</label>
        <input type="password" name="confirm_new_password" id="confirm_new_password" autocomplete="new-password">
    </div>

    <button type="submit">Simpan Perubahan</button>
</form>

<?php require_once 'templates/footer.php'; ?>