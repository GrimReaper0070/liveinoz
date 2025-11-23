// Mock data
let messages = [
    { id: 1, type: 'system', text: 'User1 joined Salon Name' },
    { id: 2, type: 'text', text: 'Hello everyone!', uid: 'user1', username: 'User1', createdAt: new Date(Date.now() - 60000) },
    { id: 3, type: 'text', text: 'Hi there!', uid: 'me', username: 'Me', createdAt: new Date(Date.now() - 30000) },
    { id: 4, type: 'text', text: 'How is everyone?', uid: 'user2', username: 'User2', createdAt: new Date() },
];

let dmDock = [
    { id: 'dm1', name: 'Friend1', photo: null, unread: true },
    { id: 'dm2', name: 'Friend2', photo: null, unread: false },
];

const meUid = 'me';
const meName = 'Me';

// DOM elements
const messagesContainer = document.getElementById('messages');
const messageInput = document.getElementById('message-input');
const sendBtn = document.getElementById('send-btn');
const toast = document.getElementById('toast');
const toastText = document.getElementById('toast-text');
const reportModal = document.getElementById('report-modal');
const dmDockEl = document.getElementById('dm-dock');

// Initialize
function init() {
    renderMessages();
    renderDmDock();
    setupEventListeners();
    scrollToBottom();
}

function setupEventListeners() {
    messageInput.addEventListener('input', () => {
        sendBtn.disabled = !messageInput.value.trim();
    });

    sendBtn.addEventListener('click', sendMessage);

    messageInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Close modal on click outside
    reportModal.addEventListener('click', (e) => {
        if (e.target === reportModal) {
            reportModal.style.display = 'none';
        }
    });

    // Report options
    document.querySelectorAll('.report-option').forEach(btn => {
        btn.addEventListener('click', () => {
            const reason = btn.dataset.reason;
            console.log('Report submitted:', reason);
            showToast('Report Sent', 2200);
            reportModal.style.display = 'none';
        });
    });
}

function sendMessage() {
    const text = messageInput.value.trim();
    if (!text) return;

    const newMsg = {
        id: Date.now(),
        type: 'text',
        text,
        uid: meUid,
        username: meName,
        createdAt: new Date(),
    };

    messages.push(newMsg);
    renderMessages();
    scrollToBottom();
    messageInput.value = '';
    sendBtn.disabled = true;
}

function renderMessages() {
    messagesContainer.innerHTML = '';
    messages.forEach(msg => {
        const msgEl = createMessageElement(msg);
        messagesContainer.appendChild(msgEl);
    });
}

function createMessageElement(msg) {
    if (msg.type === 'system') {
        const div = document.createElement('div');
        div.className = 'system-message';
        div.innerHTML = `<span class="system-text">${msg.text}</span>`;
        return div;
    }

    const row = document.createElement('div');
    row.className = `message-row ${msg.uid === meUid ? 'mine' : ''}`;

    if (msg.uid !== meUid) {
        const avatar = document.createElement('div');
        avatar.className = 'message-avatar placeholder';
        avatar.textContent = msg.username[0].toUpperCase();
        row.appendChild(avatar);
    }

    const bubble = document.createElement('div');
    bubble.className = `message-bubble ${msg.uid === meUid ? 'mine' : ''}`;

    const name = document.createElement('div');
    name.className = `message-name ${msg.uid !== meUid ? 'link' : ''}`;
    name.textContent = msg.username;
    if (msg.uid !== meUid) {
        name.addEventListener('click', () => {
            // Mock DM invite
            showToast(`Request sent to ${msg.username}`);
        });
    }
    bubble.appendChild(name);

    const text = document.createElement('div');
    text.className = 'message-text';
    text.textContent = msg.text;
    bubble.appendChild(text);

    const time = document.createElement('div');
    time.className = 'message-time';
    time.textContent = msg.createdAt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    bubble.appendChild(time);

    // Long press simulation: right click or hold
    bubble.addEventListener('contextmenu', (e) => {
        e.preventDefault();
        if (msg.uid !== meUid) {
            reportModal.style.display = 'flex';
        }
    });

    row.appendChild(bubble);

    if (msg.uid === meUid) {
        const avatar = document.createElement('div');
        avatar.className = 'message-avatar placeholder';
        avatar.textContent = meName[0].toUpperCase();
        row.appendChild(avatar);
    }

    return row;
}

function renderDmDock() {
    dmDockEl.innerHTML = '';
    dmDock.forEach(dm => {
        const bubble = document.createElement('div');
        bubble.className = 'dm-bubble';

        const avatar = document.createElement('div');
        avatar.className = 'dm-avatar placeholder';
        avatar.textContent = dm.name[0].toUpperCase();
        bubble.appendChild(avatar);

        if (dm.unread) {
            const badge = document.createElement('div');
            badge.className = 'dm-badge';
            badge.innerHTML = '<span class="dm-badge-txt">1</span>';
            bubble.appendChild(badge);
        }

        dmDockEl.appendChild(bubble);
    });
}

function scrollToBottom() {
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function showToast(text, duration = 2200) {
    toastText.textContent = text;
    toast.style.display = 'block';
    setTimeout(() => {
        toast.style.display = 'none';
    }, duration);
}

// Back button
document.querySelector('.back-btn').addEventListener('click', () => {
    alert('Back pressed');
});

// Avatar click
document.getElementById('my-avatar').addEventListener('click', () => {
    alert('Avatar pressed');
});

// Init
init();
