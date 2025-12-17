<?php
// resepsionis/index.php
session_start();
require_once '../koneksi.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'resepsionis') {
    header("Location: ../login.php");
    exit;
}

$today = date('Y-m-d');

// --- 1. QUERY STATISTIK ---

// A. Tamu yang sedang menginap (Status: Check-in)
$sql_in = "SELECT COUNT(*) as total FROM reservasi WHERE status_reservasi = 'Check-in'";
$count_in = $conn->query($sql_in)->fetch_assoc()['total'];

// B. Tamu yang Check-out HARI INI (Query Sesuai Permintaan Anda)
$sql_out = "SELECT COUNT(*) as total FROM reservasi 
            WHERE tanggal_checkout = '$today' 
            AND status_reservasi = 'Completed'";
$count_out = $conn->query($sql_out)->fetch_assoc()['total'];

// C. Kamar Tersedia
$sql_rooms = "SELECT COUNT(*) as total FROM kamar WHERE status = 'Tersedia'";
$count_available = $conn->query($sql_rooms)->fetch_assoc()['total'];

// D. Pendapatan Hari Ini (Query Sesuai Permintaan Anda)
$sql_income = "SELECT SUM(total_bayar) as total FROM reservasi 
               WHERE tanggal_checkout = '$today' 
               AND status_pembayaran = 'Lunas'";
$income_today = $conn->query($sql_income)->fetch_assoc()['total'] ?? 0;


// --- 2. QUERY AKTIVITAS TERBARU (Gabungan In & Out) ---
$sql_recent = "SELECT r.*, k.nomor_kamar 
               FROM reservasi r
               LEFT JOIN kamar k ON r.id_kamar_ditempati = k.id_kamar
               WHERE r.status_reservasi IN ('Check-in', 'Completed')
               ORDER BY r.id_reservasi DESC LIMIT 8";
$recent_activities = $conn->query($sql_recent);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Cloud Nine Inn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 text-gray-800">

    <?php include 'sidebar.php'; ?>

    <main class="ml-64 p-8">
        <header class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-black text-gray-900">Dashboard Resepsionis</h1>
                <p class="text-gray-500 font-medium">Ringkasan operasional tanggal <?= date('d F Y') ?></p>
            </div>
            <div class="text-right">
                <span class="block text-xs font-bold text-gray-400 uppercase">Pendapatan Hari Ini</span>
                <span class="text-2xl font-black text-emerald-600">Rp <?= number_format($income_today, 0, ',', '.') ?></span>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-xl mb-4">
                    <i class="fas fa-user-check"></i>
                </div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Tamu Menginap</p>
                <h3 class="text-3xl font-black"><?= $count_in ?></h3>
            </div>

            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center text-xl mb-4">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Check-out (Hari ini)</p>
                <h3 class="text-3xl font-black"><?= $count_out ?></h3>
            </div>

            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-xl mb-4">
                    <i class="fas fa-door-open"></i>
                </div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Kamar Tersedia</p>
                <h3 class="text-3xl font-black"><?= $count_available ?></h3>
            </div>

            <div class="bg-gray-900 p-6 rounded-3xl shadow-sm text-white">
                <div class="w-12 h-12 bg-gray-800 text-gray-400 rounded-2xl flex items-center justify-center text-xl mb-4">
                    <i class="fas fa-clock"></i>
                </div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Waktu Sistem</p>
                <h3 class="text-2xl font-black" id="liveClock">00:00:00</h3>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/30">
                <h2 class="text-lg font-black text-gray-900">Aktivitas Terkini (Check-in/out)</h2>
                <a href="reservasi_list.php" class="text-sky-600 font-bold text-xs hover:underline">Lihat Semua Data</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="text-gray-400 text-[10px] uppercase font-bold tracking-widest">
                        <tr>
                            <th class="p-5">Tamu / Kode</th>
                            <th class="p-5">Kamar</th>
                            <th class="p-5">Status</th>
                            <th class="p-5">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if($recent_activities->num_rows > 0): while($row = $recent_activities->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="p-5">
                                <p class="font-bold text-gray-800"><?= $row['nama_pemesan'] ?></p>
                                <p class="text-[10px] font-mono text-gray-400"><?= $row['kode_booking'] ?></p>
                            </td>
                            <td class="p-5">
                                <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-bold">Kmr <?= $row['nomor_kamar'] ?></span>
                            </td>
                            <td class="p-5">
                                <?php if($row['status_reservasi'] == 'Check-in'): ?>
                                    <span class="text-[10px] font-black uppercase px-2 py-1 rounded-full bg-blue-100 text-blue-700">Menginap</span>
                                <?php else: ?>
                                    <span class="text-[10px] font-black uppercase px-2 py-1 rounded-full bg-gray-100 text-gray-500">Selesai</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-5 text-xs font-medium text-gray-500">
                                <?= ($row['status_reservasi'] == 'Check-in') ? 'Masuk: '.date('d/m', strtotime($row['tanggal_checkin'])) : 'Keluar: '.date('d/m', strtotime($row['tanggal_checkout'])) ?>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="4" class="p-10 text-center text-gray-400 font-medium italic">Belum ada aktivitas hari ini.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        function updateClock() {
            const now = new Date();
            const time = now.getHours().toString().padStart(2, '0') + ':' + 
                         now.getMinutes().toString().padStart(2, '0') + ':' + 
                         now.getSeconds().toString().padStart(2, '0');
            document.getElementById('liveClock').textContent = time;
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>
</html>