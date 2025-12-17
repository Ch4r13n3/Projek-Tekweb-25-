<?php
session_start();
require '../koneksi.php';

// 1. Penjaga (Guard) - Memastikan hanya admin yang masuk
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// 2. Ambil ID dari URL
$id_reservasi = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id_reservasi) {
    echo "<script>alert('ID Reservasi tidak ditemukan.'); window.location='dashboard_admin.php';</script>";
    exit;
}

// 3. Query Detail Reservasi dengan JOIN ke Tipe Kamar, Kamar, dan Users
// Kita ambil data lengkap termasuk nama tamu dari tabel users atau field nama_pemesan di reservasi
$sql = "SELECT r.*, t.nama_tipe, k.nomor_kamar, u.nama_lengkap as nama_user_akun, u.email as email_user, u.no_telp as telp_user
        FROM reservasi r
        LEFT JOIN tipe_kamar t ON r.id_tipe_kamar = t.id_tipe_kamar
        LEFT JOIN kamar k ON r.id_kamar_ditempati = k.id_kamar
        LEFT JOIN users u ON r.id_user = u.id_user
        WHERE r.id_reservasi = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_reservasi);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    echo "<script>alert('Data reservasi tidak ditemukan di database.'); window.location='dashboard_admin.php';</script>";
    exit;
}

// Helper Warna Status
$status_color = [
    'Pending' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
    'Confirmed' => 'bg-green-100 text-green-700 border-green-200',
    'Checked In' => 'bg-blue-100 text-blue-700 border-blue-200',
    'Checked Out' => 'bg-gray-100 text-gray-700 border-gray-200',
    'Canceled' => 'bg-red-100 text-red-700 border-red-200'
];
$current_status_class = $status_color[$data['status_reservasi']] ?? 'bg-gray-100 text-gray-700';

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Reservasi #<?php echo $id_reservasi; ?> - Cloud Nine In</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @media print {
            .no-print { display: none; }
            .print-card { border: none; shadow: none; }
        }
    </style>
</head>
<body class="bg-gray-100 p-4 md:p-10">

    <div class="max-w-3xl mx-auto">
        <div class="mb-6 no-print flex justify-between items-center">
            <a href="dashboard_admin.php" class="text-blue-600 hover:underline flex items-center">
                ← Kembali ke Dashboard
            </a>
            <button onclick="window.print()" class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-900 transition flex items-center shadow-md">
                <span class="mr-2">🖨️</span> Cetak Detail
            </button>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200 print-card">
            
            <div class="bg-gray-900 p-6 text-white flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold text-blue-400">Cloud Nine In</h1>
                    <p class="text-gray-400 text-sm">Bukti Reservasi Kamar</p>
                </div>
                <div class="text-right">
                    <div class="text-xs uppercase tracking-widest text-gray-500 font-bold">Kode Reservasi</div>
                    <div class="text-xl font-mono font-bold text-white">#RSV-<?php echo str_pad($data['id_reservasi'], 5, '0', STR_PAD_LEFT); ?></div>
                </div>
            </div>

            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <div>
                        <h3 class="text-xs font-bold uppercase text-gray-400 mb-3 tracking-widest">Informasi Tamu</h3>
                        <p class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($data['nama_pemesan'] ?: $data['nama_user_akun']); ?></p>
                        <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($data['telp_user'] ?: '-'); ?></p>
                        <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($data['email_user'] ?: '-'); ?></p>
                    </div>

                    <div class="md:text-right">
                        <h3 class="text-xs font-bold uppercase text-gray-400 mb-3 tracking-widest">Status Reservasi</h3>
                        <span class="px-3 py-1 rounded-full text-xs font-bold border <?php echo $current_status_class; ?>">
                            <?php echo strtoupper($data['status_reservasi']); ?>
                        </span>
                        <div class="mt-4">
                            <p class="text-xs text-gray-400">Metode Pembayaran:</p>
                            <p class="font-semibold"><?php echo $data['metode_pembayaran'] ?: 'Belum ditentukan'; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 rounded-xl p-6 border border-blue-100 flex flex-wrap justify-between items-center mb-8">
                    <div class="mb-4 md:mb-0">
                        <p class="text-xs text-blue-500 font-bold uppercase">Kamar & Tipe</p>
                        <h2 class="text-xl font-bold text-gray-800">
                            No. <?php echo $data['nomor_kamar'] ?: 'Belum Diplot'; ?> 
                            <span class="text-gray-400 font-normal mx-2">|</span> 
                            <?php echo $data['nama_tipe']; ?>
                        </h2>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-blue-500 font-bold uppercase text-left md:text-right">Total Tagihan</p>
                        <p class="text-2xl font-black text-blue-700">Rp <?php echo number_format($data['total_bayar'], 0, ',', '.'); ?></p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 border-t pt-8">
                    <div class="border-r pr-4">
                        <p class="text-xs text-gray-400 font-bold uppercase mb-1">Check-In</p>
                        <p class="text-lg font-bold text-gray-800"><?php echo date('d M Y', strtotime($data['tanggal_checkin'])); ?></p>
                        <p class="text-sm text-gray-500">Mulai jam 14:00 WIB</p>
                    </div>
                    <div class="pl-4">
                        <p class="text-xs text-gray-400 font-bold uppercase mb-1">Check-Out</p>
                        <p class="text-lg font-bold text-gray-800"><?php echo date('d M Y', strtotime($data['tanggal_checkout'])); ?></p>
                        <p class="text-sm text-gray-500">Sebelum jam 12:00 WIB</p>
                    </div>
                </div>
            </div>

            <div class="p-8 bg-gray-50 border-t flex justify-between items-center italic text-xs text-gray-400">
                <p>Dicetak pada: <?php echo date('d/m/Y H:i:s'); ?></p>
                <p>Sistem Management Hotel - Cloud Nine In</p>
            </div>
        </div>

        <div class="mt-6 flex gap-3 no-print">
             <?php if ($data['status_reservasi'] == 'Pending'): ?>
                <a href="daftar_kelola_transaksi.php?aksi=konfirmasi&id=<?php echo $data['id_reservasi']; ?>" 
                   class="flex-1 bg-green-600 text-white text-center py-3 rounded-xl font-bold hover:bg-green-700 shadow-lg">
                   Konfirmasi Pembayaran
                </a>
             <?php endif; ?>
        </div>
    </div>

</body>
</html>

<?php
// Tutup koneksi di paling bawah
$conn->close();
?>