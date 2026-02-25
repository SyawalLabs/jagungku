<?php
include '../config/database.php';
include '../includes/header.php';

// Get statistics
$total_lahan = $conn->query("SELECT SUM(luas_hektar) as total FROM lahan")->fetch_assoc()['total'] ?? 0;
$tanam_aktif = $conn->query("SELECT COUNT(*) as total FROM musim_tanam WHERE status='aktif'")->fetch_assoc()['total'] ?? 0;

$bulan_ini = date('Y-m');
$panen_bulan = $conn->query("SELECT SUM(hasil_kg) as total FROM panen WHERE DATE_FORMAT(tanggal_panen, '%Y-%m') = '$bulan_ini'")->fetch_assoc()['total'] ?? 0;

$tahun_ini = date('Y');
$modal = $conn->query("SELECT SUM(p.biaya) as total FROM perawatan p JOIN musim_tanam m ON p.musim_tanam_id = m.id WHERE YEAR(p.tanggal) = '$tahun_ini'")->fetch_assoc()['total'] ?? 0;
$pendapatan = $conn->query("SELECT SUM(total_pendapatan) as total FROM panen p JOIN musim_tanam m ON p.musim_tanam_id = m.id WHERE YEAR(p.tanggal_panen) = '$tahun_ini'")->fetch_assoc()['total'] ?? 0;
$keuntungan = $pendapatan - $modal;
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1>
            <i class="bi bi-grid-fill"></i>
            Dashboard Utama
        </h1>
        <p class="text-muted mt-2 mb-0">Selamat datang, <?= $_SESSION['nama'] ?? 'Petani' ?>! Berikut ringkasan lahan Anda.</p>
    </div>
    <div class="d-flex gap-2">
        <span class="badge">
            <i class="bi bi-calendar-event-fill me-1"></i> <?= date('d F Y') ?>
        </span>
        <span class="badge" style="background: var(--primary);">
            <i class="bi bi-cloud-sun-fill me-1"></i> Musim Kemarau
        </span>
    </div>
</div>

<!-- Statistic Cards -->
<div class="row g-4 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card success">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Total Lahan</div>
                    <div class="stat-value"><?= number_format($total_lahan, 2) ?> Ha</div>
                    <small class="text-success">+2.5% dari bulan lalu</small>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-leaf"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="stat-card primary">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Tanam Aktif</div>
                    <div class="stat-value"><?= $tanam_aktif ?> Lahan</div>
                    <small class="text-primary">3 lahan panen</small>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-patch-check"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="stat-card warning">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Panen Bulan Ini</div>
                    <div class="stat-value"><?= number_format($panen_bulan) ?> Kg</div>
                    <small class="text-warning">Target: 2.500 Kg</small>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-basket-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="stat-card info">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Keuntungan</div>
                    <div class="stat-value">Rp <?= number_format($keuntungan, 0) ?></div>
                    <small class="text-info">ROI: 65%</small>
                </div>
                <div class="stat-icon">
                    <!-- <i class="bi bi-graph-up-arrow"></i> -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-bar-chart-line-fill"></i>Produksi Jagung 6 Bulan Terakhir</h5>
                <div class="d-flex gap-2">
                    <span class="badge bg-primary-light">Kg</span>
                    <span class="badge bg-primary-light"><?= date('Y') ?></span>
                </div>
            </div>
            <div class="card-body">
                <canvas id="produksiChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-diagram-3-fill"></i></i>Distribusi Lahan</h5>
            </div>
            <div class="chart-container" style="height: 350px;">
                <canvas id="lahanChart"></canvas>
                <div class="mt-3">
                    <?php
                    $lahan_result = $conn->query("SELECT nama_lahan, luas_hektar FROM lahan");
                    while ($l = $lahan_result->fetch_assoc()) {
                        echo "<div class='d-flex justify-content-between align-items-center mb-2'>";
                        echo "<span><i class='bi bi-circle-fill me-2' style='color: #2c5e2e;'></i>{$l['nama_lahan']}</span>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Active Plantations -->
<div class="card">
    <div class="card-header">
        <h5><i class="bi bi-leaf-fill me-2 text-success"></i>Lahan Aktif</h5>
        <a href="tanam.php" class="btn btn-success btn-sm">
            <i class="bi bi-plus-circle"></i> Tanam Baru
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Lahan</th>
                        <th>Bibit</th>
                        <th class="d-none d-md-table-cell">Tgl Tanam</th>
                        <th>Umur</th>
                        <th class="d-none d-lg-table-cell">Estimasi Panen</th>
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
                            ORDER BY m.tanggal_tanam DESC";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $progress = min(100, round(($row['umur'] / 100) * 100));
                            $warna = $progress > 80 ? 'bg-warning' : ($progress >= 100 ? 'bg-danger' : 'bg-success');

                            echo "<tr>";
                            echo "<td><i class='bi bi-geo-fill me-2 text-success'></i>{$row['nama_lahan']}</td>";
                            echo "<td>{$row['nama_bibit']}</td>";
                            echo "<td class='d-none d-md-table-cell'>" . date('d/m/Y', strtotime($row['tanggal_tanam'])) . "</td>";
                            echo "<td><span class='badge bg-secondary'>{$row['umur']} Hari</span></td>";
                            echo "<td class='d-none d-lg-table-cell'>" . date('d/m/Y', strtotime($row['estimasi_panen'])) . "</td>";
                            echo "<td style='min-width: 120px;'>
                                    <div class='d-flex align-items-center gap-2'>
                                        <div class='progress flex-grow-1'>
                                            <div class='progress-bar $warna' style='width: {$progress}%'></div>
                                        </div>
                                        <small class='fw-bold'>{$progress}%</small>
                                    </div>
                                  </td>";
                            echo "<td>
                                    <div class='d-flex gap-1'>
                                        <a href='perawatan.php?tanam_id={$row['id']}' class='btn btn-sm btn-info' data-bs-toggle='tooltip' title='Catat Perawatan'>
                                            <i class='bi bi-clipboard2-pulse-fill'></i>
                                        </a>
                                        <a href='panen.php?tanam_id={$row['id']}' class='btn btn-sm btn-success' data-bs-toggle='tooltip' title='Input Panen'>
                                            <i class='bi bi-basket-fill'></i>
                                        </a>
                                    </div>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center py-5'>
                                <i class='bi bi-seedling fa-3x text-muted mb-3'></i>
                                <p class='text-muted'>Belum ada lahan aktif. <a href='tanam.php' class='text-decoration-none btn btn-outline-success ms-2'>Mulai tanam</a></p>
                              </td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Reminders -->
<div class="row g-4 mt-2">
    <div class="col-md-6">
        <div class="card border-warning">
            <div class="card-header bg-warning bg-opacity-10">
                <h5 class="text-warning"><i class="bi bi-clock-fill me-2"></i>Jadwal Pupuk Minggu Ini</h5>
            </div>
            <div class="card-body">
                <?php
                $sql = "SELECT m.*, l.nama_lahan FROM musim_tanam m
                        JOIN lahan l ON m.lahan_id = l.id
                        WHERE m.status='aktif'";
                $result = $conn->query($sql);

                $ada_jadwal = false;
                while ($row = $result->fetch_assoc()) {
                    $umur = floor((strtotime(date('Y-m-d')) - strtotime($row['tanggal_tanam'])) / (60 * 60 * 24));
                    $jadwal = [];
                    if ($umur >= 25 && $umur <= 35) $jadwal[] = "Pupuk Dasar (Urea)";
                    if ($umur >= 55 && $umur <= 65) $jadwal[] = "Pupuk Susulan (NPK)";

                    if (!empty($jadwal)) {
                        $ada_jadwal = true;
                        echo "<div class='alert alert-warning d-flex align-items-center gap-3 py-2 mb-2'>";
                        echo "<i class='bi bi-leaf fa-lg'></i>";
                        echo "<div><strong>{$row['nama_lahan']}</strong><br>" . implode(', ', $jadwal) . "</div>";
                        echo "</div>";
                    }
                }

                if (!$ada_jadwal) {
                    echo "<div class='text-center py-4'>
                            <i class='bi bi-check-circle fa-3x text-success mb-3'></i>
                            <p class='text-muted'>Tidak ada jadwal pupuk minggu ini</p>
                          </div>";
                }
                ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-success">
            <div class="card-header bg-success bg-opacity-10">
                <h5 class="text-success"><i class="bi bi-calendar-check-fill me-2"></i>Estimasi Panen 2 Minggu</h5>
            </div>
            <div class="card-body">
                <?php
                $sql = "SELECT m.*, l.nama_lahan FROM musim_tanam m
                        JOIN lahan l ON m.lahan_id = l.id
                        WHERE m.status='aktif' 
                        AND m.estimasi_panen BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $sisa_hari = floor((strtotime($row['estimasi_panen']) - time()) / (60 * 60 * 24));
                        echo "<div class='alert alert-success d-flex align-items-center gap-3 py-2 mb-2'>";
                        echo "<i class='fas fa-corn fa-lg'></i>";
                        echo "<div><strong>{$row['nama_lahan']}</strong><br>";
                        echo date('d/m/Y', strtotime($row['estimasi_panen'])) . " (sisa {$sisa_hari} hari)</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<div class='text-center py-4'>
                            <i class='bi bi-calendar fa-3x text-muted mb-3'></i>
                            <p class='text-muted'>Tidak ada jadwal panen</p>
                          </div>";
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Charts Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Produksi Chart
    var ctx1 = document.getElementById('produksiChart').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
            datasets: [{
                label: 'Produksi (kg)',
                data: [1200, 1350, 1500, 1800, 2200, 1950],
                borderColor: '#2c5e2e',
                backgroundColor: 'rgba(44, 94, 46, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#2c5e2e',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    var ctx2 = document.getElementById('lahanChart').getContext('2d');

    <?php
    $labels = [];
    $values = [];
    $colors = [];

    $lahan_result = $conn->query("SELECT nama_lahan, luas_hektar, jenis_tanah FROM lahan");

    while ($l = $lahan_result->fetch_assoc()) {
        $labels[] = $l['nama_lahan'];
        $values[] = $l['luas_hektar'];

        // warna grafik berdasarkan jenis tanah
        if ($l['jenis_tanah'] == 'Latosol') {
            $colors[] = '#8B4513'; // coklat
        } elseif ($l['jenis_tanah'] == 'Grumosol') {
            $colors[] = '#4B4B4B'; // abu gelap
        } elseif ($l['jenis_tanah'] == 'Mediteran') {
            $colors[] = '#B22222'; // merah bata
        } elseif ($l['jenis_tanah'] == 'Andosol') {
            $colors[] = '#2F2F2F'; // hitam abu
        } else {
            $colors[] = '#2c5e2e'; // default hijau
        }
    }
    ?>

    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Luas Lahan (Ha)',
                data: <?= json_encode($values) ?>,
                backgroundColor: <?= json_encode($colors) ?>,
                borderRadius: 5,
                barThickness: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

<?php include '../includes/footer.php'; ?>