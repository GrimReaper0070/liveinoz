<?php
require_once 'config.php';

// Initialize response array
$response = array('success' => false, 'message' => '');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $token = isset($input['token']) ? trim($input['token']) : '';
    $password = isset($input['password']) ? $input['password'] : '';
    $confirmPassword = isset($input['confirmPassword']) ? $input['confirmPassword'] : '';
    
    // Validate input
    if (empty($token) || empty($password) || empty($confirmPassword)) {
        $response['message'] = 'Please fill in all fields.';
    } elseif ($password !== $confirmPassword) {
        $response['message'] = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $response['message'] = 'Password must be at least 6 characters long.';
    } else {
        try {
            // Get database connection
            $pdo = getDBConnection();
            
            // Check if token is valid and not expired
            $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, reset_token_expiry FROM users WHERE reset_token = ? AND is_active = 1");
            $stmt->execute([$token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Check if token is expired
                $currentDateTime = new DateTime();
                $tokenExpiry = new DateTime($user['reset_token_expiry']);
                
                if ($currentDateTime < $tokenExpiry) {
                    // Token is valid, update password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Update user password and clear reset token
                    $updateStmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
                    $updateStmt->execute([$hashedPassword, $user['id']]);
                    
                    $response['success'] = true;
                    $response['message'] = 'Password has been reset successfully. You can now login with your new password.';
                } else {
                    $response['message'] = 'Password reset link has expired. Please request a new one.';
                }
            } else {
                $response['message'] = 'Invalid password reset link. Please request a new one.';
            }
        } catch (PDOException $e) {
            $response['message'] = 'Database error occurred. Please try again later.';
            error_log("Reset password error: " . $e->getMessage());
        } catch (Exception $e) {
            $response['message'] = 'An error occurred. Please try again later.';
            error_log("Reset password error: " . $e->getMessage());
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>