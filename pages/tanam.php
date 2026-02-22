<?php
include '../config/database.php';
include '../includes/header.php';

// Proses tambah tanam
if (isset($_POST['simpan'])) {
    $lahan_id = $_POST['lahan_id'];
    $bibit_id = $_POST['bibit_id'];
    $tanggal_tanam = $_POST['tanggal_tanam'];
    $estimasi = date('Y-m-d', strtotime($tanggal_tanam . ' +100 days'));

    $sql = "INSERT INTO musim_tanam (lahan_id, bibit_id, tanggal_tanam, estimasi_panen, status) 
            VALUES ('$lahan_id', '$bibit_id', '$tanggal_tanam', '$estimasi', 'aktif')";

    if ($conn->query($sql)) {
        echo "<div class='alert alert-success'>Musim tanam berhasil dicatat!</div>";
    }
}

// Proses hapus
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $conn->query("DELETE FROM musim_tanam WHERE id=$id");
    header('Location: tanam.php');
}

// Proses panen (ubah status)
if (isset($_GET['panen'])) {
    $id = $_GET['panen'];
    $conn->query("UPDATE musim_tanam SET status='selesai' WHERE id=$id");
    header('Location: tanam.php');
}
?>

<h1>🌱 Musim Tanam</h1>

<!-- Form Tambah Tanam -->
<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h5>Catat Tanam Baru</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="row">
                <div class="col-md-3">
                    <select name="lahan_id" class="form-control" required>
                        <option value="">Pilih Lahan</option>
                        <?php
                        $lahan = $conn->query("SELECT * FROM lahan");
                        while ($l = $lahan->fetch_assoc()) {
                            echo "<option value='{$l['id']}'>{$l['nama_lahan']} ({$l['luas_hektar']} Ha)</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="bibit_id" class="form-control" required>
                        <option value="">Pilih Bibit</option>
                        <?php
                        $bibit = $conn->query("SELECT * FROM bibit");
                        while ($b = $bibit->fetch_assoc()) {
                            echo "<option value='{$b['id']}'>{$b['nama_bibit']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="tanggal_tanam" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" name="simpan" class="btn btn-success">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Tanam Aktif -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5>🌿 Tanam Aktif</h5>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
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

                while ($row = $result->fetch_assoc()) {
                    $umur = $row['umur'];
                    $progress = min(100, round(($umur / 100) * 100));

                    // Warna progress
                    $warna = 'bg-success';
                    if ($progress > 80) $warna = 'bg-warning';
                    if ($progress >= 100) $warna = 'bg-danger';

                    echo "<tr>";
                    echo "<td>{$row['nama_lahan']}</td>";
                    echo "<td>{$row['nama_bibit']}</td>";
                    echo "<td>" . date('d/m/Y', strtotime($row['tanggal_tanam'])) . "</td>";
                    echo "<td>{$umur} hari</td>";
                    echo "<td>" . date('d/m/Y', strtotime($row['estimasi_panen'])) . "</td>";
                    echo "<td width='200'>
                            <div class='progress'>
                                <div class='progress-bar $warna' style='width: {$progress}%'>{$progress}%</div>
                            </div>
                          </td>";
                    echo "<td>
                            <a href='perawatan.php?tanam_id={$row['id']}' class='btn btn-info btn-sm'>Rawat</a>
                            <a href='?panen={$row['id']}' class='btn btn-success btn-sm' onclick='return confirm(\"Tandai sudah panen?\")'>Panen</a>
                            <a href='?hapus={$row['id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Yakin hapus?\")'>Hapus</a>
                          </td>";
                    echo "</tr>";
                }

                if ($result->num_rows == 0) {
                    echo "<tr><td colspan='7' class='text-center'>Belum ada tanam aktif</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Riwayat Tanam Selesai -->
<div class="card">
    <div class="card-header bg-secondary text-white">
        <h5>📜 Riwayat Tanam (Selesai)</h5>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Lahan</th>
                    <th>Bibit</th>
                    <th>Tgl Tanam</th>
                    <th>Tgl Panen</th>
                    <th>Hasil (kg)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT m.*, l.nama_lahan, b.nama_bibit, p.hasil_kg, p.tanggal_panen
                        FROM musim_tanam m
                        JOIN lahan l ON m.lahan_id = l.id
                        JOIN bibit b ON m.bibit_id = b.id
                        LEFT JOIN panen p ON m.id = p.musim_tanam_id
                        WHERE m.status='selesai'
                        ORDER BY m.tanggal_tanam DESC";
                $result = $conn->query($sql);

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$row['nama_lahan']}</td>";
                    echo "<td>{$row['nama_bibit']}</td>";
                    echo "<td>" . date('d/m/Y', strtotime($row['tanggal_tanam'])) . "</td>";
                    echo "<td>" . ($row['tanggal_panen'] ? date('d/m/Y', strtotime($row['tanggal_panen'])) : '-') . "</td>";
                    echo "<td>" . ($row['hasil_kg'] ?? '-') . " kg</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>