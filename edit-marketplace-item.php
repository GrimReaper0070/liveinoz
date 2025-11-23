<?php
// edit-marketplace-item.php
session_start();
require 'config.php';

$pdo = getDBConnection();

// Get item ID
$itemId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($itemId === 0) {
    header('Location: marketplace.html');
    exit;
}

// Check if user is logged in
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: login.html');
    exit;
}

// Fetch item details and verify ownership
$stmt = $pdo->prepare("
    SELECT mi.*, u.first_name, u.last_name
    FROM marketplace_items mi
    JOIN users u ON mi.user_id = u.id
    WHERE mi.id = ? AND mi.status IN ('approved', 'pending')
");
$stmt->execute([$itemId]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header('Location: marketplace.html');
    exit;
}

if ($item['user_id'] != $userId) {
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item | Live in Oz Marketplace</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="global.css">
    <style>
        body {
            background: url('images/retro-background.jpg');
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

        .edit-form-container {
            background-color: rgba(26, 26, 26, 0.9);
            border: 2px solid #00f0ff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 0 30px rgba(0, 240, 255, 0.5);
        }

        .edit-form-container h1 {
            color: #ffcc00;
            text-shadow: 0 0 10px #ffcc00;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            text-align: left;
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

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .file-upload-area {
            border: 2px dashed #00f0ff;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-upload-area:hover {
            background-color: rgba(0, 240, 255, 0.1);
        }

        .file-upload-area input[type="file"] {
            display: none;
        }

        .current-photos {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .current-photo {
            position: relative;
            width: 100px;
            height: 100px;
        }

        .current-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #00f0ff;
        }

        .current-photo .remove-btn {
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

        .required {
            color: #ff00cc;
        }

        @media (max-width: 768px) {
            .edit-container {
                padding: 0 15px;
            }
        }
    </style>
</head>
<body>
    <div id="navbar-placeholder"></div>

    <div class="edit-container">
        <a href="marketplace-item.php?id=<?php echo $itemId; ?>" class="back-link">← Back to Item</a>

        <div class="edit-form-container">
            <h1>✏️ Edit Item</h1>

            <form id="editItemForm" enctype="multipart/form-data">
                <input type="hidden" name="item_id" value="<?php echo $itemId; ?>">

                <div class="form-group">
                    <label for="itemTitle">Item Title <span class="required">*</span></label>
                    <input
                        type="text"
                        id="itemTitle"
                        name="title"
                        required
                        maxlength="255"
                        value="<?php echo htmlspecialchars($item['title']); ?>"
                    />
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
                    <textarea
                        id="description"
                        name="description"
                        required
                    ><?php echo htmlspecialchars($item['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input
                            type="checkbox"
                            id="isFree"
                            name="is_free"
                            onchange="togglePrice()"
                            <?php echo $item['is_free'] == 1 ? 'checked' : ''; ?>
                        />
                        <label for="isFree">This item is FREE</label>
                    </div>
                </div>

                <div class="form-group" id="priceGroup" <?php echo $item['is_free'] == 1 ? 'style="display: none;"' : ''; ?>>
                    <label for="price">Price (AUD) <span class="required">*</span></label>
                    <input
                        type="number"
                        id="price"
                        name="price"
                        min="0"
                        step="0.01"
                        value="<?php echo $item['is_free'] == 1 ? '' : number_format($item['price'], 2); ?>"
                        <?php echo $item['is_free'] == 1 ? '' : 'required'; ?>
                    />
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
                        <input
                            type="checkbox"
                            id="contactPhone"
                            name="contact_phone_check"
                            onchange="toggleContactField('phone')"
                            <?php echo !empty($item['contact_phone']) ? 'checked' : ''; ?>
                        />
                        <label for="contactPhone">Phone</label>
                    </div>
                    <input
                        type="tel"
                        id="phoneNumber"
                        name="contact_phone"
                        style="display: <?php echo !empty($item['contact_phone']) ? 'block' : 'none'; ?>; margin-top: 10px"
                        value="<?php echo htmlspecialchars($item['contact_phone'] ?? ''); ?>"
                        <?php echo !empty($item['contact_phone']) ? 'required' : ''; ?>
                        placeholder="+61 4XX XXX XXX"
                    />

                    <div class="checkbox-group" style="margin-top: 10px">
                        <input
                            type="checkbox"
                            id="contactEmail"
                            name="contact_email_check"
                            onchange="toggleContactField('email')"
                            <?php echo !empty($item['contact_email']) ? 'checked' : ''; ?>
                        />
                        <label for="contactEmail">Email</label>
                    </div>
                    <input
                        type="email"
                        id="emailAddress"
                        name="contact_email"
                        style="display: <?php echo !empty($item['contact_email']) ? 'block' : 'none'; ?>; margin-top: 10px"
                        value="<?php echo htmlspecialchars($item['contact_email'] ?? ''); ?>"
                        <?php echo !empty($item['contact_email']) ? 'required' : ''; ?>
                        placeholder="your@email.com"
                    />

                    <div class="checkbox-group" style="margin-top: 10px">
                        <input type="checkbox" id="allowChat" name="allow_chat" <?php echo $item['allow_chat'] == 1 ? 'checked' : ''; ?> />
                        <label for="allowChat">Allow chat on Live in Oz</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Current Photos</label>
                    <div class="current-photos" id="currentPhotos">
                        <?php foreach ($photos as $index => $photo): ?>
                            <div class="current-photo">
                                <img src="<?php echo htmlspecialchars($photo); ?>" alt="Photo <?php echo $index + 1; ?>">
                                <button type="button" class="remove-btn" onclick="removeExistingPhoto('<?php echo htmlspecialchars($photo); ?>', this)">×</button>
                                <input type="hidden" name="existing_photos[]" value="<?php echo htmlspecialchars($photo); ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <label>Add New Photos (Up to <?php echo (5 - count($photos)); ?> more)</label>
                    <div class="file-upload-area" onclick="document.getElementById('photoUpload').click()">
                        <p>📸 Click to add more photos</p>
                        <p style="font-size: 0.8rem; color: #ccc">JPEG, PNG, or WebP • Max 5MB each</p>
                        <input
                            type="file"
                            id="photoUpload"
                            name="photos[]"
                            multiple
                            accept="image/jpeg,image/png,image/webp"
                            onchange="handleFileSelect(event)"
                        />
                    </div>
                    <div class="preview-container" id="previewContainer"></div>
                </div>

                <button type="submit" class="submit-btn">💾 Update Item</button>
            </form>
        </div>
    </div>

    <script src="nav-loader.js"></script>
    <script>
        let selectedFiles = [];
        let removedPhotos = [];

        // Toggle price field
        function togglePrice() {
            const isFree = document.getElementById('isFree').checked;
            const priceGroup = document.getElementById('priceGroup');
            const priceInput = document.getElementById('price');

            if (isFree) {
                priceGroup.style.display = 'none';
                priceInput.value = '0';
                priceInput.required = false;
            } else {
                priceGroup.style.display = 'block';
                priceInput.value = '';
                priceInput.required = true;
            }
        }

        // Toggle contact fields
        function toggleContactField(type) {
            if (type === 'phone') {
                const checkbox = document.getElementById('contactPhone');
                const field = document.getElementById('phoneNumber');
                field.style.display = checkbox.checked ? 'block' : 'none';
                field.required = checkbox.checked;
            } else if (type === 'email') {
                const checkbox = document.getElementById('contactEmail');
                const field = document.getElementById('emailAddress');
                field.style.display = checkbox.checked ? 'block' : 'none';
                field.required = checkbox.checked;
            }
        }

        // Handle file selection
        function handleFileSelect(event) {
            const files = Array.from(event.target.files);
            const maxTotalPhotos = 5;
            const currentPhotos = document.querySelectorAll('#currentPhotos .current-photo').length - removedPhotos.length;

            if (currentPhotos + selectedFiles.length + files.length > maxTotalPhotos) {
                alert(`You can only have up to ${maxTotalPhotos} photos total.`);
                return;
            }

            files.forEach(file => {
                if (!file.type.match(/^image\/(jpeg|png|webp)$/)) {
                    alert(`${file.name} is not a valid image format. Use JPEG, PNG, or WebP.`);
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    alert(`${file.name} is too large. Max size is 5MB.`);
                    return;
                }

                selectedFiles.push(file);
                displayPreview(file);
            });
        }

        // Display preview
        function displayPreview(file) {
            const reader = new FileReader();
            const previewContainer = document.getElementById('previewContainer');

            reader.onload = function(e) {
                const previewDiv = document.createElement('div');
                previewDiv.className = 'current-photo';

                const img = document.createElement('img');
                img.src = e.target.result;

                const removeBtn = document.createElement('button');
                removeBtn.className = 'remove-btn';
                removeBtn.innerHTML = '×';
                removeBtn.onclick = function() {
                    removeFile(file);
                    previewDiv.remove();
                };

                previewDiv.appendChild(img);
                previewDiv.appendChild(removeBtn);
                previewContainer.appendChild(previewDiv);
            };

            reader.readAsDataURL(file);
        }

        // Remove new file
        function removeFile(file) {
            selectedFiles = selectedFiles.filter(f => f !== file);
        }

        // Remove existing photo
        function removeExistingPhoto(photoPath, buttonElement) {
            if (confirm('Remove this photo?')) {
                removedPhotos.push(photoPath);
                buttonElement.parentElement.remove();
            }
        }

        // Form submission
        document.getElementById('editItemForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate contact method
            const phoneCheck = document.getElementById('contactPhone').checked;
            const emailCheck = document.getElementById('contactEmail').checked;
            const chatCheck = document.getElementById('allowChat').checked;

            if (!phoneCheck && !emailCheck && !chatCheck) {
                alert('Please select at least one contact method.');
                return;
            }

            // Create FormData
            const formData = new FormData();
            formData.append('item_id', document.querySelector('input[name="item_id"]').value);
            formData.append('title', document.getElementById('itemTitle').value);
            formData.append('category', document.getElementById('category').value);
            formData.append('description', document.getElementById('description').value);
            formData.append('is_free', document.getElementById('isFree').checked ? 1 : 0);
            formData.append('price', document.getElementById('isFree').checked ? 0 : document.getElementById('price').value);
            formData.append('city', document.getElementById('city').value);

            // Contact methods
            if (phoneCheck) {
                formData.append('contact_phone', document.getElementById('phoneNumber').value);
            }
            if (emailCheck) {
                formData.append('contact_email', document.getElementById('emailAddress').value);
            }
            formData.append('allow_chat', chatCheck ? 1 : 0);

            // Existing photos
            const existingPhotos = document.querySelectorAll('input[name="existing_photos[]"]');
            existingPhotos.forEach(input => {
                if (!removedPhotos.includes(input.value)) {
                    formData.append('existing_photos[]', input.value);
                }
            });

            // Removed photos
            removedPhotos.forEach(photo => {
                formData.append('removed_photos[]', photo);
            });

            // New photos
            selectedFiles.forEach(file => {
                formData.append('new_photos[]', file);
            });

            // Disable submit button
            const submitBtn = document.querySelector('.submit-btn');
            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ Updating...';

            // Send to server
            fetch('marketplace_update_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Item updated successfully!');
                    window.location.href = `marketplace-item.php?id=${data.item_id}`;
                } else {
                    alert('❌ Error: ' + (data.message || 'Failed to update item.'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ An error occurred. Please try again.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = '💾 Update Item';
            });
        });
    </script>
</body>
</html>
