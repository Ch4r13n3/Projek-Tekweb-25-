<?php
session_start(); // Wajib ada di setiap halaman yang butuh sesi
require '../koneksi.php';

// Penjaga (Guard)
// Cek apakah sudah login DAN rolenya 'admin'
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ambil semua data customer dari DB (READ)
// Menggunakan kolom yang ada di screenshot Anda (username, no_ktp, no_telp)
// Kolom 'created_at' dihapus karena tidak ada di tabel 'users' Anda
$query = "SELECT id_user, nama_lengkap, username, no_ktp, email, no_telp
          FROM users 
          WHERE role = 'customer' 
          ORDER BY nama_lengkap ASC";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Data Customer</title> 
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
                <li><a href="daftar_kelola_tipekamar.php" class="flex items-center p-3 hover:bg-blue-600 rounded-lg transition-colors"><span class="mr-3 text-xl">🛏️</span>Tipe Kamar</a></li>
                <li><a href="daftar_kelola_kamar.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">✨</span>Kelola Kamar</a></li>
                <li><a href="daftar_kelola_dataCustomer.php" class="flex items-center p-3 bg-blue-600 rounded-lg shadow-md"><span class="mr-3 text-xl">👥</span>Data Customer</a></li>
                <li><a href="daftar_kelola_transaksi.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">💳</span>Transaksi</a></li>
                <li><a href="laporan.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">📈</span>Laporan</a></li>
            </ul>
             <div class="p-4 border-t border-gray-800">
                <a href="../logout.php" class="flex items-center justify-center p-3 bg-red-600 hover:bg-red-700 rounded-lg transition-colors font-semibold shadow-lg">
                    <span class="mr-2">🚪</span> Logout
                </a>
            </div>
        </nav>
        
        <main class="flex-1 p-10 overflow-auto">
            <h1 class="text-3xl font-bold mb-6">Kelola Data Customer</h1>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="py-2 px-4 text-left">ID User</th>
                            <th class="py-2 px-4 text-left">Nama</th>
                            <th class="py-2 px-4 text-left">Username</th> <th class="py-2 px-4 text-left">NIK</th>
                            <th class="py-2 px-4 text-left">Email</th>
                            <th class="py-2 px-4 text-left">No. Telepon</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-gray-700">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="border-b">
                                    <td class="py-3 px-4"><?php echo $row['id_user']; ?></td>
                                    <td class="py-3 px-4"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                    <td class="py-3 px-4"><?php echo htmlspecialchars($row['username']); ?></td> <td class="py-3 px-4"><?php echo htmlspecialchars($row['no_ktp']); ?></td>
                                    <td class="py-3 px-4"><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td class="py-3 px-4"><?php echo htmlspecialchars($row['no_telp']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="py-4 px-4 text-center text-gray-500">Belum ada data customer yang terdaftar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table> 
            </div>
        </main>
    
    </div> 
</body>
</html>