<?php
/**
 * Edit Marketplace Item Page
 * File: marketplace_update.php
 * Displays form to edit existing marketplace listing
 */

session_start();
require_once __DIR__ . '/config.php';

// Check authentication
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    header('Location: login.html');
    exit;
}

$pdo = getDBConnection();

// Get item ID
$itemId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($itemId === 0) {
    header('Location: marketplace.html');
    exit;
}

// Fetch item details
$stmt = $pdo->prepare("
    SELECT * FROM marketplace_items 
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$itemId, $userId]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header('Location: marketplace.html');
    exit;
}

// Fetch existing photos
$photoStmt = $pdo->prepare("
    SELECT id, photo_path 
    FROM marketplace_photos 
    WHERE item_id = ? 
    ORDER BY photo_order ASC
");
$photoStmt->execute([$itemId]);
$photos = $photoStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Listing | Live in Oz</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="global.css">
    <style>
        body {
            background: url('images/retro-background.jpg') no-repeat center center fixed;
            background-color: #000014;
            font-family: 'Orbitron', sans-serif;
            color: #fff;
            padding-top: 60px;
        }

        .edit-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .edit-form {
            background-color: rgba(26, 26, 26, 0.9);
            border: 2px solid #00f0ff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 0 30px rgba(0, 240, 255, 0.5);
        }

        .edit-form h1 {
            color: #00f0ff;
            text-shadow: 0 0 10px #00f0ff;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            color: #00f0ff;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            background-color: #0a0a0a;
            color: #fff;
            border: 1px solid #00f0ff;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-group input[type="checkbox"] {
            width: auto;
            margin-right: 10px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .existing-photos {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 15px;
        }

        .photo-item {
            position: relative;
            width: 150px;
            height: 150px;
        }

        .photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #00f0ff;
        }

        .photo-item .remove-btn {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: #ff0000;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            font-weight: bold;
            font-size: 18px;
        }

        .file-upload-area {
            border: 2px dashed #00f0ff;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 15px;
        }

        .file-upload-area:hover {
            background-color: rgba(0, 240, 255, 0.1);
        }

        .file-upload-area input[type="file"] {
            display: none;
        }

        .preview-container {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 15px;
        }

        .preview-image {
            position: relative;
            width: 100px;
            height: 100px;
        }

        .preview-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #00f0ff;
        }

        .preview-image .remove-btn {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #ff0000;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
            font-weight: bold;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(45deg, #00f0ff, #ff00cc);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Orbitron', sans-serif;
        }

        .submit-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 0 20px rgba(0, 240, 255, 0.8);
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

        .required {
            color: #ff00cc;
        }

        .photo-count-info {
            color: #ffcc00;
            font-size: 0.9rem;
            margin-top: 10px;
        }
    </style>
    <style>
        /* Alert/Confirm Modal Styles */
        .alert-modal {
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

        .alert-modal-content {
            background: linear-gradient(145deg, #0f0f23, #0a0a1f);
            margin: 15% auto;
            padding: 30px;
            border: 3px solid #00f0ff;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 0 40px #00f0ff, 0 0 80px #00f0ff, 0 0 120px #00f0ff;
            animation: alertModalGlow 2s ease-in-out infinite alternate;
            text-align: center;
        }

        @keyframes alertModalGlow {
            0% { box-shadow: 0 0 40px #00f0ff, 0 0 80px #00f0ff, 0 0 120px #00f0ff; }
            100% { box-shadow: 0 0 50px #33ffcc, 0 0 100px #33ffcc, 0 0 150px #33ffcc; }
        }

        .alert-modal.error .alert-modal-content {
            border: 3px solid #ff33cc;
            box-shadow: 0 0 40px #ff33cc, 0 0 80px #ff33cc, 0 0 120px #ff33cc;
            animation: alertErrorGlow 2s ease-in-out infinite alternate;
        }

        @keyframes alertErrorGlow {
            0% { box-shadow: 0 0 40px #ff33cc, 0 0 80px #ff33cc, 0 0 120px #ff33cc; }
            100% { box-shadow: 0 0 50px #ff6600, 0 0 100px #ff6600, 0 0 150px #ff6600; }
        }

        .alert-modal.success .alert-modal-content {
            border: 3px solid #00ff66;
            box-shadow: 0 0 40px #00ff66, 0 0 80px #00ff66, 0 0 120px #00ff66;
            animation: alertSuccessGlow 2s ease-in-out infinite alternate;
        }

        @keyframes alertSuccessGlow {
            0% { box-shadow: 0 0 40px #00ff66, 0 0 80px #00ff66, 0 0 120px #00ff66; }
            100% { box-shadow: 0 0 50px #33ff99, 0 0 100px #33ff99, 0 0 150px #33ff99; }
        }

        .alert-title {
            color: #00f0ff;
            margin-bottom: 20px;
            font-size: 1.8rem;
            text-shadow: 0 0 15px #00f0ff;
            font-family: 'Orbitron', sans-serif;
        }

        .alert-modal.error .alert-title {
            color: #ff33cc;
            text-shadow: 0 0 15px #ff33cc;
        }

        .alert-modal.success .alert-title {
            color: #00ff66;
            text-shadow: 0 0 15px #00ff66;
        }

        .alert-message {
            color: #fff;
            font-size: 1.1rem;
            margin-bottom: 30px;
            font-family: 'Orbitron', sans-serif;
        }

        .alert-modal-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .alert-btn {
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

        .alert-btn.confirm {
            background: linear-gradient(45deg, #00ff66, #33ff99);
            color: #000;
            box-shadow: 0 0 10px #00ff66;
        }

        .alert-btn.confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 20px #00ff66;
        }

        .alert-btn.cancel {
            background: linear-gradient(45deg, #ff33cc, #ff66ff);
            color: #fff;
            box-shadow: 0 0 10px #ff33cc;
        }

        .alert-btn.cancel:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 20px #ff33cc;
        }

        .alert-btn.close {
            background: linear-gradient(45deg, #666, #999);
            color: #fff;
            box-shadow: 0 0 10px #666;
        }

        .alert-btn.close:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 20px #999;
        }
    </style>
</head>
<body>
    <div id="navbar-placeholder"></div>

    <div class="edit-container">
        <a href="marketplace-item.php?id=<?php echo $itemId; ?>" class="back-link">← Back to Listing</a>

        <div class="edit-form">
            <h1>✏️ Edit Your Listing</h1>

            <form id="editItemForm" enctype="multipart/form-data">
                <input type="hidden" name="item_id" value="<?php echo $itemId; ?>">

                <div class="form-group">
                    <label for="itemTitle">Item Title <span class="required">*</span></label>
                    <input type="text" id="itemTitle" name="title" required maxlength="255" 
                           value="<?php echo htmlspecialchars($item['title']); ?>">
                </div>

                <div class="form-group">
                    <label for="category">Category <span class="required">*</span></label>
                    <select id="category" name="category" required>
                        <option value="">Select a category</option>
                        <option value="electronics" <?php echo $item['category'] == 'electronics' ? 'selected' : ''; ?>>📱 Electronics</option>
                        <option value="furniture" <?php echo $item['category'] == 'furniture' ? 'selected' : ''; ?>>🛋️ Furniture</option>
                        <option value="vehicles" <?php echo $item['category'] == 'vehicles' ? 'selected' : ''; ?>>🚗 Vehicles</option>
                        <option value="clothing" <?php echo $item['category'] == 'clothing' ? 'selected' : ''; ?>>👕 Clothing</option>
                        <option value="miscellaneous" <?php echo $item['category'] == 'miscellaneous' ? 'selected' : ''; ?>>📦 Miscellaneous</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Description <span class="required">*</span></label>
                    <textarea id="description" name="description" required><?php echo htmlspecialchars($item['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="isFree" name="is_free" 
                               <?php echo $item['is_free'] ? 'checked' : ''; ?>
                               onchange="togglePrice()">
                        <label for="isFree">This item is FREE</label>
                    </div>
                </div>

                <div class="form-group" id="priceGroup" style="<?php echo $item['is_free'] ? 'display: none;' : ''; ?>">
                    <label for="price">Price (AUD) <span class="required">*</span></label>
                    <input type="number" id="price" name="price" min="0" step="0.01" 
                           value="<?php echo $item['price']; ?>">
                </div>

                <div class="form-group">
                    <label for="city">City <span class="required">*</span></label>
                    <select id="city" name="city" required>
                        <option value="">Select your city</option>
                        <option value="Sydney" <?php echo $item['city'] == 'Sydney' ? 'selected' : ''; ?>>Sydney, NSW</option>
                        <option value="Melbourne" <?php echo $item['city'] == 'Melbourne' ? 'selected' : ''; ?>>Melbourne, VIC</option>
                        <option value="Brisbane" <?php echo $item['city'] == 'Brisbane' ? 'selected' : ''; ?>>Brisbane, QLD</option>
                        <option value="Perth" <?php echo $item['city'] == 'Perth' ? 'selected' : ''; ?>>Perth, WA</option>
                        <option value="Adelaide" <?php echo $item['city'] == 'Adelaide' ? 'selected' : ''; ?>>Adelaide, SA</option>
                        <option value="Hobart" <?php echo $item['city'] == 'Hobart' ? 'selected' : ''; ?>>Hobart, TAS</option>
                        <option value="Darwin" <?php echo $item['city'] == 'Darwin' ? 'selected' : ''; ?>>Darwin, NT</option>
                        <option value="Canberra" <?php echo $item['city'] == 'Canberra' ? 'selected' : ''; ?>>Canberra, ACT</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Contact Method <span class="required">*</span></label>
                    <div class="checkbox-group">
                        <input type="checkbox" id="contactPhone" name="contact_phone_check"
                               <?php echo $item['contact_phone'] ? 'checked' : ''; ?>
                               onchange="toggleContactField('phone')">
                        <label for="contactPhone">Phone</label>
                    </div>
                    <input type="tel" id="phoneNumber" name="contact_phone"
                           style="<?php echo $item['contact_phone'] ? 'display: block;' : 'display: none;'; ?> margin-top: 10px"
                           placeholder="+61 4XX XXX XXX"
                           value="<?php echo htmlspecialchars($item['contact_phone'] ?? ''); ?>">

                    <div class="checkbox-group" style="margin-top: 10px">
                        <input type="checkbox" id="contactEmail" name="contact_email_check"
                               <?php echo $item['contact_email'] ? 'checked' : ''; ?>
                               onchange="toggleContactField('email')">
                        <label for="contactEmail">Email</label>
                    </div>
                    <input type="email" id="emailAddress" name="contact_email"
                           style="<?php echo $item['contact_email'] ? 'display: block;' : 'display: none;'; ?> margin-top: 10px"
                           placeholder="your@email.com"
                           value="<?php echo htmlspecialchars($item['contact_email'] ?? ''); ?>">

                    <div class="checkbox-group" style="margin-top: 10px">
                        <input type="checkbox" id="allowChat" name="allow_chat" 
                               <?php echo $item['allow_chat'] ? 'checked' : ''; ?>>
                        <label for="allowChat">Allow chat on Live in Oz</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Existing Photos</label>
                    <div class="existing-photos" id="existingPhotos">
                        <?php foreach ($photos as $photo): ?>
                            <div class="photo-item" data-photo-id="<?php echo $photo['id']; ?>">
                                <img src="<?php echo htmlspecialchars($photo['photo_path']); ?>" alt="Item photo">
                                <button type="button" class="remove-btn" onclick="markPhotoForRemoval(<?php echo $photo['id']; ?>)">×</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="photo-count-info" id="photoCountInfo">
                        Current photos: <?php echo count($photos); ?>/5
                    </p>
                </div>

                <div class="form-group">
                    <label>Add New Photos (Up to 5 total)</label>
                    <div class="file-upload-area" onclick="document.getElementById('newPhotoUpload').click()">
                        <p>📸 Click to upload new photos</p>
                        <p style="font-size: 0.8rem; color: #ccc">JPEG, PNG, or WebP • Max 5MB each</p>
                        <input type="file" id="newPhotoUpload" name="new_photos[]" multiple
                               accept="image/jpeg,image/png,image/webp"
                               onchange="handleNewFileSelect(event)">
                    </div>
                    <div class="preview-container" id="newPreviewContainer"></div>
                </div>

                <button type="submit" class="submit-btn">💾 Save Changes</button>
            </form>
        </div>
    </div>

    <!-- Alert/Confirm Modal -->
    <div id="alertModal" class="alert-modal" style="display: none;">
        <div class="alert-modal-content">
            <div class="alert-title">Alert</div>
            <div class="alert-message">Message</div>
            <div class="alert-modal-buttons">
                <button id="alertConfirmBtn" class="alert-btn confirm">Yes, Confirm</button>
                <button id="alertCancelBtn" class="alert-btn cancel">Cancel</button>
                <button id="alertCloseBtn" class="alert-btn close" style="display: none;">Close</button>
            </div>
        </div>
    </div>

    <script src="nav-loader.js"></script>
    <script>
        const photosToRemove = new Set();
        const newPhotosArray = [];
        const maxPhotos = 5;
        let currentPhotoCount = <?php echo count($photos); ?>;

        // Modal functions for marketplace_update
        function showConfirmModal(title, message) {
            return new Promise((resolve) => {
                const modal = document.getElementById('alertModal');
                const modalContent = document.querySelector('.alert-modal-content');
                const titleEl = document.querySelector('.alert-title');
                const messageEl = document.querySelector('.alert-message');
                const confirmBtn = document.getElementById('alertConfirmBtn');
                const cancelBtn = document.getElementById('alertCancelBtn');
                const closeBtn = document.getElementById('alertCloseBtn');

                // Reset modal state
                modal.classList.remove('success', 'error');
                closeBtn.style.display = 'none';
                confirmBtn.style.display = 'inline-block';
                cancelBtn.style.display = 'inline-block';

                titleEl.textContent = title;
                messageEl.textContent = message;
                modal.style.display = 'block';

                const handleConfirm = () => {
                    modal.style.display = 'none';
                    cleanup();
                    resolve(true);
                };

                const handleCancel = () => {
                    modal.style.display = 'none';
                    cleanup();
                    resolve(false);
                };

                const cleanup = () => {
                    confirmBtn.removeEventListener('click', handleConfirm);
                    cancelBtn.removeEventListener('click', handleCancel);
                };

                confirmBtn.addEventListener('click', handleConfirm);
                cancelBtn.addEventListener('click', handleCancel);
            });
        }

        function showAlert(type, title, message) {
            const modal = document.getElementById('alertModal');
            const titleEl = document.querySelector('.alert-title');
            const messageEl = document.querySelector('.alert-message');
            const confirmBtn = document.getElementById('alertConfirmBtn');
            const cancelBtn = document.getElementById('alertCancelBtn');
            const closeBtn = document.getElementById('alertCloseBtn');

            // Reset modal state
            modal.classList.remove('success', 'error');
            modal.classList.add(type);

            titleEl.textContent = title;
            messageEl.textContent = message;

            // Hide confirm/cancel buttons, show close button
            confirmBtn.style.display = 'none';
            cancelBtn.style.display = 'none';
            closeBtn.style.display = 'inline-block';

            modal.style.display = 'block';

            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        }

        function togglePrice() {
            const isFree = document.getElementById('isFree').checked;
            const priceGroup = document.getElementById('priceGroup');
            const priceInput = document.getElementById('price');
            
            if (isFree) {
                priceGroup.style.display = 'none';
                priceInput.removeAttribute('required');
            } else {
                priceGroup.style.display = 'block';
                priceInput.setAttribute('required', 'required');
            }
        }

        function toggleContactField(type) {
            const checkbox = document.getElementById(`contact${type.charAt(0).toUpperCase() + type.slice(1)}`);
            const field = type === 'phone' ? document.getElementById('phoneNumber') : document.getElementById('emailAddress');
            
            if (checkbox.checked) {
                field.style.display = 'block';
                field.setAttribute('required', 'required');
            } else {
                field.style.display = 'none';
                field.removeAttribute('required');
                field.value = '';
            }
        }

        async function markPhotoForRemoval(photoId) {
            const confirmed = await showConfirmModal('Remove Photo', 'Remove this photo?');
            if (confirmed) {
                photosToRemove.add(photoId);
                const photoItem = document.querySelector(`[data-photo-id="${photoId}"]`);
                photoItem.style.opacity = '0.3';
                photoItem.style.border = '2px solid #ff0000';
                currentPhotoCount--;
                updatePhotoCount();
            }
        }

        function handleNewFileSelect(event) {
            const files = Array.from(event.target.files);
            const container = document.getElementById('newPreviewContainer');

            // Check total photo limit
            const totalPhotos = currentPhotoCount + newPhotosArray.length + files.length;
            if (totalPhotos > maxPhotos) {
                showAlert('error', 'Photo Limit Exceeded', `You can only have up to ${maxPhotos} photos total. Current: ${currentPhotoCount}, Trying to add: ${files.length}`);
                event.target.value = '';
                return;
            }

            files.forEach((file, index) => {
                if (file.size > 5 * 1024 * 1024) {
                    showAlert('error', 'File Too Large', `${file.name} is too large (max 5MB)`);
                    return;
                }

                newPhotosArray.push(file);

                const reader = new FileReader();
                reader.onload = (e) => {
                    const preview = document.createElement('div');
                    preview.className = 'preview-image';
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="New photo">
                        <button type="button" class="remove-btn" onclick="removeNewPhoto(${newPhotosArray.length - 1})">×</button>
                    `;
                    container.appendChild(preview);
                };
                reader.readAsDataURL(file);
            });

            updatePhotoCount();
        }

        function removeNewPhoto(index) {
            newPhotosArray.splice(index, 1);
            const container = document.getElementById('newPreviewContainer');
            container.innerHTML = '';
            
            newPhotosArray.forEach((file, idx) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const preview = document.createElement('div');
                    preview.className = 'preview-image';
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="New photo">
                        <button type="button" class="remove-btn" onclick="removeNewPhoto(${idx})">×</button>
                    `;
                    container.appendChild(preview);
                };
                reader.readAsDataURL(file);
            });

            updatePhotoCount();
        }

        function updatePhotoCount() {
            const total = currentPhotoCount + newPhotosArray.length;
            document.getElementById('photoCountInfo').textContent = `Current photos: ${total}/5`;
        }

        document.getElementById('editItemForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(e.target);
            
            // Add photos to remove
            photosToRemove.forEach(id => {
                formData.append('remove_photos[]', id);
            });

            // Add new photos
            const fileInput = document.getElementById('newPhotoUpload');
            if (newPhotosArray.length > 0) {
                newPhotosArray.forEach(file => {
                    formData.append('new_photos[]', file);
                });
            }

            try {
                const response = await fetch('marketplace_update_process.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('success', 'Success!', data.message);
                    setTimeout(() => {
                        window.location.href = `marketplace-item.php?id=<?php echo $itemId; ?>`;
                    }, 2000); // Wait 2 seconds before redirect
                } else {
                    showAlert('error', 'Update Failed', data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('error', 'Connection Error', 'Failed to update listing. Please try again.');
            }
        });
    </script>
</body>
</html>
