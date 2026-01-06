<?php
require_once '../config.php';
check_login_admin();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_ext, ['xls', 'xlsx', 'csv'])) {
        alert('Format file harus Excel (.xls, .xlsx) atau CSV!', 'danger');
    } else {
        $target = '../assets/uploads/' . time() . '_' . $file['name'];
        move_uploaded_file($file['tmp_name'], $target);
        
        // Parse Excel/CSV (simplified - you'd need PHPSpreadsheet library for full implementation)
        if ($file_ext == 'csv') {
            $handle = fopen($target, 'r');
            $row = 0;
            $success = 0;
            $errors = 0;
            
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $row++;
                if ($row == 1) continue; // Skip header
                
                // Expected format: nomor_peserta, nama_lengkap, email, password, jenis_kelamin, tanggal_lahir, telepon, alamat
                $nomor = mysqli_real_escape_string($conn, trim($data[0]));
                $nama = mysqli_real_escape_string($conn, trim($data[1]));
                $email = mysqli_real_escape_string($conn, trim($data[2]));
                $password = password_hash(trim($data[3]), PASSWORD_DEFAULT);
                
                // Normalisasi jenis kelamin: L, Laki-laki, laki-laki, LAKI-LAKI -> L
                $jk_raw = strtoupper(trim($data[4]));
                if (strpos($jk_raw, 'L') === 0 || strpos($jk_raw, 'M') === 0) {
                    $jk = 'L';
                } elseif (strpos($jk_raw, 'P') === 0 || strpos($jk_raw, 'F') === 0 || strpos($jk_raw, 'W') === 0) {
                    $jk = 'P';
                } else {
                    $jk = 'L'; // default
                }
                
                $tgl_lahir = mysqli_real_escape_string($conn, trim($data[5]));
                $telepon = mysqli_real_escape_string($conn, trim($data[6]));
                $alamat = mysqli_real_escape_string($conn, trim($data[7]));
                
                $query = "INSERT INTO peserta (nomor_peserta, nama_lengkap, email, password, jenis_kelamin, tanggal_lahir, telepon, alamat, status) 
                          VALUES ('$nomor', '$nama', '$email', '$password', '$jk', '$tgl_lahir', '$telepon', '$alamat', 'aktif')";
                
                if (mysqli_query($conn, $query)) {
                    $success++;
                } else {
                    $errors++;
                }
            }
            
            fclose($handle);
            unlink($target);
            
            alert("Import selesai! Berhasil: $success, Gagal: $errors", 'success');
            redirect('admin/peserta.php');
        } else {
            alert('Untuk file Excel (.xls, .xlsx), silakan install library PHPSpreadsheet terlebih dahulu.', 'warning');
        }
    }
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-file-earmark-excel"></i> Import Peserta dari Excel</h2>
    <a href="peserta.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<?php show_alert(); ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Upload File Excel/CSV</h5>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Pilih File Excel/CSV</label>
                        <input type="file" class="form-control" name="excel_file" accept=".xls,.xlsx,.csv" required>
                        <div class="form-text">Format yang didukung: .xls, .xlsx, .csv (Max 2MB)</div>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-upload"></i> Upload & Import
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h6><i class="bi bi-info-circle"></i> Panduan Import</h6>
            </div>
            <div class="card-body">
                <p><strong>Format CSV yang dibutuhkan:</strong></p>
                <ol class="small">
                    <li>Baris pertama: Header (akan diabaikan)</li>
                    <li>Kolom 1: Nomor Peserta</li>
                    <li>Kolom 2: Nama Lengkap</li>
                    <li>Kolom 3: Email</li>
                    <li>Kolom 4: Password</li>
                    <li>Kolom 5: Jenis Kelamin (L/P)</li>
                    <li>Kolom 6: Tanggal Lahir (YYYY-MM-DD)</li>
                    <li>Kolom 7: Telepon</li>
                    <li>Kolom 8: Alamat</li>
                </ol>
                
                <a href="template_import_peserta.csv" class="btn btn-sm btn-primary mt-2">
                    <i class="bi bi-download"></i> Download Template
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
