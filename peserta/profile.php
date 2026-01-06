<?php
require_once '../config.php';
check_login_peserta();

$peserta_id = $_SESSION['peserta_id'];

// Ambil data peserta
$query = "SELECT * FROM peserta WHERE id_peserta = $peserta_id";
$result = mysqli_query($conn, $query);
$peserta = mysqli_fetch_assoc($result);

// Update profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $telepon = mysqli_real_escape_string($conn, $_POST['telepon']);
    
    $update_query = "UPDATE peserta SET nama_lengkap = '$nama', email = '$email', telepon = '$telepon' WHERE id_peserta = $peserta_id";
    
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['peserta_nama'] = $nama;
        header('Location: profile.php');
        exit;
    }
}

include 'includes/header.php';
?>

<h2><i class="bi bi-person-circle"></i> Profil Peserta</h2>

<?php show_alert(); ?>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5>Data Profil</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nomor Peserta</label>
                        <input type="text" class="form-control" value="<?php echo $peserta['nomor_peserta']; ?>" disabled>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama_lengkap" value="<?php echo $peserta['nama_lengkap']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="<?php echo $peserta['email']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="tel" class="form-control" name="telepon" value="<?php echo $peserta['telepon']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <input type="text" class="form-control" value="<?php echo $peserta['status'] == 'aktif' ? 'Aktif' : 'Nonaktif'; ?>" disabled>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
