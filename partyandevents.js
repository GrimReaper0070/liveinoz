document.addEventListener('DOMContentLoaded', function() {
    let currentCity = 'all';
    let eventsData = [];

    // Initialize the page
    loadEvents();

    // City filter buttons
    const cityButtons = document.querySelectorAll('.city-btn');
    cityButtons.forEach(button => {
        button.addEventListener('click', function() {
            const city = this.getAttribute('data-city');
            filterEvents(city);
        });
    });

    // Modal functionality
    setupModalFunctionality();
});

async function loadEvents(city = 'all') {
    try {
        const response = await fetch(`get_events.php?city=${city}&limit=50`);
        const data = await response.json();

        if (data.success) {
            eventsData = data.events;
            renderEvents(eventsData);
            console.log('Events loaded successfully:', data.debug);
        } else {
            console.error('Failed to load events:', data.message, data.error, data.debug);
            showAlert(`Error loading events: ${data.message}`);
        }
    } catch (error) {
        console.error('Network error loading events:', error);
        showAlert('Network error loading events. Please check your connection.');
    }
}

function renderEvents(events) {
    const posterGrid = document.getElementById('posterGrid');
    posterGrid.innerHTML = '';

    if (events.length === 0) {
        posterGrid.innerHTML = '<p style="text-align: center; color: #ccc; font-size: 1.2rem;">No events found for this city.</p>';
        return;
    }

    events.forEach(event => {
        const eventItem = document.createElement('div');
        eventItem.className = 'poster-item';
        eventItem.setAttribute('data-title', event.title);
        eventItem.setAttribute('data-event-id', event.id);

        eventItem.innerHTML = `
            <img src="${event.poster_path}" alt="${event.title}" loading="lazy">
        `;

        eventItem.addEventListener('click', () => openEventDetailModal(event));
        posterGrid.appendChild(eventItem);
    });
}

function filterEvents(city) {
    currentCity = city;

    // Update active button
    document.querySelectorAll('.city-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-city="${city}"]`).classList.add('active');

    // Load filtered events
    loadEvents(city);
}

function openPostEventModal() {
    document.getElementById('postEventModal').style.display = 'block';
}

function closePostEventModal() {
    document.getElementById('postEventModal').style.display = 'none';
    document.getElementById('postEventForm').reset();
    document.getElementById('eventPreviewContainer').innerHTML = '';
}

function openEventDetailModal(event) {
    // Populate modal with event data
    document.getElementById('eventTitleDetail').textContent = event.title;
    document.getElementById('eventPosterLarge').src = event.poster_path;
    document.getElementById('eventLocation').textContent = event.city;
    document.getElementById('eventDateDetail').textContent = formatDate(event.event_date);
    document.getElementById('eventTimeDetail').textContent = event.event_time ? formatTime(event.event_time) : 'TBA';
    document.getElementById('eventDescriptionDetail').textContent = event.description;

    // Setup action buttons
    const whatsappBtn = document.getElementById('whatsappBtn');
    const directionsBtn = document.getElementById('directionsBtn');

    if (event.contact_phone) {
        whatsappBtn.onclick = () => window.open(`https://wa.me/${event.contact_phone.replace(/\D/g, '')}`, '_blank');
        whatsappBtn.style.display = 'inline-flex';
    } else {
        whatsappBtn.style.display = 'none';
    }

    directionsBtn.onclick = () => {
        const query = encodeURIComponent(`${event.title} ${event.city} Australia`);
        window.open(`https://www.google.com/maps/search/?api=1&query=${query}`, '_blank');
    };

    document.getElementById('eventDetailModal').style.display = 'block';
}

function closeEventDetailModal() {
    document.getElementById('eventDetailModal').style.display = 'none';
}

function setupModalFunctionality() {
    // Post event modal
    window.onclick = function(event) {
        const postModal = document.getElementById('postEventModal');
        const eventModal = document.getElementById('eventDetailModal');
        const imageModal = document.getElementById('image-modal');

        if (event.target == postModal) {
            closePostEventModal();
        }
        if (event.target == eventModal) {
            closeEventDetailModal();
        }
        if (event.target == imageModal) {
            imageModal.style.display = 'none';
        }
    };

    // Form submission
    document.getElementById('postEventForm').addEventListener('submit', handleEventSubmission);

    // Contact field toggles
    document.getElementById('eventContactPhone').addEventListener('change', function() {
        toggleEventContactField('phone');
    });
    document.getElementById('eventContactEmail').addEventListener('change', function() {
        toggleEventContactField('email');
    });
}

function toggleEventContactField(type) {
    const phoneChecked = document.getElementById('eventContactPhone').checked;
    const emailChecked = document.getElementById('eventContactEmail').checked;

    document.getElementById('eventPhoneNumber').style.display = phoneChecked ? 'block' : 'none';
    document.getElementById('eventEmailAddress').style.display = emailChecked ? 'block' : 'none';

    // Make sure at least one contact method is selected
    if (!phoneChecked && !emailChecked && !document.getElementById('eventAllowChat').checked) {
        document.getElementById('eventAllowChat').checked = true;
    }
}

function handleEventPosterSelect(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewContainer = document.getElementById('eventPreviewContainer');
            previewContainer.innerHTML = `
                <div class="preview-image">
                    <img src="${e.target.result}" alt="Poster preview">
                </div>
            `;
        };
        reader.readAsDataURL(file);
    }
}

async function handleEventSubmission(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const submitBtn = event.target.querySelector('.submit-btn');
    const originalText = submitBtn.textContent;

    try {
        submitBtn.textContent = 'Submitting...';
        submitBtn.disabled = true;

        const response = await fetch('event_post.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            showAlert('Success! Your event has been submitted for review.', 'success');
            closePostEventModal();
            // Reload events after a short delay
            setTimeout(() => loadEvents(currentCity), 2000);
        } else {
            showAlert(data.message || 'Failed to submit event. Please try again.');
        }
    } catch (error) {
        console.error('Error submitting event:', error);
        showAlert('Error submitting event. Please check your connection and try again.');
    } finally {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-AU', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatTime(timeString) {
    if (!timeString) return 'TBA';
    const [hours, minutes] = timeString.split(':');
    const date = new Date();
    date.setHours(hours, minutes);
    return date.toLocaleTimeString('en-AU', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

function showAlert(message, type = 'error') {
    // Create alert modal if it doesn't exist
    let alertModal = document.getElementById('alertModal');
    if (!alertModal) {
        alertModal = document.createElement('div');
        alertModal.id = 'alertModal';
        alertModal.className = 'alert-modal';
        alertModal.innerHTML = `
            <div class="alert-modal-content">
                <span class="alert-close" onclick="closeAlertModal()">&times;</span>
                <h2 id="alertTitle">Alert</h2>
                <p id="alertMessage">Message</p>
                <div class="alert-modal-buttons">
                    <button id="alertCloseBtn" class="alert-btn close">Close</button>
                </div>
            </div>
        `;
        document.body.appendChild(alertModal);
    }

    document.getElementById('alertTitle').textContent = type === 'success' ? 'Success' : 'Error';
    document.getElementById('alertMessage').textContent = message;
    alertModal.style.display = 'block';
}

function closeAlertModal() {
    const alertModal = document.getElementById('alertModal');
    if (alertModal) {
        alertModal.style.display = 'none';
    }
}

// Global functions for HTML onclick handlers
window.openPostEventModal = openPostEventModal;
window.closePostEventModal = closePostEventModal;
window.closeEventDetailModal = closeEventDetailModal;
window.toggleEventContactField = toggleEventContactField;
window.handleEventPosterSelect = handleEventPosterSelect;
window.closeAlertModal = closeAlertModal;
