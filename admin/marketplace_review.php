<?php
// admin/marketplace_review.php
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
    $itemId = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
    
    if ($action == 'approve' && $itemId > 0) {
        try {
            $stmt = $pdo->prepare("
                UPDATE marketplace_items 
                SET status = 'approved', approved_at = NOW(), approved_by = ?
                WHERE id = ?
            ");
            $stmt->execute([$_SESSION['user_id'], $itemId]);
            
            // Send notification to user
            $itemStmt = $pdo->prepare("SELECT user_id, title FROM marketplace_items WHERE id = ?");
            $itemStmt->execute([$itemId]);
            $item = $itemStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($item) {
                // TODO: Send email notification to user
                $message = 'Item approved successfully!';
                $messageType = 'success';
            }
        } catch (Exception $e) {
            $message = 'Error approving item: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
    
    if ($action == 'reject' && $itemId > 0) {
        $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
        try {
            $stmt = $pdo->prepare("
                UPDATE marketplace_items 
                SET status = 'rejected', approved_by = ?
                WHERE id = ?
            ");
            $stmt->execute([$_SESSION['user_id'], $itemId]);
            
            // TODO: Send rejection email with reason
            $message = 'Item rejected.';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error rejecting item: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
    
    if ($action == 'delete' && $itemId > 0) {
        try {
            // Delete associated photos first
            $photoStmt = $pdo->prepare("SELECT photo_path FROM marketplace_photos WHERE item_id = ?");
            $photoStmt->execute([$itemId]);
            $photos = $photoStmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($photos as $photo) {
                if (file_exists('../' . $photo)) {
                    @unlink('../' . $photo);
                }
            }
            
            $stmt = $pdo->prepare("DELETE FROM marketplace_items WHERE id = ?");
            $stmt->execute([$itemId]);
            $message = 'Item deleted successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error deleting item: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get all marketplace items
try {
    $stmt = $pdo->query("
        SELECT mi.*, u.first_name, u.last_name, u.email,
               (SELECT COUNT(*) FROM marketplace_photos WHERE item_id = mi.id) as photo_count
        FROM marketplace_items mi
        LEFT JOIN users u ON mi.user_id = u.id
        ORDER BY 
            CASE mi.status 
                WHEN 'pending' THEN 1
                WHEN 'approved' THEN 2
                WHEN 'rejected' THEN 3
                WHEN 'sold' THEN 4
            END,
            mi.created_at DESC
    ");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $message = 'Error loading items: ' . $e->getMessage();
    $messageType = 'error';
    $items = [];
}

// Get statistics
try {
    $statsStmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
            SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold
        FROM marketplace_items
    ");
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $stats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'sold' => 0];
}

// Get item for viewing if view_id is set
$viewItem = null;
$viewPhotos = [];
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $viewId = (int)$_GET['view'];
    $stmt = $pdo->prepare("
        SELECT mi.*, u.first_name, u.last_name, u.email
        FROM marketplace_items mi
        LEFT JOIN users u ON mi.user_id = u.id
        WHERE mi.id = ?
    ");
    $stmt->execute([$viewId]);
    $viewItem = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($viewItem) {
        $photoStmt = $pdo->prepare("SELECT photo_path FROM marketplace_photos WHERE item_id = ? ORDER BY photo_order");
        $photoStmt->execute([$viewId]);
        $viewPhotos = $photoStmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace Management - Live in Oz</title>
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
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #0f3460, #16213e);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .stat-card h3 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #00eeff;
        }
        
        .stat-card .number {
            font-size: 32px;
            font-weight: 700;
        }
        
        .items-list {
            background: #16213e;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .item-card {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
            display: flex;
            gap: 20px;
        }
        
        .item-card:hover {
            border-color: rgba(0, 238, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .item-thumbnail {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            flex-shrink: 0;
        }
        
        .item-content {
            flex: 1;
        }
        
        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 10px;
        }
        
        .item-title {
            font-size: 18px;
            color: #00eeff;
            margin-bottom: 5px;
        }
        
        .item-meta {
            font-size: 12px;
            color: #aaa;
            margin-bottom: 10px;
        }
        
        .item-status {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pending {
            background: #ff9800;
            color: #fff;
        }
        
        .status-approved {
            background: #4caf50;
            color: #fff;
        }
        
        .status-rejected {
            background: #f44336;
            color: #fff;
        }
        
        .status-sold {
            background: #9e9e9e;
            color: #fff;
        }
        
        .item-price {
            font-size: 20px;
            color: #ffcc00;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .free-badge {
            background: #00ff00;
            color: #000;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        
        .item-description {
            color: #ccc;
            line-height: 1.6;
            margin-bottom: 10px;
            max-height: 60px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .item-location {
            font-size: 13px;
            color: #00eeff;
            margin-bottom: 15px;
        }
        
        .item-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
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
        
        .btn-view {
            background: #2196F3;
            color: white;
        }
        
        .btn-approve {
            background: #4caf50;
            color: white;
        }
        
        .btn-reject {
            background: #f44336;
            color: white;
        }
        
        .btn-delete {
            background: #9e9e9e;
            color: white;
        }
        
        .section-title {
            font-size: 24px;
            margin-bottom: 20px;
            color: #00eeff;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.9);
        }
        
        .modal-content {
            background-color: #16213e;
            margin: 2% auto;
            padding: 30px;
            border: 2px solid #00eeff;
            border-radius: 15px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .close {
            color: #ff00cc;
            float: right;
            font-size: 35px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #00eeff;
        }
        
        .photo-gallery {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 20px 0;
        }
        
        .photo-gallery img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #00eeff;
            cursor: pointer;
        }
        
        .photo-gallery img:hover {
            opacity: 0.8;
        }
        
        .detail-row {
            margin: 15px 0;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .detail-row label {
            color: #00eeff;
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
        }
        
        .detail-row p {
            color: #ccc;
            line-height: 1.6;
        }
        
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
            }
            .stats-container {
                grid-template-columns: 1fr;
            }
            .item-card {
                flex-direction: column;
            }
            .item-thumbnail {
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
            <p>Marketplace Management</p>
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
                <li><a href="marketplace_review.php"  class="active">🛍️ Marketplace</a></li>
                <li><a href="events_review.php">🎉 Party & Events</a></li>
                <li><a href="blog.php">📝 Blog</a></li>
                <li><a href="whatsapp.php">💬 WhatsApp</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="#">Settings</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="page-title">
                <div>
                    <h2>Marketplace Management</h2>
                    <p>Review and manage marketplace listings</p>
                </div>
                <a href="dashboard.php" class="btn">Back to Dashboard</a>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-container">
                <div class="stat-card">
                    <h3>Total Items</h3>
                    <div class="number"><?php echo $stats['total']; ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Pending Review</h3>
                    <div class="number"><?php echo $stats['pending']; ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Approved</h3>
                    <div class="number"><?php echo $stats['approved']; ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Rejected</h3>
                    <div class="number"><?php echo $stats['rejected']; ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Sold</h3>
                    <div class="number"><?php echo $stats['sold']; ?></div>
                </div>
            </div>

            <!-- Items List -->
            <div class="items-list">
                <h3 class="section-title">All Marketplace Items (<?php echo count($items); ?>)</h3>
                
                <?php if (count($items) > 0): ?>
                    <?php foreach ($items as $item): ?>
                        <?php
                        // Get first photo
                        $photoStmt = $pdo->prepare("SELECT photo_path FROM marketplace_photos WHERE item_id = ? ORDER BY photo_order LIMIT 1");
                        $photoStmt->execute([$item['id']]);
                        $firstPhoto = $photoStmt->fetchColumn();
                        ?>
                        <div class="item-card">
                            <?php if ($firstPhoto): ?>
                                <img src="../<?php echo htmlspecialchars($firstPhoto); ?>" 
                                     alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                     class="item-thumbnail">
                            <?php else: ?>
                                <div class="item-thumbnail" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; font-size: 48px;">
                                    📦
                                </div>
                            <?php endif; ?>
                            
                            <div class="item-content">
                                <div class="item-header">
                                    <div>
                                        <h4 class="item-title"><?php echo htmlspecialchars($item['title']); ?></h4>
                                        <div class="item-meta">
                                            Posted by <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?> 
                                            (<?php echo htmlspecialchars($item['email']); ?>) • 
                                            <?php echo date('M j, Y g:i A', strtotime($item['created_at'])); ?> •
                                            <?php echo $item['photo_count']; ?> photo(s)
                                        </div>
                                    </div>
                                    <span class="item-status status-<?php echo $item['status']; ?>">
                                        <?php echo strtoupper($item['status']); ?>
                                    </span>
                                </div>
                                
                                <div class="item-price">
                                    <?php if ($item['is_free'] == 1): ?>
                                        <span class="free-badge">FREE</span>
                                    <?php else: ?>
                                        $<?php echo number_format($item['price'], 2); ?> AUD
                                    <?php endif; ?>
                                </div>
                                
                                <div class="item-location">
                                    📍 <?php echo htmlspecialchars($item['city']); ?>, <?php echo htmlspecialchars($item['state_code']); ?> • 
                                    📦 <?php echo ucfirst(str_replace('_', ' ', $item['category'])); ?>
                                </div>
                                
                                <div class="item-description">
                                    <?php echo nl2br(htmlspecialchars(substr($item['description'], 0, 150))); ?>
                                    <?php echo strlen($item['description']) > 150 ? '...' : ''; ?>
                                </div>
                                
                                <div class="item-actions">
                                    <a href="marketplace_review.php?view=<?php echo $item['id']; ?>" class="action-btn btn-view">View Details</a>
                                    
                                    <?php if ($item['status'] == 'pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" class="action-btn btn-approve">Approve</button>
                                        </form>
                                        
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to reject this item?');">
                                            <input type="hidden" name="action" value="reject">
                                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" class="action-btn btn-reject">Reject</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this item?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="action-btn btn-delete">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #aaa; text-align: center; padding: 40px;">No marketplace items yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- View Item Modal -->
    <?php if ($viewItem): ?>
    <div id="viewModal" class="modal" style="display: block;">
        <div class="modal-content">
            <span class="close" onclick="window.location.href='marketplace_review.php'">&times;</span>
            <h2 style="color: #00eeff; margin-bottom: 20px;"><?php echo htmlspecialchars($viewItem['title']); ?></h2>
            
            <?php if (count($viewPhotos) > 0): ?>
            <div class="photo-gallery">
                <?php foreach ($viewPhotos as $photo): ?>
                    <img src="../<?php echo htmlspecialchars($photo); ?>" alt="Item photo" onclick="window.open('../<?php echo htmlspecialchars($photo); ?>', '_blank')">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <div class="detail-row">
                <label>Status</label>
                <p><span class="item-status status-<?php echo $viewItem['status']; ?>"><?php echo strtoupper($viewItem['status']); ?></span></p>
            </div>
            
            <div class="detail-row">
                <label>Price</label>
                <p>
                    <?php if ($viewItem['is_free'] == 1): ?>
                        <span class="free-badge">FREE</span>
                    <?php else: ?>
                        $<?php echo number_format($viewItem['price'], 2); ?> AUD
                    <?php endif; ?>
                </p>
            </div>
            
            <div class="detail-row">
                <label>Category</label>
                <p><?php echo ucfirst(str_replace('_', ' ', $viewItem['category'])); ?></p>
            </div>
            
            <div class="detail-row">
                <label>Location</label>
                <p><?php echo htmlspecialchars($viewItem['city']); ?>, <?php echo htmlspecialchars($viewItem['state_code']); ?></p>
            </div>
            
            <div class="detail-row">
                <label>Description</label>
                <p><?php echo nl2br(htmlspecialchars($viewItem['description'])); ?></p>
            </div>
            
            <div class="detail-row">
                <label>Posted By</label>
                <p><?php echo htmlspecialchars($viewItem['first_name'] . ' ' . $viewItem['last_name']); ?> (<?php echo htmlspecialchars($viewItem['email']); ?>)</p>
            </div>
            
            <div class="detail-row">
                <label>Contact Methods</label>
                <p>
                    <?php if ($viewItem['contact_phone']): ?>
                        📞 <?php echo htmlspecialchars($viewItem['contact_phone']); ?><br>
                    <?php endif; ?>
                    <?php if ($viewItem['contact_email']): ?>
                        ✉️ <?php echo htmlspecialchars($viewItem['contact_email']); ?><br>
                    <?php endif; ?>
                    <?php if ($viewItem['allow_chat']): ?>
                        💬 Chat enabled on Live in Oz
                    <?php endif; ?>
                </p>
            </div>
            
            <div class="detail-row">
                <label>Views</label>
                <p><?php echo number_format($viewItem['view_count']); ?> views</p>
            </div>
            
            <div class="detail-row">
                <label>Posted Date</label>
                <p><?php echo date('F j, Y g:i A', strtotime($viewItem['created_at'])); ?></p>
            </div>
            
            <div style="margin-top: 30px; display: flex; gap: 10px;">
                <?php if ($viewItem['status'] == 'pending'): ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="approve">
                        <input type="hidden" name="item_id" value="<?php echo $viewItem['id']; ?>">
                        <button type="submit" class="btn" style="background: #4caf50;">✓ Approve</button>
                    </form>
                    
                    <form method="POST" style="display: inline;" onsubmit="return confirm('Reject this item?');">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="item_id" value="<?php echo $viewItem['id']; ?>">
                        <button type="submit" class="btn" style="background: #f44336;">✗ Reject</button>
                    </form>
                <?php endif; ?>
                
                <button onclick="window.location.href='marketplace_review.php'" class="btn" style="background: #2c3e50;">Close</button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Custom Confirm Modal -->
    <div id="adminConfirmModal" class="admin-confirm-modal">
        <div class="admin-confirm-content">
            <div class="admin-confirm-title">Confirm Action</div>
            <div class="admin-confirm-message">Message</div>
            <div class="admin-confirm-buttons">
                <button id="adminConfirmYes" class="admin-confirm-btn yes">Yes, Confirm</button>
                <button id="adminConfirmNo" class="admin-confirm-btn no">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        // Custom confirm function for admin forms
        let currentFormToSubmit = null;

        function customConfirm(message) {
            return new Promise((resolve) => {
                const modal = document.getElementById('adminConfirmModal');
                const titleEl = document.querySelector('.admin-confirm-title');
                const messageEl = document.querySelector('.admin-confirm-message');
                const yesBtn = document.getElementById('adminConfirmYes');
                const noBtn = document.getElementById('adminConfirmNo');

                titleEl.textContent = 'Confirm Action';
                messageEl.textContent = message;
                modal.style.display = 'block';

                const handleYes = () => {
                    modal.style.display = 'none';
                    cleanup();
                    resolve(true);
                };

                const handleNo = () => {
                    modal.style.display = 'none';
                    cleanup();
                    resolve(false);
                };

                const cleanup = () => {
                    yesBtn.removeEventListener('click', handleYes);
                    noBtn.removeEventListener('click', handleNo);
                };

                yesBtn.addEventListener('click', handleYes);
                noBtn.addEventListener('click', handleNo);
            });
        }

        // Override form submissions with confirm dialogs
        document.addEventListener('DOMContentLoaded', function() {
            // Intercept reject buttons
            const rejectButtons = document.querySelectorAll('button[value="reject"]');
            rejectButtons.forEach(btn => {
                const form = btn.closest('form');
                if (form) {
                    btn.addEventListener('click', async function(e) {
                        e.preventDefault();
                        const confirmed = await customConfirm('Are you sure you want to reject this item? This action cannot be undone.');
                        if (confirmed) {
                            form.submit();
                        }
                    });
                }
            });

            // Intercept delete buttons
            const deleteButtons = document.querySelectorAll('button[value="delete"]');
            deleteButtons.forEach(btn => {
                const form = btn.closest('form');
                if (form) {
                    btn.addEventListener('click', async function(e) {
                        e.preventDefault();
                        const confirmed = await customConfirm('Are you sure you want to delete this item? This will permanently remove the item and all its photos.');
                        if (confirmed) {
                            form.submit();
                        }
                    });
                }
            });

            // Handle the modal reject button in view modal
            const modalRejectBtn = document.querySelector('.modal button[value="reject"]');
            if (modalRejectBtn) {
                const form = modalRejectBtn.closest('form');
                if (form) {
                    modalRejectBtn.addEventListener('click', async function(e) {
                        e.preventDefault();
                        const confirmed = await customConfirm('Reject this item? This action cannot be undone.');
                        if (confirmed) {
                            form.submit();
                        }
                    });
                }
            }
        });
    </script>

    <style>
        /* Admin Confirm Modal Styles */
        .admin-confirm-modal {
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(5px);
            display: none;
        }

        .admin-confirm-content {
            background: linear-gradient(145deg, #0f0f23, #0a0a1f);
            margin: 15% auto;
            padding: 30px;
            border: 3px solid #ffaa00;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 0 40px #ffaa00, 0 0 80px #ffaa00, 0 0 120px #ffaa00;
            animation: adminConfirmGlow 2s ease-in-out infinite alternate;
            text-align: center;
        }

        @keyframes adminConfirmGlow {
            0% { box-shadow: 0 0 40px #ffaa00, 0 0 80px #ffaa00, 0 0 120px #ffaa00; }
            100% { box-shadow: 0 0 50px #ffdd44, 0 0 100px #ffdd44, 0 0 150px #ffdd44; }
        }

        .admin-confirm-title {
            color: #ffaa00;
            margin-bottom: 20px;
            font-size: 1.8rem;
            text-shadow: 0 0 15px #ffaa00;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .admin-confirm-message {
            color: #fff;
            font-size: 1.2rem;
            margin-bottom: 30px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .admin-confirm-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .admin-confirm-btn {
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-weight: bold;
        }

        .admin-confirm-btn.yes {
            background: linear-gradient(45deg, #ffaa00, #ffdd44);
            color: #000;
            box-shadow: 0 0 10px #ffaa00;
        }

        .admin-confirm-btn.yes:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 20px #ffaa00;
        }

        .admin-confirm-btn.no {
            background: linear-gradient(45deg, #666, #999);
            color: #fff;
            box-shadow: 0 0 10px #666;
        }

        .admin-confirm-btn.no:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 20px #999;
        }
    </style>
</body>
</html>
