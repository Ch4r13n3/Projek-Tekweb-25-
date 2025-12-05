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
            <label for="role" class="block text-gray-700 text-sm font-bold mb-2">Daftar sebagai:</label>
            <select name="role" id="role" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                <option value="admin">Admin</option>
                <option value="resepsionis">Resepsionis</option>
                <option value="user">Guest</option>
            </select>
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