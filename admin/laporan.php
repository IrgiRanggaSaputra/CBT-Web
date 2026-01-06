<?php
require_once '../config.php';
check_login_admin();

// Filter
$id_jadwal = isset($_GET['jadwal']) ? (int)$_GET['jadwal'] : 0;
$tanggal_dari = isset($_GET['tanggal_dari']) ? $_GET['tanggal_dari'] : date('Y-m-01');
$tanggal_sampai = isset($_GET['tanggal_sampai']) ? $_GET['tanggal_sampai'] : date('Y-m-d');

// Build query
$where = ["DATE(pt.created_at) BETWEEN '$tanggal_dari' AND '$tanggal_sampai'"];
if ($id_jadwal > 0) {
    $where[] = "pt.id_jadwal = $id_jadwal";
}
$where_clause = implode(' AND ', $where);

$query = "SELECT pt.*, p.nama_lengkap, p.nomor_peserta, jt.nama_tes, jt.passing_grade
          FROM peserta_tes pt
          JOIN peserta p ON pt.id_peserta = p.id_peserta
          JOIN jadwal_tes jt ON pt.id_jadwal = jt.id_jadwal
          WHERE $where_clause
          ORDER BY pt.created_at DESC";
$result = mysqli_query($conn, $query);

// Get jadwal list
$jadwal_list = mysqli_query($conn, "SELECT * FROM jadwal_tes ORDER BY tanggal_mulai DESC");

// Export to Excel (CSV Format)
if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_tes_' . date('YmdHis') . '.csv"');
    header('Cache-Control: max-age=0');
    
    // BOM untuk UTF-8
    echo "\xEF\xBB\xBF";
    
    // Header
    echo "LAPORAN HASIL TES\n";
    echo "Tanggal Export: " . date('d/m/Y H:i:s') . "\n";
    echo "Filter Tanggal: " . $tanggal_dari . " s/d " . $tanggal_sampai . "\n\n";
    
    // Data Header
    echo "No,Nomor Peserta,Nama Lengkap,Nama Tes,Tanggal,Waktu Mulai,Waktu Selesai,Nilai,Passing Grade,Status\n";
    
    // Data
    $no = 1;
    mysqli_data_seek($result, 0);
    while($row = mysqli_fetch_assoc($result)) {
        $status = $row['status_tes'] == 'selesai' ? ($row['status_kelulusan'] == 'lulus' ? 'LULUS' : 'TIDAK LULUS') : 'Belum Selesai';
        
        $tanggal = date('d/m/Y', strtotime($row['created_at']));
        $waktu_mulai = $row['waktu_mulai'] ? date('H:i:s', strtotime($row['waktu_mulai'])) : '-';
        $waktu_selesai = $row['waktu_selesai'] ? date('H:i:s', strtotime($row['waktu_selesai'])) : '-';
        $nilai = $row['nilai'] ? number_format($row['nilai'], 2) : '-';
        
        echo $no . ',"' . $row['nomor_peserta'] . '","' . addslashes($row['nama_lengkap']) . '","' . addslashes($row['nama_tes']) . '","' . $tanggal . '","' . $waktu_mulai . '","' . $waktu_selesai . '",' . $nilai . ',' . $row['passing_grade'] . ',"' . $status . "\"\n";
        $no++;
    }
    
    exit;
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-file-earmark-text"></i> Laporan Hasil Tes</h2>
</div>

<?php show_alert(); ?>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Nama Tes</label>
                    <select class="form-select" name="jadwal">
                        <option value="0">Semua Tes</option>
                        <?php while($jdw = mysqli_fetch_assoc($jadwal_list)): ?>
                            <option value="<?php echo $jdw['id_jadwal']; ?>" <?php echo $id_jadwal == $jdw['id_jadwal'] ? 'selected' : ''; ?>>
                                <?php echo $jdw['nama_tes']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Dari</label>
                    <input type="date" class="form-control" name="tanggal_dari" value="<?php echo $tanggal_dari; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Sampai</label>
                    <input type="date" class="form-control" name="tanggal_sampai" value="<?php echo $tanggal_sampai; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Filter
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5>Data Hasil Tes</h5>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['export' => 1])); ?>" class="btn btn-success">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Peserta</th>
                        <th>Nama Tes</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Nilai</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    mysqli_data_seek($result, 0);
                    while($row = mysqli_fetch_assoc($result)): 
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td>
                            <strong><?php echo $row['nama_lengkap']; ?></strong><br>
                            <small class="text-muted"><?php echo $row['nomor_peserta']; ?></small>
                        </td>
                        <td><?php echo $row['nama_tes']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                        <td>
                            <small>
                                Mulai: <?php echo $row['waktu_mulai'] ? date('H:i', strtotime($row['waktu_mulai'])) : '-'; ?><br>
                                Selesai: <?php echo $row['waktu_selesai'] ? date('H:i', strtotime($row['waktu_selesai'])) : '-'; ?>
                            </small>
                        </td>
                        <td>
                            <?php if($row['nilai']): ?>
                                <h5 class="mb-0 <?php echo $row['nilai'] >= $row['passing_grade'] ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo number_format($row['nilai'], 2); ?>
                                </h5>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($row['status_tes'] == 'selesai'): ?>
                                <?php if($row['status_kelulusan'] == 'lulus'): ?>
                                    <span class="badge bg-success">LULUS</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">TIDAK LULUS</span>
                                <?php endif; ?>
                            <?php elseif($row['status_tes'] == 'sedang_tes'): ?>
                                <span class="badge bg-warning">Sedang Tes</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Belum Mulai</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="laporan_detail.php?id=<?php echo $row['id_peserta_tes']; ?>" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
