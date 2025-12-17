<!-- daftar_kelola_tipekamar.php -->
<?php
session_start();
require '../koneksi.php';

// Penjaga (Guard)
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// == HELPER FUNCTION: Menampilkan Flash Message dan membersihkannya
// =================================================================
function showFlashMessage() {
    if (isset($_SESSION['flash_message']) && isset($_SESSION['flash_type'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type']; // 'success' atau 'error'
        $color = ($type == 'success') ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
        
        echo '<div id="flash-message" class="' . $color . ' border px-4 py-3 rounded-md mb-6 transition-opacity duration-300 fade-in" role="alert">';
        echo '<p class="font-bold">' . ucfirst($type) . '</p>';
        echo '<p class="text-sm">' . htmlspecialchars($message) . '</p>';
        echo '</div>';

        // Hapus flash message setelah ditampilkan
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}
// =================================================================
// == BAGIAN "DAPUR" (LOGIKA PROSES) ==
// =================================================================

$action = $_GET['aksi'] ?? null;
$method = $_SERVER['REQUEST_METHOD'];
$redirect_to = 'daftar_kelola_tipekamar.php';

// Logika TAMBAH DATA
if ($method == 'POST' && $action == 'tambah') {
    // 1. Sanitasi Input POST
    $nama_tipe          = htmlspecialchars($_POST['nama_tipe'] ?? '');
    $harga_per_malam    = (int)($_POST['harga_per_malam'] ?? 0);
    $kapasitas          = (int)($_POST['kapasitas'] ?? 0);
    $kategori_hunian    = htmlspecialchars($_POST['kategori_hunian'] ?? '');
    $tingkat_fasilitas  = htmlspecialchars($_POST['tingkat_fasilitas'] ?? '');
    
    $input_bed          = $_POST['jenis_tempat_tidur'] ?? [];
    // Pastikan nilai di array di-sanitasi sebelum di-implode
    $sanitized_bed      = array_map('htmlspecialchars', $input_bed);
    $jenis_tempat_tidur = implode(", ", $sanitized_bed); 
    
    $luas_kamar         = htmlspecialchars($_POST['luas_kamar'] ?? '');
    $deskripsi          = htmlspecialchars($_POST['deskripsi'] ?? '');
    $add_on             = htmlspecialchars($_POST['add_on'] ?? '');

    $nama_foto = "default.jpg"; 
    $upload_success = true;
    $target_dir = "../uploads/"; 

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $file_name = $_FILES["foto"]["name"];
        $file_tmp = $_FILES["foto"]["tmp_name"];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $max_size = 5 * 1024 * 1024; // 5 MB
        if ($_FILES['foto']['size'] > $max_size) {
            $_SESSION['flash_message'] = 'Gagal: Ukuran file foto maksimal 5MB.';
            $_SESSION['flash_type'] = 'error';
            $upload_success = false;
        }

        // Cek ekstensi gambar yang diizinkan
        $allowed_ext = ['jpg', 'jpeg', 'webp','png'];
        if (in_array($ext, $allowed_ext)) {
            $nama_foto = "tipe_kamar_" . time() . "_" . uniqid() . "." . $ext; 
            $target_file = $target_dir . $nama_foto;

            // Pastikan direktori 'uploads' ada
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            if (!move_uploaded_file($file_tmp, $target_file)) {
                $_SESSION['flash_message'] = 'Gagal mengunggah file foto. Pastikan folder uploads memiliki izin tulis.';
                $_SESSION['flash_type'] = 'error';
                $upload_success = false;
            }
        } else {
            $_SESSION['flash_message'] = 'Format file foto tidak diizinkan. Hanya .jpg, .jpeg, atau .webp yang diperbolehkan.';
            $_SESSION['flash_type'] = 'error';
            $upload_success = false;
        }
    }

    if ($upload_success) {
        // Menggunakan Prepared Statement untuk INSERT
        $query_insert = "INSERT INTO tipe_kamar 
                            (nama_tipe, harga_per_malam, kapasitas, foto, deskripsi, add_on, 
                             kategori_hunian, tingkat_fasilitas, jenis_tempat_tidur, luas_kamar) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_insert = $conn->prepare($query_insert);
        if ($stmt_insert === false) {
             $_SESSION['flash_message'] = 'Error prepare: ' . $conn->error;
             $_SESSION['flash_type'] = 'error';
             header("Location: $redirect_to"); exit;
        }

        $stmt_insert->bind_param("siisssssss", 
            $nama_tipe, $harga_per_malam, $kapasitas, $nama_foto, $deskripsi, 
            $add_on, $kategori_hunian, $tingkat_fasilitas, $jenis_tempat_tidur, $luas_kamar
        );
        
        if ($stmt_insert->execute()) {
            $_SESSION['flash_message'] = 'Data Tipe Kamar berhasil ditambahkan!';
            $_SESSION['flash_type'] = 'success';
        } else {
            // Jika ada error SQL, hapus file yang sudah terlanjur diupload (kecuali default.jpg)
            if ($nama_foto != 'default.jpg' && file_exists($target_dir . $nama_foto)) {
                unlink($target_dir . $nama_foto);
            }
            $_SESSION['flash_message'] = 'Gagal menambahkan data. Error: ' . $stmt_insert->error;
            $_SESSION['flash_type'] = 'error';
        }
        $stmt_insert->close();
    }
    header("Location: $redirect_to");
    exit;
}

// Logika HAPUS
if ($action == 'hapus') {
   $id_tipe = (int)($_GET['id'] ?? 0);

    // ==================================================
    // 💡 LANGKAH 1: CEK INTEGRITAS REFERENSIAL (NEW!)
    // ==================================================
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM kamar WHERE id_tipe_kamar = ?");
    $stmt_check->bind_param("i", $id_tipe);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row_check = $result_check->fetch_row();
    $count_kamar = $row_check[0];
    $stmt_check->close();

    // Jika ada kamar yang menggunakan Tipe Kamar ini, batalkan hapus.
    if ($count_kamar > 0) {
        $_SESSION['flash_message'] = "Gagal menghapus! Tipe kamar ini masih digunakan oleh **$count_kamar unit kamar**. Harap hapus unit kamar tersebut terlebih dahulu.";
        $_SESSION['flash_type'] = 'error';
        header("Location: $redirect_to"); 
        exit;
    }

    // 2. Ambil nama file foto
    $stmt_select = $conn->prepare("SELECT foto FROM tipe_kamar WHERE id_tipe_kamar = ?");
    $stmt_select->bind_param("i", $id_tipe);
    $stmt_select->execute();
    $result_foto = $stmt_select->get_result();
    $row_foto = $result_foto->fetch_assoc();
    $stmt_select->close();
    
    // 3. Hapus file foto (jika bukan 'default.jpg')
    if ($row_foto) {
        $file_path = "../uploads/" . $row_foto['foto'];
        // Hapus hanya jika file ada dan bukan 'default.jpg'
        if (file_exists($file_path) && $row_foto['foto'] != 'default.jpg') {
            unlink($file_path);
        }
    }

    // 4. Hapus data dari database
    $stmt_delete = $conn->prepare("DELETE FROM tipe_kamar WHERE id_tipe_kamar = ?");
    $stmt_delete->bind_param("i", $id_tipe);
    
    if ($stmt_delete->execute()) {
        $_SESSION['flash_message'] = "Tipe kamar dan foto terkait berhasil dihapus!";
        $_SESSION['flash_type'] = 'success';
    } else {
        $_SESSION['flash_message'] = "Gagal menghapus data. Error: " . $stmt_delete->error;
        $_SESSION['flash_type'] = 'error';
    }
    
    $stmt_delete->close();

    // Redirect ke halaman daftar
    header("Location: $redirect_to");
    exit;
}

// AMBIL DATA
$query = "SELECT * FROM tipe_kamar ORDER BY id_tipe_kamar DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola Tipe Kamar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }
        .tab-active { background-color: #2563eb; color: white; border-color: #2563eb; }
        .tab-inactive { background-color: white; color: #4b5563; border-color: #e5e7eb; }
        .tab-inactive:hover { background-color: #f3f4f6; }
        /* Style untuk animasi fade-in */
        .fade-in {
            animation: fadeIn ease 0.5s;
            -webkit-animation: fadeIn ease 0.5s;
            -moz-animation: fadeIn ease 0.5s;
            -o-animation: fadeIn ease 0.5s;
            -ms-animation: fadeIn ease 0.5s;
        }
        @keyframes fadeIn {
            0% {opacity:0;}
            100% {opacity:1;}
        }
        @-moz-keyframes fadeIn {
            0% {opacity:0;}
            100% {opacity:1;}
        }
        @-webkit-keyframes fadeIn {
            0% {opacity:0;}
            100% {opacity:1;}
        }
        @-o-keyframes fadeIn {
            0% {opacity:0;}
            100% {opacity:1;}
        }
        @-ms-keyframes fadeIn {
            0% {opacity:0;}
            100% {opacity:1;}
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="flex h-screen overflow-hidden">
        
        <nav class="w-64 bg-gray-900 text-white flex flex-col h-full shadow-xl">
            <div class="p-6">
                <h2 class="text-3xl font-bold tracking-wider text-blue-400">Admin CNI</h2>
            </div>
            <ul class="flex-1 px-4 space-y-2 overflow-y-auto">
                <li><a href="dashboard_admin.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">🏠</span>Dashboard</a></li>
                <li><a href="daftar_kelola_tipekamar.php" class="flex items-center p-3 bg-blue-600 rounded-lg shadow-md"><span class="mr-3 text-xl">🛏️</span>Tipe Kamar</a></li>
                <li><a href="daftar_kelola_kamar.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">✨</span>Kelola Kamar</a></li>
                <li><a href="daftar_kelola_dataCustomer.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">👥</span>Data Customer</a></li>
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
                    <h1 class="text-3xl font-bold text-gray-900">Kelola Tipe Kamar</h1>
                    <p class="text-gray-500 mt-1">Tambah, edit, dan atur spesifikasi kamar hotel.</p>
                </div>
            </div>
            
            <?php showFlashMessage(); ?>
            <div class="flex space-x-2 mb-6 border-b border-gray-200 pb-1">
                <button onclick="switchTab('list')" id="btnList" class="px-6 py-2.5 rounded-t-lg font-medium text-sm transition-all duration-200 border-t border-l border-r tab-active flex items-center">
                    <span class="mr-2">📋</span> Daftar Data
                </button>
                <button onclick="switchTab('form')" id="btnForm" class="px-6 py-2.5 rounded-t-lg font-medium text-sm transition-all duration-200 border-t border-l border-r tab-inactive flex items-center">
                    <span class="mr-2">➕</span> Tambah Data Baru
                </button>
            </div>
            <div id="sectionForm" class="hidden bg-white rounded-b-xl rounded-r-xl shadow-lg border border-gray-100 overflow-hidden fade-in">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-800">Formulir Tambah Data Baru</h2>
                </div>    
                <form action="daftar_kelola_tipekamar.php?aksi=tambah" method="POST" enctype="multipart/form-data" class="p-6 md:p-8">
                    
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">1. Informasi Dasar</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Tipe Kamar</label>
                                <input type="text" name="nama_tipe" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Cth: Deluxe Room" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Harga per Malam (Rp)</label>
                                <input type="number" name="harga_per_malam" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Cth: 750000" min="0" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">2. Spesifikasi Fisik</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kategori Hunian</label>
                                <select id="kategori_hunian" name="kategori_hunian" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white" onchange="updateSpesifikasi()" required>
                                    <option value="" disabled selected>-- Pilih Kategori --</option>
                                    <option value="Single">Single Room (1 Tamu)</option>
                                    <option value="Double">Double Room (2 Tamu)</option>
                                    <option value="Family">Family Room</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kapasitas (Orang)</label>
                                <input type="number" id="kapasitas" name="kapasitas" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100" readonly required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Luas Kamar</label>
                                <input type="text" id="luas_kamar" name="luas_kamar" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100" readonly>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Jenis Tempat Tidur</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <label class="flex items-center space-x-3 cursor-pointer hover:bg-white p-2 rounded transition border border-transparent hover:border-gray-200">
                                    <input type="checkbox" name="jenis_tempat_tidur[]" value="Single Bed" class="w-4 h-4 text-blue-600 rounded">
                                    <span class="text-sm text-gray-700">Single Bed</span>
                                </label>
                                <label class="flex items-center space-x-3 cursor-pointer hover:bg-white p-2 rounded transition border border-transparent hover:border-gray-200">
                                    <input type="checkbox" name="jenis_tempat_tidur[]" value="Double Bed" class="w-4 h-4 text-blue-600 rounded">
                                    <span class="text-sm text-gray-700">Double Bed</span>
                                </label>
                                <label class="flex items-center space-x-3 cursor-pointer hover:bg-white p-2 rounded transition border border-transparent hover:border-gray-200">
                                    <input type="checkbox" name="jenis_tempat_tidur[]" value="Queen Size Bed" class="w-4 h-4 text-blue-600 rounded">
                                    <span class="text-sm text-gray-700">Queen Size</span>
                                </label>
                                <label class="flex items-center space-x-3 cursor-pointer hover:bg-white p-2 rounded transition border border-transparent hover:border-gray-200">
                                    <input type="checkbox" name="jenis_tempat_tidur[]" value="King Size Bed" class="w-4 h-4 text-blue-600 rounded">
                                    <span class="text-sm text-gray-700">King Size</span>
                                </label>
                                <label class="flex items-center space-x-3 cursor-pointer hover:bg-white p-2 rounded transition border border-transparent hover:border-gray-200">
                                    <input type="checkbox" name="jenis_tempat_tidur[]" value="Twin Bed" class="w-4 h-4 text-blue-600 rounded">
                                    <span class="text-sm text-gray-700">Twin Bed</span>
                                </label>
                                <label class="flex items-center space-x-3 cursor-pointer hover:bg-white p-2 rounded transition border border-transparent hover:border-gray-200">
                                    <input type="checkbox" name="jenis_tempat_tidur[]" value="Bunk Bed" class="w-4 h-4 text-blue-600 rounded">
                                    <span class="text-sm text-gray-700">Bunk Bed</span>
                                </label>
                                <label class="flex items-center space-x-3 cursor-pointer hover:bg-white p-2 rounded transition border border-transparent hover:border-gray-200">
                                    <input type="checkbox" name="jenis_tempat_tidur[]" value="Extra Bed" class="w-4 h-4 text-blue-600 rounded">
                                    <span class="text-sm text-gray-700">Extra Bed</span>
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 mt-2 ml-1">*Centang semua kasur yang tersedia di kamar ini.</p>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">3. Fasilitas & Media</h3>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tingkat Fasilitas</label>
                            <select id="tingkat_fasilitas" name="tingkat_fasilitas" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white" onchange="updateDeskripsi()" required>
                                <option value="" disabled selected>-- Pilih Fasilitas --</option>
                                <option value="Standard">Standard</option>
                                <option value="Superior">Superior</option>
                                <option value="Deluxe">Deluxe</option>
                                <option value="Family Room">Family Room</option>
                                <option value="Smoking Room">Smoking Room</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1 ml-1">*Pemilihan ini akan mengisi deskripsi otomatis.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi (Otomatis)</label>
                                <textarea id="deskripsi" name="deskripsi" class="w-full px-4 py-2 border border-gray-300 rounded-lg h-32" placeholder="Otomatis terisi..."></textarea>
                            </div>
                            <div class="flex flex-col gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Add-on</label>
                                    <input type="text" name="add_on" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="Cth: Akses Lounge, Voucher Spa">
                                    <p class="text-xs text-gray-500 mt-1 ml-1">*Pisahkan dengan koma jika lebih dari satu.</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Foto Utama (JPG, JPEG, WEBP, PNG)</label>
                                    <input type="file" name="foto" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" accept="image/jpeg, image/jpg, image/webp, image/png">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t">
                        <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 font-bold shadow-md">Simpan Data</button>
                    </div>
                </form>
            </div>

            <div id="sectionList" class="bg-white rounded-b-xl rounded-r-xl shadow-lg border border-gray-100 overflow-hidden fade-in">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-gray-800">Daftar Tipe Kamar Saat Ini</h2>
                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full font-bold">Total: <?php echo $result->num_rows; ?></span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-800 text-white uppercase tracking-wider">
                            <tr>
                                <th class="py-4 px-6">No</th>
                                <th class="py-4 px-6">Nama Tipe</th>
                                <th class="py-4 px-6">Harga</th>
                                <th class="py-4 px-6">Kapasitas</th>
                                <th class="py-4 px-6">Luas</th>
                                <th class="py-4 px-6">Fasilitas</th>
                                <th class="py-4 px-6">Bed</th>
                                <th class="py-4 px-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if ($result->num_rows > 0): ?>
                                <?php $no=1; while($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="py-4 px-6"><?php echo $no++; ?></td>
                                    <td class="py-4 px-6 font-bold"><?php echo htmlspecialchars($row['nama_tipe']); ?></td>
                                    <td class="py-4 px-6 text-green-600 font-bold">Rp <?php echo number_format($row['harga_per_malam']); ?></td>
                                    <td class="py-4 px-6"><?php echo $row['kapasitas'];?> Org</td>
                                    <td class="py-4 px-6"><?php echo htmlspecialchars($row['luas_kamar']); ?></td>
                                    <td class="py-4 px-6"><?php echo htmlspecialchars($row['tingkat_fasilitas']); ?></td>
                                    <td class="py-4 px-6"><?php echo htmlspecialchars($row['jenis_tempat_tidur']); ?></td>
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex items-center justify-center space-x-2">
                                            <a href="edit_tipe_kamar.php?id=<?php echo $row['id_tipe_kamar']; ?>" class="bg-yellow-400 hover:bg-yellow-500 text-white p-2 rounded-lg shadow-sm" title="Edit">✏️</a>
                                            <a href="daftar_kelola_tipekamar.php?aksi=hapus&id=<?php echo $row['id_tipe_kamar']; ?>" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg shadow-sm" onclick="return confirm('Yakin hapus data kamar dan file fotonya?');" title="Hapus">🗑️</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center py-4">Belum ada data.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
    // FUNGSI 1: Update Kapasitas & Luas
    function updateSpesifikasi() {
        let kategori = document.getElementById("kategori_hunian").value;
        let inputKapasitas = document.getElementById("kapasitas");
        let inputLuas = document.getElementById("luas_kamar");

        let kapasitas = 0;
        let luas = "";

        if (kategori === "Single") {
            kapasitas = 1;
            luas = "20 m²"; 
        } else if (kategori === "Double") {
            kapasitas = 2;
            luas = "32 m²";
        } else if (kategori === "Family") {
            kapasitas = 4;
            luas = "55 m²";
        } else {
             kapasitas = 0;
             luas = "";
        }

        inputKapasitas.value = kapasitas;
        inputLuas.value = luas;
        
        // Panggil juga fungsi deskripsi agar jika connecting room, catatannya langsung muncul
        updateDeskripsi();
    }

    // FUNGSI 2: Update Deskripsi
    function updateDeskripsi() {
        let kategoriHunian = document.getElementById("kategori_hunian").value;
        let fasilitas = document.getElementById("tingkat_fasilitas").value;
        let deskripsiBox = document.getElementById("deskripsi");
        let teks = "";

        switch(fasilitas){
            case "Standard":
                teks = "Fasilitas Dasar: AC, TV Kabel 32 inch, Wi-Fi gratis, Kamar mandi shower (Hot/Cold), Air mineral botol, dan Perlengkapan mandi dasar.";
                break;
            case "Superior":
                teks = "Upgrade dari Standard: Lokasi kamar dengan view lebih baik, tambahan fasilitas pembuat Kopi/Teh (Coffee Maker), dan Meja kerja compact.";
                break;
            case "Deluxe":
                teks = "Ukuran lebih luas dengan Balkon pribadi. Fasilitas mencakup: Bathtub, Kulkas mini (Minibar), Hairdryer, TV 40 inch, dan Brankas pribadi.";
                break;
            case "Family Room": 
                teks = "Kamar luas yang didesain untuk keluarga. Memiliki dua area tidur terpisah (atau King Bed + Twin Bed), kamar mandi besar dengan bathtub, dan area duduk yang nyaman.";
                break;
            case "Smoking Room":
                teks = "Kamar Khusus Merokok: Memiliki ventilasi udara khusus (Exhaust fan) atau akses langsung ke balkon terbuka. Dilengkapi asbak dan area sirkulasi udara yang baik.";
                break;
            default: 
                teks = ""; // Kosongkan jika belum dipilih
        }

        // Jika Connecting Room, tambahkan catatan (walaupun fasilitas belum dipilih/masih kosong)
        if (kategoriHunian === "Connecting Room") {
            // Jika fasilitas sudah ada isinya, tambahkan enter. Jika belum, langsung tulis catatan.
            if(teks !== "") teks += "\n\n";
            teks += "CATATAN CONNECTING: Unit ini terdiri dari 2 kamar terpisah yang memiliki pintu penghubung di tengah (Connecting Door). Anda akan mendapatkan 2 kamar mandi dan privasi ganda.";
        }

        deskripsiBox.value = teks; 
    }

    function switchTab(tabName) {
        const btnList = document.getElementById('btnList');
        const btnForm = document.getElementById('btnForm');
        const sectionList = document.getElementById('sectionList');
        const sectionForm = document.getElementById('sectionForm');

        if(tabName === 'list') {
            // Tampilkan List
            sectionList.classList.remove('hidden');
            sectionForm.classList.add('hidden');
            
            // Ubah Style Tombol
            btnList.classList.add('tab-active');
            btnList.classList.remove('tab-inactive');
            btnForm.classList.add('tab-inactive');
            btnForm.classList.remove('tab-active');
        } else {
            // Tampilkan Form
            sectionList.classList.add('hidden');
            sectionForm.classList.remove('hidden');

            // Ubah Style Tombol
            btnForm.classList.add('tab-active');
            btnForm.classList.remove('tab-inactive');
            btnList.classList.add('tab-inactive');
            btnList.classList.remove('tab-active');
        }
    }
    
    // Panggil switchTab saat halaman pertama kali dimuat untuk memastikan tampilan yang benar
    document.addEventListener('DOMContentLoaded', () => {
        // Cek apakah ada aksi 'tambah' di URL, jika ada, pindah ke form
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('aksi') === 'tambah' && window.history.length > 1) {
             // Asumsi: jika user baru submit form (ada aksi=tambah di URL tapi methodnya GET setelah redirect error/sukses), kembali ke list
             switchTab('list');
        } else {
             switchTab('list'); // Default: tampilkan list
        }
        
        // Atur agar spesifikasi terisi jika ada kategori yang dipilih saat load (untuk antisipasi error back)
        updateSpesifikasi();
    });
    </script>
</body>
</html>