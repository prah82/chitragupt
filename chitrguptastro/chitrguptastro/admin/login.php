<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require './includes/db.php';

if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require './includes/db.php';

    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $user);
    $stmt->execute();

    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

   if ($admin && md5($pass) === $admin['password']) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];

        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
        }
        .login-header {
            background: #1a202c;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .login-header h3 {
            margin: 0;
            font-weight: 600;
        }
        .login-body {
            background: #fff;
            padding: 40px 30px;
        }
        .btn-login {
            background-color: #667eea;
            border: none;
            padding: 10px;
            font-weight: 600;
        }
        .btn-login:hover {
            background-color: #5a6cd6;
        }
        .input-group-text {
            background: transparent;
            border-right: none;
        }
        .form-control {
            border-left: none;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #dee2e6;
        }
        .input-group:focus-within {
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
            border-radius: 0.375rem;
        }
        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control {
            border-color: #86b7fe;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <h3>Admin Login</h3>
            <p class="text-white-50 mb-0 mt-2">Sign in to manage your system</p>
        </div>
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label for="username" class="form-label text-muted fw-semibold">Username</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text"><i class="bi bi-person text-muted"></i></span>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label text-muted fw-semibold">Password</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text"><i class="bi bi-lock text-muted"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-login w-100 text-white shadow-sm mt-2">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
