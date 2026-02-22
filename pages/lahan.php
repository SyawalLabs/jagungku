<?php
include '../config/database.php';
include '../includes/header.php';

// Proses tambah data
if (isset($_POST['simpan'])) {
    $nama = $_POST['nama_lahan'];
    $luas = $_POST['luas_hektar'];
    $lokasi = $_POST['lokasi'];
    $jenis = $_POST['jenis_tanah'];

    $sql = "INSERT INTO lahan (nama_lahan, luas_hektar, lokasi, jenis_tanah)
            VALUES ('$nama', '$luas', '$lokasi', '$jenis')";

    if ($conn->query($sql)) {
        echo "<div class='alert alert-success'>Data berhasil disimpan</div>";
    }
}

// Proses hapus
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $conn->query("DELETE FROM lahan WHERE id=$id");
    header('Location: lahan.php');
}
?>

<h1>🌾 Data Lahan</h1>

<!-- Form Tambah Lahan -->
<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h5>Tambah Lahan Baru</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" name="nama_lahan" class="form-control" placeholder="Nama Lahan" required>
                </div>
                <div class="col-md-2">
                    <input type="number" step="0.01" name="luas_hektar" class="form-control" placeholder="Luas (Hektar)" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="lokasi" class="form-control" placeholder="Lokasi" required>
                </div>
                <div class="col-md-2">
                    <select name="jenis_tanah" class="form-control">
                        <option value="Tanah Hitam">Tanah Hitam</option>
                        <option value="Tanah Merah">Tanah Merah</option>
                        <option value="Tanah Berpasir">Tanah Berpasir</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="simpan" class="btn btn-success">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Data Lahan -->
<div class="card">
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Lahan</th>
                    <th>Luas (Ha)</th>
                    <th>Lokasi</th>
                    <th>Jenis Tanah</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                $result = $conn->query("SELECT * FROM lahan");
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $no++ . "</td>";
                    echo "<td>" . $row['nama_lahan'] . "</td>";
                    echo "<td>" . $row['luas_hektar'] . "</td>";
                    echo "<td>" . $row['lokasi'] . "</td>";
                    echo "<td>" . $row['jenis_tanah'] . "</td>";
                    echo "<td>
                            <a href='?hapus=" . $row['id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Yakin?\")'>Hapus</a>
                          </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>