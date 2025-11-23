<?php
session_start();
require_once 'config.php';

// Get article ID from URL
$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($article_id <= 0) {
    header('Location: blog.html');
    exit;
}

try {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("
        SELECT bp.id, bp.title, bp.content, bp.image, bp.created_at, u.first_name, u.last_name
        FROM blog_posts bp
        LEFT JOIN users u ON bp.author_id = u.id
        WHERE bp.id = ? AND bp.status = 'published'
    ");
    $stmt->execute([$article_id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$article) {
        header('Location: blog.html');
        exit;
    }
} catch (Exception $e) {
    error_log("Article fetch error: " . $e->getMessage());
    header('Location: blog.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <title><?php echo htmlspecialchars($article['title']); ?> | Live in Oz</title>
    <link
      href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="global.css" />
    <link rel="stylesheet" href="blog.css" />
    <style>
      body {
        margin: 0;
        padding: 0;
        background: #1a1a1a;
        color: #ffffff;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        overflow: hidden;
      }
      
      .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        z-index: 9998;
      }
      
      .modal-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow-y: auto;
        z-index: 9999;
        padding: 30px;
        box-sizing: border-box;
      }
      
      .modal-content {
        max-width: 1200px;
        margin: 0;
        padding-left: 30px;
        padding-right: 30px;
        padding-bottom: 60px;
      }
      
      .close-button {
        position: fixed;
        top: 20px;
        right: 30px;
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid #00eeff;
        border-radius: 50%;
        color: #00eeff;
        font-size: 24px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        z-index: 10000;
        text-decoration: none;
      }
      
      .close-button:hover {
        background: #00eeff;
        color: #000;
        transform: rotate(90deg);
      }
      
      .article-header {
        margin-top: 40px;
        margin-bottom: 30px;
        text-align: left;
      }
      
      .article-title {
        font-size: 2.5em;
        color: #00eeff;
        margin-bottom: 15px;
        line-height: 1.2;
        text-align: left;
      }
      
      .article-meta {
        color: #aaa;
        font-size: 0.9em;
        margin-bottom: 30px;
        text-align: left;
      }
      
      .article-image {
        width: 30%;
        max-width: 300px;
    
        height: auto;
        border-radius: 10px;
        margin-bottom: 30px;
        display: block;
      }
      
      .article-content {
        color: #ffffff;
        font-size: 1.1em;
        line-height: 1.8;
        white-space: pre-wrap;
        word-wrap: break-word;
        text-align: left;
        max-width: 800px;
      }
      
      @media (max-width: 768px) {
        .modal-container {
          padding: 20px;
        }
        
        .modal-content {
          padding-left: 20px;
          padding-right: 20px;
        }
        
        .article-title {
          font-size: 1.8em;
        }
        
        .article-content {
          font-size: 1em;
        }
        
        .close-button {
          top: 15px;
          right: 15px;
          width: 35px;
          height: 35px;
          font-size: 20px;
        }
      }
    </style>
  </head>
  <body>
    <div class="modal-overlay"></div>
    
    <a href="blog.html" class="close-button" title="Close">×</a>
    
    <div class="modal-container">
      <div class="modal-content">
        <article>
          <div class="article-header">
            <h1 class="article-title"><?php echo htmlspecialchars($article['title']); ?></h1>
            <div class="article-meta">
              <?php if ($article['first_name'] && $article['last_name']): ?>
                By <?php echo htmlspecialchars($article['first_name'] . ' ' . $article['last_name']); ?> • 
              <?php endif; ?>
              <?php echo date('F j, Y', strtotime($article['created_at'])); ?>
            </div>
          </div>
          
          <?php if ($article['image']): ?>
            <img src="<?php echo htmlspecialchars($article['image']); ?>" 
                 alt="<?php echo htmlspecialchars($article['title']); ?>" 
                 class="article-image">
          <?php endif; ?>
          
          <div class="article-content">
            <?php echo nl2br(htmlspecialchars($article['content'])); ?>
          </div>
        </article>
      </div>
    </div>

    <script>
      // Close modal with ESC key
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          window.location.href = 'blog.html';
        }
      });
      
      // Close modal when clicking on overlay
      document.querySelector('.modal-overlay').addEventListener('click', function() {
        window.location.href = 'blog.html';
      });
    </script>
  </body>
</html>