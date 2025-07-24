<?php
require_once 'core/init.php';

// Set agar skrip tidak timeout saat dijalankan
set_time_limit(300); // 5 menit

echo "<h1>Membuat Data Dummy...</h1>";
echo "<p>Proses ini mungkin memakan waktu beberapa saat. Mohon jangan tutup halaman ini.</p>";
ob_flush();
flush();

try {
    // Daftar nama untuk digabungkan secara acak
    $first_names = explode(',', 'Budi,Adi,Eko,Agus,Siti,Ani,Dewi,Lestari,Indah,Putra,Putri,Wulan,Dian,Rina,Sari,Joko,Bambang,Asep,Ujang,Iwan');
    $last_names = explode(',', 'Susanto,Wijaya,Hartono,Salim,Kusuma,Nugroho,Setiawan,Halim,Santoso,Gunawan,Purnomo,Wibowo,Lesmana,Tanuwijaya,Tjahjono');

    // Password yang sudah di-hash menggunakan MD5 untuk 'password123'
    $hashed_pass = '202cb962ac59075b964b07152d234b70';

    // Mulai transaksi database untuk mempercepat proses
    $pdo->beginTransaction();

    for ($i = 1; $i <= 100; $i++) {
        // 1. MEMBUAT USER NASABAH BARU
        $random_first_name = $first_names[array_rand($first_names)];
        $random_last_name = $last_names[array_rand($last_names)];
        $full_name = $random_first_name . ' ' . $random_last_name;

        $user_sql = "INSERT INTO users (nama_lengkap, email, password, role, alamat, no_telepon, tanggal_lahir) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_user = $pdo->prepare($user_sql);
        
        $stmt_user->execute([
            $full_name,
            'nasabah' . $i . '@example.com',
            $hashed_pass,
            'nasabah',
            'Jl. Sejahtera No. ' . $i . ', Jakarta',
            '081' . mt_rand(100000000, 999999999),
            date('Y-m-d', mt_rand(strtotime('1985-01-01'), strtotime('2005-01-01')))
        ]);

        $new_user_id = $pdo->lastInsertId();
        echo "User '{$full_name}' dibuat dengan ID: {$new_user_id}.<br>";

        // 2. MEMBUAT TRANSAKSI ACAK UNTUK USER TERSEBUT
        $num_transactions = mt_rand(5, 15);
        for ($j = 1; $j <= $num_transactions; $j++) {
            $is_pemasukan = mt_rand(1, 10) > 3; // 70% kemungkinan pemasukan

            if ($is_pemasukan) {
                $tipe = 'pemasukan';
                $jumlah = mt_rand(50000, 2000000);
                $deskripsi_options = ['Setoran tunai', 'Gaji bulanan', 'Transfer masuk'];
                $deskripsi = $deskripsi_options[array_rand($deskripsi_options)];
            } else {
                $tipe = 'pengeluaran';
                $jumlah = mt_rand(10000, 500000);
                $deskripsi_options = ['Pembayaran listrik', 'Belanja online', 'Tarik tunai ATM', 'Makan siang'];
                $deskripsi = $deskripsi_options[array_rand($deskripsi_options)];
            }

            $trx_sql = "INSERT INTO transactions (user_id, tipe, jumlah, deskripsi, tanggal_transaksi) VALUES (?, ?, ?, ?, ?)";
            $stmt_trx = $pdo->prepare($trx_sql);
            $stmt_trx->execute([
                $new_user_id,
                $tipe,
                $jumlah,
                $deskripsi,
                date('Y-m-d H:i:s', mt_rand(strtotime('-1 year'), time()))
            ]);
        }
        ob_flush();
        flush();
    }

    // Selesaikan transaksi database
    $pdo->commit();

    echo "<h2>SELESAI!</h2>";
    echo "<p>100 data nasabah dummy beserta transaksinya berhasil dibuat.</p>";
    echo "<p><strong>PENTING:</strong> Hapus file ini (`generate_dummy_data.php`) dari server Anda setelah selesai digunakan.</p>";

} catch (Exception $e) {
    // Batalkan transaksi jika terjadi error
    $pdo->rollBack();
    die("Terjadi error: " . $e->getMessage());
}