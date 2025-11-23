<?php
require_once 'config.php';

// Initialize response array
$response = array('success' => false, 'message' => '');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $email = isset($input['email']) ? trim($input['email']) : '';
    
    // Validate input
    if (empty($email)) {
        $response['message'] = 'Please enter your email address.';
    } else {
        try {
            // Get database connection
            $pdo = getDBConnection();
            
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id, first_name, last_name, email FROM users WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Generate reset token
                $resetToken = bin2hex(random_bytes(32));
                $resetTokenExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Update user with reset token
                $updateStmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
                $updateStmt->execute([$resetToken, $resetTokenExpiry, $user['id']]);
                
                // In a real application, you would send an email with the reset link
                // For this example, we'll just display the link
                $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.html?token=" . $resetToken;
                
                // For demo purposes, we're showing the link in the response
                // In a real application, you would send this via email
                $response['success'] = true;
                $response['message'] = 'Password reset link has been generated. In a real application, this would be sent to your email. For demo purposes, here is your reset link: ' . $resetLink;
                $response['reset_link'] = $resetLink; // This would not be included in a real application
            } else {
                // For security reasons, we don't reveal if the email exists or not
                $response['success'] = true;
                $response['message'] = 'If your email is registered with us, you will receive a password reset link shortly.';
            }
        } catch (PDOException $e) {
            $response['message'] = 'Database error occurred. Please try again later.';
            error_log("Forgot password error: " . $e->getMessage());
        } catch (Exception $e) {
            $response['message'] = 'An error occurred. Please try again later.';
            error_log("Forgot password error: " . $e->getMessage());
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>