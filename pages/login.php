<?php
session_start();
include '../config/database.php';

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
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - JagungKu Modern Farming</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="../assets/bootstrap-icons/bootstrap-icons.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../assets/css/login.css">

</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>JagungKu</h1>
                <p>Modern Farming Dashboard</p>
            </div>

            <div class="login-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-floating mb-3">
                        <input type="text" name="username" class="form-control" id="username" placeholder="Username" required>
                        <label for="username">
                            <i class="fas fa-user me-2 text-muted"></i>Username
                        </label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
                        <label for="password">
                            <i class="fas fa-lock me-2 text-muted"></i>Password
                        </label>
                    </div>

                    <button type="submit" name="login" class="btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Masuk ke Dashboard
                    </button>
                </form>

                <div class="demo-credentials">
                    <h6><i class="fas fa-info-circle me-2"></i>Demo Credentials</h6>
                    <div class="cred-item">
                        <i class="fas fa-user"></i>
                        <span><strong>Admin:</strong> admin / admin123</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>