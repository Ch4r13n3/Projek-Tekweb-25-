<?php
session_start();
include 'koneksi.php';

$error_message = $_SESSION['register_error'] ?? null;
if (isset($_SESSION['register_error'])) {
    unset($_SESSION['register_error']);
}

if (isset($_SESSION['loggedin'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard_admin.php");
    } elseif ($_SESSION['role'] == 'resepsionis') {
        header("Location: resepsionis/dashboard_resepsionis.php");
    } else {
        header("Location: guest/index.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun Baru - Cloud Nine In</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen py-10">
    
    <form action="proses_register.php" method="POST" class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
        <h2 class="text-3xl font-bold mb-2 text-center text-blue-600">Buat Akun Baru</h2>
        <p class="text-gray-500 text-center mb-6 text-sm">Silakan isi data diri Anda untuk mendaftar.</p>

        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 text-sm" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>

        <div class="space-y-4">
            <div>
                <label for="nama_lengkap" class="block text-gray-700 text-sm font-semibold mb-1">Nama Lengkap:</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" required 
                       placeholder="Masukkan nama lengkap"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition">
            </div>

            <div>
                <label for="username" class="block text-gray-700 text-sm font-semibold mb-1">Username:</label>
                <input type="text" id="username" name="username" required 
                       placeholder="Minimal 5 karakter"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition">
            </div>

            <div>
                <label for="email" class="block text-gray-700 text-sm font-semibold mb-1">Email:</label>
                <input type="email" id="email" name="email" required 
                       placeholder="contoh@gmail.com atau @cloudninein"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition">
                <p class="text-[10px] text-gray-400 mt-1 italic leading-tight">
                    *Gunakan email berakhiran <strong>@cloudninein</strong> untuk akun staf. 
                    (Contoh: admin.nama@... atau recep.nama@...)
                </p>
            </div>

            <div>
                <label for="password" class="block text-gray-700 text-sm font-semibold mb-1">Password:</label>
                <input type="password" id="password" name="password" required
                       placeholder="Minimal 6 karakter"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition">
            </div>

            <div>
                <label for="no_telp" class="block text-gray-700 text-sm font-semibold mb-1">Nomor Telepon (Opsional):</label>
                <input type="tel" id="no_telp" name="no_telp" 
                       placeholder="0812xxxxxx"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition">
            </div>
        </div>
        
        <div class="mt-8">
            <button type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-md w-full transition duration-300">
                Daftar Sekarang
            </button>
        </div>

        <p class="text-center mt-6 text-sm text-gray-600">
            Sudah punya akun? <a href="login.php" class="text-blue-500 font-semibold hover:underline">Login di sini</a>
        </p>
    </form>

</body>
</html>