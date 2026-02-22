<?php
include '../config/database.php';

if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama_lahan'];
    $luas = $_POST['luas_hektar'];
    $lokasi = $_POST['lokasi'];
    $jenis = $_POST['jenis_tanah'];

    $sql = "UPDATE lahan SET 
            nama_lahan='$nama', 
            luas_hektar='$luas', 
            lokasi='$lokasi', 
            jenis_tanah='$jenis' 
            WHERE id=$id";

    if ($conn->query($sql)) {
        echo "<script>alert('Data lahan berhasil diupdate!'); window.location='lahan.php';</script>";
    } else {
        echo "<script>alert('Gagal update data!'); window.location='lahan.php';</script>";
    }
} else {
    header('Location: lahan.php');
}
