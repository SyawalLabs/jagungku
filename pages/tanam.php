<?php
include '../config/database.php';
include '../includes/header.php';

// Process form
if (isset($_POST['simpan'])) {
    $lahan_id = $_POST['lahan_id'];
    $bibit_id = $_POST['bibit_id'];
    $tanggal_tanam = $_POST['tanggal_tanam'];
    $estimasi = date('Y-m-d', strtotime($tanggal_tanam . ' +100 days'));

    $sql = "INSERT INTO musim_tanam (lahan_id, bibit_id, tanggal_tanam, estimasi_panen, status) 
            VALUES ('$lahan_id', '$bibit_id', '$tanggal_tanam', '$estimasi', 'aktif')";

    if ($conn->query($sql)) {
        echo "<script>alert('Musim tanam berhasil dicatat!'); window.location='tanam.php';</script>";
    }
}
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1>
            <i class="fas fa-seedling"></i>
            Musim Tanam
        </h1>
        <p class="text-muted mt-2 mb-0">Kelola siklus tanam jagung Anda</p>
    </div>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tanamModal">
        <i class="fas fa-plus me-2"></i>Catat Tanam Baru
    </button>
</div>

<!-- Status Tabs -->
<ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="aktif-tab" data-bs-toggle="tab" data-bs-target="#aktif" type="button">
            <i class="fas fa-leaf me-2"></i>Aktif
            <span class="badge bg-success ms-2">
                <?= $conn->query("SELECT COUNT(*) as total FROM musim_tanam WHERE status='aktif'")->fetch_assoc()['total'] ?>
            </span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="selesai-tab" data-bs-toggle="tab" data-bs-target="#selesai" type="button">
            <i class="fas fa-history me-2"></i>Riwayat
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content" id="myTabContent">
    <!-- Tab Aktif -->
    <div class="tab-pane fade show active" id="aktif" role="tabpanel">
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Lahan</th>
                                <th>Bibit</th>
                                <th>Tgl Tanam</th>
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
                                    ORDER BY m.tanggal_tanam DESC";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $progress = min(100, round(($row['umur'] / 100) * 100));
                                    $warna = $progress > 80 ? 'bg-warning' : ($progress >= 100 ? 'bg-danger' : 'bg-success');

                                    echo "<tr>";
                                    echo "<td><i class='fas fa-map-marker-alt me-2 text-success'></i>{$row['nama_lahan']}</td>";
                                    echo "<td>{$row['nama_bibit']}</td>";
                                    echo "<td>" . date('d/m/Y', strtotime($row['tanggal_tanam'])) . "</td>";
                                    echo "<td><span class='badge bg-secondary'>{$row['umur']} hari</span></td>";
                                    echo "<td>" . date('d/m/Y', strtotime($row['estimasi_panen'])) . "</td>";
                                    echo "<td style='min-width: 150px;'>
                                            <div class='d-flex align-items-center gap-2'>
                                                <div class='progress flex-grow-1'>
                                                    <div class='progress-bar $warna' style='width: {$progress}%'></div>
                                                </div>
                                                <small class='fw-bold'>{$progress}%</small>
                                            </div>
                                          </td>";
                                    echo "<td>
                                            <div class='d-flex gap-1'>
                                                <a href='perawatan.php?tanam_id={$row['id']}' class='btn btn-sm btn-info' title='Rawat'>
                                                    <i class='fas fa-notes-medical'></i>
                                                </a>
                                                <a href='panen.php?tanam_id={$row['id']}' class='btn btn-sm btn-success' title='Panen'>
                                                    <i class='fas fa-corn'></i>
                                                </a>
                                            </div>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center py-5'>
                                        <i class='fas fa-seedling fa-3x text-muted mb-3'></i>
                                        <p class='text-muted'>Belum ada musim tanam aktif</p>
                                        <button class='btn btn-success' data-bs-toggle='modal' data-bs-target='#tanamModal'>
                                            <i class='fas fa-plus me-2'></i>Mulai Tanam
                                        </button>
                                      </td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Riwayat -->
    <div class="tab-pane fade" id="selesai" role="tabpanel">
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Lahan</th>
                                <th>Bibit</th>
                                <th>Tgl Tanam</th>
                                <th>Tgl Panen</th>
                                <th>Hasil (kg)</th>
                                <th>Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT m.*, l.nama_lahan, b.nama_bibit, p.hasil_kg, p.tanggal_panen, p.total_pendapatan
                                    FROM musim_tanam m
                                    JOIN lahan l ON m.lahan_id = l.id
                                    JOIN bibit b ON m.bibit_id = b.id
                                    LEFT JOIN panen p ON m.id = p.musim_tanam_id
                                    WHERE m.status='selesai'
                                    ORDER BY m.tanggal_tanam DESC";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>{$row['nama_lahan']}</td>";
                                    echo "<td>{$row['nama_bibit']}</td>";
                                    echo "<td>" . date('d/m/Y', strtotime($row['tanggal_tanam'])) . "</td>";
                                    echo "<td>" . ($row['tanggal_panen'] ? date('d/m/Y', strtotime($row['tanggal_panen'])) : '-') . "</td>";
                                    echo "<td>" . ($row['hasil_kg'] ? number_format($row['hasil_kg']) . ' kg' : '-') . "</td>";
                                    echo "<td>" . ($row['total_pendapatan'] ? 'Rp ' . number_format($row['total_pendapatan']) : '-') . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center py-5'>
                                        <i class='fas fa-history fa-3x text-muted mb-3'></i>
                                        <p class='text-muted'>Belum ada riwayat panen</p>
                                      </td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Tanam -->
<div class="modal fade" id="tanamModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Catat Musim Tanam Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Lahan</label>
                        <select name="lahan_id" class="form-control" required>
                            <option value="">-- Pilih Lahan --</option>
                            <?php
                            $lahan = $conn->query("SELECT * FROM lahan");
                            while ($l = $lahan->fetch_assoc()) {
                                echo "<option value='{$l['id']}'>{$l['nama_lahan']} ({$l['luas_hektar']} Ha)</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pilih Bibit</label>
                        <select name="bibit_id" class="form-control" required>
                            <option value="">-- Pilih Bibit --</option>
                            <?php
                            $bibit = $conn->query("SELECT * FROM bibit");
                            while ($b = $bibit->fetch_assoc()) {
                                echo "<option value='{$b['id']}'>{$b['nama_bibit']} (Rp " . number_format($b['harga_per_kg']) . "/kg)</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Tanam</label>
                        <input type="date" name="tanggal_tanam" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Estimasi panen akan dihitung otomatis 100 hari dari tanggal tanam.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>