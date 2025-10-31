<?php
require_once '../config.php';

// If already logged in, redirect to dashboard
if(isset($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if(!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password';
        }
    } else {
        $error = 'Please fill in all fields';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Portfolio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #332D3C 0%, #4A403A 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Animated background elements */
        body::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(187, 134, 252, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            top: -250px;
            right: -250px;
            animation: float 20s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(74, 64, 58, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            bottom: -200px;
            left: -200px;
            animation: float 15s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0);
            }
            50% {
                transform: translateY(-20px) translateX(20px);
            }
        }
        
        .login-container {
            background: #1E1E1E;
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.5);
            width: 90%;
            max-width: 420px;
            border: 1px solid #2A2A2A;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header .icon-wrapper {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #BB86FC 0%, #332D3C 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 8px 20px rgba(187, 134, 252, 0.3);
        }

        .login-header .icon-wrapper i {
            font-size: 2.5rem;
            color: white;
        }
        
        .login-header h1 {
            color: #E0E0E0;
            font-size: 2rem;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .login-header p {
            color: #E0E0E0;
            opacity: 0.7;
            font-size: 0.95rem;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #E0E0E0;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-group label i {
            margin-right: 8px;
            color: #BB86FC;
        }

        .input-wrapper {
            position: relative;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #2A2A2A;
            border-radius: 10px;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            background-color: #121212;
            color: #E0E0E0;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #BB86FC;
            box-shadow: 0 0 0 3px rgba(187, 134, 252, 0.1);
            background-color: #1A1A1A;
        }

        .form-group input::placeholder {
            color: #666;
        }
        
        .error-message {
            background: #2D1B1B;
            color: #CF6679;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            border-left: 4px solid #CF6679;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .error-message i {
            font-size: 1.2rem;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background-color: #BB86FC;
            color: #121212;
            border: 2px solid #BB86FC;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
        }
        
        .btn-login:hover {
            background-color: transparent;
            color: #BB86FC;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(187, 134, 252, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login i {
            margin-right: 8px;
        }
        
        .back-link {
            text-align: center;
            margin-top: 25px;
        }
        
        .back-link a {
            color: #BB86FC;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-link a:hover {
            color: #E0E0E0;
        }

        .back-link a i {
            transition: transform 0.3s;
        }

        .back-link a:hover i {
            transform: translateX(-3px);
        }

        /* Loading animation */
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .btn-login.loading::after {
            content: "";
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid transparent;
            border-top-color: currentColor;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                padding: 40px 30px;
            }

            .login-header h1 {
                font-size: 1.75rem;
            }

            .login-header .icon-wrapper {
                width: 70px;
                height: 70px;
            }

            .login-header .icon-wrapper i {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="icon-wrapper">
                <i class="fas fa-user-shield"></i>
            </div>
            <h1>Admin Login</h1>
            <p>Enter your credentials to access the admin panel</p>
        </div>
        
        <?php if($error): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">
                    <i class="fas fa-user"></i> Username
                </label>
                <div class="input-wrapper">
                    <input type="text" id="username" name="username" placeholder="Enter your username" required autofocus>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Password
                </label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
            </div>
            
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
        
        <div class="back-link">
            <a href="../index.php">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Portfolio</span>
            </a>
        </div>
    </div>
</body>
</html>