<?php
// resepsionis/reservasi_list.php
session_start(); 
require_once '../koneksi.php'; 

// 1. Autentikasi Resepsionis
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'resepsionis') {
    header("Location: ../login.php");
    exit;
}

$today = date('Y-m-d');
$msg = $_SESSION['msg'] ?? '';
$alert_class = $_SESSION['alert_class'] ?? '';
unset($_SESSION['msg'], $_SESSION['alert_class']);

// --- PERBAIKAN DI SINI: Mapping Status ---
$status_param = $_GET['status'] ?? '';
$search_query = $_GET['search'] ?? '';

// Mapping slug URL ke String Database yang tepat
$status_map = [
    'pending'   => 'Pending',
    'confirmed' => 'Confirmed',
    'checkin'   => 'Check-in',   // Mengarahkan 'checkin' di URL ke 'Check-in' di DB
    'completed' => 'Completed',
    'batal'     => 'Batal'
];

// Tentukan nilai filter yang akan masuk ke SQL
$filter_status = $status_map[strtolower($status_param)] ?? $status_param;

function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Fungsi Badge Status
function get_tailwind_badge($status) {
    $text = htmlspecialchars($status);
    $base_class = "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold shadow-sm border";
    
    $map = [
        'Pending'             => 'bg-yellow-100 text-yellow-700 border-yellow-200',
        'Confirmed'           => 'bg-blue-100 text-blue-700 border-blue-200',
        'Check-in'            => 'bg-green-100 text-green-700 border-green-200',
        'Completed'           => 'bg-gray-100 text-gray-700 border-gray-200', 
        'Batal'               => 'bg-red-100 text-red-700 border-red-200',
        'Lunas'               => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        'Belum Bayar'         => 'bg-rose-100 text-rose-700 border-rose-200',
        'Menunggu Verifikasi' => 'bg-orange-100 text-orange-700 border-orange-200',
    ];

    $class = $map[$status] ?? 'bg-gray-100 text-gray-600 border-gray-200';
    return "<span class='{$base_class} {$class}'>{$text}</span>";
}

// 2. Logic Filter & Search
$where_clauses = ["1=1"]; 
$params = [];
$types = "";

if (!empty($filter_status)) {
    $where_clauses[] = "r.status_reservasi = ?";
    $params[] = $filter_status;
    $types .= "s";
}

if (!empty($search_query)) {
    $st = "%$search_query%";
    $where_clauses[] = "(r.kode_booking LIKE ? OR u.nama_lengkap LIKE ? OR r.nama_pemesan LIKE ?)";
    $params[] = $st; $params[] = $st; $params[] = $st;
    $types .= "sss";
}

$where_sql = "WHERE " . implode(" AND ", $where_clauses);

$sql_list = "SELECT r.*, u.nama_lengkap, tk.nama_tipe, k.nomor_kamar
             FROM reservasi r
             LEFT JOIN users u ON r.id_user = u.id_user
             JOIN tipe_kamar tk ON r.id_tipe_kamar = tk.id_tipe_kamar
             LEFT JOIN kamar k ON r.id_kamar_ditempati = k.id_kamar 
             $where_sql
             ORDER BY r.tanggal_checkin DESC";

$stmt = $conn->prepare($sql_list);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$reservations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Reservasi - Cloud Nine Inn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 text-gray-800">

    <?php include 'sidebar.php'; ?>

    <main class="ml-64 p-8 min-h-screen">
        <header class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">📝 Data Reservasi</h1>
                <p class="text-gray-500 font-medium">Status Filter: <span class="text-sky-600 font-bold"><?= $filter_status ?: 'Semua' ?></span></p>
            </div>
            <a href="walk_in.php" class="bg-sky-600 hover:bg-sky-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg transition-all flex items-center">
                <i class="fas fa-plus mr-2"></i> Reservasi Walk-in
            </a>
        </header>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 mb-8">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                <div class="md:col-span-2">
                    <label class="text-xs font-bold text-gray-400 uppercase mb-2 block">Cari Tamu / Kode Booking</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>" 
                               class="w-full pl-10 p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none transition" 
                               placeholder="Ketik nama atau kode booking...">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase mb-2 block">Status Reservasi</label>
                    <select name="status" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Semua Status</option>
                        <option value="pending" <?= ($status_param=='pending'?'selected':'') ?>>Pending</option>
                        <option value="confirmed" <?= ($status_param=='confirmed'?'selected':'') ?>>Confirmed</option>
                        <option value="checkin" <?= ($status_param=='checkin'?'selected':'') ?>>Check-in</option>
                        <option value="completed" <?= ($status_param=='completed'?'selected':'') ?>>Completed</option>
                        <option value="batal" <?= ($status_param=='batal'?'selected':'') ?>>Batal</option>
                    </select>
                </div>
                <button type="submit" class="bg-gray-900 hover:bg-black text-white px-6 py-3 rounded-xl font-bold transition shadow-md">
                    Cari Data
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-bold border-b">
                        <tr>
                            <th class="p-5">Kode Booking</th>
                            <th class="p-5">Tamu</th>
                            <th class="p-5">Kamar</th>
                            <th class="p-5">Jadwal</th>
                            <th class="p-5 text-center">Status</th>
                            <th class="p-5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if($reservations): foreach($reservations as $r): 
                            $is_due = ($r['tanggal_checkin'] <= $today);
                            $nama_display = $r['nama_lengkap'] ?: ($r['nama_pemesan'] ?: 'Tamu');
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="p-5 font-mono font-bold text-sky-700"><?= $r['kode_booking'] ?></td>
                            <td class="p-5">
                                <p class="font-bold text-gray-900"><?= htmlspecialchars($nama_display) ?></p>
                                <p class="text-xs text-emerald-600 font-bold"><?= formatRupiah($r['total_bayar']) ?></p>
                            </td>
                            <td class="p-5">
                                <?php if($r['nomor_kamar']): ?>
                                    <div class="flex items-center text-gray-900 font-bold italic">
                                        <i class="fas fa-door-closed mr-2 text-gray-400"></i> <?= $r['nomor_kamar'] ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-rose-500 text-[10px] font-black uppercase bg-rose-50 px-2 py-1 rounded border border-rose-100">Belum Alokasi</span>
                                <?php endif; ?>
                                <p class="text-xs text-gray-400 mt-1"><?= $r['nama_tipe'] ?></p>
                            </td>
                            <td class="p-5 text-sm">
                                <div class="flex flex-col">
                                    <span class="text-gray-700 font-bold font-mono text-xs"><?= date('d M Y', strtotime($r['tanggal_checkin'])) ?></span>
                                    <span class="text-gray-400 text-[10px] uppercase font-bold tracking-tighter">s/d <?= date('d M Y', strtotime($r['tanggal_checkout'])) ?></span>
                                </div>
                            </td>
                            <td class="p-5 text-center">
                                <div class="flex flex-col gap-1 items-center">
                                    <?= get_tailwind_badge($r['status_reservasi']) ?>
                                    <?= get_tailwind_badge($r['status_pembayaran']) ?>
                                </div>
                            </td>
                            <td class="p-5">
                                <div class="flex items-center justify-center gap-2">
                                    <?php if($r['status_reservasi'] == 'Confirmed' && $is_due): ?>
                                        <a href="checkin_process.php?kode=<?= $r['kode_booking'] ?>" 
                                           class="bg-green-600 text-white w-9 h-9 flex items-center justify-center rounded-lg hover:bg-green-700 shadow-sm" title="Check-in">
                                            <i class="fas fa-sign-in-alt text-sm"></i>
                                        </a>
                                    <?php elseif($r['status_reservasi'] == 'Check-in'): ?>
                                        <a href="checkout_process.php?kode=<?= $r['kode_booking'] ?>" 
                                           class="bg-rose-600 text-white w-9 h-9 flex items-center justify-center rounded-lg hover:bg-rose-700 shadow-sm" title="Check-out">
                                            <i class="fas fa-sign-out-alt text-sm"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="reservasi_detail.php?kode=<?= $r['kode_booking'] ?>" 
                                       class="bg-white border border-gray-200 text-gray-400 w-9 h-9 flex items-center justify-center rounded-lg hover:text-sky-600 hover:border-sky-600 transition shadow-sm" title="Detail">
                                        <i class="fas fa-eye text-sm"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td colspan="6" class="p-20 text-center">
                                <div class="flex flex-col items-center opacity-20">
                                    <i class="fas fa-inbox text-6xl mb-4 text-gray-400"></i>
                                    <p class="text-xl font-bold text-gray-500">Tidak ada data untuk filter ini</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>