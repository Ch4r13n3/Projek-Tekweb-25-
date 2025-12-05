<?php
session_start();
require '../koneksi.php';

// 1. Penjaga (Guard)
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// 2. Cek ID Kamar di URL
if (!isset($_GET['id'])) {
    header("Location: daftar_kelola_kamar.php");
    exit;
}

$id_kamar = $_GET['id'];

// 3. PROSES UPDATE (Jika Tombol Simpan Ditekan)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nomor_kamar    = $_POST['nomor_kamar'];
    $lantai         = $_POST['lantai'];
    $id_tipe_kamar  = $_POST['id_tipe_kamar'];
    $status         = $_POST['status'];

    // Cek apakah nomor kamar bentrok dengan kamar lain? (Kecuali punya sendiri)
    $cek = $conn->query("SELECT nomor_kamar FROM kamar WHERE nomor_kamar = '$nomor_kamar' AND id_kamar != '$id_kamar'");
    if ($cek->num_rows > 0) {
        echo "<script>alert('Gagal! Nomor Kamar $nomor_kamar sudah digunakan oleh kamar lain.'); window.history.back();</script>";
        exit;
    }

    $stmt = $conn->prepare("UPDATE kamar SET nomor_kamar=?, lantai=?, id_tipe_kamar=?, status=? WHERE id_kamar=?");
    $stmt->bind_param("siisi", $nomor_kamar, $lantai, $id_tipe_kamar, $status, $id_kamar);

    if ($stmt->execute()) {
        echo "<script>alert('Data kamar berhasil diperbarui!'); window.location='daftar_kelola_kamar.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
    exit;
}

// 4. AMBIL DATA LAMA (Untuk Form)
$stmt = $conn->prepare("SELECT * FROM kamar WHERE id_kamar = ?");
$stmt->bind_param("i", $id_kamar);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    echo "Data kamar tidak ditemukan.";
    exit;
}

// Ambil Daftar Tipe Kamar (Untuk Dropdown)
$tipe_result = $conn->query("SELECT id_tipe_kamar, nama_tipe FROM tipe_kamar ORDER BY nama_tipe ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kamar - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="flex h-screen items-center justify-center">
        
        <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-200 w-full max-w-lg">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Edit Data Kamar</h2>
                <a href="daftar_kelola_kamar.php" class="text-gray-500 hover:text-gray-700 font-medium">✕ Batal</a>
            </div>

            <form action="" method="POST">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Kamar</label>
                    <input type="text" name="nomor_kamar" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($data['nomor_kamar']); ?>" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lantai</label>
                    <select name="lantai" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white" required>
                        <option value="1" <?php echo ($data['lantai'] == 1) ? 'selected' : ''; ?>>Lantai 1</option>
                        <option value="2" <?php echo ($data['lantai'] == 2) ? 'selected' : ''; ?>>Lantai 2</option>
                        <option value="3" <?php echo ($data['lantai'] == 3) ? 'selected' : ''; ?>>Lantai 3</option>
                        <option value="4" <?php echo ($data['lantai'] == 4) ? 'selected' : ''; ?>>Lantai 4</option>
                        <option value="5" <?php echo ($data['lantai'] == 5) ? 'selected' : ''; ?>>Lantai 5</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Kamar</label>
                    <select name="id_tipe_kamar" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white" required>
                        <?php while($tipe = $tipe_result->fetch_assoc()): ?>
                            <option value="<?php echo $tipe['id_tipe_kamar']; ?>" <?php echo ($data['id_tipe_kamar'] == $tipe['id_tipe_kamar']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($tipe['nama_tipe']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Saat Ini</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white font-medium text-gray-700" required>
                        <option value="Tersedia" <?php echo ($data['status'] == 'Tersedia') ? 'selected' : ''; ?>>🟢 Tersedia</option>
                        <option value="Terisi" <?php echo ($data['status'] == 'Terisi') ? 'selected' : ''; ?>>🔴 Terisi</option>
                        <option value="Kotor" <?php echo ($data['status'] == 'Kotor') ? 'selected' : ''; ?>>🟡 Kotor</option>
                        <option value="Perbaikan" <?php echo ($data['status'] == 'Perbaikan') ? 'selected' : ''; ?>>⚫ Perbaikan</option>
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