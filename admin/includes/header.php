<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>CBT KIYORAKA Admin</title>
    <link rel="icon" type="image/png" href="../assets/image/kiyoraka.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="navbar-brand">
            <img src="../assets/image/kiyoraka.png" alt="Logo" style="height: 1em;"> CBT KIYORAKA
        </div>
        <ul class="nav flex-column mt-4">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'peserta') !== false ? 'active' : ''; ?>" href="peserta.php">
                    <i class="bi bi-people-fill"></i> Data Peserta
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'kategori') !== false ? 'active' : ''; ?>" href="kategori_soal.php">
                    <i class="bi bi-bookmarks-fill"></i> Kategori Soal
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'bank_soal') !== false ? 'active' : ''; ?>" href="bank_soal.php">
                    <i class="bi bi-journal-text"></i> Bank Soal
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'jadwal') !== false ? 'active' : ''; ?>" href="jadwal_tes.php">
                    <i class="bi bi-calendar-event"></i> Jadwal Tes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'monitoring.php' ? 'active' : ''; ?>" href="monitoring.php">
                    <i class="bi bi-display"></i> Monitoring
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : ''; ?>" href="laporan.php">
                    <i class="bi bi-file-earmark-text"></i> Laporan
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div></div>
            <div>
                <i class="bi bi-person-circle"></i>
                <strong><?php echo $_SESSION['admin_nama']; ?></strong>
            </div>
        </div>
