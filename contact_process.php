<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get form data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    // Validate required fields
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all fields']);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
        exit;
    }

    // Validate field lengths
    if (strlen($name) > 255 || strlen($email) > 255 || strlen($subject) > 255) {
        echo json_encode(['success' => false, 'message' => 'Input data too long']);
        exit;
    }

    if (strlen($message) > 65535) { // TEXT field limit
        echo json_encode(['success' => false, 'message' => 'Message too long']);
        exit;
    }

    // Get database connection
    $pdo = getDBConnection();

    // Insert contact message
    $stmt = $pdo->prepare("
        INSERT INTO contact_messages (name, email, subject, message, status, created_at)
        VALUES (?, ?, ?, ?, 'unread', NOW())
    ");

    $result = $stmt->execute([$name, $email, $subject, $message]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Message sent successfully! We will get back to you soon.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send message. Please try again.']);
    }

} catch (Exception $e) {
    error_log("Contact form error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again later.']);
}
?>
