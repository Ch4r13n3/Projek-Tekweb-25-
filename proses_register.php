// proses_register.php (setelah perbaikan)
<?php
session_start();
require 'koneksi.php'; 

// 1. Ambil dan Sanitasi Input
$nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
$username     = trim($_POST['username'] ?? '');
// [PERBAIKAN]: Ambil data email dan no_telp dari form
$email        = trim($_POST['email'] ?? '');     
$no_telp      = trim($_POST['no_telp'] ?? '');
$password     = $_POST['password'] ?? ''; 

// [KRITIS]: Role harus di hardcode 'user'
$role         = 'customer'; 

// 2. Validasi Input di Sisi Server
if (empty($nama_lengkap) || empty($email) || empty($username) || empty($password)) {
    $_SESSION['register_error'] = "Semua kolom wajib (kecuali Nomor Telepon) harus diisi.";
    header("Location: register.php");
    exit;
}
// Tambahkan validasi Email dasar
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
    $_SESSION['register_error'] = "Username atau Email sudah terdaftar. Gunakan yang lain.";
    $stmt_check->close();
    $conn->close();
    header("Location: register.php");
    exit;
}
$stmt_check->close();

// 4. Hashing Password (KEAMANAN WAJIB!)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 5. Masukkan Data ke Database
$stmt_insert = $conn->prepare(
    "INSERT INTO users (nama_lengkap, username, password, email, no_telp, role) 
     VALUES (?, ?, ?, ?, ?, ?)"
);
// Binding parameter: ssssss = 6 string
$stmt_insert->bind_param("ssssss", $nama_lengkap, $username, $hashed_password, $email, $no_telp, $role);

if ($stmt_insert->execute()) {
    $_SESSION['login_success'] = "Akun berhasil didaftarkan! Silakan **Login**.";
    $stmt_insert->close();
    $conn->close();
    header("Location: login.php");
    exit;
} else {
    $db_error = $stmt_insert->error; 
    $_SESSION['register_error'] = "Pendaftaran gagal karena masalah database. Pesan error DB: **" . htmlspecialchars($db_error) . "**"; 
    $stmt_insert->close();
    $conn->close();
    header("Location: register.php");
    exit;
}
?>