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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Petani Jagung</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fc;
            overflow-x: hidden;
        }

        .main-content {
            margin-left: 260px;
            padding: 20px;
            min-height: 100vh;
            transition: margin-left 0.3s;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }

        /* Card styling */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid #eef2f7;
            border-radius: 12px 12px 0 0 !important;
            padding: 15px 20px;
            font-weight: 600;
        }

        .btn-success {
            background-color: #2c5e2e;
            border-color: #2c5e2e;
        }

        .btn-success:hover {
            background-color: #1e4a1e;
            border-color: #1e4a1e;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
            padding: 5px 10px;
            border-radius: 20px;
        }

        .table thead th {
            border-bottom: 2px solid #dee2e6;
            color: #495057;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="main-content">