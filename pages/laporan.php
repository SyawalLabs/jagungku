<?php
include '../config/database.php';
include '../includes/header.php';

// Ambil tahun untuk filter
$tahun = $_GET['tahun'] ?? date('Y');
?>

<h1>💰 Laporan Keuangan & Produksi</h1>

<!-- Filter Tahun -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row">
            <div class="col-md-2">
                <select name="tahun" class="form-control" onchange="this.form.submit()">
                    <?php
                    for ($th = date('Y'); $th >= date('Y') - 2; $th--) {
                        $selected = ($th == $tahun) ? 'selected' : '';
                        echo "<option value='$th' $selected>$th</option>";
                    }
                    ?>
                </select>
            </div>
        </form>
    </div>
</div>

<?php
// Ambil total modal (biaya perawatan)
$sql_modal = "SELECT SUM(p.biaya) as total_modal 
              FROM perawatan p
              JOIN musim_tanam m ON p.musim_tanam_id = m.id
              WHERE YEAR(p.tanggal) = '$tahun'";
$modal = $conn->query($sql_modal)->fetch_assoc()['total_modal'] ?? 0;

// Ambil total pendapatan dari panen
$sql_pendapatan = "SELECT SUM(total_pendapatan) as total_pendapatan 
                  FROM panen p
                  JOIN musim_tanam m ON p.musim_tanam_id = m.id
                  WHERE YEAR(p.tanggal_panen) = '$tahun'";
$pendapatan = $conn->query($sql_pendapatan)->fetch_assoc()['total_pendapatan'] ?? 0;

$keuntungan = $pendapatan - $modal;
$persen = ($modal > 0) ? round(($keuntungan / $modal) * 100) : 0;
?>

<!-- Statistik Keuangan -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5>Total Modal (Biaya)</h5>
                <h3>Rp <?= number_format($modal, 0, ',', '.') ?></h3>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5>Total Pendapatan</h5>
                <h3>Rp <?= number_format($pendapatan, 0, ',', '.') ?></h3>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5>Keuntungan</h5>
                <h3>Rp <?= number_format($keuntungan, 0, ',', '.') ?></h3>
                <small>ROI: <?= $persen ?>%</small>
            </div>
        </div>
    </div>
</div>

<!-- Grafik Produksi per Bulan -->
<div class="card mb-4">
    <div class="card-header">
        <h5>📈 Grafik Produksi Jagung per Bulan (<?= $tahun ?>)</h5>
    </div>
    <div class="card-body">
        <canvas id="grafikProduksi" height="100"></canvas>
    </div>
</div>

<!-- Grafik Keuangan per Bulan -->
<div class="card mb-4">
    <div class="card-header">
        <h5>💰 Grafik Modal vs Pendapatan per Bulan</h5>
    </div>
    <div class="card-body">
        <canvas id="grafikKeuangan" height="100"></canvas>
    </div>
</div>

<!-- Rincian per Lahan -->
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5>📋 Rincian per Lahan (<?= $tahun ?>)</h5>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Lahan</th>
                    <th>Total Tanam</th>
                    <th>Total Panen (kg)</th>
                    <th>Total Modal</th>
                    <th>Total Pendapatan</th>
                    <th>Keuntungan</th>
                    <th>Efisiensi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT l.id, l.nama_lahan,
                        COUNT(DISTINCT m.id) as jumlah_tanam,
                        SUM(p.hasil_kg) as total_kg,
                        COALESCE((SELECT SUM(biaya) FROM perawatan pr WHERE pr.musim_tanam_id IN (SELECT id FROM musim_tanam WHERE lahan_id = l.id)), 0) as total_modal,
                        COALESCE(SUM(p.total_pendapatan), 0) as total_pendapatan
                        FROM lahan l
                        LEFT JOIN musim_tanam m ON l.id = m.lahan_id AND YEAR(m.tanggal_tanam) <= '$tahun'
                        LEFT JOIN panen p ON m.id = p.musim_tanam_id AND YEAR(p.tanggal_panen) = '$tahun'
                        GROUP BY l.id";
                $result = $conn->query($sql);

                while ($row = $result->fetch_assoc()) {
                    $untung = $row['total_pendapatan'] - $row['total_modal'];
                    $efisiensi = ($row['total_modal'] > 0) ? round(($untung / $row['total_modal']) * 100) : 0;

                    echo "<tr>";
                    echo "<td>{$row['nama_lahan']}</td>";
                    echo "<td>{$row['jumlah_tanam']}x</td>";
                    echo "<td>" . number_format($row['total_kg'] ?? 0, 0, ',', '.') . " kg</td>";
                    echo "<td>Rp " . number_format($row['total_modal'], 0, ',', '.') . "</td>";
                    echo "<td>Rp " . number_format($row['total_pendapatan'], 0, ',', '.') . "</td>";
                    echo "<td class='" . ($untung >= 0 ? 'text-success' : 'text-danger') . "'>Rp " . number_format($untung, 0, ',', '.') . "</td>";
                    echo "<td>{$efisiensi}%</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php
// Ambil data produksi per bulan
$bulan = [];
$produksi = [];
$modal_bulan = [];
$pendapatan_bulan = [];

for ($b = 1; $b <= 12; $b++) {
    $bulan[] = date('F', mktime(0, 0, 0, $b, 1));

    // Produksi
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

    // Pendapatan
    $sql = "SELECT SUM(total_pendapatan) as total FROM panen p
            JOIN musim_tanam m ON p.musim_tanam_id = m.id
            WHERE YEAR(p.tanggal_panen) = '$tahun' AND MONTH(p.tanggal_panen) = '$b'";
    $pend = $conn->query($sql)->fetch_assoc()['total'] ?? 0;
    $pendapatan_bulan[] = $pend;
}
?>

<script>
    // Grafik Produksi
    new Chart(document.getElementById('grafikProduksi'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($bulan) ?>,
            datasets: [{
                label: 'Produksi (kg)',
                data: <?= json_encode($produksi) ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.6)'
            }]
        }
    });

    // Grafik Keuangan
    new Chart(document.getElementById('grafikKeuangan'), {
        type: 'line',
        data: {
            labels: <?= json_encode($bulan) ?>,
            datasets: [{
                    label: 'Modal (Rp)',
                    data: <?= json_encode($modal_bulan) ?>,
                    borderColor: 'red',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    yAxisID: 'y'
                },
                {
                    label: 'Pendapatan (Rp)',
                    data: <?= json_encode($pendapatan_bulan) ?>,
                    borderColor: 'green',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    yAxisID: 'y'
                }
            ]
        }
    });
</script>

<?php include '../includes/footer.php'; ?>