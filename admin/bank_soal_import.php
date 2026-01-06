<?php
require_once '../config.php';
check_login_admin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi file upload
    if (!isset($_FILES['file']) || $_FILES['file']['error'] != 0) {
        alert('Silakan pilih file Excel untuk diimport!', 'danger');
    } else {
        $file = $_FILES['file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, ['csv', 'xls', 'xlsx'])) {
            alert('Format file harus CSV atau Excel (.xls, .xlsx)!', 'danger');
        } else {
            // Save uploaded file
            $filename = time() . '_' . $file['name'];
            $filepath = '../assets/uploads/' . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Process CSV file
                if ($ext == 'csv') {
                    $success = 0;
                    $failed = 0;
                    $errors = [];
                    
                    if (($handle = fopen($filepath, "r")) !== FALSE) {
                        // Skip header row
                        $header = fgetcsv($handle, 1000, ",");
                        
                        $row_num = 1;
                        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                            $row_num++;
                            
                            // Validate data (minimal 9 kolom, gambar opsional)
                            if (count($data) < 9) {
                                $failed++;
                                $errors[] = "Baris $row_num: Data tidak lengkap (minimal 9 kolom)";
                                continue;
                            }
                            
                            $id_kategori = (int)trim($data[0]);
                            $pertanyaan = mysqli_real_escape_string($conn, trim($data[1]));
                            $pilihan_a = mysqli_real_escape_string($conn, trim($data[2]));
                            $pilihan_b = mysqli_real_escape_string($conn, trim($data[3]));
                            $pilihan_c = mysqli_real_escape_string($conn, trim($data[4]));
                            $pilihan_d = mysqli_real_escape_string($conn, trim($data[5]));
                            $pilihan_e = mysqli_real_escape_string($conn, trim($data[6]));
                            $jawaban_benar = strtoupper(trim($data[7]));
                            $bobot = (int)trim($data[8]);
                            $gambar = isset($data[9]) ? trim($data[9]) : '';
                            
                            // Validate gambar file exists if specified
                            if (!empty($gambar) && !file_exists('../assets/uploads/' . $gambar)) {
                                $failed++;
                                $errors[] = "Baris $row_num: File gambar '$gambar' tidak ditemukan di folder assets/uploads/";
                                continue;
                            }
                            
                            // Validate required fields
                            if (empty($id_kategori) || empty($pertanyaan) || empty($pilihan_a) || 
                                empty($pilihan_b) || empty($pilihan_c) || empty($pilihan_d) || 
                                empty($jawaban_benar) || empty($bobot)) {
                                $failed++;
                                $errors[] = "Baris $row_num: Ada field yang kosong";
                                continue;
                            }
                            
                            // Validate jawaban_benar
                            if (!in_array($jawaban_benar, ['A', 'B', 'C', 'D', 'E'])) {
                                $failed++;
                                $errors[] = "Baris $row_num: Jawaban benar harus A, B, C, D, atau E";
                                continue;
                            }
                            
                            // Check if kategori exists
                            $check_kat = mysqli_query($conn, "SELECT id_kategori FROM kategori_soal WHERE id_kategori = $id_kategori");
                            if (mysqli_num_rows($check_kat) == 0) {
                                $failed++;
                                $errors[] = "Baris $row_num: ID Kategori $id_kategori tidak ditemukan";
                                continue;
                            }
                            
                            // Insert data
                            $query = "INSERT INTO bank_soal (id_kategori, pertanyaan, pilihan_a, pilihan_b, pilihan_c, pilihan_d, pilihan_e, jawaban_benar, bobot, gambar) 
                                      VALUES ($id_kategori, '$pertanyaan', '$pilihan_a', '$pilihan_b', '$pilihan_c', '$pilihan_d', '$pilihan_e', '$jawaban_benar', $bobot, '$gambar')";
                            
                            if (mysqli_query($conn, $query)) {
                                $success++;
                            } else {
                                $failed++;
                                $errors[] = "Baris $row_num: " . mysqli_error($conn);
                            }
                        }
                        fclose($handle);
                    }
                    
                    // Show result
                    $message = "Import selesai! Berhasil: $success, Gagal: $failed";
                    if (count($errors) > 0) {
                        $message .= "<br><br><strong>Detail Error:</strong><br>" . implode("<br>", array_slice($errors, 0, 10));
                        if (count($errors) > 10) {
                            $message .= "<br>... dan " . (count($errors) - 10) . " error lainnya";
                        }
                    }
                    
                    alert($message, $success > 0 ? 'success' : 'danger');
                    
                    if ($success > 0) {
                        redirect('admin/bank_soal.php');
                    }
                    
                } else {
                    // For Excel files (.xls, .xlsx), need additional library
                    alert('Format Excel (.xls, .xlsx) memerlukan library tambahan. Silakan gunakan format CSV!', 'warning');
                }
            } else {
                alert('Gagal mengupload file!', 'danger');
            }
        }
    }
}

$kategori_list = mysqli_query($conn, "SELECT * FROM kategori_soal ORDER BY nama_kategori");

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-file-earmark-excel"></i> Import Soal dari Excel</h2>
    <a href="bank_soal.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<?php show_alert(); ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Upload File Excel</h5>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Pilih File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="file" accept=".csv,.xls,.xlsx" required>
                        <small class="text-muted">Format: CSV, XLS, XLSX (Max 5MB)</small>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle"></i> Catatan Penting:</h6>
                        <ul class="mb-0">
                            <li>Gunakan template yang disediakan untuk memastikan format yang benar</li>
                            <li>Pastikan ID Kategori sudah ada di sistem</li>
                            <li>Jawaban benar harus berupa huruf: A, B, C, D, atau E</li>
                            <li>Pilihan E boleh dikosongkan jika tidak digunakan</li>
                            <li><strong>Untuk gambar:</strong> Upload file gambar ke folder <code>assets/uploads/</code> terlebih dahulu, lalu isi nama file di kolom gambar</li>
                            <li>Format CSV lebih direkomendasikan untuk kompatibilitas maksimal</li>
                        </ul>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload"></i> Upload & Import
                    </button>
                    <a href="template_import_soal.csv" class="btn btn-success" download>
                        <i class="bi bi-download"></i> Download Template CSV
                    </a>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Format Template</h5>
                <p class="small">File CSV harus memiliki kolom berikut (sesuai urutan):</p>
                <ol class="small">
                    <li><strong>id_kategori</strong> - ID Kategori soal (angka)</li>
                    <li><strong>pertanyaan</strong> - Teks pertanyaan</li>
                    <li><strong>pilihan_a</strong> - Pilihan jawaban A</li>
                    <li><strong>pilihan_b</strong> - Pilihan jawaban B</li>
                    <li><strong>pilihan_c</strong> - Pilihan jawaban C</li>
                    <li><strong>pilihan_d</strong> - Pilihan jawaban D</li>
                    <li><strong>pilihan_e</strong> - Pilihan jawaban E (opsional)</li>
                    <li><strong>jawaban_benar</strong> - Huruf jawaban yang benar (A/B/C/D/E)</li>
                    <li><strong>bobot</strong> - Bobot nilai (angka)</li>
                    <li><strong>gambar</strong> - Nama file gambar (opsional)</li>
                </ol>
                <div class="alert alert-warning alert-sm mt-2">
                    <small><strong>Catatan Gambar:</strong><br>
                    File gambar harus sudah di-upload terlebih dahulu ke folder <code>assets/uploads/</code><br>
                    Isi kolom dengan nama file saja, contoh: <code>soal_1.jpg</code></small>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title">Daftar ID Kategori</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Kategori</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($kat = mysqli_fetch_assoc($kategori_list)): ?>
                            <tr>
                                <td><strong><?php echo $kat['id_kategori']; ?></strong></td>
                                <td><?php echo $kat['nama_kategori']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
