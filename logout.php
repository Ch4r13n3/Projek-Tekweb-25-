<?php
session_start(); // Mulai sesi untuk bisa mengaksesnya

// Hapus semua variabel sesi
$_SESSION = array();

// Hancurkan sesi
session_destroy();

// Alihkan ke halaman utama (index.html atau login.php)
header("Location: index.php");
exit;
?>