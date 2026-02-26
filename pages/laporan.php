<?php
include '../config/database.php';
include '../includes/header.php';

// Ambil data untuk laporan
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : 'all';

// Query untuk laporan panen dengan join ke musim_tanam, lahan, dan bibit
$query_panen = "SELECT 
                    p.id,
                    p.tanggal_panen,
                    p.hasil_kg,
                    p.harga_jual,
                    p.pembeli,
                    p.total_pendapatan,
                    m.tanggal_tanam,
                    l.nama_lahan,
                    l.luas_hektar,
                    l.lokasi,
                    b.nama_bibit,
                    b.sumber as sumber_bibit,
                    DATE_FORMAT(p.tanggal_panen, '%Y-%m') as bulan
                FROM panen p
                LEFT JOIN musim_tanam m ON p.musim_tanam_id = m.id
                LEFT JOIN lahan l ON m.lahan_id = l.id
                LEFT JOIN bibit b ON m.bibit_id = b.id
                WHERE YEAR(p.tanggal_panen) = ? ";

if ($bulan != 'all') {
    $query_panen .= " AND MONTH(p.tanggal_panen) = ? ";
    $params = [$tahun, $bulan];
    $types = "si";
} else {
    $params = [$tahun];
    $types = "s";
}

$query_panen .= " ORDER BY p.tanggal_panen DESC";

$stmt = $conn->prepare($query_panen);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result_panen = $stmt->get_result();

// Query untuk ringkasan bulanan (statistik per bulan)
$query_bulanan = "SELECT 
                    DATE_FORMAT(p.tanggal_panen, '%Y-%m') as bulan,
                    COUNT(*) as jumlah_panen,
                    SUM(p.hasil_kg) as total_hasil,
                    AVG(p.hasil_kg) as rata_rata_hasil,
                    SUM(p.total_pendapatan) as total_pendapatan,
                    AVG(p.harga_jual) as rata_rata_harga
                FROM panen p
                WHERE YEAR(p.tanggal_panen) = ? ";

if ($bulan != 'all') {
    $query_bulanan .= " AND MONTH(p.tanggal_panen) = ? ";
    $params_bulanan = [$tahun, $bulan];
    $types_bulanan = "si";
} else {
    $params_bulanan = [$tahun];
    $types_bulanan = "s";
}

$query_bulanan .= " GROUP BY DATE_FORMAT(p.tanggal_panen, '%Y-%m') ORDER BY bulan DESC";

$stmt_bulanan = $conn->prepare($query_bulanan);
$stmt_bulanan->bind_param($types_bulanan, ...$params_bulanan);
$stmt_bulanan->execute();
$result_bulanan = $stmt_bulanan->get_result();

// Query untuk ringkasan tahunan
$query_ringkasan = "SELECT 
                        YEAR(p.tanggal_panen) as tahun,
                        COUNT(*) as total_panen,
                        SUM(p.hasil_kg) as total_hasil,
                        SUM(p.total_pendapatan) as total_pendapatan,
                        AVG(p.harga_jual) as rata_harga,
                        COUNT(DISTINCT m.lahan_id) as jumlah_lahan,
                        COUNT(DISTINCT m.bibit_id) as jumlah_bibit
                    FROM panen p
                    LEFT JOIN musim_tanam m ON p.musim_tanam_id = m.id
                    GROUP BY YEAR(p.tanggal_panen)
                    ORDER BY tahun DESC";

$result_ringkasan = $conn->query($query_ringkasan);

// Query untuk statistik keseluruhan
$query_statistik = "SELECT 
                        COUNT(*) as total_transaksi,
                        SUM(hasil_kg) as total_hasil_keseluruhan,
                        SUM(total_pendapatan) as total_pendapatan_keseluruhan,
                        AVG(harga_jual) as rata_rata_harga,
                        MAX(hasil_kg) as hasil_tertinggi,
                        MIN(hasil_kg) as hasil_terendah
                    FROM panen";

$result_statistik = $conn->query($query_statistik);
$statistik = $result_statistik->fetch_assoc();

// Query untuk statistik berdasarkan lahan
$query_per_lahan = "SELECT 
                        l.nama_lahan,
                        COUNT(p.id) as jumlah_panen,
                        SUM(p.hasil_kg) as total_hasil,
                        SUM(p.total_pendapatan) as total_pendapatan,
                        AVG(p.hasil_kg) as rata_rata_per_panen
                    FROM lahan l
                    LEFT JOIN musim_tanam m ON l.id = m.lahan_id
                    LEFT JOIN panen p ON m.id = p.musim_tanam_id
                    GROUP BY l.id
                    HAVING jumlah_panen > 0
                    ORDER BY total_hasil DESC";

$result_per_lahan = $conn->query($query_per_lahan);

// Query untuk statistik berdasarkan bibit
$query_per_bibit = "SELECT 
                        b.nama_bibit,
                        COUNT(p.id) as jumlah_panen,
                        SUM(p.hasil_kg) as total_hasil,
                        AVG(p.hasil_kg) as rata_rata_hasil,
                        SUM(p.total_pendapatan) as total_pendapatan
                    FROM bibit b
                    LEFT JOIN musim_tanam m ON b.id = m.bibit_id
                    LEFT JOIN panen p ON m.id = p.musim_tanam_id
                    GROUP BY b.id
                    HAVING jumlah_panen > 0
                    ORDER BY total_hasil DESC";

$result_per_bibit = $conn->query($query_per_bibit);
?>

<style>
    .report-container {
        padding: 20px;
    }

    .report-header {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
    }

    .filter-section {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .summary-card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        gap: 15px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
    }

    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    }

    .card-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .card-icon.green {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .card-icon.blue {
        background: #e3f2fd;
        color: #1565c0;
    }

    .card-icon.orange {
        background: #fff3e0;
        color: #f57c00;
    }

    .card-icon.purple {
        background: #f3e5f5;
        color: #7b1fa2;
    }

    .card-icon.red {
        background: #ffebee;
        color: #c62828;
    }

    .card-icon.teal {
        background: #e0f2f1;
        color: #00695c;
    }

    .card-info h4 {
        font-size: 14px;
        color: #666;
        margin-bottom: 5px;
    }

    .card-info .value {
        font-size: 24px;
        font-weight: bold;
        color: #333;
    }

    .card-info .sub-value {
        font-size: 12px;
        color: #999;
    }

    .chart-container {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .table-container {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        overflow-x: auto;
        margin-bottom: 20px;
    }

    .table-container:hover {
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    }

    .section-title {
        border-left: 5px solid #28a745;
        padding-left: 15px;
        margin-bottom: 20px;
        color: #333;
    }

    .export-buttons {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        flex-wrap: wrap;
    }

    .btn-export {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-pdf {
        background: #dc3545;
        color: white;
    }

    .btn-excel {
        background: #28a745;
        color: white;
    }

    .btn-print {
        background: #6c757d;
        color: white;
    }

    .btn-export:hover {
        opacity: 0.9;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px dashed #eee;
    }

    .info-label {
        color: #666;
        font-weight: 500;
    }

    .info-value {
        font-weight: 600;
        color: #333;
    }

    /* Animasi */
    .report-container {
        animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .filter-form .row {
            flex-direction: column;
        }

        .filter-form .col-md-3,
        .filter-form .col-md-2 {
            width: 100%;
            margin-bottom: 10px;
        }

        .btn-filter {
            width: 100%;
        }
    }
</style>
</head>

<div class="report-container">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1>
                <i class="bi bi-bar-chart-fill"></i>
                Laporan Panen Jagung
            </h1>
            <p class="text-muted mt-2 mb-0">
                Analisis dan rekap data panen dari lahan Anda
            </p>
        </div>

        <div class="d-flex gap-2">
            <select class="form-control" style="width: 150px;"
                onchange="window.location='laporan.php?tahun='+this.value">

                <?php
                $tahun_sekarang = date('Y');
                for ($th = $tahun_sekarang; $th >= $tahun_sekarang - 2; $th--) {
                    $selected = ($th == $tahun) ? 'selected' : '';
                    echo "<option value='$th' $selected>$th</option>";
                }
                ?>

            </select>

            <button class="btn btn-success" onclick="window.print()">
                <i class="bi bi-printer-fill me-2"></i>
                Cetak Laporan
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <form method="GET" class="filter-form">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-calendar3"></i> Tahun
                    </label>
                    <select name="tahun" class="form-select">
                        <?php
                        $tahun_sekarang = date('Y');
                        // Ambil tahun dari database
                        $query_tahun = "SELECT DISTINCT YEAR(tanggal_panen) as tahun FROM panen ORDER BY tahun DESC";
                        $result_tahun = $conn->query($query_tahun);

                        if ($result_tahun->num_rows > 0) {
                            while ($row_tahun = $result_tahun->fetch_assoc()) {
                                $selected = ($tahun == $row_tahun['tahun']) ? 'selected' : '';
                                echo "<option value='{$row_tahun['tahun']}' $selected>{$row_tahun['tahun']}</option>";
                            }
                        } else {
                            for ($i = $tahun_sekarang; $i >= $tahun_sekarang - 2; $i--) {
                                $selected = ($tahun == $i) ? 'selected' : '';
                                echo "<option value='$i' $selected>$i</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-calendar-month"></i> Bulan
                    </label>
                    <select name="bulan" class="form-select">
                        <option value="all" <?= ($bulan == 'all') ? 'selected' : '' ?>>Semua Bulan</option>
                        <?php
                        $nama_bulan = [
                            'Januari',
                            'Februari',
                            'Maret',
                            'April',
                            'Mei',
                            'Juni',
                            'Juli',
                            'Agustus',
                            'September',
                            'Oktober',
                            'November',
                            'Desember'
                        ];
                        for ($i = 1; $i <= 12; $i++) {
                            $selected = ($bulan == $i) ? 'selected' : '';
                            echo "<option value='$i' $selected>$nama_bulan[$i1]</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-funnel-fill"></i> Tampilkan
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="laporan.php" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-4">
            <div class="summary-card">
                <div class="card-icon green">
                    <i class="bi bi-tree-fill"></i>
                </div>
                <div class="card-info">
                    <h4>Total Panen</h4>
                    <div class="value"><?= number_format($statistik['total_transaksi'] ?? 0) ?> Kali</div>
                    <div class="sub-value">Transaksi panen</div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="summary-card">
                <div class="card-icon blue">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
                <div class="card-info">
                    <h4>Total Hasil</h4>
                    <div class="value"><?= number_format($statistik['total_hasil_keseluruhan'] ?? 0) ?> Kg</div>
                    <div class="sub-value">Tertinggi: <?= number_format($statistik['hasil_tertinggi'] ?? 0) ?> Kg</div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="summary-card">
                <div class="card-icon orange">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="card-info">
                    <h4>Total Pendapatan</h4>
                    <div class="value">Rp <?= number_format($statistik['total_pendapatan_keseluruhan'] ?? 0) ?></div>
                    <div class="sub-value">Rp <?= number_format($statistik['rata_rata_harga'] ?? 0) ?>/Kg</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik dan Statistik -->
    <div class="row">
        <div class="col-md-8">
            <!-- Chart Section -->
            <div class="chart-container">
                <canvas id="chartPanen"></canvas>
            </div>
        </div>
        <div class="col-md-4">
            <!-- Statistik Cepat -->
            <div class="table-container">
                <h5 class="section-title"><i class="bi bi-info-circle-fill"></i> Informasi Periode</h5>
                <?php
                // Hitung total untuk periode yang dipilih
                $total_periode = 0;
                $total_pendapatan_periode = 0;
                $rata_harga_periode = 0;
                $jumlah_transaksi = 0;

                if ($result_bulanan->num_rows > 0) {
                    $result_bulanan->data_seek(0);
                    while ($row = $result_bulanan->fetch_assoc()) {
                        $total_periode += $row['total_hasil'];
                        $total_pendapatan_periode += $row['total_pendapatan'];
                        $jumlah_transaksi += $row['jumlah_panen'];
                    }
                    $rata_harga_periode = ($total_periode > 0) ? $total_pendapatan_periode / $total_periode : 0;
                }
                ?>
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-calendar-check"></i> Periode:</span>
                    <span class="info-value"><?= $bulan != 'all' ? $nama_bulan[$bulan - 1] : 'Semua Bulan' ?> <?= $tahun ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-calculator"></i> Total Panen:</span>
                    <span class="info-value"><?= number_format($jumlah_transaksi) ?> Kali</span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-box"></i> Total Hasil:</span>
                    <span class="info-value"><?= number_format($total_periode) ?> Kg</span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-cash"></i> Total Pendapatan:</span>
                    <span class="info-value">Rp <?= number_format($total_pendapatan_periode) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="bi bi-currency-dollar"></i> Rata-rata Harga:</span>
                    <span class="info-value">Rp <?= number_format($rata_harga_periode) ?>/Kg</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Detail Panen -->
    <div class="table-container mt-4">
        <h5 class="section-title"><i class="bi bi-table"></i> Detail Panen <?= $bulan != 'all' ? 'Bulan ' . $nama_bulan[$bulan - 1] : '' ?> <?= $tahun ?></h5>
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-success">
                    <tr>
                        <th>Tanggal</th>
                        <th>Lahan</th>
                        <th>Bibit</th>
                        <th>Hasil (Kg)</th>
                        <th>Harga/Kg</th>
                        <th>Total Pendapatan</th>
                        <th>Pembeli</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_panen->num_rows > 0): ?>
                        <?php while ($row = $result_panen->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <i class="bi bi-calendar3"></i>
                                    <?= date('d/m/Y', strtotime($row['tanggal_panen'])) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row['nama_lahan'] ?? '-') ?>
                                    <small class="d-block text-muted"><?= htmlspecialchars($row['lokasi'] ?? '') ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row['nama_bibit'] ?? '-') ?>
                                    <small class="d-block text-muted"><?= htmlspecialchars($row['sumber_bibit'] ?? '') ?></small>
                                </td>
                                <td class="fw-bold"><?= number_format($row['hasil_kg']) ?> Kg</td>
                                <td>Rp <?= number_format($row['harga_jual']) ?></td>
                                <td class="fw-bold text-success">Rp <?= number_format($row['total_pendapatan']) ?></td>
                                <td><?= htmlspecialchars($row['pembeli'] ?? '-') ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="bi bi-inbox-fill d-block fs-1 text-muted mb-2"></i>
                                Tidak ada data panen untuk periode ini
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Statistik per Lahan dan per Bibit -->
    <div class="row">
        <div class="col-md-6">
            <div class="table-container">
                <h5 class="section-title"><i class="bi bi-pin-map-fill"></i> Statistik per Lahan</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Lahan</th>
                                <th>Panen</th>
                                <th>Total Hasil</th>
                                <th>Rata-rata</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_per_lahan->num_rows > 0): ?>
                                <?php while ($row = $result_per_lahan->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['nama_lahan']) ?></td>
                                        <td><?= $row['jumlah_panen'] ?>x</td>
                                        <td><?= number_format($row['total_hasil']) ?> Kg</td>
                                        <td><?= number_format($row['rata_rata_per_panen']) ?> Kg</td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">Belum ada data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="table-container">
                <h5 class="section-title"><i class="bi bi-flower1"></i> Statistik per Bibit</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Bibit</th>
                                <th>Panen</th>
                                <th>Total Hasil</th>
                                <th>Rata-rata</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_per_bibit->num_rows > 0): ?>
                                <?php while ($row = $result_per_bibit->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['nama_bibit']) ?></td>
                                        <td><?= $row['jumlah_panen'] ?>x</td>
                                        <td><?= number_format($row['total_hasil']) ?> Kg</td>
                                        <td><?= number_format($row['rata_rata_hasil']) ?> Kg</td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">Belum ada data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Ringkasan Tahunan -->
    <div class="table-container mt-4">
        <h5 class="section-title"><i class="bi bi-pie-chart-fill"></i> Ringkasan Tahunan</h5>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Tahun</th>
                        <th>Jumlah Panen</th>
                        <th>Total Hasil (Kg)</th>
                        <th>Total Pendapatan</th>
                        <th>Rata-rata Harga</th>
                        <th>Lahan Aktif</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_ringkasan->num_rows > 0): ?>
                        <?php while ($row = $result_ringkasan->fetch_assoc()): ?>
                            <tr>
                                <td><i class="bi bi-calendar-year"></i> <strong><?= $row['tahun'] ?></strong></td>
                                <td><span class="badge bg-info"><?= $row['total_panen'] ?>x</span></td>
                                <td><?= number_format($row['total_hasil']) ?> Kg</td>
                                <td class="fw-bold text-success">Rp <?= number_format($row['total_pendapatan']) ?></td>
                                <td>Rp <?= number_format($row['rata_harga']) ?></td>
                                <td><?= $row['jumlah_lahan'] ?> lahan</td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="bi bi-database-slash d-block fs-1 text-muted mb-2"></i>
                                Belum ada data panen
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Export Buttons -->
        <div class="export-buttons">
            <button class="btn-export btn-pdf" onclick="exportToPDF()">
                <i class="bi bi-file-pdf-fill"></i> Export PDF
            </button>
            <button class="btn-export btn-excel" onclick="exportToExcel()">
                <i class="bi bi-file-excel-fill"></i> Export Excel
            </button>
            <button class="btn-export btn-print" onclick="window.print()">
                <i class="bi bi-printer-fill"></i> Print
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Data untuk chart
    <?php
    // Reset pointer untuk query bulanan
    $result_bulanan->data_seek(0);
    $labels = [];
    $data_hasil = [];
    $data_pendapatan = [];

    while ($row = $result_bulanan->fetch_assoc()) {
        $bulan_tahun = explode('-', $row['bulan']);
        $labels[] = $nama_bulan[$bulan_tahun[1] - 1] . ' ' . $bulan_tahun[0];
        $data_hasil[] = $row['total_hasil'];
        $data_pendapatan[] = $row['total_pendapatan'];
    }
    ?>

    const ctx = document.getElementById('chartPanen').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                    label: 'Total Hasil Panen (Kg)',
                    data: <?= json_encode($data_hasil) ?>,
                    borderColor: 'rgb(40, 167, 69)',
                    backgroundColor: 'rgba(40, 167, 69, 0.5)',
                    yAxisID: 'y',
                    type: 'bar'
                },
                {
                    label: 'Total Pendapatan (Rp)',
                    data: <?= json_encode($data_pendapatan) ?>,
                    borderColor: 'rgb(255, 193, 7)',
                    backgroundColor: 'rgba(255, 193, 7, 0.5)',
                    yAxisID: 'y1',
                    type: 'line',
                    tension: 0.1,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Grafik Hasil dan Pendapatan Panen <?= $tahun ?>',
                    font: {
                        size: 16
                    }
                },
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Jumlah (Kg)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Pendapatan (Rp)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                },
            }
        }
    });

    // Fungsi export (placeholder)
    function exportToPDF() {
        alert('Fitur export PDF akan segera tersedia');
    }

    function exportToExcel() {
        alert('Fitur export Excel akan segera tersedia');
    }
</script>
<?php include '../includes/footer.php'; ?>