<?php
require_once '../config.php';
require_once 'admin_auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            $pdo = getDBConnection();
            
            // Prepare statement to prevent SQL injection
            $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, password, role FROM users WHERE email = ? AND role = 'admin' AND is_active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if user exists and password is correct
            if ($user && password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['logged_in'] = true;
                
                // Redirect to dashboard
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Invalid admin credentials.';
            }
        } catch (PDOException $e) {
            $error = 'Database error occurred. Please try again later.';
            error_log("Admin login error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Live in Oz</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #1a2a6c, #2c3e50);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            color: white;
            font-size: 28px;
            letter-spacing: 1px;
        }
        
        .logo p {
            color: #00eeff;
            margin-top: 5px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: white;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.15);
            color: white;
            font-size: 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #00eeff;
            background: rgba(255, 255, 255, 0.25);
        }
        
        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(90deg, #00eeff, #008cff);
            color: #000;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 238, 255, 0.4);
        }
        
        .error {
            background: rgba(255, 77, 77, 0.2);
            color: #ff4d4d;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 77, 77, 0.3);
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #00eeff;
            text-decoration: none;
            font-size: 14px;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>Admin Panel</h1>
            <p>Live in Oz - User Management</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">Admin Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your admin email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            
            <button type="submit" class="btn">Login to Admin Panel</button>
        </form>
        
        <div class="back-link">
            <a href="../index.html">← Back to Main Site</a>
        </div>
    </div>
</body>
</html>