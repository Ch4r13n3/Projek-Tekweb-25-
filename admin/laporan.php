<?php
session_start();
require '../koneksi.php';

// 1. Penjaga (Guard) - Wajib ada
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// =================================================================
// LOGIKA AMBIL DATA LAPORAN
// =================================================================

// Definisikan tanggal awal dan akhir (default: bulan ini)
$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-t'); // 't' = Last day of the month

$laporan_data = [];
$total_pendapatan = 0;

// Logika hanya berjalan jika tabel transaksi ada dan koneksi berhasil
if (isset($conn) && $conn->connect_error) {
    // Reconnect jika koneksi terputus
    require '../koneksi.php';
}

$cek_tabel = $conn->query("SHOW TABLES LIKE 'transaksi'");
if($cek_tabel->num_rows > 0) {
    // Ambil transaksi yang statusnya Selesai atau Check In (karena uang sudah diterima/terkonfirmasi)
    $sql_laporan = "SELECT 
                        t.*, 
                        k.nomor_kamar, 
                        tk.nama_tipe, 
                        u.nama_lengkap AS nama_tamu
                    FROM transaksi t
                    JOIN kamar k ON t.id_kamar = k.id_kamar
                    JOIN tipe_kamar tk ON k.id_tipe_kamar = tk.id_tipe_kamar
                    JOIN users u ON t.id_user = u.id_user
                    WHERE (t.status_transaksi = 'Selesai' OR t.status_transaksi = 'Check In')
                    AND t.tgl_transaksi BETWEEN ? AND ?
                    ORDER BY t.tgl_transaksi ASC";

    $stmt = $conn->prepare($sql_laporan);
    $stmt->bind_param("ss", $tgl_mulai, $tgl_akhir);
    $stmt->execute();
    $result_laporan = $stmt->get_result();

    while ($row = $result_laporan->fetch_assoc()) {
        $laporan_data[] = $row;
        $total_pendapatan += $row['total_harga'];
    }
    $stmt->close();
}

// Tutup koneksi setelah semua operasi database selesai
if (isset($conn)) {
    $conn->close();
}

// Fungsi untuk mendapatkan badge warna status
function get_status_badge($status) {
    $st = strtolower($status);
    $badge = "bg-gray-100 text-gray-600";
    if($st == 'check in') $badge = "bg-blue-100 text-blue-800 border border-blue-200";
    elseif($st == 'selesai') $badge = "bg-green-100 text-green-800 border border-green-200";
    return "<span class='$badge px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide'>$status</span>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola Laporan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Style untuk Print */
        @media print {
            .no-print { display: none; }
            .print-area { width: 100%; margin: 0; padding: 0; }
            table { font-size: 10px; }
            h1 { font-size: 20px; }
            /* Memaksa warna agar terlihat di cetakan hitam putih */
            .bg-green-100 { background-color: #d1fae5 !important; -webkit-print-color-adjust: exact; } 
            .bg-blue-600 { background-color: #2563eb !important; -webkit-print-color-adjust: exact; } 
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="flex h-screen overflow-hidden">
        
        <nav class="w-64 bg-gray-900 text-white flex flex-col h-full shadow-xl no-print">
            <div class="p-6">
                <h2 class="text-3xl font-bold tracking-wider text-blue-400">Admin CNI</h2>
            </div>
            <ul class="flex-1 px-4 space-y-2 overflow-y-auto">
                <li><a href="dashboard_admin.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">🏠</span>Dashboard</a></li>
                <li><a href="daftar_kelola_tipekamar.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">🛏️</span>Tipe Kamar</a></li>
                <li><a href="daftar_kelola_kamar.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">✨</span>Kelola Kamar</a></li>
                <li><a href="daftar_kelola_dataCustomer.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">👥</span>Data Customer</a></li>
                <li><a href="daftar_kelola_transaksi.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">💳</span>Transaksi</a></li>
                <li><a href="laporan.php" class="flex items-center p-3 bg-blue-600 rounded-lg shadow-md"><span class="mr-3 text-xl">📈</span>Laporan</a></li>
            </ul>
             <div class="p-4 border-t border-gray-800">
                <a href="../logout.php" class="flex items-center justify-center p-3 bg-red-600 hover:bg-red-700 rounded-lg transition-colors font-semibold shadow-lg">
                    <span class="mr-2">🚪</span> Logout
                </a>
            </div>
        </nav>

        <main class="flex-1 p-6 md:p-8 overflow-y-auto print-area">
            <div class="no-print">
                <h1 class="text-3xl font-bold mb-2 text-gray-900">Laporan Pendapatan</h1>
                <p class="text-gray-500 mb-6">Filter transaksi untuk membuat laporan keuangan.</p>

                <form method="GET" class="bg-white p-6 rounded-xl shadow-md border border-gray-100 mb-8 flex items-end space-x-4">
                    <div>
                        <label for="tgl_mulai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai:</label>
                        <input type="date" id="tgl_mulai" name="tgl_mulai" value="<?= htmlspecialchars($tgl_mulai) ?>" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="tgl_akhir" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir:</label>
                        <input type="date" id="tgl_akhir" name="tgl_akhir" value="<?= htmlspecialchars($tgl_akhir) ?>" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">Tampilkan Laporan</button>
                    <button type="button" onclick="window.print()" class="bg-gray-400 text-white px-4 py-2 rounded-lg hover:bg-gray-500 transition ml-auto">🖨️ Cetak</button>
                </form>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Ringkasan Laporan</h2>
                <div class="flex justify-between items-center border-b pb-2 mb-4">
                    <p class="text-md text-gray-600">Periode:</p>
                    <p class="text-md font-semibold"><?= date('d M Y', strtotime($tgl_mulai)) ?> s/d <?= date('d M Y', strtotime($tgl_akhir)) ?></p>
                </div>
                
                <div class="flex justify-between items-center bg-green-100 p-4 rounded-lg shadow-inner mb-6 border border-green-200">
                    <p class="text-lg font-bold text-green-700">TOTAL PENDAPATAN BERSIH</p>
                    <p class="text-3xl font-extrabold text-green-800">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></p>
                </div>

                <h3 class="text-lg font-bold text-gray-800 mt-8 mb-4">Detail Transaksi (Total: <?= count($laporan_data) ?>)</h3>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left border-collapse">
                        <thead class="bg-gray-800 text-white uppercase tracking-wider">
                            <tr>
                                <th class="py-2 px-3">Tanggal</th>
                                <th class="py-2 px-4">ID Booking</th>
                                <th class="py-2 px-4">Tamu & Kamar</th>
                                <th class="py-2 px-4 text-right">Durasi</th>
                                <th class="py-2 px-4 text-right">Harga Total</th>
                                <th class="py-2 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (!empty($laporan_data)): ?>
                                <?php foreach($laporan_data as $row): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-2 px-3 text-gray-600"><?= date('d/m/Y', strtotime($row['tgl_transaksi'])) ?></td>
                                        <td class="py-2 px-4 font-mono text-xs text-gray-600">#<?= $row['kode_booking'] ?></td>
                                        <td class="py-2 px-4">
                                            <div class="font-medium"><?= htmlspecialchars($row['nama_tamu']) ?></div>
                                            <div class="text-xs text-indigo-600 font-bold"><?= $row['nomor_kamar'] ?> (<?= $row['nama_tipe'] ?>)</div>
                                        </td>
                                        <td class="py-2 px-4 text-right">
                                            <?php
                                                $checkin = new DateTime($row['tgl_checkin']);
                                                $checkout = new DateTime($row['tgl_checkout']);
                                                $interval = $checkin->diff($checkout);
                                                echo $interval->days . " Hari";
                                            ?>
                                        </td>
                                        <td class="py-2 px-4 text-right font-bold text-green-700">
                                            Rp <?= number_format($row['total_harga'], 0, ',', '.') ?>
                                        </td>
                                        <td class="py-2 px-4 text-center">
                                            <?= get_status_badge($row['status_transaksi']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center py-8 text-gray-500">Tidak ada transaksi yang tercatat dalam periode ini.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</body>
</html>