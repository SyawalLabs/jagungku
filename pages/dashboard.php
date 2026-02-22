<?php
include '../config/database.php';
include '../includes/header.php';
?>

<div class="container-fluid">
    <!-- Header with welcome message -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">🌽 Dashboard Utama</h1>
        <div>
            <span class="badge badge-success px-3 py-2">
                <i class="fas fa-calendar me-1"></i>
                <?= date('d F Y') ?>
            </span>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="row">
        <?php
        // Total lahan
        $result = $conn->query("SELECT SUM(luas_hektar) as total FROM lahan");
        $total_lahan = $result->fetch_assoc()['total'] ?? 0;

        // Tanam aktif
        $result = $conn->query("SELECT COUNT(*) as total FROM musim_tanam WHERE status='aktif'");
        $tanam_aktif = $result->fetch_assoc()['total'] ?? 0;

        // Panen bulan ini
        $bulan_ini = date('Y-m');
        $result = $conn->query("SELECT SUM(hasil_kg) as total FROM panen WHERE DATE_FORMAT(tanggal_panen, '%Y-%m') = '$bulan_ini'");
        $panen_bulan = $result->fetch_assoc()['total'] ?? 0;

        // Keuntungan tahun ini
        $tahun_ini = date('Y');
        $sql_modal = "SELECT SUM(p.biaya) as total_modal FROM perawatan p JOIN musim_tanam m ON p.musim_tanam_id = m.id WHERE YEAR(p.tanggal) = '$tahun_ini'";
        $modal = $conn->query($sql_modal)->fetch_assoc()['total_modal'] ?? 0;

        $sql_pendapatan = "SELECT SUM(total_pendapatan) as total_pendapatan FROM panen p JOIN musim_tanam m ON p.musim_tanam_id = m.id WHERE YEAR(p.tanggal_panen) = '$tahun_ini'";
        $pendapatan = $conn->query($sql_pendapatan)->fetch_assoc()['total_pendapatan'] ?? 0;

        $keuntungan = $pendapatan - $modal;
        ?>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Lahan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_lahan ?> Hektar</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-leaf fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tanam Aktif</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $tanam_aktif ?> Lahan</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-seedling fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Panen Bulan Ini</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($panen_bulan, 0, ',', '.') ?> Kg</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-corn fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Keuntungan (Tahun Ini)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp <?= number_format($keuntungan, 0, ',', '.') ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lahan Aktif -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-leaf me-2 text-success"></i>Lahan Aktif (Sedang Ditanami)</h5>
                    <a href="tanam.php" class="btn btn-sm btn-success">
                        <i class="fas fa-plus me-1"></i> Tanam Baru
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Lahan</th>
                                    <th>Bibit</th>
                                    <th>Tanggal Tanam</th>
                                    <th>Umur</th>
                                    <th>Estimasi Panen</th>
                                    <th>Progress</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT m.*, l.nama_lahan, b.nama_bibit,
                                        DATEDIFF(CURDATE(), m.tanggal_tanam) as umur
                                        FROM musim_tanam m
                                        JOIN lahan l ON m.lahan_id = l.id
                                        JOIN bibit b ON m.bibit_id = b.id
                                        WHERE m.status='aktif'
                                        ORDER BY m.tanggal_tanam DESC
                                        LIMIT 5";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $umur = $row['umur'];
                                        $progress = min(100, round(($umur / 100) * 100));

                                        // Warna progress
                                        $warna = 'bg-success';
                                        if ($progress > 80) $warna = 'bg-warning';
                                        if ($progress >= 100) $warna = 'bg-danger';

                                        echo "<tr>";
                                        echo "<td><i class='fas fa-map-marker-alt me-1 text-success'></i> {$row['nama_lahan']}</td>";
                                        echo "<td>{$row['nama_bibit']}</td>";
                                        echo "<td>" . date('d/m/Y', strtotime($row['tanggal_tanam'])) . "</td>";
                                        echo "<td><span class='badge bg-secondary'>{$umur} hari</span></td>";
                                        echo "<td>" . date('d/m/Y', strtotime($row['estimasi_panen'])) . "</td>";
                                        echo "<td width='200'>
                                                <div class='progress' style='height: 10px;'>
                                                    <div class='progress-bar $warna' style='width: {$progress}%'>{$progress}%</div>
                                                </div>
                                              </td>";
                                        echo "<td>
                                                <a href='perawatan.php?tanam_id={$row['id']}' class='btn btn-sm btn-info' data-bs-toggle='tooltip' title='Catat Perawatan'>
                                                    <i class='fas fa-notes-medical'></i>
                                                </a>
                                                <a href='panen.php?tanam_id={$row['id']}' class='btn btn-sm btn-success' data-bs-toggle='tooltip' title='Input Panen'>
                                                    <i class='fas fa-corn'></i>
                                                </a>
                                              </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='7' class='text-center py-4'>Belum ada lahan aktif. <a href='tanam.php'>Mulai tanam sekarang</a></td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pengingat dan Estimasi -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card border-warning">
                <div class="card-header bg-warning bg-opacity-10">
                    <h5 class="mb-0"><i class="fas fa-clock me-2 text-warning"></i>Jadwal Pupuk Minggu Ini</h5>
                </div>
                <div class="card-body">
                    <?php
                    $sql = "SELECT m.*, l.nama_lahan, 
                            DATEDIFF(CURDATE(), m.tanggal_tanam) as umur
                            FROM musim_tanam m
                            JOIN lahan l ON m.lahan_id = l.id
                            WHERE m.status='aktif'";
                    $result = $conn->query($sql);

                    $ada_jadwal = false;
                    while ($row = $result->fetch_assoc()) {
                        $umur = $row['umur'];
                        $jadwal = [];
                        if ($umur >= 25 && $umur <= 35) $jadwal[] = "Pupuk Dasar (Urea)";
                        if ($umur >= 55 && $umur <= 65) $jadwal[] = "Pupuk Susulan (NPK)";

                        if (!empty($jadwal)) {
                            $ada_jadwal = true;
                            echo "<div class='alert alert-info py-2 mb-2'>";
                            echo "<i class='fas fa-leaf me-2'></i> <strong>{$row['nama_lahan']}</strong>: " . implode(', ', $jadwal);
                            echo "</div>";
                        }
                    }

                    if (!$ada_jadwal) {
                        echo "<p class='text-muted mb-0'>Tidak ada jadwal pupuk minggu ini</p>";
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-success">
                <div class="card-header bg-success bg-opacity-10">
                    <h5 class="mb-0"><i class="fas fa-calendar-check me-2 text-success"></i>Estimasi Panen 2 Minggu ke Depan</h5>
                </div>
                <div class="card-body">
                    <?php
                    $sql = "SELECT m.*, l.nama_lahan 
                            FROM musim_tanam m
                            JOIN lahan l ON m.lahan_id = l.id
                            WHERE m.status='aktif' 
                            AND m.estimasi_panen BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $sisa_hari = floor((strtotime($row['estimasi_panen']) - time()) / (60 * 60 * 24));
                            echo "<div class='alert alert-success py-2 mb-2'>";
                            echo "<i class='fas fa-corn me-2'></i> <strong>{$row['nama_lahan']}</strong> - " . date('d/m/Y', strtotime($row['estimasi_panen']));
                            echo " <span class='badge bg-success ms-2'>Sisa {$sisa_hari} hari</span>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p class='text-muted mb-0'>Tidak ada jadwal panen dalam 2 minggu ke depan</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>