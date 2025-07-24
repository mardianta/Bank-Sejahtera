<?php

// File ini akan berisi kumpulan fungsi-fungsi yang dapat digunakan kembali
// di seluruh aplikasi.

// Contoh fungsi untuk memeriksa apakah pengguna sudah login
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Contoh fungsi untuk memformat angka menjadi format Rupiah
function format_rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

/**
 * Memeriksa apakah pengguna memiliki peran yang diizinkan untuk mengakses halaman.
 * Jika tidak, redirect ke dashboard dengan pesan error.
 * @param array $allowed_roles Daftar peran yang diizinkan.
 */
function authorize_role(array $allowed_roles) {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit();
    }
    if (!in_array($_SESSION['user_role'], $allowed_roles)) {
        $_SESSION['flash_error'] = "Anda tidak memiliki izin untuk mengakses halaman tersebut.";
        header('Location: dashboard.php');
        exit();
    }
}

/**
 * Menampilkan foto profil pengguna atau inisial nama jika tidak ada foto.
 * @param array $user Data pengguna yang berisi 'foto_profil' dan 'nama_lengkap'.
 * @param string $class_name Kelas CSS tambahan untuk elemen.
 */
function display_profile_picture(array $user, string $class_name = 'profile-pic-small') {
    $upload_dir = '/assets/uploads/profiles/';
    $file_path = $_SERVER['DOCUMENT_ROOT'] . '/Bank-Sejahtera' . $upload_dir . ($user['foto_profil'] ?? '');

    if (!empty($user['foto_profil']) && file_exists($file_path)) {
        echo '<img src="' . $upload_dir . htmlspecialchars($user['foto_profil']) . '" alt="Foto Profil" class="' . $class_name . '">';
    } else {
        $initial = strtoupper(substr($user['nama_lengkap'], 0, 1));
        // Generate warna random berdasarkan nama untuk konsistensi
        $hue = crc32($user['nama_lengkap']) % 360;
        echo '<div class="' . $class_name . ' profile-initial" style="background-color: hsl(' . $hue . ', 40%, 50%);">';
        echo '<span>' . htmlspecialchars($initial) . '</span>';
        echo '</div>';
    }
}