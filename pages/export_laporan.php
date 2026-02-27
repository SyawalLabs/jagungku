<?php
// export_laporan.php
session_start();
require_once '../config/database.php';

// Cek login dengan lebih fleksibel
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    // Coba cek apakah user sudah login dengan cara lain
    $logged_in = false;

    // Cek berbagai kemungkinan session
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        $logged_in = true;
    } elseif (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
        $logged_in = true;
    } elseif (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        $logged_in = true;
    }

    if (!$logged_in) {
        // Redirect ke halaman login
        header('Location: login.php');
        exit;
    }
}

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Get parameters dengan sanitasi
$format = isset($_GET['format']) ? $_GET['format'] : 'excel';
$data_type = isset($_GET['type']) ? $_GET['type'] : 'detail';
$period = isset($_GET['period']) ? $_GET['period'] : 'current';
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : 'all';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Validasi input
$tahun = intval($tahun);
if ($bulan != 'all') {
    $bulan = intval($bulan);
}

// Di bagian awal export_laporan.php (setelah get parameters)

// Tentukan nama file berdasarkan pilihan export
$tipe_export = "";
switch ($data_type) {
    case 'detail':
        $tipe_export = "Detail_Panen";
        break;
    case 'ringkasan':
        $tipe_export = "Ringkasan_Tahunan";
        break;
    case 'per_lahan':
        $tipe_export = "Statistik_Per_Lahan";
        break;
    case 'per_bibit':
        $tipe_export = "Statistik_Per_Bibit";
        break;
    case 'semua':
        $tipe_export = "Laporan_Lengkap";
        break;
    default:
        $tipe_export = "Laporan_Panen";
}

// Tentukan periode untuk nama file
$periode_text = "";

if ($period == 'current') {
    if ($bulan != 'all') {
        $nama_bulan = [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'Mei',
            'Jun',
            'Jul',
            'Agu',
            'Sep',
            'Okt',
            'Nov',
            'Des'
        ];
        $periode_text = $nama_bulan[$bulan - 1] . "_" . $tahun;
    } else {
        $periode_text = "Tahun_" . $tahun;
    }
} elseif ($period == 'tahun_ini') {
    $periode_text = "Tahun_" . date('Y');
} elseif ($period == 'custom' && $start_date && $end_date) {
    // Format tanggal: YYYYMMDD
    $tgl_awal = date('Ymd', strtotime($start_date));
    $tgl_akhir = date('Ymd', strtotime($end_date));
    $periode_text = $tgl_awal . "_sd_" . $tgl_akhir;
} else {
    $periode_text = "Semua_Data";
}

// Nama file final (tanpa ekstensi)
$filename = $tipe_export . "_" . $periode_text;

// Hapus karakter yang tidak diinginkan (spasi, dll)
$filename = str_replace(' ', '_', $filename);
$filename = preg_replace('/[^a-zA-Z0-9_]/', '', $filename);

// Nama file akan digunakan di header nanti

// Panggil fungsi export
if ($format == 'excel') {
    exportToExcel($conn, $data_type, $period, $tahun, $bulan, $start_date, $end_date, $filename);
} elseif ($format == 'pdf') {
    exportToPDF($conn, $data_type, $period, $tahun, $bulan, $start_date, $end_date, $filename);
} else {
    die('Format tidak didukung');
}

function exportToExcel($conn, $data_type, $period, $tahun, $bulan, $start_date, $end_date, $filename)
{
    // Header untuk download Excel
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"{$filename}.xls\"");
    header("Cache-Control: max-age=0");
    header("Pragma: public");
    header("Expires: 0");

    // Nama bulan
    $nama_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    // Output HTML untuk Excel
    echo "<html><head><meta charset='UTF-8'>";
    echo "<style>
        body { font-family: Arial, sans-serif; }
        h2 { color: #2c5e2e; text-align: center; }
        th { background-color: #2c5e2e; color: white; padding: 8px; text-align: center; font-weight: bold; }
        td { padding: 6px; border: 1px solid #ddd; }
        .total-row { background-color: #e8f5e8; font-weight: bold; }
        .footer { margin-top: 20px; font-size: 12px; color: #666; text-align: right; }
    </style>";
    echo "</head><body>";

    // Tampilkan data sesuai tipe
    switch ($data_type) {
        case 'detail':
            exportDetailPanen($conn, $period, $tahun, $bulan, $start_date, $end_date, $nama_bulan);
            break;
        case 'ringkasan':
            exportRingkasanTahunan($conn);
            break;
        case 'per_lahan':
            exportPerLahan($conn);
            break;
        case 'per_bibit':
            exportPerBibit($conn);
            break;
        case 'semua':
            exportSemuaData($conn, $period, $tahun, $bulan, $start_date, $end_date, $nama_bulan);
            break;
        default:
            echo "<p>Jenis data tidak valid</p>";
    }

    echo "</body></html>";
}

function exportToPDF($conn, $data_type, $period, $tahun, $bulan, $start_date, $end_date, $filename)
{
    // Untuk PDF, kita akan redirect ke file terpisah atau tampilkan pesan
    // Karena implementasi PDF lebih kompleks, untuk sementara kita tampilkan pesan
    header("Content-Type: text/html");
    echo "<html><head><title>Export PDF</title>";
    echo "<style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .info { background: #e8f5e8; padding: 30px; border-radius: 10px; display: inline-block; max-width: 500px; }
        .btn { background: #2c5e2e; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 20px; display: inline-block; }
    </style>";
    echo "</head><body>";
    echo "<div class='info'>";
    echo "<h2 style='color: #dc3545;'><i class='bi bi-file-pdf-fill'></i> Export PDF</h2>";
    echo "<p>Fitur export PDF sedang dalam pengembangan.</p>";
    echo "<p>Untuk sementara, Anda bisa menggunakan fitur Print (Ctrl+P) dan save sebagai PDF.</p>";
    echo "<a href='javascript:window.close()' class='btn'>Tutup</a>";
    echo "</div>";
    echo "</body></html>";
}

function buildWhereClause($period, $tahun, $bulan, $start_date, $end_date)
{
    $where = "WHERE 1=1";

    if ($period == 'current') {
        $where .= " AND YEAR(p.tanggal_panen) = '$tahun'";
        if ($bulan != 'all') {
            $bulan = intval($bulan);
            $where .= " AND MONTH(p.tanggal_panen) = '$bulan'";
        }
    } elseif ($period == 'tahun_ini') {
        $where .= " AND YEAR(p.tanggal_panen) = '" . date('Y') . "'";
    } elseif ($period == 'custom' && $start_date && $end_date) {
        // Validasi tanggal
        $start_date = mysqli_real_escape_string($conn, $start_date);
        $end_date = mysqli_real_escape_string($conn, $end_date);
        $where .= " AND p.tanggal_panen BETWEEN '$start_date' AND '$end_date'";
    }

    return $where;
}

function exportDetailPanen($conn, $period, $tahun, $bulan, $start_date, $end_date, $nama_bulan)
{
    $where_clause = buildWhereClause($period, $tahun, $bulan, $start_date, $end_date);

    $query = "SELECT 
                p.tanggal_panen,
                l.nama_lahan,
                l.lokasi,
                b.nama_bibit,
                p.hasil_kg,
                p.harga_jual,
                p.total_pendapatan,
                p.pembeli
            FROM panen p
            LEFT JOIN musim_tanam m ON p.musim_tanam_id = m.id
            LEFT JOIN lahan l ON m.lahan_id = l.id
            LEFT JOIN bibit b ON m.bibit_id = b.id
            $where_clause
            ORDER BY p.tanggal_panen DESC";

    $result = $conn->query($query);

    if (!$result) {
        echo "<p>Error query: " . $conn->error . "</p>";
        return;
    }

    echo "<h2>DETAIL PANEN</h2>";

    // Info periode
    echo "<p><strong>Periode: </strong>";
    if ($period == 'current') {
        if ($bulan != 'all') {
            echo $nama_bulan[$bulan - 1] . " " . $tahun;
        } else {
            echo "Tahun " . $tahun;
        }
    } elseif ($period == 'custom') {
        echo date('d/m/Y', strtotime($start_date)) . " - " . date('d/m/Y', strtotime($end_date));
    } else {
        echo "Semua Data";
    }
    echo "</p>";

    echo "<table border='1' cellspacing='0' cellpadding='5'>";
    echo "<tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Nama Lahan</th>
            <th>Lokasi</th>
            <th>Bibit</th>
            <th>Hasil (Kg)</th>
            <th>Harga/Kg</th>
            <th>Total Pendapatan</th>
            <th>Pembeli</th>
          </tr>";

    $no = 1;
    $total_hasil = 0;
    $total_pendapatan = 0;

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $total_hasil += $row['hasil_kg'];
            $total_pendapatan += $row['total_pendapatan'];

            echo "<tr>";
            echo "<td align='center'>" . $no++ . "</td>";
            echo "<td>" . date('d/m/Y', strtotime($row['tanggal_panen'])) . "</td>";
            echo "<td>" . htmlspecialchars($row['nama_lahan'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['lokasi'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($row['nama_bibit'] ?? '-') . "</td>";
            echo "<td align='right'>" . number_format($row['hasil_kg'], 0, ',', '.') . "</td>";
            echo "<td align='right'>Rp " . number_format($row['harga_jual'], 0, ',', '.') . "</td>";
            echo "<td align='right'>Rp " . number_format($row['total_pendapatan'], 0, ',', '.') . "</td>";
            echo "<td>" . htmlspecialchars($row['pembeli'] ?? '-') . "</td>";
            echo "</tr>";
        }

        // Baris total
        echo "<tr class='total-row'>";
        echo "<td colspan='5' align='center'><strong>TOTAL</strong></td>";
        echo "<td align='right'><strong>" . number_format($total_hasil, 0, ',', '.') . " Kg</strong></td>";
        echo "<td></td>";
        echo "<td align='right'><strong>Rp " . number_format($total_pendapatan, 0, ',', '.') . "</strong></td>";
        echo "<td></td>";
        echo "</tr>";
    } else {
        echo "<tr><td colspan='9' align='center'>Tidak ada data untuk periode ini</td></tr>";
    }

    echo "</table>";
    echo "<p class='footer'>Diexport pada: " . date('d/m/Y H:i:s') . "</p>";
}

function exportRingkasanTahunan($conn)
{
    $query = "SELECT 
                YEAR(p.tanggal_panen) as tahun,
                COUNT(*) as total_panen,
                SUM(p.hasil_kg) as total_hasil,
                SUM(p.total_pendapatan) as total_pendapatan,
                AVG(p.harga_jual) as rata_harga,
                COUNT(DISTINCT m.lahan_id) as jumlah_lahan
            FROM panen p
            LEFT JOIN musim_tanam m ON p.musim_tanam_id = m.id
            GROUP BY YEAR(p.tanggal_panen)
            ORDER BY tahun DESC";

    $result = $conn->query($query);

    if (!$result) {
        echo "<p>Error query: " . $conn->error . "</p>";
        return;
    }

    echo "<h2>RINGKASAN TAHUNAN</h2>";
    echo "<table border='1' cellspacing='0' cellpadding='5'>";
    echo "<tr>
            <th>No</th>
            <th>Tahun</th>
            <th>Jumlah Panen</th>
            <th>Total Hasil (Kg)</th>
            <th>Total Pendapatan</th>
            <th>Rata-rata Harga</th>
            <th>Lahan Aktif</th>
          </tr>";

    $no = 1;
    $grand_total_hasil = 0;
    $grand_total_pendapatan = 0;

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $grand_total_hasil += $row['total_hasil'];
            $grand_total_pendapatan += $row['total_pendapatan'];

            echo "<tr>";
            echo "<td align='center'>" . $no++ . "</td>";
            echo "<td align='center'><strong>" . $row['tahun'] . "</strong></td>";
            echo "<td align='center'>" . $row['total_panen'] . "x</td>";
            echo "<td align='right'>" . number_format($row['total_hasil'], 0, ',', '.') . " Kg</td>";
            echo "<td align='right'>Rp " . number_format($row['total_pendapatan'], 0, ',', '.') . "</td>";
            echo "<td align='right'>Rp " . number_format($row['rata_harga'], 0, ',', '.') . "</td>";
            echo "<td align='center'>" . $row['jumlah_lahan'] . "</td>";
            echo "</tr>";
        }

        // Grand total
        echo "<tr class='total-row'>";
        echo "<td colspan='3' align='center'><strong>GRAND TOTAL</strong></td>";
        echo "<td align='right'><strong>" . number_format($grand_total_hasil, 0, ',', '.') . " Kg</strong></td>";
        echo "<td align='right'><strong>Rp " . number_format($grand_total_pendapatan, 0, ',', '.') . "</strong></td>";
        echo "<td colspan='2'></td>";
        echo "</tr>";
    } else {
        echo "<tr><td colspan='7' align='center'>Belum ada data panen</td></tr>";
    }

    echo "</table>";
    echo "<p class='footer'>Diexport pada: " . date('d/m/Y H:i:s') . "</p>";
}

function exportPerLahan($conn)
{
    $query = "SELECT 
                l.nama_lahan,
                l.luas_hektar,
                COUNT(p.id) as jumlah_panen,
                SUM(p.hasil_kg) as total_hasil,
                SUM(p.total_pendapatan) as total_pendapatan,
                AVG(p.hasil_kg) as rata_rata_per_panen
            FROM lahan l
            LEFT JOIN musim_tanam m ON l.id = m.lahan_id
            LEFT JOIN panen p ON m.id = p.musim_tanam_id
            GROUP BY l.id, l.nama_lahan, l.luas_hektar
            HAVING jumlah_panen > 0
            ORDER BY total_hasil DESC";

    $result = $conn->query($query);

    if (!$result) {
        echo "<p>Error query: " . $conn->error . "</p>";
        return;
    }

    echo "<h2>STATISTIK PER LAHAN</h2>";
    echo "<table border='1' cellspacing='0' cellpadding='5'>";
    echo "<tr>
            <th>No</th>
            <th>Nama Lahan</th>
            <th>Luas (Ha)</th>
            <th>Jumlah Panen</th>
            <th>Total Hasil (Kg)</th>
            <th>Rata-rata (Kg)</th>
            <th>Total Pendapatan</th>
          </tr>";

    $no = 1;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td align='center'>" . $no++ . "</td>";
            echo "<td>" . htmlspecialchars($row['nama_lahan']) . "</td>";
            echo "<td align='right'>" . number_format($row['luas_hektar'], 2, ',', '.') . "</td>";
            echo "<td align='center'>" . $row['jumlah_panen'] . "x</td>";
            echo "<td align='right'>" . number_format($row['total_hasil'], 0, ',', '.') . " Kg</td>";
            echo "<td align='right'>" . number_format($row['rata_rata_per_panen'], 0, ',', '.') . " Kg</td>";
            echo "<td align='right'>Rp " . number_format($row['total_pendapatan'], 0, ',', '.') . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='7' align='center'>Belum ada data panen per lahan</td></tr>";
    }

    echo "</table>";
    echo "<p class='footer'>Diexport pada: " . date('d/m/Y H:i:s') . "</p>";
}

function exportPerBibit($conn)
{
    $query = "SELECT 
                b.nama_bibit,
                b.sumber,
                COUNT(p.id) as jumlah_panen,
                SUM(p.hasil_kg) as total_hasil,
                AVG(p.hasil_kg) as rata_rata_hasil,
                SUM(p.total_pendapatan) as total_pendapatan
            FROM bibit b
            LEFT JOIN musim_tanam m ON b.id = m.bibit_id
            LEFT JOIN panen p ON m.id = p.musim_tanam_id
            GROUP BY b.id, b.nama_bibit, b.sumber
            HAVING jumlah_panen > 0
            ORDER BY total_hasil DESC";

    $result = $conn->query($query);

    if (!$result) {
        echo "<p>Error query: " . $conn->error . "</p>";
        return;
    }

    echo "<h2>STATISTIK PER BIBIT</h2>";
    echo "<table border='1' cellspacing='0' cellpadding='5'>";
    echo "<tr>
            <th>No</th>
            <th>Nama Bibit</th>
            <th>Sumber</th>
            <th>Jumlah Panen</th>
            <th>Total Hasil (Kg)</th>
            <th>Rata-rata (Kg)</th>
            <th>Total Pendapatan</th>
          </tr>";

    $no = 1;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td align='center'>" . $no++ . "</td>";
            echo "<td>" . htmlspecialchars($row['nama_bibit']) . "</td>";
            echo "<td>" . htmlspecialchars($row['sumber'] ?? '-') . "</td>";
            echo "<td align='center'>" . $row['jumlah_panen'] . "x</td>";
            echo "<td align='right'>" . number_format($row['total_hasil'], 0, ',', '.') . " Kg</td>";
            echo "<td align='right'>" . number_format($row['rata_rata_hasil'], 0, ',', '.') . " Kg</td>";
            echo "<td align='right'>Rp " . number_format($row['total_pendapatan'], 0, ',', '.') . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='7' align='center'>Belum ada data panen per bibit</td></tr>";
    }

    echo "</table>";
    echo "<p class='footer'>Diexport pada: " . date('d/m/Y H:i:s') . "</p>";
}

function exportSemuaData($conn, $period, $tahun, $bulan, $start_date, $end_date, $nama_bulan)
{
    // Export semua data dalam satu file
    exportDetailPanen($conn, $period, $tahun, $bulan, $start_date, $end_date, $nama_bulan);

    echo "<br><br>";
    exportRingkasanTahunan($conn);

    echo "<br><br>";
    exportPerLahan($conn);

    echo "<br><br>";
    exportPerBibit($conn);
}
