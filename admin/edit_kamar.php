<?php
session_start();
require '../koneksi.php';

// 1. Penjaga (Guard)
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// 2. Cek ID Kamar di URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: daftar_kelola_kamar.php");
    exit;
}

$id_kamar = (int) $_GET['id']; // Pastikan ID di-cast ke integer

// 3. PROSES UPDATE (Jika Tombol Simpan Ditekan)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // --- KEAMANAN: Sanitasi Input POST ---
    $nomor_kamar    = htmlspecialchars(trim($_POST['nomor_kamar']));
    $lantai         = (int) $_POST['lantai'];
    $id_tipe_kamar  = (int) $_POST['id_tipe_kamar'];
    $status         = htmlspecialchars(trim($_POST['status']));

    // 🔥 PERBAIKAN: Cek Duplikasi menggunakan Prepared Statement
    $cek_stmt = $conn->prepare("SELECT nomor_kamar FROM kamar WHERE nomor_kamar = ? AND id_kamar != ?");
    $cek_stmt->bind_param("si", $nomor_kamar, $id_kamar);
    $cek_stmt->execute();
    $cek_result = $cek_stmt->get_result();

    if ($cek_result->num_rows > 0) {
        $cek_stmt->close();
        $_SESSION['flash_message'] = [
            'type' => 'error', 
            'text' => "Gagal! Nomor Kamar **$nomor_kamar** sudah digunakan oleh kamar lain."
        ];
        header("Location: edit_kamar.php?id=$id_kamar");
        exit;
    }
    $cek_stmt->close();

    // UPDATE DATA KAMAR (Sudah menggunakan Prepared Statement)
    $stmt = $conn->prepare("UPDATE kamar SET nomor_kamar=?, lantai=?, id_tipe_kamar=?, status=? WHERE id_kamar=?");
    $stmt->bind_param("siisi", $nomor_kamar, $lantai, $id_tipe_kamar, $status, $id_kamar);

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = [
            'type' => 'success', 
            'text' => "Data Kamar **$nomor_kamar** berhasil diperbarui."
        ];
        $stmt->close();
        header("Location: daftar_kelola_kamar.php");
        exit;
    } else {
        error_log("Error update kamar: " . $stmt->error);
        $_SESSION['flash_message'] = [
            'type' => 'error', 
            'text' => "Gagal memperbarui data. Error: " . $stmt->error
        ];
        $stmt->close();
        header("Location: edit_kamar.php?id=$id_kamar");
        exit;
    }
}

// 4. AMBIL DATA LAMA (Untuk Form)
$stmt = $conn->prepare("SELECT * FROM kamar WHERE id_kamar = ?");
$stmt->bind_param("i", $id_kamar);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close(); // Tutup statement SELECT

if (!$data) {
    // Tambahkan Flash Message jika data tidak ditemukan
    $_SESSION['flash_message'] = ['type' => 'error', 'text' => "Data kamar dengan ID $id_kamar tidak ditemukan."];
    header("Location: daftar_kelola_kamar.php");
    exit;
}

// Ambil Daftar Tipe Kamar (Untuk Dropdown)
$tipe_result = $conn->query("SELECT id_tipe_kamar, nama_tipe FROM tipe_kamar ORDER BY nama_tipe ASC");

// Ambil dan hapus Flash Message
$flash = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kamar - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .flash-success { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .flash-error { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

    <?php if ($flash): ?>
    <div class="fixed top-5 left-1/2 transform -translate-x-1/2 z-50 p-4 rounded-lg shadow-xl <?= 'flash-' . $flash['type'] ?> text-sm font-medium transition-all duration-300" role="alert">
        <?= $flash['text'] ?>
    </div>
    <?php endif; ?>

    <div class="flex h-screen items-center justify-center">
        
        <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-200 w-full max-w-lg">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Edit Data Kamar: #<?= htmlspecialchars($data['nomor_kamar']) ?></h2>
                <a href="daftar_kelola_kamar.php" class="text-gray-500 hover:text-gray-700 font-medium">✕ Batal</a>
            </div>

            <form action="" method="POST">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Kamar</label>
                    <input type="text" name="nomor_kamar" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" value="<?= htmlspecialchars($data['nomor_kamar']); ?>" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lantai</label>
                    <select name="lantai" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white" required>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?= $i ?>" <?= ($data['lantai'] == $i) ? 'selected' : ''; ?>>Lantai <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Kamar</label>
                    <select name="id_tipe_kamar" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white" required>
                        <?php while($tipe = $tipe_result->fetch_assoc()): ?>
                            <option value="<?= $tipe['id_tipe_kamar']; ?>" <?= ($data['id_tipe_kamar'] == $tipe['id_tipe_kamar']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($tipe['nama_tipe']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Saat Ini</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white font-medium text-gray-700" required>
                        <?php
                        $statuses = [
                            'Tersedia' => '🟢 Tersedia', 
                            'Terisi' => '🔴 Terisi', 
                            'Kotor' => '🟡 Kotor', 
                            'Perbaikan' => '⚫ Perbaikan'
                        ];
                        foreach ($statuses as $val => $label): ?>
                            <option value="<?= $val ?>" <?= ($data['status'] == $val) ? 'selected' : ''; ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 font-bold shadow-md transition transform hover:-translate-y-0.5">
                    💾 Simpan Perubahan
                </button>

            </form>
        </div>

    </div>
</body>
</html>