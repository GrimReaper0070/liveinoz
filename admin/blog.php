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
    
    // Handle blog post creation/update
    if ($action == 'create' || $action == 'update') {
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        $status = isset($_POST['status']) ? $_POST['status'] : 'draft';
        $postId = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
        
        if (empty($title) || empty($content)) {
            $message = 'Title and content are required.';
            $messageType = 'error';
        } else {
            try {
                // Handle image upload
                $imagePath = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $fileType = $_FILES['image']['type'];
                    
                    if (in_array($fileType, $allowedTypes)) {
                        $uploadDir = '../uploads/blog/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                        
                        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $fileName = uniqid('blog_') . '.' . $extension;
                        $targetPath = $uploadDir . $fileName;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                            $imagePath = 'uploads/blog/' . $fileName;
                        }
                    }
                }
                
                if ($action == 'create') {
                    // Insert new blog post
                    $stmt = $pdo->prepare("INSERT INTO blog_posts (title, content, image, author_id, status) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $content, $imagePath, $_SESSION['user_id'], $status]);
                    $message = 'Blog post created successfully!';
                    $messageType = 'success';
                } else {
                    // Update existing blog post
                    if ($imagePath) {
                        $stmt = $pdo->prepare("UPDATE blog_posts SET title = ?, content = ?, image = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                        $stmt->execute([$title, $content, $imagePath, $status, $postId]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE blog_posts SET title = ?, content = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                        $stmt->execute([$title, $content, $status, $postId]);
                    }
                    $message = 'Blog post updated successfully!';
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
        $postId = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
        if ($postId > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
                $stmt->execute([$postId]);
                $message = 'Blog post deleted successfully!';
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'Error deleting post: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// Get all blog posts
try {
    $stmt = $pdo->query("
        SELECT bp.*, u.first_name, u.last_name 
        FROM blog_posts bp
        LEFT JOIN users u ON bp.author_id = u.id
        ORDER BY bp.created_at DESC
    ");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $message = 'Error loading posts: ' . $e->getMessage();
    $messageType = 'error';
    $posts = [];
}

// Get post for editing if edit_id is set
$editPost = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->execute([$editId]);
    $editPost = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Management - Live in Oz</title>
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
        
        .blog-form {
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
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #00eeff;
            background: rgba(0, 0, 0, 0.4);
        }
        
        .form-group textarea {
            min-height: 400px;
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
        
        .posts-list {
            background: #16213e;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .post-card {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }
        
        .post-card:hover {
            border-color: rgba(0, 238, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        
        .post-title {
            font-size: 20px;
            color: #00eeff;
            margin-bottom: 5px;
        }
        
        .post-meta {
            font-size: 12px;
            color: #aaa;
        }
        
        .post-status {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-published {
            background: #4caf50;
            color: #fff;
        }
        
        .status-draft {
            background: #ff9800;
            color: #fff;
        }
        
        .post-image {
            width: 100%;
            max-width: 200px;
            height: auto;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        .post-content {
            color: #ccc;
            line-height: 1.6;
            margin-bottom: 15px;
            max-height: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .post-actions {
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
        
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
            }
            .form-actions {
                flex-direction: column;
            }
            .post-header {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <h1>Live in Oz</h1>
            <p>Blog Management</p>
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
                <li><a href="blog.php" class="active">📝 Blog</a></li>
                <li><a href="whatsapp.php">💬 WhatsApp</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="#">Settings</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="page-title">
                <div>
                    <h2><?php echo $editPost ? 'Edit' : 'Create'; ?> Blog Post</h2>
                    <p>Manage blog posts and announcements</p>
                </div>
                <a href="dashboard.php" class="btn">Back to Dashboard</a>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Blog Post Form -->
            <div class="blog-form">
                <h3 class="section-title"><?php echo $editPost ? 'Edit Post' : 'Create New Post'; ?></h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $editPost ? 'update' : 'create'; ?>">
                    <?php if ($editPost): ?>
                        <input type="hidden" name="post_id" value="<?php echo $editPost['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="title">Post Title *</label>
                        <input type="text" id="title" name="title" 
                               value="<?php echo $editPost ? htmlspecialchars($editPost['title']) : ''; ?>" 
                               placeholder="Enter blog post title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Featured Image</label>
                        <?php if ($editPost && $editPost['image']): ?>
                            <img src="../<?php echo htmlspecialchars($editPost['image']); ?>" 
                                 alt="Current image" class="post-image" style="margin-bottom: 10px;">
                            <p style="color: #aaa; font-size: 12px; margin-bottom: 10px;">Upload a new image to replace the current one</p>
                        <?php endif; ?>
                        <input type="file" id="image" name="image" accept="image/*">
                    </div>
                    
                    <div class="form-group">
                        <label for="content">Article Content *</label>
                        <textarea id="content" name="content" 
                                  placeholder="Write your blog post content here..." 
                                  required><?php echo $editPost ? htmlspecialchars($editPost['content']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="draft" <?php echo ($editPost && $editPost['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo ($editPost && $editPost['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $editPost ? 'Update Post' : 'Create Post'; ?>
                        </button>
                        <?php if ($editPost): ?>
                            <a href="blog.php" class="btn btn-secondary">Cancel Edit</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Blog Posts List -->
            <div class="posts-list">
                <h3 class="section-title">All Blog Posts (<?php echo count($posts); ?>)</h3>
                
                <?php if (count($posts) > 0): ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="post-card">
                            <div class="post-header">
                                <div>
                                    <h4 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h4>
                                    <div class="post-meta">
                                        By <?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?> • 
                                        <?php echo date('M j, Y g:i A', strtotime($post['created_at'])); ?>
                                        <?php if ($post['updated_at'] != $post['created_at']): ?>
                                            (Updated: <?php echo date('M j, Y', strtotime($post['updated_at'])); ?>)
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="post-status status-<?php echo $post['status']; ?>">
                                    <?php echo strtoupper($post['status']); ?>
                                </span>
                            </div>
                            
                            <?php if ($post['image']): ?>
                                <img src="../<?php echo htmlspecialchars($post['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($post['title']); ?>" 
                                     class="post-image">
                            <?php endif; ?>
                            
                            <div class="post-content">
                                <?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 200))); ?>
                                <?php echo strlen($post['content']) > 200 ? '...' : ''; ?>
                            </div>
                            
                            <div class="post-actions">
                                <a href="blog.php?edit=<?php echo $post['id']; ?>" class="action-btn btn-edit">Edit</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <button type="submit" class="action-btn btn-delete">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #aaa; text-align: center; padding: 40px;">No blog posts yet. Create your first post above!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>