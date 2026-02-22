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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #2c5e2e;
            --primary-dark: #1e4a1e;
            --primary-light: #4a7b4c;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .login-container {
            max-width: 420px;
            width: 100%;
        }

        .login-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .login-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .login-header p {
            opacity: 0.9;
            margin: 0;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-floating {
            margin-bottom: 20px;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 12px 16px;
            height: auto;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(44, 94, 46, 0.2);
        }

        .btn-login {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            font-size: 1.1rem;
            width: 100%;
            transition: all 0.3s;
            margin-top: 20px;
        }

        .btn-login:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(44, 94, 46, 0.3);
        }

        .demo-credentials {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-top: 30px;
            border: 2px dashed #dee2e6;
        }

        .demo-credentials h6 {
            color: var(--gray-600);
            margin-bottom: 15px;
        }

        .cred-item {
            background: white;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
        }

        .cred-item i {
            color: var(--primary);
            width: 20px;
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
        }

        .footer-text {
            text-align: center;
            margin-top: 20px;
            color: var(--gray-600);
            font-size: 0.9rem;
        }

        .footer-text a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
    </style>
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
                    <div class="cred-item">
                        <i class="fas fa-user"></i>
                        <span><strong>Petani:</strong> petani / petani123</span>
                    </div>
                </div>

                <div class="footer-text">
                    <p>© <?= date('Y') ?> syawallabs.id | All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>