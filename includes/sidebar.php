<?php
// Ambil nama halaman saat ini untuk active class
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div class="sidebar-header text-center py-4">
        <h4 class="text-white mb-0">🌽 JagungKu</h4>
        <small class="text-white-50">Petani Cerdas</small>
    </div>

    <div class="user-info bg-success-dark p-3 mb-3">
        <div class="d-flex align-items-center">
            <div class="avatar bg-white text-success rounded-circle p-2 me-2">
                <span class="fw-bold"><?= strtoupper(substr($_SESSION['nama'] ?? 'P', 0, 1)) ?></span>
            </div>
            <div class="text-white">
                <div class="fw-bold"><?= $_SESSION['nama'] ?? 'Petani' ?></div>
                <small><?= $_SESSION['role'] ?? 'petani' ?></small>
            </div>
        </div>
    </div>

    <ul class="nav nav-pills flex-column mb-auto">
        <!-- Dashboard -->
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link text-white <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
                <span class="me-2">📊</span> Dashboard
            </a>
        </li>

        <!-- Menu Master Data -->
        <li class="nav-item mt-3">
            <small class="text-white-50 px-3">MASTER DATA</small>
        </li>

        <li class="nav-item">
            <a href="lahan.php" class="nav-link text-white <?= ($current_page == 'lahan.php') ? 'active' : '' ?>">
                <span class="me-2">🌾</span> Data Lahan
            </a>
        </li>

        <!-- Menu Transaksi -->
        <li class="nav-item mt-3">
            <small class="text-white-50 px-3">TRANSAKSI</small>
        </li>

        <li class="nav-item">
            <a href="tanam.php" class="nav-link text-white <?= ($current_page == 'tanam.php') ? 'active' : '' ?>">
                <span class="me-2">🌱</span> Musim Tanam
            </a>
        </li>

        <li class="nav-item">
            <a href="perawatan.php" class="nav-link text-white <?= ($current_page == 'perawatan.php') ? 'active' : '' ?>">
                <span class="me-2">💊</span> Perawatan
            </a>
        </li>

        <li class="nav-item">
            <a href="panen.php" class="nav-link text-white <?= ($current_page == 'panen.php') ? 'active' : '' ?>">
                <span class="me-2">🌽</span> Panen
            </a>
        </li>

        <!-- Menu Laporan -->
        <li class="nav-item mt-3">
            <small class="text-white-50 px-3">LAPORAN</small>
        </li>

        <li class="nav-item">
            <a href="laporan.php" class="nav-link text-white <?= ($current_page == 'laporan.php') ? 'active' : '' ?>">
                <span class="me-2">💰</span> Keuangan & Produksi
            </a>
        </li>

        <!-- Menu Lainnya -->
        <li class="nav-item mt-4">
            <hr class="border-white-50">
        </li>

        <li class="nav-item">
            <a href="logout.php" class="nav-link text-white" onclick="return confirm('Yakin logout?')">
                <span class="me-2">🚪</span> Logout
            </a>
        </li>
    </ul>

    <div class="sidebar-footer text-center text-white-50 small py-3">
        <p class="mb-0">© 2025 JagungKu</p>
        <p class="mb-0">v1.0.0</p>
    </div>
</div>

<style>
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 260px;
        background: linear-gradient(180deg, #2c5e2e 0%, #1e4a1e 100%);
        color: white;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    }

    .sidebar .nav-link {
        padding: 12px 20px;
        margin: 4px 8px;
        border-radius: 8px;
        transition: all 0.3s;
    }

    .sidebar .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.2);
        transform: translateX(5px);
    }

    .sidebar .nav-link.active {
        background-color: #ffc107;
        color: #1e4a1e !important;
        font-weight: bold;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .sidebar .nav-link.active span {
        color: #1e4a1e;
    }

    .sidebar .avatar {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .bg-success-dark {
        background-color: #1e4a1e;
    }

    .text-white-50 {
        color: rgba(255, 255, 255, 0.7) !important;
    }

    .border-white-50 {
        border-color: rgba(255, 255, 255, 0.2) !important;
    }

    /* Scrollbar styling */
    .sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: #1e4a1e;
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: #ffc107;
        border-radius: 3px;
    }

    /* Konten utama bergeser ke kanan */
    .main-content {
        margin-left: 260px;
        padding: 20px;
        min-height: 100vh;
        background-color: #f8f9fc;
    }

    @media (max-width: 768px) {
        .sidebar {
            width: 0;
            transform: translateX(-100%);
        }

        .main-content {
            margin-left: 0;
        }
    }
</style>