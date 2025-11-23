// DOM elements
let loginForm, signupForm, forgotPasswordForm, resetPasswordForm;
let loginBtn, signupBtn, forgotPasswordBtn, resetPasswordBtn, message;

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  // Check if we're on login page
  if (document.getElementById("loginForm")) {
    loginForm = document.getElementById("loginForm");
    loginBtn = document.getElementById("loginBtn");
    message = document.getElementById("message");

    loginForm.addEventListener("submit", handleLogin);

    const forgotPasswordLink = document.getElementById("forgotPassword");
    if (forgotPasswordLink) {
      forgotPasswordLink.addEventListener("click", handleForgotPasswordClick);
    }
  }

  // Check if we're on signup page
  if (document.getElementById("signupForm")) {
    signupForm = document.getElementById("signupForm");
    signupBtn = document.getElementById("signupBtn");
    message = document.getElementById("message");

    signupForm.addEventListener("submit", handleSignup);
  }

  // Check if we're on forgot password page
  if (document.getElementById("forgotPasswordForm")) {
    forgotPasswordForm = document.getElementById("forgotPasswordForm");
    forgotPasswordBtn = document.getElementById("forgotPasswordBtn");
    message = document.getElementById("message");

    forgotPasswordForm.addEventListener("submit", handleForgotPassword);
  }

  // Check if we're on reset password page
  if (document.getElementById("resetPasswordForm")) {
    resetPasswordForm = document.getElementById("resetPasswordForm");
    resetPasswordBtn = document.getElementById("resetPasswordBtn");
    message = document.getElementById("message");

    resetPasswordForm.addEventListener("submit", handleResetPassword);

    // Get token from URL
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get("token");
    if (!token) {
      showMessage("Invalid or missing reset token.", "error");
      resetPasswordBtn.disabled = true;
    }
  }
});

// Handle login with improved error handling
async function handleLogin(e) {
  e.preventDefault();

  const email = document.getElementById("email").value;
  const password = document.getElementById("password").value;

  if (!email || !password) {
    showMessage("Please fill in all fields", "error");
    return;
  }

  try {
    loginBtn.disabled = true;
    loginBtn.innerHTML = '<span class="button-text">🔄 Logging in...</span>';

    const formData = new FormData();
    formData.append("email", email);
    formData.append("password", password);

    const response = await fetch("login_process.php", {
      method: "POST",
      body: formData,
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const responseText = await response.text();
    let result;

    try {
      result = JSON.parse(responseText);
    } catch (jsonError) {
      console.error("Invalid JSON response:", responseText);
      throw new Error(
        "Server returned invalid response. Please check server logs."
      );
    }

    if (result.success) {
      showMessage(result.message, "success");
      sessionStorage.setItem("loggedIn", "true");

      setTimeout(() => {
        const redirectUrl =
          localStorage.getItem("redirectAfterLogin") || "dashboard.html";
        localStorage.removeItem("redirectAfterLogin");
        window.location.href = redirectUrl;
      }, 1500);
    } else {
      showMessage(result.message, "error");
      resetLoginButton();
    }
  } catch (error) {
    console.error("Login error:", error);

    if (error.message.includes("Failed to fetch")) {
      showMessage(
        "Unable to connect to server. Please check your connection.",
        "error"
      );
    } else if (error.message.includes("invalid response")) {
      showMessage("Server error. Please try again later.", "error");
    } else {
      showMessage("An error occurred. Please try again.", "error");
    }

    resetLoginButton();
  }
}

// Handle signup with improved error handling
async function handleSignup(e) {
  e.preventDefault();

  const firstName = document.getElementById("firstName").value;
  const lastName = document.getElementById("lastName").value;
  const email = document.getElementById("email").value;
  const password = document.getElementById("password").value;
  const confirmPassword = document.getElementById("confirmPassword").value;
  const termsAccepted = document.getElementById("terms").checked;

  // Validation
  if (!firstName || !lastName || !email || !password || !confirmPassword) {
    showMessage("Please fill in all fields", "error");
    return;
  }

  if (password !== confirmPassword) {
    showMessage("Passwords do not match", "error");
    return;
  }

  if (password.length < 6) {
    showMessage("Password must be at least 6 characters long", "error");
    return;
  }

  if (!termsAccepted) {
    showMessage("Please accept the Terms of Use and Privacy Policy", "error");
    return;
  }

  try {
    signupBtn.disabled = true;
    signupBtn.innerHTML =
      '<span class="button-text">🔄 Creating Account...</span>';

    // Get selected user type
    const userType = document.querySelector('input[name="userType"]:checked').value;

    const formData = new FormData();
    formData.append("firstName", firstName);
    formData.append("lastName", lastName);
    formData.append("email", email);
    formData.append("password", password);
    formData.append("confirmPassword", confirmPassword);
    formData.append("userType", userType);
    formData.append("terms", termsAccepted ? "accepted" : "");

    // Handle profile picture upload
    const profilePicture = document.getElementById("profilePicture").files[0];
    if (profilePicture) {
        formData.append("profilePicture", profilePicture);
    }

    const response = await fetch("register_process.php", {
      method: "POST",
      body: formData,
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const responseText = await response.text();
    let result;

    try {
      result = JSON.parse(responseText);
    } catch (jsonError) {
      console.error("Invalid JSON response:", responseText);
      throw new Error(
        "Server returned invalid response. Please check server logs."
      );
    }

    if (result.success) {
      showMessage(result.message, "success");
      sessionStorage.setItem("loggedIn", "true");

      setTimeout(() => {
        const redirectUrl =
          localStorage.getItem("redirectAfterLogin") || "dashboard.html";
        localStorage.removeItem("redirectAfterLogin");
        window.location.href = redirectUrl;
      }, 1500);
    } else {
      showMessage(result.message, "error");
      resetSignupButton();
    }
  } catch (error) {
    console.error("Signup error:", error);

    if (error.message.includes("Failed to fetch")) {
      showMessage(
        "Unable to connect to server. Please check your connection.",
        "error"
      );
    } else if (error.message.includes("invalid response")) {
      showMessage("Server error. Please try again later.", "error");
    } else {
      showMessage("An error occurred. Please try again.", "error");
    }

    resetSignupButton();
  }
}

// Handle forgot password click from login page
function handleForgotPasswordClick(e) {
  e.preventDefault();
  window.location.href = "forgot_password.html";
}

// Handle forgot password form submission
async function handleForgotPassword(e) {
  e.preventDefault();

  const email = document.getElementById("email").value;

  if (!email) {
    showMessage("Please enter your email address", "error");
    return;
  }

  if (!isValidEmail(email)) {
    showMessage("Please enter a valid email address", "error");
    return;
  }

  try {
    forgotPasswordBtn.disabled = true;
    forgotPasswordBtn.innerHTML =
      '<span class="button-text">🔄 Sending...</span>';

    const response = await fetch("forgot_password_process.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ email: email }),
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const responseText = await response.text();
    let result;

    try {
      result = JSON.parse(responseText);
    } catch (jsonError) {
      console.error("Invalid JSON response:", responseText);
      throw new Error(
        "Server returned invalid response. Please check server logs."
      );
    }

    if (result.success) {
      showMessage(result.message, "success");

      // If there's a reset link in the response (for demo purposes)
      if (result.reset_link) {
        // Show additional message with clickable link
        const linkMessage = document.createElement("div");
        linkMessage.className = "demo-link-message";
        linkMessage.innerHTML = `
                    <p style="margin-top: 10px; padding: 10px; background: #e3f2fd; border-radius: 5px;">
                        <strong>Demo Mode:</strong> 
                        <a href="${result.reset_link}" style="color: #1976d2;">Click here to reset your password</a>
                    </p>
                `;
        message.appendChild(linkMessage);
      }

      // Disable form after successful submission
      document.getElementById("email").disabled = true;
      forgotPasswordBtn.style.display = "none";
    } else {
      showMessage(result.message, "error");
      resetForgotPasswordButton();
    }
  } catch (error) {
    console.error("Forgot password error:", error);

    if (error.message.includes("Failed to fetch")) {
      showMessage(
        "Unable to connect to server. Please check your connection.",
        "error"
      );
    } else if (error.message.includes("invalid response")) {
      showMessage("Server error. Please try again later.", "error");
    } else {
      showMessage("An error occurred. Please try again.", "error");
    }

    resetForgotPasswordButton();
  }
}

// Handle reset password form submission
async function handleResetPassword(e) {
  e.preventDefault();

  const password = document.getElementById("password").value;
  const confirmPassword = document.getElementById("confirmPassword").value;
  const urlParams = new URLSearchParams(window.location.search);
  const token = urlParams.get("token");

  // Validation
  if (!password || !confirmPassword) {
    showMessage("Please fill in all fields", "error");
    return;
  }

  if (password !== confirmPassword) {
    showMessage("Passwords do not match", "error");
    return;
  }

  if (password.length < 6) {
    showMessage("Password must be at least 6 characters long", "error");
    return;
  }

  if (!token) {
    showMessage("Invalid or missing reset token", "error");
    return;
  }

  try {
    resetPasswordBtn.disabled = true;
    resetPasswordBtn.innerHTML =
      '<span class="button-text">🔄 Resetting...</span>';

    const response = await fetch("reset_password_process.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        token: token,
        password: password,
        confirmPassword: confirmPassword,
      }),
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const responseText = await response.text();
    let result;

    try {
      result = JSON.parse(responseText);
    } catch (jsonError) {
      console.error("Invalid JSON response:", responseText);
      throw new Error(
        "Server returned invalid response. Please check server logs."
      );
    }

    if (result.success) {
      showMessage(result.message, "success");

      // Redirect to login page after successful reset
      setTimeout(() => {
        window.location.href = "login.html";
      }, 2000);
    } else {
      showMessage(result.message, "error");
      resetResetPasswordButton();
    }
  } catch (error) {
    console.error("Reset password error:", error);

    if (error.message.includes("Failed to fetch")) {
      showMessage(
        "Unable to connect to server. Please check your connection.",
        "error"
      );
    } else if (error.message.includes("invalid response")) {
      showMessage("Server error. Please try again later.", "error");
    } else {
      showMessage("An error occurred. Please try again.", "error");
    }

    resetResetPasswordButton();
  }
}

// Helper functions to reset buttons
function resetSignupButton() {
  if (signupBtn) {
    signupBtn.disabled = false;
    signupBtn.innerHTML = '<span class="button-text">🚀 Create Account</span>';
  }
}

function resetLoginButton() {
  if (loginBtn) {
    loginBtn.disabled = false;
    loginBtn.innerHTML = '<span class="button-text">🚀 Login</span>';
  }
}

function resetForgotPasswordButton() {
  if (forgotPasswordBtn) {
    forgotPasswordBtn.disabled = false;
    forgotPasswordBtn.innerHTML =
      '<span class="button-text">📧 Send Reset Link</span>';
  }
}

function resetResetPasswordButton() {
  if (resetPasswordBtn) {
    resetPasswordBtn.disabled = false;
    resetPasswordBtn.innerHTML =
      '<span class="button-text">🔒 Reset Password</span>';
  }
}

// Show message function
function showMessage(text, type) {
  if (message) {
    // Clear any existing demo link messages
    const existingLinkMessage = message.querySelector(".demo-link-message");
    if (existingLinkMessage) {
      existingLinkMessage.remove();
    }

    message.textContent = text;
    message.className = `message ${type}`;

    // Clear message after 5 seconds for success/error (but not for info)
    if (type !== "info") {
      setTimeout(() => {
        if (message.textContent === text) {
          // Only clear if message hasn't changed
          message.textContent = "";
          message.className = "message";
        }
      }, 5000);
    }
  }
}

// Email validation helper
function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

// Check if user is authenticated
async function isAuthenticated() {
  try {
    const response = await fetch("check_auth.php", {
      credentials: 'same-origin'
    });
    const result = await response.json();
    return result.authenticated;
  } catch (error) {
    console.error("Authentication check error:", error);
    return false;
  }
}

// Logout function
async function logout() {
  try {
    const response = await fetch("logout_process.php", {
      method: "POST",
    });

    const result = await response.json();

    if (result.success) {
      sessionStorage.removeItem("loggedIn");
      localStorage.removeItem("redirectAfterLogin");
      window.location.href = "login.html";
    }
  } catch (error) {
    console.error("Logout error:", error);
    // Force logout even if request fails
    sessionStorage.removeItem("loggedIn");
    localStorage.removeItem("redirectAfterLogin");
    window.location.href = "login.html";
  }
}
