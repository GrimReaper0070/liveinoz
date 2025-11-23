<?php
require_once '../config.php';
require_once 'admin_auth.php';

// Check if admin is authenticated
requireAdminAuth();

// Get database connection
$pdo = getDBConnection();

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    // Handle WhatsApp group creation/update
    if ($action == 'create' || $action == 'update') {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $whatsapp_link = isset($_POST['whatsapp_link']) ? trim($_POST['whatsapp_link']) : '';
        $state_code = isset($_POST['state_code']) ? $_POST['state_code'] : null;
        $city_name = isset($_POST['city_name']) ? $_POST['city_name'] : null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $groupId = isset($_POST['group_id']) ? (int)$_POST['group_id'] : 0;
        
        // Validate WhatsApp link
        if (!empty($whatsapp_link) && !preg_match('/^https:\/\/(chat\.whatsapp\.com|wa\.me)\//', $whatsapp_link)) {
            $message = 'Please enter a valid WhatsApp link (e.g., https://chat.whatsapp.com/... or https://wa.me/...)';
            $messageType = 'error';
        } elseif (empty($name) || empty($description) || empty($whatsapp_link)) {
            $message = 'Name, description, and WhatsApp link are required.';
            $messageType = 'error';
        } else {
            try {
                // Handle image upload
                $imagePath = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $fileType = $_FILES['image']['type'];
                    
                    if (in_array($fileType, $allowedTypes)) {
                        $uploadDir = '../uploads/whatsapp/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                        
                        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $fileName = uniqid('whatsapp_') . '.' . $extension;
                        $targetPath = $uploadDir . $fileName;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                            $imagePath = 'uploads/whatsapp/' . $fileName;
                        }
                    }
                }
                
                if ($action == 'create') {
                    // Insert new WhatsApp group
                    $stmt = $pdo->prepare("INSERT INTO whatsapp_groups (name, description, image, whatsapp_link, state_code, city_name, is_active, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $description, $imagePath, $whatsapp_link, $state_code, $city_name, $is_active, $_SESSION['user_id']]);
                    $message = 'WhatsApp group added successfully!';
                    $messageType = 'success';
                } else {
                    // Update existing WhatsApp group
                    if ($imagePath) {
                        $stmt = $pdo->prepare("UPDATE whatsapp_groups SET name = ?, description = ?, image = ?, whatsapp_link = ?, state_code = ?, city_name = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                        $stmt->execute([$name, $description, $imagePath, $whatsapp_link, $state_code, $city_name, $is_active, $groupId]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE whatsapp_groups SET name = ?, description = ?, whatsapp_link = ?, state_code = ?, city_name = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                        $stmt->execute([$name, $description, $whatsapp_link, $state_code, $city_name, $is_active, $groupId]);
                    }
                    $message = 'WhatsApp group updated successfully!';
                    $messageType = 'success';
                }
            } catch (Exception $e) {
                $message = 'Error: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
    
    // Handle delete action
    if ($action == 'delete') {
        $groupId = isset($_POST['group_id']) ? (int)$_POST['group_id'] : 0;
        if ($groupId > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM whatsapp_groups WHERE id = ?");
                $stmt->execute([$groupId]);
                $message = 'WhatsApp group deleted successfully!';
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'Error deleting group: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// Get all states for dropdown
try {
    $statesStmt = $pdo->query("SELECT code, name FROM states ORDER BY name");
    $states = $statesStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $states = [];
}

// Get all WhatsApp groups
try {
    $stmt = $pdo->query("
        SELECT wg.*, u.first_name, u.last_name, s.name as state_name
        FROM whatsapp_groups wg
        LEFT JOIN users u ON wg.created_by = u.id
        LEFT JOIN states s ON wg.state_code = s.code
        ORDER BY wg.created_at DESC
    ");
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $message = 'Error loading groups: ' . $e->getMessage();
    $messageType = 'error';
    $groups = [];
}

// Get group for editing if edit_id is set
$editGroup = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM whatsapp_groups WHERE id = ?");
    $stmt->execute([$editId]);
    $editGroup = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Groups Management - Live in Oz</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background: #1a1a2e;
            color: #fff;
            min-height: 100vh;
        }
        .header {
            background: linear-gradient(90deg, #0f3460, #16213e);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .logo h1 { font-size: 24px; color: #00eeff; }
        .logo p { font-size: 14px; opacity: 0.8; }
        .user-info { text-align: right; }
        .user-info p { margin-bottom: 5px; }
        .user-info a {
            color: #ff4d4d; text-decoration: none; font-size: 14px;
        }
        .user-info a:hover { text-decoration: underline; }
        .container { display: flex; min-height: calc(100vh - 80px); }
        .sidebar {
            width: 250px; background: #16213e; padding: 20px 0;
        }
        .sidebar ul { list-style: none; }
        .sidebar ul li { margin-bottom: 5px; }
        .sidebar ul li a {
            display: block; padding: 15px 30px;
            color: #ccc; text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        .sidebar ul li a:hover, .sidebar ul li a.active {
            background: rgba(0, 238, 255, 0.1);
            color: #00eeff;
            border-left: 3px solid #00eeff;
        }
        .main-content { flex: 1; padding: 30px; }
        .page-title {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .page-title h2 { font-size: 28px; }
        .page-title p { color: #aaa; }
        .btn {
            padding: 10px 20px;
            background: linear-gradient(90deg, #00eeff, #008cff);
            color: #000;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover { opacity: 0.9; }
        
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .message.success {
            background: rgba(76, 175, 80, 0.2);
            border: 1px solid rgba(76, 175, 80, 0.5);
        }
        .message.error {
            background: rgba(244, 67, 54, 0.2);
            border: 1px solid rgba(244, 67, 54, 0.5);
        }
        
        .group-form {
            background: #16213e;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #00eeff;
            font-weight: 600;
        }
        
        .form-group input[type="text"],
        .form-group input[type="url"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            color: #fff;
            font-size: 14px;
        }
        
        .form-group input[type="text"]:focus,
        .form-group input[type="url"]:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #00eeff;
            background: rgba(0, 0, 0, 0.4);
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
            font-family: inherit;
            line-height: 1.6;
        }
        
        .form-group input[type="file"] {
            width: 100%;
            padding: 10px;
            background: rgba(0, 0, 0, 0.3);
            border: 2px dashed rgba(0, 238, 255, 0.3);
            border-radius: 5px;
            color: #fff;
            cursor: pointer;
        }
        
        .form-group input[type="file"]:hover {
            border-color: #00eeff;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .checkbox-group label {
            margin-bottom: 0;
            cursor: pointer;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(90deg, #00eeff, #008cff);
            color: #000;
        }
        
        .btn-secondary {
            background: #2c3e50;
            color: #fff;
        }
        
        .btn-danger {
            background: #f44336;
            color: #fff;
        }
        
        .groups-list {
            background: #16213e;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .group-card {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
            display: flex;
            gap: 20px;
        }
        
        .group-card:hover {
            border-color: rgba(0, 238, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .group-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            flex-shrink: 0;
        }
        
        .group-content {
            flex: 1;
        }
        
        .group-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 10px;
        }
        
        .group-title {
            font-size: 20px;
            color: #00eeff;
            margin-bottom: 5px;
        }
        
        .group-meta {
            font-size: 12px;
            color: #aaa;
            margin-bottom: 10px;
        }
        
        .group-status {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-active {
            background: #4caf50;
            color: #fff;
        }
        
        .status-inactive {
            background: #f44336;
            color: #fff;
        }
        
        .group-description {
            color: #ccc;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .group-link {
            display: inline-block;
            padding: 8px 16px;
            background: #25D366;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .group-link:hover {
            background: #20bd5a;
        }
        
        .group-location {
            font-size: 13px;
            color: #00eeff;
            margin-bottom: 15px;
        }
        
        .group-actions {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-edit {
            background: #2196F3;
            color: white;
        }
        
        .btn-delete {
            background: #f44336;
            color: white;
        }
        
        .section-title {
            font-size: 24px;
            margin-bottom: 20px;
            color: #00eeff;
        }
        
        .helper-text {
            font-size: 12px;
            color: #aaa;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
            .form-actions {
                flex-direction: column;
            }
            .group-card {
                flex-direction: column;
            }
            .group-image {
                width: 100%;
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <h1>Live in Oz</h1>
            <p>WhatsApp Groups Management</p>
        </div>
        <div class="user-info">
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?>!</p>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="sidebar">
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="users.php">User Management</a></li>
                <li><a href="rooms.php">Room Management</a></li>
                 <li><a href="subscriptions.php">Subscriptions</a></li>
                <li><a href="marketplace_review.php">🛍️ Marketplace</a></li>
                <li><a href="events_review.php">🎉 Party & Events</a></li>
                <li><a href="blog.php">📝 Blog</a></li>
                <li><a href="whatsapp.php" class="active">💬 WhatsApp</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="#">Settings</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="page-title">
                <div>
                    <h2><?php echo $editGroup ? 'Edit' : 'Add'; ?> WhatsApp Group</h2>
                    <p>Manage WhatsApp community groups</p>
                </div>
                <a href="dashboard.php" class="btn">Back to Dashboard</a>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- WhatsApp Group Form -->
            <div class="group-form">
                <h3 class="section-title"><?php echo $editGroup ? 'Edit Group' : 'Add New Group'; ?></h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $editGroup ? 'update' : 'create'; ?>">
                    <?php if ($editGroup): ?>
                        <input type="hidden" name="group_id" value="<?php echo $editGroup['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="name">Group Name *</label>
                        <input type="text" id="name" name="name" 
                               value="<?php echo $editGroup ? htmlspecialchars($editGroup['name']) : ''; ?>" 
                               placeholder="e.g., Sydney Latino Community" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Group Image</label>
                        <?php if ($editGroup && $editGroup['image']): ?>
                            <img src="../<?php echo htmlspecialchars($editGroup['image']); ?>" 
                                 alt="Current image" class="group-image" style="margin-bottom: 10px;">
                            <p class="helper-text">Upload a new image to replace the current one</p>
                        <?php endif; ?>
                        <input type="file" id="image" name="image" accept="image/*">
                        <p class="helper-text">Recommended size: 300x300px (square image works best)</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" 
                                  placeholder="Describe the purpose and community of this WhatsApp group..." 
                                  required><?php echo $editGroup ? htmlspecialchars($editGroup['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="whatsapp_link">WhatsApp Group Link *</label>
                        <input type="url" id="whatsapp_link" name="whatsapp_link" 
                               value="<?php echo $editGroup ? htmlspecialchars($editGroup['whatsapp_link']) : ''; ?>" 
                               placeholder="https://chat.whatsapp.com/..." required>
                        <p class="helper-text">Enter the invite link (e.g., https://chat.whatsapp.com/ABC123...)</p>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="state_code">State (Optional)</label>
                            <select id="state_code" name="state_code">
                                <option value="">All States</option>
                                <?php foreach ($states as $state): ?>
                                    <option value="<?php echo htmlspecialchars($state['code']); ?>"
                                            <?php echo ($editGroup && $editGroup['state_code'] == $state['code']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($state['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="city_name">City (Optional)</label>
                            <input type="text" id="city_name" name="city_name" 
                                   value="<?php echo $editGroup ? htmlspecialchars($editGroup['city_name']) : ''; ?>" 
                                   placeholder="e.g., Sydney, Melbourne">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="is_active" name="is_active" 
                                   <?php echo (!$editGroup || $editGroup['is_active']) ? 'checked' : ''; ?>>
                            <label for="is_active">Active (Show this group to users)</label>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $editGroup ? 'Update Group' : 'Add Group'; ?>
                        </button>
                        <?php if ($editGroup): ?>
                            <a href="whatsapp.php" class="btn btn-secondary">Cancel Edit</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- WhatsApp Groups List -->
            <div class="groups-list">
                <h3 class="section-title">All WhatsApp Groups (<?php echo count($groups); ?>)</h3>
                
                <?php if (count($groups) > 0): ?>
                    <?php foreach ($groups as $group): ?>
                        <div class="group-card">
                            <?php if ($group['image']): ?>
                                <img src="../<?php echo htmlspecialchars($group['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($group['name']); ?>" 
                                     class="group-image">
                            <?php else: ?>
                                <div class="group-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; font-size: 48px;">
                                    💬
                                </div>
                            <?php endif; ?>
                            
                            <div class="group-content">
                                <div class="group-header">
                                    <div>
                                        <h4 class="group-title"><?php echo htmlspecialchars($group['name']); ?></h4>
                                        <div class="group-meta">
                                            Added by <?php echo htmlspecialchars($group['first_name'] . ' ' . $group['last_name']); ?> • 
                                            <?php echo date('M j, Y', strtotime($group['created_at'])); ?>
                                        </div>
                                    </div>
                                    <span class="group-status status-<?php echo $group['is_active'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $group['is_active'] ? 'ACTIVE' : 'INACTIVE'; ?>
                                    </span>
                                </div>
                                
                                <?php if ($group['state_name'] || $group['city_name']): ?>
                                    <div class="group-location">
                                        📍 <?php echo htmlspecialchars($group['city_name'] ? $group['city_name'] . ', ' : ''); ?>
                                        <?php echo htmlspecialchars($group['state_name'] ?? ''); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="group-description">
                                    <?php echo nl2br(htmlspecialchars($group['description'])); ?>
                                </div>
                                
                                <a href="<?php echo htmlspecialchars($group['whatsapp_link']); ?>" 
                                   target="_blank" class="group-link">
                                    Join WhatsApp Group →
                                </a>
                                
                                <div class="group-actions">
                                    <a href="whatsapp.php?edit=<?php echo $group['id']; ?>" class="action-btn btn-edit">Edit</a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this group?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
                                        <button type="submit" class="action-btn btn-delete">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #aaa; text-align: center; padding: 40px;">No WhatsApp groups yet. Add your first group above!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>