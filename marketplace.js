// marketplace.js - Complete marketplace functionality

let selectedFiles = [];
const MAX_FILES = 5;
const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

// Load marketplace items on page load
document.addEventListener("DOMContentLoaded", function () {
  loadMarketplaceItems();
});

// Open post modal
function openPostModal() {
  console.log("openPostModal called");
  // Check user authentication and type before opening modal
  checkUserAuth(function(isLoggedIn) {
    console.log("isLoggedIn value:", isLoggedIn);
    if (!isLoggedIn) {
      console.log("Showing login required alert");
      showAlertModal('error', 'Login Required', 'You must be logged in to post items.');
      return;
    }

    // Check user type
    fetch("check_auth.php", { credentials: 'include' })
      .then((response) => {
        console.log("User type check response status:", response.status);
        return response.json();
      })
      .then((data) => {
        console.log("User type check response:", data);
        if (data.authenticated && data.user) {
          if (data.user.user_type === 'chat_only') {
            showAlertModal('error', 'Access Restricted', 'Your account type only allows chat access. Please upgrade to post marketplace items.');
            return;
          }

          // User has full access, show the modal
          console.log("Opening post modal...");
          document.getElementById("postModal").style.display = "block";
        } else {
          showAlertModal('error', 'Authentication Error', 'Please log in again.');
        }
      })
      .catch((error) => {
        console.error("Auth check error:", error);
        showAlertModal('error', 'Error', 'Unable to verify your account. Please try again.');
      });
  });
}

// Close post modal
function closePostModal() {
  document.getElementById("postModal").style.display = "none";
  document.getElementById("postItemForm").reset();
  selectedFiles = [];
  document.getElementById("previewContainer").innerHTML = "";
}

// Toggle price field based on "is free" checkbox
function togglePrice() {
  const isFree = document.getElementById("isFree").checked;
  const priceGroup = document.getElementById("priceGroup");
  const priceInput = document.getElementById("price");

  if (isFree) {
    priceGroup.style.display = "none";
    priceInput.value = "0";
    priceInput.required = false;
  } else {
    priceGroup.style.display = "block";
    priceInput.value = "";
    priceInput.required = true;
  }
}

// Toggle contact method fields
function toggleContactField(type) {
  if (type === "phone") {
    const checkbox = document.getElementById("contactPhone");
    const field = document.getElementById("phoneNumber");
    field.style.display = checkbox.checked ? "block" : "none";
    field.required = checkbox.checked;
  } else if (type === "email") {
    const checkbox = document.getElementById("contactEmail");
    const field = document.getElementById("emailAddress");
    field.style.display = checkbox.checked ? "block" : "none";
    field.required = checkbox.checked;
  }
}

// Handle file selection
function handleFileSelect(event) {
  const files = Array.from(event.target.files);

  if (selectedFiles.length + files.length > MAX_FILES) {
    showAlertModal('error', 'Upload Limit', `You can only upload up to ${MAX_FILES} images.`);
    return;
  }

  files.forEach((file) => {
    if (!file.type.match(/^image\/(jpeg|png|webp)$/)) {
      showAlertModal('error', 'Invalid Format', `${file.name} is not a valid image format. Use JPEG, PNG, or WebP.`);
      return;
    }

    if (file.size > MAX_FILE_SIZE) {
      showAlertModal('error', 'File Too Large', `${file.name} is too large. Max size is 5MB.`);
      return;
    }

    selectedFiles.push(file);
    displayPreview(file);
  });
}

// Display image preview
function displayPreview(file) {
  const reader = new FileReader();
  const previewContainer = document.getElementById("previewContainer");

  reader.onload = function (e) {
    const previewDiv = document.createElement("div");
    previewDiv.className = "preview-image";

    const img = document.createElement("img");
    img.src = e.target.result;

    const removeBtn = document.createElement("button");
    removeBtn.className = "remove-btn";
    removeBtn.innerHTML = "&times;";
    removeBtn.onclick = function () {
      removeFile(file);
      previewDiv.remove();
    };

    previewDiv.appendChild(img);
    previewDiv.appendChild(removeBtn);
    previewContainer.appendChild(previewDiv);
  };

  reader.readAsDataURL(file);
}

// Remove file from selection
function removeFile(file) {
  selectedFiles = selectedFiles.filter((f) => f !== file);
}

// Handle form submission
document
  .getElementById("postItemForm")
  .addEventListener("submit", function (e) {
    e.preventDefault();

    // Validate at least one contact method
    const phoneCheck = document.getElementById("contactPhone").checked;
    const emailCheck = document.getElementById("contactEmail").checked;
    const chatCheck = document.getElementById("allowChat").checked;

    if (!phoneCheck && !emailCheck && !chatCheck) {
      showAlertModal('error', 'Contact Required', "Please select at least one contact method.");
      return;
    }

    // Validate at least one photo
    if (selectedFiles.length === 0) {
      showAlertModal('error', 'Photos Required', "Please upload at least one photo of your item.");
      return;
    }

    // Create FormData
    const formData = new FormData();
    formData.append("title", document.getElementById("itemTitle").value);
    formData.append("category", document.getElementById("category").value);
    formData.append(
      "description",
      document.getElementById("description").value
    );
    formData.append(
      "is_free",
      document.getElementById("isFree").checked ? 1 : 0
    );
    formData.append(
      "price",
      document.getElementById("isFree").checked
        ? 0
        : document.getElementById("price").value
    );
    formData.append("city", document.getElementById("city").value);

    // Contact methods
    if (phoneCheck) {
      formData.append(
        "contact_phone",
        document.getElementById("phoneNumber").value
      );
    }
    if (emailCheck) {
      formData.append(
        "contact_email",
        document.getElementById("emailAddress").value
      );
    }
    formData.append("allow_chat", chatCheck ? 1 : 0);

    // Append photos
    selectedFiles.forEach((file, index) => {
      formData.append("photos[]", file);
    });

    // Disable submit button
    const submitBtn = document.querySelector(".submit-btn");
    submitBtn.disabled = true;
    submitBtn.textContent = "⏳ Uploading...";

    // Send to server (NO api/ prefix)
    fetch("marketplace_post.php", {
      method: "POST",
      body: formData,
      credentials: 'include'
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showAlertModal('success', 'Success!', "Your item has been submitted for review! You'll be notified once it's approved.");
          closePostModal();
          loadMarketplaceItems();
        } else {
          if (data.message && data.message.includes("log in")) {
            showAlertModal('error', 'Login Required', "You need to log in to post items. Please visit the login page.");
          } else {
            showAlertModal('error', 'Error', (data.message || "Failed to post item."));
          }
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showAlertModal('error', 'Error', "An error occurred. Please try again.");
      })
      .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = "🚀 Submit for Review";
      });
  });

// Load marketplace items
function loadMarketplaceItems() {
  const category = document.getElementById("categoryFilter")?.value || "";
  const city = document.getElementById("cityFilter")?.value || "";
  const sort = document.getElementById("priceSort")?.value || "";

  // Updated path - NO api/ prefix
  fetch(`marketplace_get.php?category=${category}&city=${city}&sort=${sort}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        displayMarketplaceItems(data.items);
      } else {
        console.error("Failed to load items:", data.message);
      }
    })
    .catch((error) => {
      console.error("Error loading items:", error);
    });
}

// Display marketplace items
function displayMarketplaceItems(items) {
  const grid = document.getElementById("marketplaceGrid");

  if (items.length === 0) {
    grid.innerHTML =
      '<div style="grid-column: 1/-1; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 300px; text-align: center; color: #00f0ff; text-shadow: 0 0 10px #00f0ff, 0 0 20px #00f0ff; font-size: 1.2em; font-family: Orbitron, sans-serif;"><p>No items yet — be the first to light up the Marketplace! 💡</p><p style="font-size: 0.9em; color: #ccc; margin-top: 10px;">(Click \'Post an Item\' above to share what you have!)</p></div>';
    return;
  }

  grid.innerHTML = items
    .map((item) => {
      const priceDisplay =
        item.is_free == 1
          ? '<span class="free-badge">FREE</span>'
          : `<p class="price">$${parseFloat(item.price).toFixed(2)}</p>`;

      const photoPath =
        item.photos && item.photos.length > 0
          ? item.photos[0]
          : "images/placeholder-item.jpg";

      return `
            <div class="marketplace-item" onclick="viewItemDetail(${item.id})">
                <img src="${photoPath}" alt="${
        item.title
      }" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22280%22 height=%22200%22%3E%3Crect width=%22280%22 height=%22200%22 fill=%22%231a1a1a%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%2300f0ff%22 font-family=%22Arial%22 font-size=%2220%22%3ENo Image%3C/text%3E%3C/svg%3E'">
                <h3>${escapeHtml(item.title)}</h3>
                ${priceDisplay}
                <p class="description">${truncateText(
                  escapeHtml(item.description),
                  100
                )}</p>
                <p class="location">📍 ${escapeHtml(item.city)}</p>
            </div>
        `;
    })
    .join("");
}

// View item detail
function viewItemDetail(itemId) {
  window.location.href = `marketplace-item.php?id=${itemId}`;
}

// Filter items
function filterItems() {
  loadMarketplaceItems();
}

// Check user authentication
function checkUserAuth(callback) {
  fetch("check_auth.php", { credentials: 'include' })
    .then((response) => {
      if (!response.ok) {
        throw new Error('Network response was not ok: ' + response.status);
      }
      return response.json();
    })
    .then((data) => {
      console.log("Auth check response:", data);
      callback(data.loggedIn);
    })
    .catch((error) => {
      console.error("Auth check error:", error);
      alert("Auth check failed: " + error.message);
      callback(false);
    });
}

// Utility: Escape HTML
function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

// Utility: Truncate text
function truncateText(text, maxLength) {
  if (text.length <= maxLength) return text;
  return text.substring(0, maxLength) + "...";
}

// Close modal when clicking outside
window.onclick = function (event) {
  const modal = document.getElementById("postModal");
  if (event.target == modal) {
    closePostModal();
  }

  const alertModal = document.getElementById("alertModal");
  if (event.target == alertModal) {
    closeAlertModal();
  }
};

// Alert modal functions
function showAlertModal(type, title, message) {
  const modal = document.getElementById("alertModal");
  const modalContent = document.querySelector(".alert-modal-content");
  const titleEl = document.getElementById("alertTitle");
  const messageEl = document.getElementById("alertMessage");

  // Remove existing type classes
  modal.classList.remove('success', 'error');

  // Add type class for styling
  if (type === 'success') {
    modal.classList.add('success');
  } else if (type === 'error') {
    modal.classList.add('error');
  }

  titleEl.textContent = title;
  messageEl.textContent = message;
  modal.style.display = "block";

  // Auto-close success messages after 5 seconds
  if (type === 'success') {
    setTimeout(() => {
      closeAlertModal();
    }, 5000);
  }
}

function closeAlertModal() {
  document.getElementById("alertModal").style.display = "none";
}

// Add event listener for alert close button
document.addEventListener("DOMContentLoaded", function() {
  const alertCloseBtn = document.getElementById("alertCloseBtn");
  if (alertCloseBtn) {
    alertCloseBtn.addEventListener("click", closeAlertModal);
  }
});
