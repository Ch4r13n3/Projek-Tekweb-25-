<?php
require_once '../koneksi.php';

$id_reservasi = isset($_GET['id']) ? $conn->real_escape_string($_GET['id']) : die("ID Reservasi tidak ditemukan.");

// 1. Query Utama (Info Reservasi, Tamu, dan Kamar)
$sql_invoice = "SELECT r.*, u.nama_lengkap, u.no_telp, u.email, tk.nama_tipe, k.nomor_kamar
                 FROM reservasi r
                 JOIN users u ON r.id_user = u.id_user
                 JOIN tipe_kamar tk ON r.id_tipe_kamar = tk.id_tipe_kamar
                 LEFT JOIN kamar k ON r.id_kamar_ditempati = k.id_kamar
                 WHERE r.id_reservasi = '$id_reservasi' LIMIT 1";

$result_invoice = $conn->query($sql_invoice);
if ($result_invoice->num_rows == 0) die("Data Invoice tidak ditemukan.");
$data = $result_invoice->fetch_assoc();

// 2. Query Tambahan (Mengambil rincian Add-on jika ada)
$sql_addons = "SELECT a.nama_addon, a.harga 
                FROM detail_reservasi_addon dra
                JOIN addon a ON dra.id_addon = a.id_addon
                WHERE dra.id_reservasi = '$id_reservasi'";
$result_addons = $conn->query($sql_addons);

// Hitung Durasi
$d1 = new DateTime($data['tanggal_checkin']);
$d2 = new DateTime($data['tanggal_checkout']);
$durasi = $d1->diff($d2)->days ?: 1; // Minimal 1 hari

$format_currency = fn($amount) => "Rp " . number_format($amount, 0, ',', '.');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?php echo $data['kode_booking']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { padding: 30px; background-color: #f8f9fa; }
        .invoice-box { max-width: 850px; margin: auto; padding: 40px; border: 1px solid #ddd; background: #fff; border-top: 8px solid #0ea5e9; }
        .header-logo { color: #0ea5e9; font-weight: 800; font-size: 24px; }
        @media print { .no-print { display: none !important; } body { padding: 0; background: none; } .invoice-box { border: none; box-shadow: none; width: 100%; max-width: 100%; } }
    </style>
</head>
<body>

<div class="invoice-box shadow-sm">
    <div class="row mb-4">
        <div class="col-6">
            <div class="header-logo">CLOUD NINE INN</div>
            <p class="text-muted">Jl. Raya Hotel No. 123, Surabaya<br>Telp: (031) 555-0199</p>
        </div>
        <div class="col-6 text-end">
            <h2 class="fw-bold">INVOICE</h2>
            <p class="mb-0">#<?php echo $data['kode_booking']; ?></p>
            <p class="text-muted small">Cetak: <?php echo date('d/m/Y H:i'); ?></p>
        </div>
    </div>

    <hr>

    <div class="row mb-4">
        <div class="col-6">
            <p class="text-secondary small fw-bold mb-1">DITAGIHKAN KEPADA:</p>
            <h5 class="fw-bold mb-0 uppercase"><?php echo $data['nama_lengkap']; ?></h5>
            <p class="text-muted mb-0"><?php echo $data['no_telp']; ?></p>
            <p class="text-muted small"><?php echo $data['email']; ?></p>
        </div>
        <div class="col-6 text-end">
            <p class="text-secondary small fw-bold mb-1">STATUS PEMBAYARAN:</p>
            <h5 class="fw-bold text-success"><?php echo strtoupper($data['status_pembayaran']); ?></h5>
            <p class="text-muted small">Metode: <?php echo $data['metode_pembayaran']; ?></p>
        </div>
    </div>

    <table class="table table-striped border">
        <thead class="table-dark">
            <tr>
                <th>Layanan / Item</th>
                <th>Rincian</th>
                <th class="text-center">Kuantitas</th>
                <th class="text-end">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Kamar: <?php echo $data['nama_tipe']; ?></strong></td>
                <td class="small">
                    No. Kamar: <?php echo $data['nomor_kamar'] ?: 'Belum Diatur'; ?><br>
                    <?php echo date('d M', strtotime($data['tanggal_checkin'])); ?> - <?php echo date('d M Y', strtotime($data['tanggal_checkout'])); ?>
                </td>
                <td class="text-center"><?php echo $durasi; ?> Malam</td>
                <td class="text-end"><?php echo $format_currency($data['total_biaya_kamar']); ?></td>
            </tr>

            <?php if ($result_addons->num_rows > 0): ?>
                <?php while($addon = $result_addons->fetch_assoc()): ?>
                <tr>
                    <td>Layanan: <?php echo $addon['nama_addon']; ?></td>
                    <td class="small text-muted italic">Fasilitas Tambahan</td>
                    <td class="text-center">1</td>
                    <td class="text-end"><?php echo $format_currency($addon['harga']); ?></td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>

            <tr class="table-light">
                <td colspan="3" class="text-end fw-bold">TOTAL AKHIR</td>
                <td class="text-end fw-bold text-primary fs-5"><?php echo $format_currency($data['total_bayar']); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="mt-5 row">
        <div class="col-8">
            <p class="small text-muted"><strong>Syarat & Ketentuan:</strong><br>
            1. Invoice ini sah jika status sudah dinyatakan LUNAS.<br>
            2. Kehilangan kunci kamar dikenakan denda sesuai kebijakan hotel.</p>
        </div>
        <div class="col-4 text-center">
            <p class="small mb-5">Hormat Kami,</p>
            <br>
            <p class="fw-bold border-top pt-2">Resepsionis Hotel</p>
        </div>
    </div>

    <div class="text-center mt-4 no-print border-top pt-4">
        <button class="btn btn-primary px-4" onclick="window.print()">
            <i class="fas fa-print me-2"></i> Cetak Sekarang
        </button>
        <a href="reservasi_list.php" class="btn btn-outline-secondary px-4 ms-2">
            <i class="fas fa-arrow-left me-2"></i> Kembali
        </a>
    </div>
</div>

</body>
</html>