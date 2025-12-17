<!-- daftar_kelola_dataCustomer.php -->
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
$query = "SELECT id_user, nama_lengkap, username, no_ktp, email, no_telp, status 
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
        
        <main class="flex-1 p-6 md:p-8 overflow-y-auto">
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Kelola Data Customer</h1>
                    <p class="text-gray-500 mt-1">Daftar lengkap pengguna yang terdaftar sebagai customer.</p>
                </div>
                </div>

            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></span>
            </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></span>
            </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-800 text-white uppercase tracking-wider">
                            <tr>
                                <th class="py-3 px-6">ID User</th>
                                <th class="py-3 px-6">Nama Lengkap</th>
                                <th class="py-3 px-6">Username</th> 
                                <th class="py-3 px-6">NIK/No. KTP</th>
                                <th class="py-3 px-6">Email</th>
                                <th class="py-3 px-6">No. Telepon</th>
                                <th class="py-3 px-6 text-center">Status</th> <th class="py-3 px-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-3 px-6 text-xs text-gray-500"><?php echo $row['id_user']; ?></td>
                                        <td class="py-3 px-6 font-medium text-gray-900"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                        <td class="py-3 px-6 text-gray-700"><?php echo htmlspecialchars($row['username']); ?></td> 
                                        <td class="py-3 px-6 text-gray-700"><?php echo htmlspecialchars($row['no_ktp']); ?></td>
                                        <td class="py-3 px-6 text-blue-600 hover:underline"><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td class="py-3 px-6 text-gray-700"><?php echo htmlspecialchars($row['no_telp']); ?></td>
                                        <td class="py-3 px-6 text-center">
                                            <?php 
                                            $status_class = ($row['status'] == 'active') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                            $status_text = ($row['status'] == 'active') ? 'Aktif' : 'Nonaktif';
                                            ?>
                                            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full <?php echo $status_class; ?>">
                                                <?php echo $status_text; ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-6 text-center">
                                            <?php if ($row['status'] == 'active'): ?>
                                                <a href="deaktivasi_customer.php?id=<?php echo $row['id_user']; ?>&current_status=active" 
                                                   onclick="return confirm('Anda yakin ingin menonaktifkan akun <?php echo htmlspecialchars($row['nama_lengkap']); ?>?')"
                                                   class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg text-xs font-bold shadow-sm transition duration-150">
                                                   Nonaktifkan
                                                </a>
                                            <?php else: ?>
                                                <a href="deaktivasi_customer.php?id=<?php echo $row['id_user']; ?>&current_status=inactive" 
                                                   onclick="return confirm('Anda yakin ingin mengaktifkan kembali akun <?php echo htmlspecialchars($row['nama_lengkap']); ?>?')"
                                                   class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-lg text-xs font-bold shadow-sm transition duration-150">
                                                   Aktifkan
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-500">
                                        </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table> 
                </div>
            </div>
        </main>
    
    </div> 
</body>
</html>