<?php
include '../config/database.php';
include '../includes/header.php';

// Process form
if (isset($_POST['simpan'])) {
    $musim_tanam_id = $_POST['musim_tanam_id'];
    $tanggal_panen = $_POST['tanggal_panen'];
    $hasil_kg = $_POST['hasil_kg'];
    $harga_jual = $_POST['harga_jual'];
    $pembeli = $_POST['pembeli'];
    $total = $hasil_kg * $harga_jual;

    // Cek apakah sudah panen
    $cek = $conn->prepare("SELECT id FROM panen WHERE musim_tanam_id = ?");
    $cek->bind_param("i", $musim_tanam_id);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        // Data sudah ada → lakukan UPDATE
        $stmt = $conn->prepare("UPDATE panen SET tanggal_panen = ?, hasil_kg = ?, harga_jual = ?, pembeli = ?, total_pendapatan = ? WHERE musim_tanam_id = ?");
        $stmt->bind_param("siiii", $tanggal_panen, $hasil_kg, $harga_jual, $pembeli, $total, $musim_tanam_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Data belum ada → lakukan INSERT
        $stmt = $conn->prepare("INSERT INTO panen (musim_tanam_id, tanggal_panen, hasil_kg, harga_jual, pembeli, total_pendapatan) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isiiii", $musim_tanam_id, $tanggal_panen, $hasil_kg, $harga_jual, $pembeli, $total);
        $stmt->execute();
        $stmt->close();
    }

    $cek->close();

    // Update status musim tanam
    $updateStatus = $conn->prepare("UPDATE musim_tanam SET status = 'selesai' WHERE id = ?");
    $updateStatus->bind_param("i", $musim_tanam_id);
    $updateStatus->execute();
    $updateStatus->close();

    echo "<script>alert('Data panen berhasil disimpan!'); window.location='panen.php';</script>";
}
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1>
            <i class="bi bi-basket-fill"></i>
            Panen Jagung
        </h1>
        <p class="text-muted mt-2 mb-0">Catat hasil panen dan pendapatan Anda</p>
    </div>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#panenModal">
        <i class="bi bi-plus-circle me-2"></i>Input Panen Baru
    </button>
</div>

<!-- Stats Summary -->
<?php
$total_panen = $conn->query("SELECT SUM(hasil_kg) as total FROM panen")->fetch_assoc()['total'] ?? 0;
$total_pendapatan = $conn->query("SELECT SUM(total_pendapatan) as total FROM panen")->fetch_assoc()['total'] ?? 0;
$rata_harga = $conn->query("SELECT AVG(harga_jual) as rata FROM panen")->fetch_assoc()['rata'] ?? 0;
?>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Total Panen</div>
                    <div class="stat-value"><?= number_format($total_panen) ?> Kg</div>
                </div>
                <i class="bi bi-box-seam-fill fa-3x text-success-light"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card primary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Total Pendapatan</div>
                    <div class="stat-value">Rp <?= number_format($total_pendapatan) ?></div>
                </div>
                <i class="bi bi-currency-dollar fa-3x text-primary-light"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Rata-rata Harga</div>
                    <div class="stat-value">Rp <?= number_format($rata_harga) ?>/kg</div>
                </div>
                <i class="bi bi-bar-chart fa-3x text-info-light"></i>
            </div>
        </div>
    </div>
</div>

<!-- Harvest History -->
<div class="card">
    <div class="card-header">
        <h5><i class="bi bi-clock-history me-2"></i>Riwayat Panen</h5>
        <div class="d-flex gap-2">
            <select class="form-control form-control-sm" style="width: auto;" id="tahunFilter">
                <option value="">Semua Tahun</option>
                <?php
                $tahun = $conn->query("SELECT DISTINCT YEAR(tanggal_panen) as tahun FROM panen ORDER BY tahun DESC");
                while ($t = $tahun->fetch_assoc()) {
                    echo "<option value='{$t['tahun']}'>{$t['tahun']}</option>";
                }
                ?>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Lahan</th>
                        <th>Bibit</th>
                        <th>Hasil (kg)</th>
                        <th>Harga/kg</th>
                        <th>Total</th>
                        <th>Pembeli</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT p.*, l.nama_lahan, b.nama_bibit 
                            FROM panen p
                            JOIN musim_tanam m ON p.musim_tanam_id = m.id
                            JOIN lahan l ON m.lahan_id = l.id
                            JOIN bibit b ON m.bibit_id = b.id
                            ORDER BY p.tanggal_panen DESC";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . date('d/m/Y', strtotime($row['tanggal_panen'])) . "</td>";
                            echo "<td>{$row['nama_lahan']}</td>";
                            echo "<td>{$row['nama_bibit']}</td>";
                            echo "<td class='fw-bold'>" . number_format($row['hasil_kg']) . " kg</td>";
                            echo "<td>Rp " . number_format($row['harga_jual']) . "</td>";
                            echo "<td class='fw-bold text-success'>Rp " . number_format($row['total_pendapatan']) . "</td>";
                            echo "<td>{$row['pembeli']}</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center py-5'>
                                <i class='bi bi-leaf fa-3x text-muted mb-3'></i>
                                <p class='text-muted'>Belum ada data panen</p>
                                <button class='btn btn-success' data-bs-toggle='modal' data-bs-target='#panenModal'>
                                    <i class='bi bi-plus-circle me-2'></i>Input Panen Pertama
                                </button>
                              </td></tr>";
                    }
                    ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="3" class="text-end">TOTAL:</th>
                        <th><?= number_format($total_panen) ?> kg</th>
                        <th></th>
                        <th colspan="2">Rp <?= number_format($total_pendapatan) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Modal Input Panen -->
<div class="modal fade" id="panenModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Input Hasil Panen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Lahan Siap Panen</label>
                        <select name="musim_tanam_id" class="form-control" required>
                            <option value="">-- Pilih Lahan --</option>
                            <?php
                            $sql = "SELECT m.*, l.nama_lahan 
                                    FROM musim_tanam m
                                    JOIN lahan l ON m.lahan_id = l.id
                                    WHERE m.status='aktif' AND m.estimasi_panen <= CURDATE()";
                            $result = $conn->query($sql);
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['id']}'>{$row['nama_lahan']} - Estimasi: " . date('d/m/Y', strtotime($row['estimasi_panen'])) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Panen</label>
                            <input type="date" name="tanggal_panen" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Hasil (kg)</label>
                            <input type="number" name="hasil_kg" class="form-control" placeholder="Contoh: 2500" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Harga Jual (Rp/kg)</label>
                            <input type="number" name="harga_jual" class="form-control" placeholder="Contoh: 3500" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Pembeli</label>
                            <input type="text" name="pembeli" class="form-control" placeholder="Nama pembeli">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan" class="btn btn-success">Simpan Panen</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>