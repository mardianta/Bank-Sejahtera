<?php require_once 'templates/header.php'; ?>

<?php
// Jika sudah login, redirect ke dashboard
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $errors[] = "Email dan password wajib diisi.";
    } else {
        // VULNERABLE TO SQL INJECTION
        $hashed_password = md5($password);
        $sql = "SELECT * FROM users WHERE email = '{$email}' AND password = '{$hashed_password}'";
        $user = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Login berhasil, buat session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nama'] = $user['nama_lengkap'];
            $_SESSION['user_role'] = $user['role'];
            header('Location: dashboard.php');
            exit();
        } else {
            $errors[] = "Email atau password salah.";
        }
    }
}
?>

<h2>Login</h2>
<p>Silakan masuk untuk melanjutkan.</p>

<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-success"><?php echo $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?></div>
<?php endif; ?>

<?php if (!empty($errors)): ?><div class="alert alert-danger"><?php echo $errors[0]; ?></div><?php endif; ?>

<form action="login.php" method="POST">
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" required>
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
    </div>
    <button type="submit">Login</button>
</form>
<p>Belum punya akun? <a href="register.php">Daftar di sini</a>.</p>

<?php require_once 'templates/footer.php'; ?>