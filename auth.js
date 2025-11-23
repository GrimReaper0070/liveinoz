// Authentication functions for Live in Oz
// This version works with PHP backend instead of Firebase

// DOM elements
let loginForm, signupForm, loginBtn, signupBtn, message;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on login page
    if (document.getElementById('loginForm')) {
        loginForm = document.getElementById('loginForm');
        loginBtn = document.getElementById('loginBtn');
        message = document.getElementById('message');

        loginForm.addEventListener('submit', handleLogin);
    }

    // Check if we're on signup page
    if (document.getElementById('signupForm')) {
        signupForm = document.getElementById('signupForm');
        signupBtn = document.getElementById('signupBtn');
        message = document.getElementById('message');

        signupForm.addEventListener('submit', handleSignup);
    }
});

// Handle login
async function handleLogin(e) {
    e.preventDefault();

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    if (!email || !password) {
        showMessage('Please fill in all fields', 'error');
        return;
    }

    try {
        loginBtn.disabled = true;
        loginBtn.innerHTML = '<span class="button-text">🔄 Logging in...</span>';

        // Send login request to server
        const response = await fetch('login_process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'email': email,
                'password': password
            })
        });

        const data = await response.json();

        if (data.success) {
            showMessage(data.message, 'success');
            // Redirect to dashboard or home page after successful login
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 2000);
        } else {
            showMessage(data.message, 'error');
            loginBtn.disabled = false;
            loginBtn.innerHTML = '<span class="button-text">🚀 Login</span>';
        }
    } catch (error) {
        console.error('Login error:', error);
        showMessage('Login failed. Please try again.', 'error');
        loginBtn.disabled = false;
        loginBtn.innerHTML = '<span class="button-text">🚀 Login</span>';
    }
}

// Handle signup
async function handleSignup(e) {
    e.preventDefault();

    const firstName = document.getElementById('firstName').value;
    const lastName = document.getElementById('lastName').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const termsAccepted = document.getElementById('terms').checked;

    // Validation
    if (!firstName || !lastName || !email || !password || !confirmPassword) {
        showMessage('Please fill in all fields', 'error');
        return;
    }

    if (password !== confirmPassword) {
        showMessage('Passwords do not match', 'error');
        return;
    }

    if (password.length < 6) {
        showMessage('Password must be at least 6 characters long', 'error');
        return;
    }

    if (!termsAccepted) {
        showMessage('Please accept the Terms of Use and Privacy Policy', 'error');
        return;
    }

    try {
        signupBtn.disabled = true;
        signupBtn.innerHTML = '<span class="button-text">🔄 Creating Account...</span>';

        // Redirect to PHP signup page
        window.location.href = 'signup.html';
    } catch (error) {
        console.error('Signup error:', error);
        showMessage('Account creation failed. Please try again.', 'error');
        signupBtn.disabled = false;
        signupBtn.innerHTML = '<span class="button-text">🚀 Create Account</span>';
    }
}

// Show message function
function showMessage(text, type) {
    if (message) {
        message.textContent = text;
        message.className = `message ${type}`;

        // Clear message after 5 seconds for success/error
        if (type !== 'info') {
            setTimeout(() => {
                message.textContent = '';
                message.className = 'message';
            }, 5000);
        }
    }
}

// Logout function
function logout() {
    // Redirect to logout script
    window.location.href = 'logout.php';
}

// Check if user is logged in (utility function for other pages)
function isUserLoggedIn() {
    // In a real implementation, this would check PHP session
    // For now, we'll return false to indicate not logged in
    return false;
}

// Export functions for use in other scripts
window.logout = logout;
window.isUserLoggedIn = isUserLoggedIn;