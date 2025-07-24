<?php
// Mulai session
session_start();

// Muat file konfigurasi database
require_once __DIR__ . '/../config/database.php';

// Muat file fungsi
require_once __DIR__ . '/functions.php';

// Atur zona waktu default
date_default_timezone_set('Asia/Jakarta');