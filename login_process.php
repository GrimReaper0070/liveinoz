<?php
session_start();
require_once 'config.php';

// Initialize response array
$response = array('success' => false, 'message' => '');

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Validate input
    if (empty($email) || empty($password)) {
        $response['message'] = 'Please fill in all fields.';
    } else {
        try {
            // Get database connection
            $pdo = getDBConnection();
            
            // First check if user exists with correct password (regardless of verification status)
            $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, password, role, is_active, is_verified, user_type FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check if user exists and password is correct
            if ($user && password_verify($password, $user['password'])) {
                // Check verification and active status
                if ($user['is_active'] == 1 && $user['is_verified'] == 1) {
                    // Login successful - user is verified and active
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['logged_in'] = true;

                    $response['success'] = true;
                    $response['message'] = 'Login successful! Redirecting...';
                } elseif ($user['is_verified'] == 0) {
                    // Account not verified
                    $response['message'] = 'Your account is pending verification. Please wait for admin approval.';
                } elseif ($user['is_active'] == 0) {
                    // Account deactivated
                    $response['message'] = 'Your account has been deactivated. Please contact support.';
                } else {
                    // Other issues
                    $response['message'] = 'Account access restricted. Please contact support.';
                }
            } else {
                // Login failed - invalid credentials
                $response['message'] = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $response['message'] = 'Database error occurred. Please try again later.';
            error_log("Login error: " . $e->getMessage());
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
