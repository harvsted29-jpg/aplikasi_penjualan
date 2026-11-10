<?php // config/database.php 
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'penjualan'; // Inisialisasi koneksi dengan MySQLi secara Object Oriented 
$koneksi = new mysqli($host, $user, $pass, $db);

$conn = mysqli_connect($host, $user, $pass, $db);