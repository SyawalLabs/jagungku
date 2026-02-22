<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) != 'login.php') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>🌽 JagungKu - Dashboard Petani Modern</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom Modern CSS -->
    <link rel="stylesheet" href="../assets/css/modern.css">
</head>

<body>

    <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Hamburger Button -->
        <button class="hamburger-btn" id="hamburgerBtn">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">