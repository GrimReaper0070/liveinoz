document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    const statusMessage = document.getElementById('statusMessage');
    const submitBtn = contactForm.querySelector('.submit-btn');

    // Add a submit event listener to the form
    contactForm.addEventListener('submit', function(event) {
        // Prevent the default form submission
        event.preventDefault();

        // Clear any previous messages
        statusMessage.textContent = '';
        statusMessage.style.color = '';

        // Disable submit button to prevent double submission
        submitBtn.disabled = true;
        submitBtn.textContent = 'Sending...';

        // Get form data
        const formData = new FormData(contactForm);

        // Send AJAX request
        fetch('contact_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Success
                showAlertModal('success', 'Success', data.message);
                contactForm.reset();
            } else {
                // Error
                showAlertModal('error', 'Error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlertModal('error', 'Error', 'An error occurred. Please try again.');
        })
        .finally(() => {
            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.textContent = 'Send Message';
        });
    });
});

// Alert Modal Functions
function showAlertModal(type, title, message) {
    const modal = document.getElementById("alertModal");
    const modalContent = document.querySelector(".alert-modal-content");
    const titleEl = document.getElementById("alertTitle");
    const messageEl = document.getElementById("alertMessage");

    // Set modal type class
    modal.className = `alert-modal ${type}`;

    // Set content
    titleEl.textContent = title;
    messageEl.textContent = message;

    // Show modal
    modal.style.display = "block";
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

    // Close modal when clicking outside
    const alertModal = document.getElementById("alertModal");
    if (alertModal) {
        alertModal.addEventListener("click", function(event) {
            if (event.target == alertModal) {
                closeAlertModal();
            }
        });
    }
});
