<?php
session_start();
require '../koneksi.php';

// 1. Penjaga (Guard)
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// =================================================================
// LOGIKA PROSES (UPDATE STATUS TRANSAKSI & KAMAR)
// =================================================================

$action = $_GET['aksi'] ?? null;
$id_transaksi = $_GET['id'] ?? null;

if ($action && $id_transaksi) {
    
    // 1. Ambil ID Kamar dari transaksi ini dulu
    $stmt_cek = $conn->prepare("SELECT id_kamar FROM transaksi WHERE id_transaksi = ?");
    $stmt_cek->bind_param("i", $id_transaksi);
    $stmt_cek->execute();
    $res_cek = $stmt_cek->get_result();
    $data_tr = $res_cek->fetch_assoc();
    $id_kamar = $data_tr['id_kamar'];
    $stmt_cek->close();

    $new_status_transaksi = '';
    $new_status_kamar = '';

    // 2. Tentukan Status Baru
    if ($action == 'konfirmasi') {
        // Tamu Check-in
        $new_status_transaksi = 'Check In'; 
        $new_status_kamar = 'Terisi'; 
    } elseif ($action == 'selesai') {
        // Tamu Check-out
        $new_status_transaksi = 'Selesai'; 
        $new_status_kamar = 'Kotor'; // Kamar jadi kotor setelah tamu keluar
    } elseif ($action == 'batalkan') {
        // Booking Dibatalkan
        $new_status_transaksi = 'Dibatalkan'; 
        $new_status_kamar = 'Tersedia'; // Kamar kembali bisa dipesan
    }

    // 3. Eksekusi Update (Dua Tabel Sekaligus)
    if ($new_status_transaksi && $id_kamar) {
        // Update Transaksi
        $stmt1 = $conn->prepare("UPDATE transaksi SET status_transaksi = ? WHERE id_transaksi = ?");
        $stmt1->bind_param("si", $new_status_transaksi, $id_transaksi);
        $stmt1->execute();
        $stmt1->close();

        // Update Status Kamar (Sinkronisasi Otomatis)
        $stmt2 = $conn->prepare("UPDATE kamar SET status = ? WHERE id_kamar = ?");
        $stmt2->bind_param("si", $new_status_kamar, $id_kamar);
        $stmt2->execute();
        $stmt2->close();

        echo "<script>alert('Status berhasil diperbarui!'); window.location='daftar_kelola_transaksi.php';</script>";
        exit;
    }
}

// =================================================================
// AMBIL DATA (READ & SEARCH)
// =================================================================

$search = $_GET['cari'] ?? '';
$sql = "SELECT t.*, k.nomor_kamar, k.lantai, tk.nama_tipe 
        FROM transaksi t
        JOIN kamar k ON t.id_kamar = k.id_kamar
        JOIN tipe_kamar tk ON k.id_tipe_kamar = tk.id_tipe_kamar
        WHERE t.nama_tamu LIKE ? OR k.nomor_kamar LIKE ?
        ORDER BY t.tgl_transaksi DESC, t.id_transaksi DESC";

$stmt = $conn->prepare($sql);
$param = "%$search%";
$stmt->bind_param("ss", $param, $param);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola Transaksi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="flex h-screen overflow-hidden">
        
        <nav class="w-64 bg-gray-900 text-white flex flex-col h-full shadow-xl">
            <div class="p-6">
                <h2 class="text-3xl font-bold tracking-wider text-blue-400">Admin CNI</h2>
            </div>
            <ul class="flex-1 px-4 space-y-2 overflow-y-auto">
                <li><a href="dashboard_admin.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">🏠</span>Dashboard</a></li>
                <li><a href="daftar_kelola_tipekamar.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">🛏️</span>Tipe Kamar</a></li>
                <li><a href="daftar_kelola_kamar.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">✨</span>Kelola Kamar</a></li>
                <li><a href="daftar_kelola_dataCustomer.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">👥</span>Data Customer</a></li>
                <li><a href="daftar_kelola_transaksi.php" class="flex items-center p-3 bg-blue-600 rounded-lg shadow-md"><span class="mr-3 text-xl">💳</span>Transaksi</a></li>
                <li><a href="laporan.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">📈</span>Laporan</a></li>
                <li class="absolute bottom-6 w-52"><a href="../logout.php" class="flex items-center p-2 bg-red-600 hover:bg-red-700 rounded-lg"><span class="mr-2">🚪</span>Logout</a></li>
            </ul>
        </nav>

        <main class="flex-1 p-6 md:p-8 overflow-y-auto relative">
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Data Transaksi</h1>
                    <p class="text-gray-500 mt-1">Kelola check-in, check-out, dan pembayaran tamu.</p>
                </div>
                
                <form method="GET" class="flex">
                    <input type="text" name="cari" value="<?= htmlspecialchars($search) ?>" class="px-4 py-2 border rounded-l-lg focus:ring-blue-500 focus:outline-none" placeholder="Cari nama / no kamar...">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-r-lg hover:bg-blue-700">🔍</button>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-800 text-white uppercase tracking-wider">
                            <tr>
                                <th class="py-4 px-6">ID</th>
                                <th class="py-4 px-6">Tamu</th>
                                <th class="py-4 px-6">Kamar</th>
                                <th class="py-4 px-6">Check-In / Out</th>
                                <th class="py-4 px-6 text-right">Total Bayar</th>
                                <th class="py-4 px-6 text-center">Status</th>
                                <th class="py-4 px-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-blue-50 transition duration-150">
                                    <td class="py-4 px-6 font-mono text-gray-500">#<?= $row['id_transaksi'] ?></td>
                                    
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-gray-800"><?= htmlspecialchars($row['nama_tamu']) ?></div>
                                        <div class="text-xs text-gray-500"><?= date('d M Y', strtotime($row['tgl_transaksi'])) ?></div>
                                    </td>
                                    
                                    <td class="py-4 px-6">
                                        <span class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded text-xs font-bold">
                                            <?= $row['nomor_kamar'] ?>
                                        </span>
                                        <div class="text-xs text-gray-500 mt-1"><?= $row['nama_tipe'] ?></div>
                                    </td>

                                    <td class="py-4 px-6 text-gray-600">
                                        <div class="text-xs">In: <span class="font-medium"><?= date('d/m/Y', strtotime($row['tgl_checkin'])) ?></span></div>
                                        <div class="text-xs">Out: <span class="font-medium"><?= date('d/m/Y', strtotime($row['tgl_checkout'])) ?></span></div>
                                    </td>

                                    <td class="py-4 px-6 text-right font-bold text-green-600">
                                        Rp <?= number_format($row['total_harga'], 0, ',', '.') ?>
                                    </td>

                                    <td class="py-4 px-6 text-center">
                                        <?php 
                                            $st = strtolower($row['status_transaksi']);
                                            $badge = "bg-gray-100 text-gray-600";
                                            if($st == 'pending') $badge = "bg-yellow-100 text-yellow-800 border border-yellow-200";
                                            elseif($st == 'check in') $badge = "bg-blue-100 text-blue-800 border border-blue-200";
                                            elseif($st == 'selesai') $badge = "bg-green-100 text-green-800 border border-green-200";
                                            elseif($st == 'dibatalkan') $badge = "bg-red-100 text-red-800 border border-red-200";
                                        ?>
                                        <span class="<?= $badge ?> px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide">
                                            <?= $row['status_transaksi'] ?>
                                        </span>
                                    </td>

                                    <td class="py-4 px-6 text-center">
                                        <div class="flex items-center justify-center space-x-2">
                                            <button onclick="bukaModalDetail(
                                                '<?= $row['id_transaksi'] ?>',
                                                '<?= htmlspecialchars($row['nama_tamu']) ?>',
                                                '<?= $row['nomor_kamar'] . ' - ' . $row['nama_tipe'] ?>',
                                                '<?= date('d M Y', strtotime($row['tgl_checkin'])) ?>',
                                                '<?= date('d M Y', strtotime($row['tgl_checkout'])) ?>',
                                                'Rp <?= number_format($row['total_harga'], 0, ',', '.') ?>',
                                                '<?= $row['status_transaksi'] ?>'
                                            )" class="bg-gray-200 hover:bg-gray-300 text-gray-700 p-2 rounded-lg" title="Detail">
                                                📄
                                            </button>

                                            <?php if($st == 'pending'): ?>
                                                <a href="daftar_kelola_transaksi.php?aksi=konfirmasi&id=<?= $row['id_transaksi'] ?>" class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg shadow-sm" title="Check In" onclick="return confirm('Proses Check-In untuk tamu ini?')">✅</a>
                                                <a href="daftar_kelola_transaksi.php?aksi=batalkan&id=<?= $row['id_transaksi'] ?>" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg shadow-sm" title="Batalkan" onclick="return confirm('Batalkan pesanan?')">❌</a>
                                            
                                            <?php elseif($st == 'check in'): ?>
                                                <a href="daftar_kelola_transaksi.php?aksi=selesai&id=<?= $row['id_transaksi'] ?>" class="bg-green-500 hover:bg-green-600 text-white p-2 rounded-lg shadow-sm" title="Check Out" onclick="return confirm('Proses Check-Out? Kamar akan diset menjadi Kotor.')">👋</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center py-10 text-gray-500">Tidak ada data transaksi.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="modalDetail" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden z-50 flex justify-center items-center backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 transform scale-100">
            <div class="flex justify-between items-center bg-gray-50 px-6 py-4 border-b rounded-t-xl">
                <h3 class="text-lg font-bold text-gray-800">📄 Detail Transaksi</h3>
                <button onclick="tutupModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase">ID Transaksi</p>
                        <p class="font-mono font-bold" id="d_id"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Status</p>
                        <p class="font-bold" id="d_status"></p>
                    </div>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Nama Tamu</p>
                    <p class="text-lg font-semibold text-gray-800" id="d_nama"></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Kamar</p>
                    <p class="font-medium" id="d_kamar"></p>
                </div>
                <div class="grid grid-cols-2 gap-4 bg-gray-50 p-3 rounded-lg border">
                    <div>
                        <p class="text-xs text-gray-500">Check In</p>
                        <p class="font-bold text-blue-600" id="d_in"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Check Out</p>
                        <p class="font-bold text-red-600" id="d_out"></p>
                    </div>
                </div>
                <div class="pt-2 border-t flex justify-between items-center">
                    <span class="text-gray-600">Total Biaya:</span>
                    <span class="text-2xl font-bold text-green-600" id="d_harga"></span>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 rounded-b-xl flex justify-end">
                <button onclick="tutupModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        function bukaModalDetail(id, nama, kamar, inDate, outDate, harga, status) {
            document.getElementById('d_id').innerText = '#' + id;
            document.getElementById('d_nama').innerText = nama;
            document.getElementById('d_kamar').innerText = kamar;
            document.getElementById('d_in').innerText = inDate;
            document.getElementById('d_out').innerText = outDate;
            document.getElementById('d_harga').innerText = harga;
            document.getElementById('d_status').innerText = status;
            
            document.getElementById('modalDetail').classList.remove('hidden');
        }

        function tutupModal() {
            document.getElementById('modalDetail').classList.add('hidden');
        }

        window.onclick = function(event) {
            let modal = document.getElementById('modalDetail');
            if (event.target == modal) {
                tutupModal();
            }
        }
    </script>
</body>
</html>