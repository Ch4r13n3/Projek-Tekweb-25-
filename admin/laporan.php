<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola Laporan</title>
    <script src="https://cdn.tailwindcss.com"></script>

</head>
<body class="bg-gray-50 text-gray-800">
    <div class="flex h-screen overflow-hidden">
        <nav class="w-64 bg-gray-900 text-white flex flex-col h-full shadow-xl">
            <div class="p-6">
                <h2 class="text-3xl font-bold tracking-wider text-blue-400">Admin CNI</h2>
            </div>
            <ul class="flex-1 px-4 space-y-2 overflow-y-auto">
                <li><a href="dashboard_admin.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">🏠</span>Dashboard</a></li>
                <li><a href="daftar_kelola_tipekamar.php" class="flex items-center hover:bg-gray-800 rounded-lg shadow-md"><span class="mr-3 text-xl">🛏️</span>Tipe Kamar</a></li>
                <li><a href="daftar_kelola_kamar.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">✨</span>Kelola Kamar</a></li>
                <li><a href="daftar_kelola_dataCustomer.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">👥</span>Data Customer</a></li>
                <li><a href="daftar_kelola_transaksi.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">💳</span>Transaksi</a></li>
                <li><a href="laporan.php" class="flex items-center p-3 bg-blue-600 rounded-lg transition-colors"><span class="mr-3 text-xl">📈</span>Laporan</a></li>
            </ul>
             <div class="p-4 border-t border-gray-800">
                <a href="../logout.php" class="flex items-center justify-center p-3 bg-red-600 hover:bg-red-700 rounded-lg transition-colors font-semibold shadow-lg">
                    <span class="mr-2">🚪</span> Logout
                </a>
            </div>
        </nav>
    </div>
        <main class="flex-1 p-10 overflow-auto">
            <h1 class="text-3xl font-bold mb-6">Kelola Kamar
        </main>
</body>
</html>


