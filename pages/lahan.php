<?php
include '../config/database.php';
include '../includes/header.php';

// Process form
if (isset($_POST['simpan'])) {
    $nama = $_POST['nama_lahan'];
    $luas = $_POST['luas_hektar'];
    $lokasi = $_POST['lokasi'];
    $jenis = $_POST['jenis_tanah'];

    $sql = "INSERT INTO lahan (nama_lahan, luas_hektar, lokasi, jenis_tanah) 
            VALUES ('$nama', '$luas', '$lokasi', '$jenis')";

    if ($conn->query($sql)) {
        echo "<script>alert('Data lahan berhasil disimpan!'); window.location='lahan.php';</script>";
    }
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $conn->query("DELETE FROM lahan WHERE id=$id");
    echo "<script>alert('Data lahan berhasil dihapus!'); window.location='lahan.php';</script>";
}
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1>
            <i class="fas fa-map-marked-alt"></i>
            Data Lahan
        </h1>
        <p class="text-muted mt-2 mb-0">Kelola data lahan pertanian jagung Anda</p>
    </div>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tambahLahanModal">
        <i class="fas fa-plus me-2"></i>Tambah Lahan Baru
    </button>
</div>

<!-- Lahan List -->
<div class="row g-4">
    <?php
    $result = $conn->query("SELECT * FROM lahan ORDER BY id DESC");

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Hitung jumlah tanam di lahan ini
            $tanam_count = $conn->query("SELECT COUNT(*) as total FROM musim_tanam WHERE lahan_id = {$row['id']}")->fetch_assoc()['total'];

            // Hitung total panen
            $panen_total = $conn->query("SELECT SUM(hasil_kg) as total FROM panen p 
                                        JOIN musim_tanam m ON p.musim_tanam_id = m.id 
                                        WHERE m.lahan_id = {$row['id']}")->fetch_assoc()['total'] ?? 0;
    ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title mb-1">
                                    <i class="fas fa-leaf text-success me-2"></i>
                                    <?= $row['nama_lahan'] ?>
                                </h5>
                                <small class="text-muted">
                                    <i class="fas fa-map-pin me-1"></i> <?= $row['lokasi'] ?>
                                </small>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">
                                            <i class="fas fa-edit me-2"></i>Edit
                                        </a></li>
                                    <li><a class="dropdown-item text-danger" href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus lahan ini?')">
                                            <i class="fas fa-trash me-2"></i>Hapus
                                        </a></li>
                                </ul>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <div class="bg-light p-3 rounded text-center">
                                    <small class="text-muted d-block">Luas</small>
                                    <span class="h5 mb-0 fw-bold"><?= $row['luas_hektar'] ?> Ha</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light p-3 rounded text-center">
                                    <small class="text-muted d-block">Jenis Tanah</small>
                                    <span class="h6 mb-0"><?= $row['jenis_tanah'] ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between text-center py-2 border-top">
                            <div>
                                <small class="text-muted d-block">Musim Tanam</small>
                                <span class="fw-bold"><?= $tanam_count ?>x</span>
                            </div>
                            <div>
                                <small class="text-muted d-block">Total Panen</small>
                                <span class="fw-bold"><?= number_format($panen_total) ?> kg</span>
                            </div>
                            <div>
                                <small class="text-muted d-block">Produktivitas</small>
                                <span class="fw-bold"><?= $row['luas_hektar'] > 0 ? round($panen_total / $row['luas_hektar']) : 0 ?> kg/Ha</span>
                            </div>
                        </div>

                        <div class="mt-3">
                            <a href="tanam.php?lahan_id=<?= $row['id'] ?>" class="btn btn-success w-100">
                                <i class="fas fa-seedling me-2"></i>Mulai Tanam
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Modal -->
            <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Lahan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="lahan_edit.php">
                            <div class="modal-body">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <div class="mb-3">
                                    <label class="form-label">Nama Lahan</label>
                                    <input type="text" name="nama_lahan" class="form-control" value="<?= $row['nama_lahan'] ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Luas (Hektar)</label>
                                    <input type="number" step="0.01" name="luas_hektar" class="form-control" value="<?= $row['luas_hektar'] ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Lokasi</label>
                                    <input type="text" name="lokasi" class="form-control" value="<?= $row['lokasi'] ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Jenis Tanah</label>
                                    <select name="jenis_tanah" class="form-control">
                                        <option value="Tanah Hitam" <?= $row['jenis_tanah'] == 'Tanah Hitam' ? 'selected' : '' ?>>Tanah Hitam</option>
                                        <option value="Tanah Merah" <?= $row['jenis_tanah'] == 'Tanah Merah' ? 'selected' : '' ?>>Tanah Merah</option>
                                        <option value="Tanah Berpasir" <?= $row['jenis_tanah'] == 'Tanah Berpasir' ? 'selected' : '' ?>>Tanah Berpasir</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" name="edit" class="btn btn-success">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php
        }
    } else {
        ?>
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-map-marked-alt fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">Belum Ada Data Lahan</h4>
                    <p class="mb-4">Mulai dengan menambahkan lahan pertama Anda</p>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tambahLahanModal">
                        <i class="fas fa-plus me-2"></i>Tambah Lahan Sekarang
                    </button>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

<!-- Add Modal -->
<div class="modal fade" id="tambahLahanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Lahan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lahan</label>
                        <input type="text" name="nama_lahan" class="form-control" placeholder="Contoh: Lahan A, Sawah Timur" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Luas (Hektar)</label>
                        <input type="number" step="0.01" name="luas_hektar" class="form-control" placeholder="Contoh: 2.5" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi</label>
                        <input type="text" name="lokasi" class="form-control" placeholder="Contoh: Desa Sukamaju" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Tanah</label>
                        <select name="jenis_tanah" class="form-control">
                            <option value="Tanah Hitam">Tanah Hitam</option>
                            <option value="Tanah Merah">Tanah Merah</option>
                            <option value="Tanah Berpasir">Tanah Berpasir</option>
                        </select>
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