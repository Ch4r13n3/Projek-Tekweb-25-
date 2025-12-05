<?php
// 1. Panggil file koneksi Anda
require 'koneksi.php';

// 2. Ambil data dari form register.php
$nama_lengkap = $_POST['nama_lengkap'];
$username     = $_POST['username'];
$password     = $_POST['password']; // Ini adalah KUNCI (misal: 'abc')
$role         = $_POST['role'];
$email        = $username . '@cloudninein.com'; // Email dummy
$no_telp      = '08123456789'; // No. telp dummy

// 3. ENKRIPSI PASSWORD (Membuat GEMBOK)
// Ini adalah langkah KUNCI yang tidak bisa Anda lakukan di phpMyAdmin
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 4. Siapkan query SQL untuk memasukkan data
$stmt = $conn->prepare(
    "INSERT INTO users (nama_lengkap, username, password, email, no_telp, role) 
     VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("ssssss", $nama_lengkap, $username, $hashed_password, $email, $no_telp, $role);

// 5. Eksekusi query
if ($stmt->execute()) {
    // Jika berhasil mendaftar
    echo "Registrasi berhasil! <br>";
    echo "Anda sekarang bisa login menggunakan akun ini. <br>";
    echo "<a href='login.php'>Klik di sini untuk Login</a>";
} else {
    // Jika gagal (misal: username sudah ada)
    echo "Registrasi gagal. Username mungkin sudah terdaftar. <br>";
    echo "Error: " . $stmt->error . "<br>";
    echo "<a href='register.php'>Coba lagi</a>";
}

// 6. Tutup koneksi
$stmt->close();
$conn->close();
?>