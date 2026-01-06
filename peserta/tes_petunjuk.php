<?php
require_once '../config.php';
check_login_peserta();

$id_jadwal = (int)$_GET['id'];
$peserta_id = $_SESSION['peserta_id'];

// Get test info
$query = "SELECT * FROM jadwal_tes WHERE id_jadwal = $id_jadwal";
$tes = mysqli_fetch_assoc(mysqli_query($conn, $query));

if (!$tes) {
    alert('Jadwal tes tidak ditemukan!', 'danger');
    redirect('peserta/dashboard.php');
}

// Check if already taken
$check = mysqli_query($conn, "SELECT * FROM peserta_tes WHERE id_jadwal = $id_jadwal AND id_peserta = $peserta_id");
if (mysqli_num_rows($check) > 0) {
    $peserta_tes = mysqli_fetch_assoc($check);
    if ($peserta_tes['status_tes'] == 'selesai') {
        alert('Anda sudah menyelesaikan tes ini!', 'info');
        redirect('peserta/dashboard.php');
    }
}

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4><i class="bi bi-info-circle"></i> Petunjuk Tes</h4>
            </div>
            <div class="card-body">
                <h5><?php echo $tes['nama_tes']; ?></h5>
                
                <div class="alert alert-warning mt-3">
                    <h6><i class="bi bi-exclamation-triangle"></i> Informasi Penting:</h6>
                    <ul>
                        <li>Durasi tes: <strong><?php echo $tes['durasi']; ?> menit</strong></li>
                        <li>Jumlah soal: <strong><?php echo $tes['jumlah_soal']; ?> soal</strong></li>
                        <li>Passing grade: <strong><?php echo $tes['passing_grade']; ?></strong></li>
                        <li>Tes akan dimulai segera setelah Anda klik tombol "Mulai Tes"</li>
                        <li>Timer akan berjalan otomatis dan tidak dapat dihentikan</li>
                    </ul>
                </div>

                <?php if($tes['instruksi']): ?>
                <div class="alert alert-info">
                    <h6><i class="bi bi-list-check"></i> Instruksi Khusus:</h6>
                    <p><?php echo nl2br($tes['instruksi']); ?></p>
                </div>
                <?php endif; ?>

                <div class="alert alert-danger">
                    <h6><i class="bi bi-exclamation-octagon"></i> Perhatian:</h6>
                    <ul class="mb-0">
                        <li>Pastikan koneksi internet stabil</li>
                        <li>Jangan menutup atau refresh halaman browser</li>
                        <li>Jawaban akan tersimpan otomatis setiap kali Anda menjawab</li>
                        <li>Tes akan otomatis submit jika waktu habis</li>
                        <li>Setiap peserta hanya dapat mengerjakan 1 kali</li>
                    </ul>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <a href="tes_mulai.php?id=<?php echo $id_jadwal; ?>" class="btn btn-primary btn-lg">
                        <i class="bi bi-play-circle-fill"></i> Mulai Tes Sekarang
                    </a>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
