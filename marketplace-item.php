<?php
// marketplace-item.php
session_start();
require 'config.php';

$pdo = getDBConnection();

// Get item ID
$itemId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($itemId === 0) {
    header('Location: marketplace.html');
    exit;
}

// Fetch item details
$stmt = $pdo->prepare("
    SELECT 
        mi.*,
        u.first_name,
        u.last_name,
        u.email as user_email
    FROM marketplace_items mi
    JOIN users u ON mi.user_id = u.id
    WHERE mi.id = ? AND mi.status = 'approved'
");
$stmt->execute([$itemId]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header('Location: marketplace.html');
    exit;
}

// Fetch photos
$photoStmt = $pdo->prepare("
    SELECT photo_path 
    FROM marketplace_photos 
    WHERE item_id = ? 
    ORDER BY photo_order ASC
");
$photoStmt->execute([$itemId]);
$photos = $photoStmt->fetchAll(PDO::FETCH_COLUMN);

// Increment view count
$updateStmt = $pdo->prepare("UPDATE marketplace_items SET view_count = view_count + 1 WHERE id = ?");
$updateStmt->execute([$itemId]);

$isOwner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $item['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($item['title']); ?> | Live in Oz Marketplace</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="global.css">
    <style>
        body {
            background: urlbackground: url('images/retro-background.jpg');
            background-color: #000014;
            font-family: 'Orbitron', sans-serif;
            color: #fff;
            padding-top: 60px;
        }

        .item-detail-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .back-link {
            color: #00f0ff;
            text-decoration: none;
            font-size: 16px;
            margin-bottom: 20px;
            display: inline-block;
        }

        .back-link:hover {
            text-shadow: 0 0 10px #00f0ff;
        }

        .item-detail {
            background-color: rgba(26, 26, 26, 0.9);
            border: 2px solid #00f0ff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 0 30px rgba(0, 240, 255, 0.5);
        }

        .item-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .photo-gallery {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .main-photo {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #00f0ff;
        }

        .thumbnail-container {
            display: flex;
            gap: 10px;
            overflow-x: auto;
        }

        .thumbnail {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
            border: 2px solid #333;
            cursor: pointer;
            transition: all 0.3s;
        }

        .thumbnail:hover,
        .thumbnail.active {
            border-color: #00f0ff;
            box-shadow: 0 0 10px #00f0ff;
        }

        .item-info h1 {
            color: #ffcc00;
            font-size: 2.5rem;
            margin: 0 0 20px 0;
            text-shadow: 0 0 10px #ffcc00;
        }

        .price-tag {
            font-size: 2rem;
            color: #00f0ff;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .free-badge {
            background-color: #00ff00;
            color: #000;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: bold;
            display: inline-block;
        }

        .info-section {
            margin: 30px 0;
        }

        .info-section h3 {
            color: #00f0ff;
            margin-bottom: 10px;
        }

        .info-section p {
            line-height: 1.6;
            color: #ccc;
        }

        .detail-item {
            margin: 15px 0;
            color: #ccc;
        }

        .detail-item strong {
            color: #00f0ff;
        }

       .contact-section {
    background-color: rgba(0, 240, 255, 0.1);
    border: 2px solid #00f0ff;
    border-radius: 10px;
    padding: 20px;
    margin-top: 30px;
    display: flex;
    flex-direction: column; /* Stack buttons vertically */
    align-items: center; /* Center them horizontally */
    gap: 12px; /* Space between buttons */
}

        .contact-section h3 {
            color: #ffcc00;
            margin-bottom: 15px;
        }

        .contact-btn {
            background: linear-gradient(45deg, #00f0ff, #ff00cc);
            color: #fff;
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            margin: 10px 10px 10px 0;
            transition: all 0.3s;
        }

        .contact-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(0, 240, 255, 0.8);
        }

        .owner-actions {
            margin-top: 30px;
        }

        .edit-btn, .delete-btn {
            padding: 10px 25px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            margin-right: 10px;
        }

        .edit-btn {
            background-color: #ffcc00;
            color: #000;
        }

        .delete-btn {
            background-color: #ff0000;
            color: #fff;
        }

       /* 🌐 Mobile Responsive Styles */
@media (max-width: 768px) {
    body {
        padding-top: 30px;
        background-position: center;
        background-size: cover;
    }

    .item-detail-container {
        margin: 20px auto;
        padding: 0 10px;
    }

    .item-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .main-photo {
        height: 250px;
    }

    .thumbnail {
        width: 70px;
        height: 70px;
    }

    .item-info h1 {
        font-size: 1.8rem;
        text-align: center;
    }

    .price-tag {
        font-size: 1.5rem;
        text-align: center;
    }

    .free-badge {
        display: block;
        margin: 10px auto;
        text-align: center;
    }

    .info-section {
        margin: 20px 0;
        text-align: center;
    }

    .contact-section {
        padding: 15px;
        border-width: 1.5px;
        gap: 10px;
    }

    .contact-section h3 {
        font-size: 1.2rem;
        text-align: center;
    }

    .contact-btn {
        width: 100%;
        max-width: none;
        padding: 10px 0;
        font-size: 1rem;
        margin: 5px 0;
        text-align: center;
    }

    .owner-actions {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .edit-btn, .delete-btn {
        width: 100%;
        max-width: 250px;
    }
}

/* 📱 Extra Small Devices (Phones under 480px) */
@media (max-width: 480px) {
    body {
        padding-top: 20px;
        font-size: 0.9rem;
    }

    .item-detail {
        padding: 20px;
        border-width: 1.5px;
    }

    .main-photo {
        height: 200px;
    }

    .thumbnail {
        width: 60px;
        height: 60px;
    }

    .item-info h1 {
        font-size: 1.5rem;
    }

    .price-tag {
        font-size: 1.3rem;
    }

    .contact-section {
        padding: 10px;
        gap: 8px;
    }

    .contact-section h3 {
        font-size: 1rem;
    }

    .contact-btn {
        padding: 8px 0;
        font-size: 0.95rem;
    }

    .edit-btn, .delete-btn {
        font-size: 0.9rem;
        padding: 8px 0;
    }

    .free-badge {
        padding: 8px 16px;
        font-size: 0.9rem;
    }
}

/* Confirm Modal Styles */
.confirm-modal {
    position: fixed;
    z-index: 1001;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.9);
    backdrop-filter: blur(5px);
    display: none;
}

.confirm-modal-content {
    background: linear-gradient(145deg, #0f0f23, #0a0a1f);
    margin: 15% auto;
    padding: 30px;
    border: 3px solid #00f0ff;
    border-radius: 15px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 0 40px #00f0ff, 0 0 80px #00f0ff, 0 0 120px #00f0ff;
    animation: confirmModalGlow 2s ease-in-out infinite alternate;
    text-align: center;
}

@keyframes confirmModalGlow {
    0% { box-shadow: 0 0 40px #00f0ff, 0 0 80px #00f0ff, 0 0 120px #00f0ff; }
    100% { box-shadow: 0 0 50px #33ffcc, 0 0 100px #33ffcc, 0 0 150px #33ffcc; }
}

.confirm-modal .confirm-title {
    color: #00f0ff;
    margin-bottom: 20px;
    font-size: 1.8rem;
    text-shadow: 0 0 15px #00f0ff;
    font-family: 'Orbitron', sans-serif;
}

.confirm-modal .confirm-message {
    color: #fff;
    font-size: 1.2rem;
    margin-bottom: 30px;
    font-family: 'Orbitron', sans-serif;
}

.confirm-modal-buttons {
    display: flex;
    justify-content: center;
    gap: 20px;
}

.confirm-btn {
    padding: 12px 25px;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    font-family: 'Orbitron', sans-serif;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    font-weight: bold;
}

.confirm-btn.confirm {
    background: linear-gradient(45deg, #00ff66, #33ff99);
    color: #000;
    box-shadow: 0 0 10px #00ff66;
}

.confirm-btn.confirm:hover {
    transform: translateY(-2px);
    box-shadow: 0 0 20px #00ff66;
}

.confirm-btn.cancel {
    background: linear-gradient(45deg, #ff33cc, #ff66ff);
    color: #fff;
    box-shadow: 0 0 10px #ff33cc;
}

.confirm-btn.cancel:hover {
    transform: translateY(-2px);
    box-shadow: 0 0 20px #ff33cc;
}

    </style>
</head>
<body>
    <div id="navbar-placeholder"></div>

    <div class="item-detail-container">
        <a href="marketplace.html" class="back-link">← Back to Marketplace</a>

        <div class="item-detail">
            <div class="item-grid">
                <div class="photo-gallery">
                    <img src="<?php echo htmlspecialchars($photos[0] ?? 'images/placeholder-item.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($item['title']); ?>" 
                         class="main-photo" 
                         id="mainPhoto">
                    
                    <?php if (count($photos) > 1): ?>
                    <div class="thumbnail-container">
                        <?php foreach ($photos as $index => $photo): ?>
                            <img src="<?php echo htmlspecialchars($photo); ?>" 
                                 alt="Photo <?php echo $index + 1; ?>" 
                                 class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                                 onclick="changeMainPhoto('<?php echo htmlspecialchars($photo); ?>', this)">
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="item-info">
                    <h1><?php echo htmlspecialchars($item['title']); ?></h1>
                    
                    <div class="price-tag">
                        <?php if ($item['is_free'] == 1): ?>
                            <span class="free-badge">FREE</span>
                        <?php else: ?>
                            $<?php echo number_format($item['price'], 2); ?> AUD
                        <?php endif; ?>
                    </div>

                    <div class="detail-item">
                        <strong>📍 Location:</strong> <?php echo htmlspecialchars($item['city']); ?>, <?php echo htmlspecialchars($item['state_code']); ?>
                    </div>

                    <div class="detail-item">
                        <strong>📦 Category:</strong> <?php echo ucfirst(str_replace('_', ' ', $item['category'])); ?>
                    </div>

                    <div class="detail-item">
                        <strong>👁️ Views:</strong> <?php echo number_format($item['view_count']); ?>
                    </div>

                    <div class="detail-item">
                        <strong>📅 Posted:</strong> <?php echo date('F j, Y', strtotime($item['created_at'])); ?>
                    </div>

                    <div class="info-section">
                        <h3>Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                    </div>

                    <?php if (!$isOwner): ?>
                    <div class="contact-section">
                        <h3>Contact Seller</h3>
                        
                        <?php if ($item['contact_phone']): ?>
                            <a href="tel:<?php echo htmlspecialchars($item['contact_phone']); ?>" class="contact-btn">
                                📞 Call: <?php echo htmlspecialchars($item['contact_phone']); ?>
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($item['contact_email']): ?>
                            <a href="mailto:<?php echo htmlspecialchars($item['contact_email']); ?>" class="contact-btn">
                                ✉️ Email Seller
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($item['allow_chat'] == 1): ?>
                            <button class="contact-btn" onclick="initiateChat(<?php echo $item['user_id']; ?>)">
                                💬 Chat on Live in Oz
                            </button>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="owner-actions">
                        <p style="color: #ffcc00; font-weight: bold;">This is your listing</p>
                        <button class="edit-btn" onclick="editItem(<?php echo $itemId; ?>)">✏️ Edit</button>
                        <button class="delete-btn" onclick="markAsSold(<?php echo $itemId; ?>)">✅ Mark as Sold</button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Modal -->
    <div id="confirmModal" class="confirm-modal" style="display: none;">
        <div class="confirm-modal-content">
            <div class="confirm-title">Confirm Action</div>
            <div class="confirm-message">Message</div>
            <div class="confirm-modal-buttons">
                <button id="confirmYesBtn" class="confirm-btn confirm">Yes, Confirm</button>
                <button id="confirmNoBtn" class="confirm-btn cancel">Cancel</button>
            </div>
        </div>
    </div>

    <script src="nav-loader.js"></script>
    <script>
        function changeMainPhoto(photoSrc, thumbnail) {
            document.getElementById('mainPhoto').src = photoSrc;
            document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
            thumbnail.classList.add('active');
        }

        function initiateChat(sellerId) {
            // Redirect to DM with seller
            window.location.href = `chat.php?dm=${sellerId}`;
        }

        function editItem(itemId) {
            window.location.href = `edit-marketplace-item.php?id=${itemId}`;
        }

        // Alert modal functions for marketplace-item
        function showConfirmModal(title, message) {
            return new Promise((resolve) => {
                const modal = document.getElementById('confirmModal');
                const titleEl = document.querySelector('.confirm-title');
                const messageEl = document.querySelector('.confirm-message');
                const yesBtn = document.getElementById('confirmYesBtn');
                const noBtn = document.getElementById('confirmNoBtn');

                titleEl.textContent = title;
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

        function showAlert(title, message) {
            // Simple alert using the confirm modal but with only close button
            const modal = document.getElementById('confirmModal');
            const titleEl = document.querySelector('.confirm-title');
            const messageEl = document.querySelector('.confirm-message');
            const buttonsEl = document.querySelector('.confirm-modal-buttons');

            titleEl.textContent = title;
            messageEl.textContent = message;

            // Hide confirm buttons and add close button
            buttonsEl.innerHTML = '<button id="alertCloseBtn" class="confirm-btn cancel">Close</button>';

            modal.style.display = 'block';

            document.getElementById('alertCloseBtn').addEventListener('click', function() {
                modal.style.display = 'none';
            });
        }

        async function markAsSold(itemId) {
            const confirmed = await showConfirmModal('Mark as Sold', 'Mark this item as sold? It will be removed from the marketplace.');

            if (confirmed) {
                fetch('marketplace_mark_sold.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({item_id: itemId})
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Success!', 'Item marked as sold!');
                        setTimeout(() => {
                            window.location.href = 'marketplace.html';
                        }, 2000); // Wait 2 seconds before redirect
                    } else {
                        showAlert('Error', 'Error: ' + data.message);
                    }
                })
                .catch(error => {
                    showAlert('Error', 'An error occurred. Please try again.');
                });
            }
        }
    </script>
</body>
</html>
