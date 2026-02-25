<?php
include '../config/database.php';
include '../includes/header.php';

$tanam_id = $_GET['tanam_id'] ?? 0;

// Process form
if (isset($_POST['simpan'])) {
    $musim_tanam_id = $_POST['musim_tanam_id'];
    $tanggal = $_POST['tanggal'];
    $jenis = $_POST['jenis'];
    $item = $_POST['item'];
    $dosis = $_POST['dosis'];
    $biaya = $_POST['biaya'];
    $keterangan = $_POST['keterangan'];

    $sql = "INSERT INTO perawatan (musim_tanam_id, tanggal, jenis, item, dosis, biaya, keterangan) 
            VALUES ('$musim_tanam_id', '$tanggal', '$jenis', '$item', '$dosis', '$biaya', '$keterangan')";

    if ($conn->query($sql)) {
        echo "<script>alert('Perawatan berhasil dicatat!'); window.location='perawatan.php?tanam_id=$musim_tanam_id';</script>";
    }
}
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1>
            <i class="bi bi-clipboard-check-fill"></i>
            Perawatan Tanaman
        </h1>
        <p class="text-muted mt-2 mb-0">Catat semua aktivitas perawatan jagung Anda</p>
    </div>
</div>

<!-- Pilih Lahan -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Pilih Lahan Aktif</label>
                <select name="tanam_id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Pilih Lahan --</option>
                    <?php
                    $sql = "SELECT m.*, l.nama_lahan 
                            FROM musim_tanam m
                            JOIN lahan l ON m.lahan_id = l.id
                            WHERE m.status='aktif'";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        $selected = ($tanam_id == $row['id']) ? 'selected' : '';
                        echo "<option value='{$row['id']}' $selected>{$row['nama_lahan']} - Tanam: " . date('d/m/Y', strtotime($row['tanggal_tanam'])) . "</option>";
                    }
                    ?>
                </select>
            </div>
        </form>
    </div>
</div>

<?php if ($tanam_id > 0):
    // Get plantation data
    $tanam = $conn->query("SELECT m.*, l.nama_lahan FROM musim_tanam m JOIN lahan l ON m.lahan_id = l.id WHERE m.id=$tanam_id")->fetch_assoc();
    $umur = floor((time() - strtotime($tanam['tanggal_tanam'])) / (60 * 60 * 24));

    // Get total biaya
    $total_biaya = $conn->query("SELECT SUM(biaya) as total FROM perawatan WHERE musim_tanam_id=$tanam_id")->fetch_assoc()['total'] ?? 0;
?>

    <!-- Info Lahan -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card primary">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="stat-label">Lahan</div>
                        <div class="stat-value h6"><?= $tanam['nama_lahan'] ?></div>
                    </div>
                    <i class="bi bi-leaf-fill fa-2x text-primary-light"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card warning">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="stat-label">Umur Tanaman</div>
                        <div class="stat-value h6"><?= $umur ?> Hari</div>
                    </div>
                    <i class="bi bi-clock-fill fa-2x text-warning"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card success">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="stat-label">Total Biaya</div>
                        <div class="stat-value h6">Rp <?= number_format($total_biaya) ?></div>
                    </div>
                    <i class="bi bi-currency-dollar fa-2x text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Perawatan -->
    <div class="card mb-4">
        <div class="card-header">
            <h5><i class="bi bi-plus-circle me-2"></i>Catat Perawatan Baru</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="musim_tanam_id" value="<?= $tanam_id ?>">

                <!-- Baris 1 -->
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Jenis</label>
                        <input type="text" name="jenis" class="form-control" placeholder="Pupuk/Obat/Irigasi" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Nama Item</label>
                        <input type="text" name="item" class="form-control" placeholder="Urea/Decis" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Dosis</label>
                        <input type="text" name="dosis" class="form-control" placeholder="kg/ml">
                    </div>
                </div>

                <!-- Baris 2 -->
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Biaya (Rp)</label>
                        <input type="number" name="biaya" class="form-control" value="0" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Keterangan</label>
                        <input type="text" name="keterangan" class="form-control" placeholder="Catatan">
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" name="simpan" class="btn btn-success w-100">
                            <i class="bi bi-save-fill me-1"></i> Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Riwayat Perawatan -->
    <div class="card">
        <div class="card-header">
            <h5><i class="bi bi-clock-history me-2"></i>Riwayat Perawatan</h5>
            <span class="badge bg-primary">Total: <?= $conn->query("SELECT COUNT(*) as total FROM perawatan WHERE musim_tanam_id=$tanam_id")->fetch_assoc()['total'] ?> kegiatan</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Item</th>
                            <th>Dosis</th>
                            <th>Biaya</th>
                            <th>Keterangan</th>
                            <th>Umur</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT p.*, DATEDIFF(p.tanggal, '{$tanam['tanggal_tanam']}') as umur_saat
                            FROM perawatan p
                            WHERE p.musim_tanam_id = $tanam_id
                            ORDER BY p.tanggal DESC";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $icon = $row['jenis'] == 'pupuk' ? '<i class="bi bi-leaf"></i>' : ($row['jenis'] == 'obat' ? '<i class="bi bi-syringe"></i>' : ($row['jenis'] == 'irigasi' ? '<i class="bi bi-droplet"></i>' : '<i class="bi bi-tools"></i>'));
                                echo "<tr>";
                                echo "<td>" . date('d/m/Y', strtotime($row['tanggal'])) . "</td>";
                                echo "<td>$icon " . ucfirst($row['jenis']) . "</td>";
                                echo "<td>{$row['item']}</td>";
                                echo "<td>{$row['dosis']}</td>";
                                echo "<td>Rp " . number_format($row['biaya']) . "</td>";
                                echo "<td>{$row['keterangan']}</td>";
                                echo "<td><span class='badge bg-secondary'>{$row['umur_saat']} hr</span></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center py-5'>
                                <i class='fas fa-notes-medical fa-3x text-muted mb-3'></i>
                                <p class='text-muted'>Belum ada catatan perawatan</p>
                              </td></tr>";
                        }
                        ?>
                    </tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="4" class="text-end">TOTAL BIAYA PERAWATAN:</th>
                                <th colspan="3">Rp <?= number_format($total_biaya) ?></th>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

<?php endif; ?>

<?php include '../includes/footer.php'; ?>