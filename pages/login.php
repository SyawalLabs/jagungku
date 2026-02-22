<?php
session_start();
include '../config/database.php';

// Buat tabel users dulu
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    nama_lengkap VARCHAR(100),
    role ENUM('admin', 'petani', 'pengamat')
)");

// Insert default user
$conn->query("INSERT IGNORE INTO users (username, password, nama_lengkap, role) VALUES 
    ('admin', MD5('admin123'), 'Admin Utama', 'admin'),
    ('petani', MD5('petani123'), 'Petani Jagung', 'petani')");

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    $result = $conn->query("SELECT * FROM users WHERE username='$username' AND password='$password'");

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama'] = $user['nama_lengkap'];
        $_SESSION['role'] = $user['role'];

        header('Location: dashboard.php');
    } else {
        $error = "Username/password salah!";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login - Petani Jagung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-success">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white text-center">
                        <h3>🌽 Login Petani</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" name="login" class="btn btn-success w-100">Login</button>
                        </form>

                        <div class="mt-3 text-center">
                            <small>Demo: admin / admin123</small><br>
                            <small>petani / petani123</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>