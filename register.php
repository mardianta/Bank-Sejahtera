<?php require_once 'templates/header.php'; ?>

<?php
// Jika sudah login, redirect ke dashboard
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validasi sederhana
    if (empty($nama_lengkap)) $errors[] = "Nama lengkap wajib diisi.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format email tidak valid.";
    if (strlen($password) < 6) $errors[] = "Password minimal harus 6 karakter.";

    // Cek apakah email sudah terdaftar
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Email sudah terdaftar. Silakan gunakan email lain.";
        }
    }

    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        $hashed_password = md5($password); // Menggunakan MD5
        $sql = "INSERT INTO users (nama_lengkap, email, password) VALUES (?, ?, ?)";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nama_lengkap, $email, $hashed_password]);
            
            // Set pesan sukses dan redirect ke halaman login
            $_SESSION['flash_message'] = "Registrasi berhasil! Silakan login.";
            header('Location: login.php');
            exit();
        } catch (PDOException $e) {
            $errors[] = "Terjadi kesalahan saat menyimpan data: " . $e->getMessage();
        }
    }
}
?>

<h2>Registrasi Akun Baru</h2>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?><p><?php echo $error; ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>

<form action="register.php" method="POST">
    <div class="form-group">
        <label for="nama_lengkap">Nama Lengkap</label>
        <input type="text" name="nama_lengkap" id="nama_lengkap" required>
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" required>
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
    </div>
    <button type="submit">Daftar</button>
</form>
<p>Sudah punya akun? <a href="login.php">Login di sini</a>.</p>

<?php require_once 'templates/footer.php'; ?>