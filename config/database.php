<?php

$host = 'localhost';
$db_name = 'dbbank';
$username = 'root';
$password = ''; // Sesuaikan dengan password database Anda

try {
    $pdo = new PDO("mysql:host={$host};dbname={$db_name}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi ke database gagal: " . $e->getMessage());
}