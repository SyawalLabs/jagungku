<?php
include '../config/database.php';
include '../includes/header.php';

$tahun = isset($_GET['tahun']) && is_numeric($_GET['tahun']) ? $_GET['tahun'] : date('Y');
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1>
            <i class="bi bi-cash-stack"></i>
            Laporan Keuangan & Produksi
        </h1>
        <p class="text-muted mt-2 mb-0">Analisis lengkap usaha tani jagung Anda</p>
    </div>
    <div class="d-flex gap-2">
        <select class="form-control" style="width: 150px;" onchange="window.location='laporan.php?tahun='+this.value">
            <?php
            $tahun_sekarang = date('Y');
            for ($th = $tahun_sekarang; $th >= $tahun_sekarang - 2; $th--) {
                $selected = ($th == $tahun) ? 'selected' : '';
                echo "<option value='$th' $selected>$th</option>";
            }
            ?>
        </select>
        <button class="btn btn-success" onclick="window.print()">
            <i class="bi bi-printer-fill me-2"></i>Cetak Laporan
        </button>
    </div>
</div>

<?php
// Calculate totals
$sql_modal = "SELECT SUM(p.biaya) as total_modal 
              FROM perawatan p
              JOIN musim_tanam m ON p.musim_tanam_id = m.id
              WHERE YEAR(p.tanggal) = '$tahun'";
$modal = $conn->query($sql_modal)->fetch_assoc()['total_modal'] ?? 0;

$sql_pendapatan = "SELECT SUM(total_pendapatan) as total_pendapatan 
                  FROM panen p
                  JOIN musim_tanam m ON p.musim_tanam_id = m.id
                  WHERE YEAR(p.tanggal_panen) = '$tahun'";
$pendapatan = $conn->query($sql_pendapatan)->fetch_assoc()['total_pendapatan'] ?? 0;

$keuntungan = $pendapatan - $modal;
$roi = ($modal > 0) ? round(($keuntungan / $modal) * 100) : 0;
?>

<!-- Summary Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card danger">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Total Modal</div>
                    <div class="stat-value">Rp <?= number_format($modal, 0) ?></div>
                </div>
                <i class="bi bi-wallet2 fa-2x text-danger"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Total Pendapatan</div>
                    <div class="stat-value">Rp <?= number_format($pendapatan, 0) ?></div>
                </div>
                <i class="bi bi-cash-coin fa-2x text-success"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Keuntungan</div>
                    <div class="stat-value">Rp <?= number_format($keuntungan, 0) ?></div>
                    <small>ROI: <?= $roi ?>%</small>
                </div>
                <i class="bi bi-graph-up-arrow fa-2x text-warning"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-bar-chart me-2"></i>Produksi Jagung per Bulan (<?= $tahun ?>)</h5>
            </div>
            <div class="card-body">
                <canvas id="produksiChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-graph-up me-2"></i>Modal vs Pendapatan</h5>
            </div>
            <div class="card-body">
                <canvas id="keuanganChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Land Analysis -->
<!-- <div class="card mb-4">
    <div class="card-header">
        <h5><i class="bi bi-map-fill me-2"></i>Analisis per Lahan</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Lahan</th>
                        <th>Luas (Ha)</th>
                        <th>Jumlah Tanam</th>
                        <th>Total Panen</th>
                        <th>Produktivitas</th>
                        <th>Total Modal</th>
                        <th>Total Pendapatan</th>
                        <th>Keuntungan</th>
                        <th>ROI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT l.id, l.nama_lahan, l.luas_hektar,
                            COUNT(DISTINCT m.id) as jumlah_tanam,
                            COALESCE(SUM(p.hasil_kg), 0) as total_kg,
                            COALESCE((SELECT SUM(biaya) FROM perawatan pr WHERE pr.musim_tanam_id IN (SELECT id FROM musim_tanam WHERE lahan_id = l.id)), 0) as total_modal,
                            COALESCE(SUM(p.total_pendapatan), 0) as total_pendapatan
                            FROM lahan l
                            LEFT JOIN musim_tanam m ON l.id = m.lahan_id
                            LEFT JOIN panen p ON m.id = p.musim_tanam_id
                            GROUP BY l.id";
                    $result = $conn->query($sql);

                    $grand_total_modal = 0;
                    $grand_total_pendapatan = 0;
                    $grand_total_kg = 0;

                    while ($row = $result->fetch_assoc()) {
                        $untung = $row['total_pendapatan'] - $row['total_modal'];
                        $roi_lahan = ($row['total_modal'] > 0) ? round(($untung / $row['total_modal']) * 100) : 0;
                        $produktivitas = ($row['luas_hektar'] > 0) ? round($row['total_kg'] / $row['luas_hektar']) : 0;

                        $grand_total_modal += $row['total_modal'];
                        $grand_total_pendapatan += $row['total_pendapatan'];
                        $grand_total_kg += $row['total_kg'];

                        echo "<tr>";
                        echo "<td><i class='bi bi-geo-fill me-2 text-success'></i>{$row['nama_lahan']}</td>";
                        echo "<td>{$row['luas_hektar']} Ha</td>";
                        echo "<td>{$row['jumlah_tanam']}x</td>";
                        echo "<td>" . number_format($row['total_kg']) . " kg</td>";
                        echo "<td><span class='badge bg-info'>{$produktivitas} kg/Ha</span></td>";
                        echo "<td>Rp " . number_format($row['total_modal']) . "</td>";
                        echo "<td>Rp " . number_format($row['total_pendapatan']) . "</td>";
                        echo "<td class='" . ($untung >= 0 ? 'text-success' : 'text-danger') . " fw-bold'>Rp " . number_format($untung) . "</td>";
                        echo "<td><span class='badge " . ($roi_lahan >= 0 ? 'bg-success' : 'bg-danger') . "'>{$roi_lahan}%</span></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="3" class="text-end">TOTAL:</td>
                        <td><?= number_format($grand_total_kg) ?> kg</td>
                        <td></td>
                        <td>Rp <?= number_format($grand_total_modal) ?></td>
                        <td>Rp <?= number_format($grand_total_pendapatan) ?></td>
                        <td colspan="2">Rp <?= number_format($grand_total_pendapatan - $grand_total_modal) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div> -->

<!-- Production Summary -->
<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-trophy-fill me-2"></i>Pencapaian Terbaik</h5>
            </div>
            <div class="card-body">
                <?php
                // Best harvest
                $best = $conn->query("SELECT p.*, l.nama_lahan FROM panen p
                                    JOIN musim_tanam m ON p.musim_tanam_id = m.id
                                    JOIN lahan l ON m.lahan_id = l.id
                                    ORDER BY p.hasil_kg DESC LIMIT 1")->fetch_assoc();
                ?>
                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded mb-3">
                    <i class="bi bi-award-fill fa-3x text-warning"></i>
                    <div>
                        <h6 class="mb-1">Panen Terbanyak</h6>
                        <p class="mb-0">
                            <strong><?= $best['nama_lahan'] ?? '-' ?></strong> -
                            <?= number_format($best['hasil_kg'] ?? 0) ?> kg
                            (<?= isset($best['tanggal_panen']) ? date('d/m/Y', strtotime($best['tanggal_panen'])) : '' ?>)
                        </p>
                    </div>
                </div>

                <?php
                // Best income
                $best_income = $conn->query("SELECT p.*, l.nama_lahan FROM panen p
                                        JOIN musim_tanam m ON p.musim_tanam_id = m.id
                                        JOIN lahan l ON m.lahan_id = l.id
                                        ORDER BY p.total_pendapatan DESC LIMIT 1")->fetch_assoc();
                ?>
                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded">
                    <i class="bi bi-cash-coin fa-3x text-success"></i>
                    <div>
                        <h6 class="mb-1">Pendapatan Tertinggi</h6>
                        <p class="mb-0">
                            <strong><?= $best_income['nama_lahan'] ?? '-' ?></strong> -
                            Rp <?= number_format($best_income['total_pendapatan'] ?? 0) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-pie-chart-fill me-2"></i>Komposisi Biaya</h5>
            </div>
            <div class="card-body">
                <canvas id="biayaChart" style="height: 250px;"></canvas>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Chart Scripts -->
<script>
    // Get data for charts
    <?php
    $bulan = [];
    $produksi = [];
    $modal_bulan = [];
    $pendapatan_bulan = [];

    for ($b = 1; $b <= 12; $b++) {
        $bulan[] = date('F', mktime(0, 0, 0, $b, 1));

        // Production
        $sql = "SELECT SUM(hasil_kg) as total FROM panen p
            JOIN musim_tanam m ON p.musim_tanam_id = m.id
            WHERE YEAR(p.tanggal_panen) = '$tahun' AND MONTH(p.tanggal_panen) = '$b'";
        $prod = $conn->query($sql)->fetch_assoc()['total'] ?? 0;
        $produksi[] = $prod;

        // Modal
        $sql = "SELECT SUM(biaya) as total FROM perawatan pr
            JOIN musim_tanam m ON pr.musim_tanam_id = m.id
            WHERE YEAR(pr.tanggal) = '$tahun' AND MONTH(pr.tanggal) = '$b'";
        $mod = $conn->query($sql)->fetch_assoc()['total'] ?? 0;
        $modal_bulan[] = $mod;

        // Income
        $sql = "SELECT SUM(total_pendapatan) as total FROM panen p
            JOIN musim_tanam m ON p.musim_tanam_id = m.id
            WHERE YEAR(p.tanggal_panen) = '$tahun' AND MONTH(p.tanggal_panen) = '$b'";
        $pend = $conn->query($sql)->fetch_assoc()['total'] ?? 0;
        $pendapatan_bulan[] = $pend;
    }

    // Cost composition
    $biaya_pupuk = $conn->query("SELECT SUM(biaya) as total FROM perawatan WHERE jenis='pupuk' AND YEAR(tanggal)='$tahun'")->fetch_assoc()['total'] ?? 0;
    $biaya_obat = $conn->query("SELECT SUM(biaya) as total FROM perawatan WHERE jenis='obat' AND YEAR(tanggal)='$tahun'")->fetch_assoc()['total'] ?? 0;
    $biaya_air = $conn->query("SELECT SUM(biaya) as total FROM perawatan WHERE jenis='air' AND YEAR(tanggal)='$tahun'")->fetch_assoc()['total'] ?? 0;
    $biaya_lain = $modal - ($biaya_pupuk + $biaya_obat + $biaya_air);
    ?>

    // Production Chart
    new Chart(document.getElementById('produksiChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($bulan) ?>,
            datasets: [{
                label: 'Produksi (kg)',
                data: <?= json_encode($produksi) ?>,
                backgroundColor: '#2c5e2e',
                borderRadius: 8
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

    // Financial Chart
    new Chart(document.getElementById('keuanganChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($bulan) ?>,
            datasets: [{
                    label: 'Modal',
                    data: <?= json_encode($modal_bulan) ?>,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Pendapatan',
                    data: <?= json_encode($pendapatan_bulan) ?>,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Cost Composition Chart
    new Chart(document.getElementById('biayaChart'), {
        type: 'doughnut',
        data: {
            labels: ['Pupuk', 'Obat Hama', 'Pengairan', 'Lainnya'],
            datasets: [{
                data: [<?= $biaya_pupuk ?>, <?= $biaya_obat ?>, <?= $biaya_air ?>, <?= $biaya_lain ?>],
                backgroundColor: ['#2c5e2e', '#ffc107', '#17a2b8', '#6c757d'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            cutout: '60%'
        }
    });
</script>

<?php include '../includes/footer.php'; ?>