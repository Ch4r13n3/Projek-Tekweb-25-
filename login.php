<!-- UNTUK ALUR LOGIN STAFF -->
<!-- login.php >> proses_login.php >> dashboard_admin.php-->


<?php
// 1. BLOK PHP HARUS ADA DI PALING ATAS
session_start();

// Jika pengguna SUDAH login, langsung alihkan ke dashboard-nya
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard_admin.php");
    } elseif ($_SESSION['role'] == 'resepsionis') {
        header("Location: resepsionis/dashboard_resepsionis.php");
    } else {
        header("Location: guest/index.php");
    }
    exit; // Pastikan script berhenti setelah redirect
}

// Ambil pesan error
$error_message = $_SESSION['login_error'] ?? null;
if (isset($_SESSION['login_error'])) {
    unset($_SESSION['login_error']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Cloud Nine In</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    
    <div class="w-full max-w-sm px-4"> 

        <?php if (isset($_SESSION['login_success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 shadow-md" role="alert">
                <strong>Berhasil! </strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['login_success']); ?></span>
            </div>
            <?php unset($_SESSION['login_success']); ?>
        <?php endif; ?>

        <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'wajib_login'): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 shadow-md" role="alert">
                <strong class="font-bold">Perhatian!</strong>
                <span class="block sm:inline"> Silakan login terlebih dahulu untuk melanjutkan pemesanan kamar.</span>
            </div>
        <?php endif; ?>
        
        <form action="proses_login.php" method="POST" class="bg-white p-8 rounded-lg shadow-md w-full">
            <h2 class="text-3xl font-bold mb-6 text-center text-blue-600">Silakan Login</h2>
            
            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username:</label>
                <input type="text" id="username" name="username" required 
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            
            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
                <input type="password" id="password" name="password" required
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            
            <div class="flex items-center justify-between">
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                    Login
                </button>
            </div>

            <p class="text-center mt-4 text-sm">
                Belum punya akun? <a href="register.php" class="text-blue-500 hover:underline">Register di sini</a>
            </p>
        </form>

    </div> </body>
</html>