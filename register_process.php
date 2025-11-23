<?php
// Start session first - this is crucial for session variables to work
session_start();

require_once 'config.php';

// Function to handle profile picture upload and resizing
function handleProfilePictureUpload($file) {
    error_log("Profile picture upload started. File info: " . json_encode($file));

    // Check if GD extension is available
    if (!extension_loaded('gd')) {
        error_log("GD extension not available. Available extensions: " . implode(', ', get_loaded_extensions()));
        // For now, let's try to save the file without resizing
        $uploadDir = 'uploads/profile_pictures/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                error_log("Failed to create upload directory: " . $uploadDir);
                return false;
            }
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('profile_', true) . '.' . $extension;
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            error_log("File saved without resizing: " . $filepath);
            return $filepath;
        } else {
            error_log("Failed to save file without resizing");
            return false;
        }
    }

    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        error_log("Invalid file type: " . $file['type']);
        return false;
    }

    // Validate file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        error_log("File too large: " . $file['size']);
        return false;
    }

    // Check if file was actually uploaded
    if (!is_uploaded_file($file['tmp_name'])) {
        error_log("File was not uploaded properly");
        return false;
    }

    // Create uploads directory if it doesn't exist
    $uploadDir = 'uploads/profile_pictures/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            error_log("Failed to create upload directory: " . $uploadDir);
            return false;
        }
    }

    // Check if directory is writable
    if (!is_writable($uploadDir)) {
        error_log("Upload directory is not writable: " . $uploadDir);
        return false;
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('profile_', true) . '.' . $extension;
    $filepath = $uploadDir . $filename;

    error_log("Attempting to resize image to: " . $filepath);

    // Resize and save image
    if (resizeImage($file['tmp_name'], $filepath, 34, 34)) {
        error_log("Image resize successful: " . $filepath);
        return $filepath;
    }

    error_log("Image resize failed");
    return false;
}

// Function to resize image to specified dimensions
function resizeImage($sourcePath, $destPath, $width, $height) {
    // Get image info
    $imageInfo = getimagesize($sourcePath);
    if (!$imageInfo) {
        return false;
    }

    $mime = $imageInfo['mime'];

    // Create image resource based on type
    switch ($mime) {
        case 'image/jpeg':
            $sourceImage = imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $sourceImage = imagecreatefrompng($sourcePath);
            break;
        case 'image/gif':
            $sourceImage = imagecreatefromgif($sourcePath);
            break;
        default:
            return false;
    }

    if (!$sourceImage) {
        return false;
    }

    // Create new image with desired dimensions
    $resizedImage = imagecreatetruecolor($width, $height);

    // Preserve transparency for PNG/GIF
    if ($mime === 'image/png' || $mime === 'image/gif') {
        imagecolortransparent($resizedImage, imagecolorallocatealpha($resizedImage, 0, 0, 0, 127));
        imagealphablending($resizedImage, false);
        imagesavealpha($resizedImage, true);
    }

    // Resize the image
    imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $width, $height, $imageInfo[0], $imageInfo[1]);

    // Save the resized image
    $success = false;
    switch ($mime) {
        case 'image/jpeg':
            $success = imagejpeg($resizedImage, $destPath, 90);
            break;
        case 'image/png':
            $success = imagepng($resizedImage, $destPath, 9);
            break;
        case 'image/gif':
            $success = imagegif($resizedImage, $destPath);
            break;
    }

    // Free memory
    imagedestroy($sourceImage);
    imagedestroy($resizedImage);

    return $success;
}

// Set content type header early
header('Content-Type: application/json');

// Initialize response array
$response = array('success' => false, 'message' => '');

try {
    // Check if form is submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Get form data
        $firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : '';
        $lastName = isset($_POST['lastName']) ? trim($_POST['lastName']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $confirmPassword = isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : '';
        $termsAccepted = isset($_POST['terms']) ? $_POST['terms'] : '';
        $userType = isset($_POST['userType']) ? $_POST['userType'] : 'full_access'; // Default to full_access

        // Handle profile picture upload
        $profilePicturePath = null;
        if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === UPLOAD_ERR_OK) {
            $profilePicturePath = handleProfilePictureUpload($_FILES['profilePicture']);
            if ($profilePicturePath === false) {
                $response['message'] = 'Invalid profile picture. Please upload a valid image file (JPG, PNG, GIF) under 5MB.';
                echo json_encode($response);
                exit();
            }
        }

        // Debug: Log the received userType
        error_log("Registration userType received: " . $userType);

        // Validate userType
        if (!in_array($userType, ['chat_only', 'full_access'])) {
            $response['message'] = 'Invalid user type selected.';
        } elseif (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword)) {
            $response['message'] = 'Please fill in all fields.';
        } elseif ($password !== $confirmPassword) {
            $response['message'] = 'Passwords do not match.';
        } elseif (strlen($password) < 6) {
            $response['message'] = 'Password must be at least 6 characters long.';
        } elseif (empty($termsAccepted)) {
            $response['message'] = 'Please accept the Terms of Use and Privacy Policy.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Please enter a valid email address.';
        } else {
            try {
                // Get database connection
                $pdo = getDBConnection();
                
                // Check if email already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                
                if ($stmt->fetch()) {
                    $response['message'] = 'An account with this email already exists.';
                } else {
                    // Hash password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    error_log("Inserting user with profile_picture: " . ($profilePicturePath ?: 'NULL'));

                    // Insert new user (inactive and unverified until admin approval)
                    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, profile_picture, email, password, user_type, is_active, is_verified, created_at) VALUES (?, ?, ?, ?, ?, ?, 0, 0, NOW())");
                    $result = $stmt->execute([$firstName, $lastName, $profilePicturePath, $email, $hashedPassword, $userType]);
                    
                    if ($result) {
                        // Registration successful - user needs admin verification
                        $userId = $pdo->lastInsertId();

                        $response['success'] = true;
                        $response['message'] = 'Account created successfully! Your account needs admin verification before you can log in. Please wait for approval.';
                        $response['user'] = array(
                            'id' => $userId,
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                            'email' => $email,
                            'needs_verification' => true
                        );
                    } else {
                        $response['message'] = 'Account creation failed. Please try again.';
                    }
                }
            } catch (PDOException $e) {
                $response['message'] = 'Database error occurred. Please try again later.';
                error_log("Registration error: " . $e->getMessage());
            }
        }
    } else {
        $response['message'] = 'Invalid request method.';
    }
} catch (Exception $e) {
    $response['message'] = 'An unexpected error occurred. Please try again later.';
    error_log("Unexpected error in register_process.php: " . $e->getMessage());
}

// Return JSON response
echo json_encode($response);
exit();
?>
