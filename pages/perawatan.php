<?php
include '../config/database.php';
include '../includes/header.php';

$tanam_id = $_GET['tanam_id'] ?? 0;

// Proses simpan perawatan
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
        echo "<div class='alert alert-success'>Perawatan berhasil dicatat!</div>";
    }
}
?>

<h1>💊 Perawatan Tanaman</h1>

<!-- Pilih Lahan -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row">
            <div class="col-md-4">
                <select name="tanam_id" class="form-control" onchange="this.form.submit()">
                    <option value="">Pilih Lahan Aktif</option>
                    <?php
                    $sql = "SELECT m.*, l.nama_lahan 
                            FROM musim_tanam m
                            JOIN lahan l ON m.lahan_id = l.id
                            WHERE m.status='aktif'";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        $selected = ($tanam_id == $row['id']) ? 'selected' : '';
                        echo "<option value='{$row['id']}' $selected>{$row['nama_lahan']} - Tanam: " . date('d/m', strtotime($row['tanggal_tanam'])) . "</option>";
                    }
                    ?>
                </select>
            </div>
        </form>
    </div>
</div>

<?php if ($tanam_id > 0):
    // Ambil data tanam
    $tanam = $conn->query("SELECT m.*, l.nama_lahan FROM musim_tanam m JOIN lahan l ON m.lahan_id = l.id WHERE m.id=$tanam_id")->fetch_assoc();
    $umur = floor((time() - strtotime($tanam['tanggal_tanam'])) / (60 * 60 * 24));
?>

    <!-- Form Input Perawatan -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5>📝 Catat Perawatan - <?= $tanam['nama_lahan'] ?> (Umur: <?= $umur ?> hari)</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="musim_tanam_id" value="<?= $tanam_id ?>">

                <div class="row">
                    <div class="col-md-2">
                        <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="col-md-2">
                        <select name="jenis" class="form-control" required id="jenisPerawatan">
                            <option value="">Jenis</option>
                            <option value="pupuk">🌿 Pupuk</option>
                            <option value="obat">🧪 Obat Hama</option>
                            <option value="air">💧 Pengairan</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input type="text" name="item" class="form-control" placeholder="Nama (Urea/Decis)" required>
                    </div>

                    <div class="col-md-1">
                        <input type="text" name="dosis" class="form-control" placeholder="Dosis">
                    </div>

                    <div class="col-md-2">
                        <input type="number" name="biaya" class="form-control" placeholder="Biaya (Rp)" value="0">
                    </div>

                    <div class="col-md-3">
                        <div class="input-group">
                            <input type="text" name="keterangan" class="form-control" placeholder="Keterangan">
                            <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Riwayat Perawatan -->
    <div class="card">
        <div class="card-header">
            <h5>📋 Riwayat Perawatan</h5>
        </div>
        <div class="card-body">
            <table class="table">
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
                    $sql = "SELECT p.*, 
                        DATEDIFF(p.tanggal, '{$tanam['tanggal_tanam']}') as umur_saat_perawatan
                        FROM perawatan p
                        WHERE p.musim_tanam_id = $tanam_id
                        ORDER BY p.tanggal DESC";
                    $result = $conn->query($sql);

                    $total_biaya = 0;
                    while ($row = $result->fetch_assoc()) {
                        $total_biaya += $row['biaya'];

                        $icon = $row['jenis'] == 'pupuk' ? '🌿' : ($row['jenis'] == 'obat' ? '🧪' : '💧');
                        echo "<tr>";
                        echo "<td>" . date('d/m/Y', strtotime($row['tanggal'])) . "</td>";
                        echo "<td>$icon " . ucfirst($row['jenis']) . "</td>";
                        echo "<td>{$row['item']}</td>";
                        echo "<td>{$row['dosis']}</td>";
                        echo "<td>Rp " . number_format($row['biaya'], 0, ',', '.') . "</td>";
                        echo "<td>{$row['keterangan']}</td>";
                        echo "<td>{$row['umur_saat_perawatan']} hari</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr class="table-info">
                        <th colspan="4">TOTAL BIAYA PERAWATAN</th>
                        <th colspan="3">Rp <?= number_format($total_biaya, 0, ',', '.') ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

<?php endif; ?>

<?php include '../includes/footer.php'; ?>