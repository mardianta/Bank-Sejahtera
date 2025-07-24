<?php
require_once __DIR__ . '/core/init.php';

// Hapus semua variabel session
$_SESSION = array();

// Hancurkan session
session_destroy();

// Arahkan kembali ke halaman login
header('Location: login.php');
exit();