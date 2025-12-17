<!-- register.php -->
<?php
session_start();
include 'koneksi.php';

// Ambil pesan error untuk ditampilkan
$error_message = $_SESSION['register_error'] ?? null;
if (isset($_SESSION['register_error'])) {
    unset($_SESSION['register_error']);
}

// Pastikan tidak ada pengguna yang sudah login mencoba mengakses register
if (isset($_SESSION['loggedin'])) {
    // Alihkan ke dashboard yang sesuai jika sudah login
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
    <title>Registrasi Akun Baru</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    
    <form action="proses_register.php" method="POST" class="bg-white p-8 rounded-lg shadow-md w-full max-w-sm">
        <h2 class="text-3xl font-bold mb-6 text-center text-blue-600">Buat Akun Baru</h2>
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>

        <div class="mb-4">
            <label for="nama_lengkap" class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap:</label>
            <input type="text" id="nama_lengkap" name="nama_lengkap" required 
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
        </div>

        <div class="mb-4">
            <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username:</label>
            <input type="text" id="username" name="username" required 
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
        </div>

        <div class="mb-4">
            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
            <input type="password" id="password" name="password" required
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
        </div>

        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
            <input type="email" id="email" name="email" required 
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
        </div>

        <div class="mb-4">
            <label for="no_telp" class="block text-gray-700 text-sm font-bold mb-2">Nomor Telepon (Opsional):</label>
            <input type="tel" id="no_telp" name="no_telp" 
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
        </div>
        
        <div class="flex items-center justify-between mt-6">
            <button type="submit" 
                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                Daftar
            </button>
        </div>

        <p class="text-center mt-4 text-sm">
            Sudah punya akun? <a href="login.php" class="text-blue-500 hover:underline">Login di sini</a>
        </p>
    </form>

</body>
</html>