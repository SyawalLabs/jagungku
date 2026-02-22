<?php
include '../config/database.php';
include '../includes/header.php';

// Proses simpan panen
if (isset($_POST['simpan'])) {
    $musim_tanam_id = $_POST['musim_tanam_id'];
    $tanggal_panen = $_POST['tanggal_panen'];
    $hasil_kg = $_POST['hasil_kg'];
    $harga_jual = $_POST['harga_jual'];
    $pembeli = $_POST['pembeli'];
    $total = $hasil_kg * $harga_jual;

    // Cek apakah sudah pernah panen
    $cek = $conn->query("SELECT * FROM panen WHERE musim_tanam_id=$musim_tanam_id");

    if ($cek->num_rows > 0) {
        // Update
        $conn->query("UPDATE panen SET tanggal_panen='$tanggal_panen', hasil_kg='$hasil_kg', harga_jual='$harga_jual', pembeli='$pembeli', total_pendapatan='$total' WHERE musim_tanam_id=$musim_tanam_id");
    } else {
        // Insert
        $conn->query("INSERT INTO panen (musim_tanam_id, tanggal_panen, hasil_kg, harga_jual, pembeli, total_pendapatan) VALUES ('$musim_tanam_id', '$tanggal_panen', '$hasil_kg', '$harga_jual', '$pembeli', '$total')");
    }

    // Update status musim tanam jadi selesai
    $conn->query("UPDATE musim_tanam SET status='selesai' WHERE id=$musim_tanam_id");

    echo "<div class='alert alert-success'>Data panen berhasil disimpan!</div>";
}
?>

<h1>🌽 Catatan Panen</h1>

<!-- Form Input Panen -->
<div class="card mb-4">
    <div class="card-header bg-warning">
        <h5>📝 Input Hasil Panen</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="row">
                <div class="col-md-3">
                    <select name="musim_tanam_id" class="form-control" required>
                        <option value="">Pilih Lahan Siap Panen</option>
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
                <div class="col-md-2">
                    <input type="date" name="tanggal_panen" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-1">
                    <input type="number" name="hasil_kg" class="form-control" placeholder="Kg" required>
                </div>
                <div class="col-md-2">
                    <input type="number" name="harga_jual" class="form-control" placeholder="Harga/kg" required>
                </div>
                <div class="col-md-2">
                    <input type="text" name="pembeli" class="form-control" placeholder="Pembeli">
                </div>
                <div class="col-md-2">
                    <button type="submit" name="simpan" class="btn btn-warning">Simpan Panen</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Riwayat Panen -->
<div class="card">
    <div class="card-header bg-success text-white">
        <h5>📊 Riwayat Panen</h5>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Lahan</th>
                    <th>Hasil (kg)</th>
                    <th>Harga/kg</th>
                    <th>Total</th>
                    <th>Pembeli</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT p.*, l.nama_lahan 
                        FROM panen p
                        JOIN musim_tanam m ON p.musim_tanam_id = m.id
                        JOIN lahan l ON m.lahan_id = l.id
                        ORDER BY p.tanggal_panen DESC";
                $result = $conn->query($sql);

                $total_kg = 0;
                $total_rp = 0;

                while ($row = $result->fetch_assoc()) {
                    $total_kg += $row['hasil_kg'];
                    $total_rp += $row['total_pendapatan'];

                    echo "<tr>";
                    echo "<td>" . date('d/m/Y', strtotime($row['tanggal_panen'])) . "</td>";
                    echo "<td>{$row['nama_lahan']}</td>";
                    echo "<td>" . number_format($row['hasil_kg'], 0, ',', '.') . " kg</td>";
                    echo "<td>Rp " . number_format($row['harga_jual'], 0, ',', '.') . "</td>";
                    echo "<td>Rp " . number_format($row['total_pendapatan'], 0, ',', '.') . "</td>";
                    echo "<td>{$row['pembeli']}</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
            <tfoot>
                <tr class="table-success">
                    <th colspan="2">TOTAL</th>
                    <th><?= number_format($total_kg, 0, ',', '.') ?> kg</th>
                    <th></th>
                    <th>Rp <?= number_format($total_rp, 0, ',', '.') ?></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>