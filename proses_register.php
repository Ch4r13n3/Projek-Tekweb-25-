<?php
session_start();
require 'koneksi.php'; 

// 1. Ambil dan Sanitasi Input
$nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
$username     = trim($_POST['username'] ?? '');
$email        = trim($_POST['email'] ?? '');     
$no_telp      = trim($_POST['no_telp'] ?? '');
$password     = $_POST['password'] ?? ''; 

// --- [LOGIKA ROLE BERDASARKAN EMAIL] ---
$domain_internal = "@cloudninein";

if (str_ends_with($email, $domain_internal)) {
    // Jika email mengandung @cloudninein, cek apakah admin atau resepsionis
    // Contoh logic: admin.nama@cloudninein atau recep.nama@cloudninein
    if (str_starts_with($email, 'admin')) {
        $role = 'admin';
    } else {
        $role = 'resepsionis';
    }
} else {
    // Selain domain tersebut, otomatis menjadi customer
    $role = 'customer';
}
// ----------------------------------------

// 2. Validasi Input di Sisi Server
if (empty($nama_lengkap) || empty($email) || empty($username) || empty($password)) {
    $_SESSION['register_error'] = "Semua kolom wajib (kecuali Nomor Telepon) harus diisi.";
    header("Location: register.php");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['register_error'] = "Format Email tidak valid.";
    header("Location: register.php");
    exit;
}

if (strlen($username) < 5) {
    $_SESSION['register_error'] = "Username minimal harus 5 karakter.";
    header("Location: register.php");
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['register_error'] = "Password minimal harus 6 karakter.";
    header("Location: register.php");
    exit;
}

// 3. Cek Duplikasi Username ATAU Email
$stmt_check = $conn->prepare("SELECT id_user FROM users WHERE username = ? OR email = ?");
$stmt_check->bind_param("ss", $username, $email);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    $_SESSION['register_error'] = "Username atau Email sudah terdaftar.";
    $stmt_check->close();
    header("Location: register.php");
    exit;
}
$stmt_check->close();

// 4. Hashing Password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 5. Masukkan Data ke Database
$stmt_insert = $conn->prepare(
    "INSERT INTO users (nama_lengkap, username, password, email, no_telp, role) 
     VALUES (?, ?, ?, ?, ?, ?)"
);

$stmt_insert->bind_param("ssssss", $nama_lengkap, $username, $hashed_password, $email, $no_telp, $role);

if ($stmt_insert->execute()) {
    $_SESSION['login_success'] = "Berhasil mendaftar sebagai **" . ucfirst($role) . "**. Silakan Login.";
    $stmt_insert->close();
    $conn->close();
    header("Location: login.php");
    exit;
} else {
    $_SESSION['register_error'] = "Gagal mendaftar: " . htmlspecialchars($stmt_insert->error); 
    $stmt_insert->close();
    $conn->close();
    header("Location: register.php");
    exit;
}
?>