<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar" id="sidebar">
    <!-- Brand -->
    <div class="sidebar-header">
        <h4 class="text-white">JagungKu</h4>
        <small>Petani Cerdas • Modern Farming</small>
    </div>

    <!-- User Info -->
    <div class="user-info">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar">
                <?= strtoupper(substr($_SESSION['nama'] ?? 'P', 0, 1)) ?>
            </div>
            <div>
                <div class="fw-bold text-white"><?= $_SESSION['nama'] ?? 'Petani' ?></div>
                <small class="text-white-50">
                    <i class="fas fa-circle me-1" style="color: #28a745; font-size: 8px;"></i>
                    <?= $_SESSION['role'] ?? 'Petani' ?>
                </small>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <ul class="nav flex-column">
        <li class="nav-item">
            <small class="text-white-50 px-3 py-2 d-block">UTAMA</small>
        </li>

        <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
                <i class="bi bi-grid-fill"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="nav-item mt-3">
            <small class="text-white-50 px-3 py-2 d-block">MASTER DATA</small>
        </li>

        <li class="nav-item">
            <a href="lahan.php" class="nav-link <?= ($current_page == 'lahan.php') ? 'active' : '' ?>">
                <i class="bi bi-database-fill"></i>
                <span>Data Lahan</span>
            </a>
        </li>

        <li class="nav-item mt-3">
            <small class="text-white-50 px-3 py-2 d-block">TRANSAKSI</small>
        </li>

        <li class="nav-item">
            <a href="tanam.php" class="nav-link <?= ($current_page == 'tanam.php') ? 'active' : '' ?>">
                <i class="bi bi-calendar-event-fill"></i>
                <span>Musim Tanam</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="perawatan.php" class="nav-link <?= ($current_page == 'perawatan.php') ? 'active' : '' ?>">
                <i class="bi bi-clipboard-check-fill"></i>
                <span>Perawatan</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="panen.php" class="nav-link <?= ($current_page == 'panen.php') ? 'active' : '' ?>">
                <i class="bi bi-basket-fill"></i>
                <span>Panen</span>
            </a>
        </li>

        <li class="nav-item mt-3">
            <small class="text-white-50 px-3 py-2 d-block">LAPORAN</small>
        </li>

        <li class="nav-item">
            <a href="keuangan.php" class="nav-link <?= ($current_page == 'keuangan.php') ? 'active' : '' ?>">
                <i class="bi bi-cash-stack"></i>
                <span>Keuangan & Produksi</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="laporan.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : '' ?>">
                <i class="bi bi-bar-chart-fill"></i>
                <span>Laporan</span>
            </a>
        </li>

        <li class="nav-item mt-4">
            <hr class="mx-3" style="border-color: rgba(255,255,255,0.1);">
        </li>

        <li class="nav-item">
            <a href="logout.php" class="nav-link text-white" onclick="return confirm('Yakin ingin logout?')">
                <i class="bi bi-box-arrow-left"></i>
                <span>Logout</span>
            </a>
        </li>


    </ul>

    <div class="sidebar-footer text-center">
        <p class="mb-1">© <?= date('Y') ?> syawallabs.id</p>
        <p class="mb-0">v2.0.0 • Modern Farming</p>
        <hr class="my-2" style="border-color: rgba(255,255,255,0.1);">

    </div>
</div>