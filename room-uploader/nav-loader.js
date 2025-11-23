// Function to load the navigation bar
function loadNavbar() {
  // Check if the placeholder element exists
  const placeholder = document.getElementById("navbar-placeholder");
  if (!placeholder) {
    console.error("Navbar placeholder not found");
    return;
  }

  // Fetch the navbar HTML
  fetch("nav.html")
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      return response.text();
    })
    .then((data) => {
      // Insert the navbar into the placeholder
      placeholder.innerHTML = data;

      // Add special links for specific pages
      addSpecialLinks();

      // Set the active class on the current page link
      setActiveNavLink();

      // Check authentication status and update navbar
      checkAuthStatusAndUpdateNav();
    })
    .catch((error) => {
      console.error("Error loading navbar:", error);
      placeholder.innerHTML =
        '<nav class="main-menu"><p>Error loading navigation</p></nav>';
    });
}

// Function to check authentication status and update navbar
function checkAuthStatusAndUpdateNav() {
  // Make an AJAX request to check if the user is authenticated
  fetch("check_auth.php")
    .then((response) => response.json())
    .then((data) => {
      const navMenu = document.querySelector(".main-menu");
      if (navMenu) {
        if (data.authenticated) {
          // User is logged in, add user info and logout link
          const userInfo = document.createElement("div");
          userInfo.className = "user-info";
          userInfo.innerHTML = `
    <a href="dashboard.html" class="welcome-text">Welcome, ${data.user.name}</a>
    <a href="logout.php" class="auth-link">🚪 Logout</a>
`;
          navMenu.appendChild(userInfo);
          // Update chat link for authenticated user
          updateChatLink(true);


        } else {
          // User is not logged in, add login link
          const loginLink = document.createElement("a");
          loginLink.href = "login.html";
          loginLink.className = "auth-link";
          loginLink.textContent = "🔑 Login";
          navMenu.appendChild(loginLink);
          // Update chat link for unauthenticated user
          updateChatLink(false);
        }
      }
    })
    .catch((error) => {
      console.error("Error checking authentication status:", error);
      // Default to unauthenticated state
      updateChatLink(false);
    });
}

// Function to add special links for specific pages
function addSpecialLinks() {
  // Check if we're on the dashboard page
  const path = window.location.pathname;
  const page = path.split("/").pop();

  // Check if user is admin and add admin panel link
  checkAdminAndAddLink();

  // Add other special cases here if needed
}

// Function to check if user is admin and add admin panel link
function checkAdminAndAddLink() {
  // Make an AJAX request to check if the user is an admin
  fetch("check_admin.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.is_admin) {
        const navMenu = document.querySelector(".main-menu");
        if (navMenu) {
          // Add admin panel link
          const adminLink = document.createElement("a");
          adminLink.href = "admin/dashboard.php";
          adminLink.className = "admin-link";
          adminLink.textContent = "🔒 Admin Panel";
          // Insert before the logout link if it exists, otherwise append
          const logoutLink = navMenu.querySelector('a[href="logout.php"]');
          if (logoutLink) {
            navMenu.insertBefore(adminLink, logoutLink);
          } else {
            navMenu.appendChild(adminLink);
          }
        }
      }
    })
    .catch((error) => {
      console.error("Error checking admin status:", error);
    });
}

// Function to update the chat link based on authentication status
function updateChatLink(isAuthenticated) {
  const chatLink = document.querySelector(
    '.main-menu a[href="general-chat.html"]'
  );
  if (chatLink) {
    if (isAuthenticated) {
      chatLink.href = "chat-directory.html";
    } else {
      chatLink.href = "chat-landing.html";
    }
  }
}

// Function to set the active navigation link
function setActiveNavLink() {
  // Get the current page filename
  const path = window.location.pathname;
  const page = path.split("/").pop();

  // If we're on the home page (empty or index.html), set active class on home link
  const isHomePage = !page || page === "index.html" || page === "";

  // Get all navigation links
  const navLinks = document.querySelectorAll(".main-menu a");

  // Loop through links and set active class
  navLinks.forEach((link) => {
    const linkHref = link.getAttribute("href");

    if (isHomePage && linkHref === "index.html") {
      link.classList.add("active");
    } else if (linkHref === page) {
      link.classList.add("active");
    } else {
      link.classList.remove("active");
    }
  });
}

// Load the navbar when the DOM is fully loaded
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", loadNavbar);
} else {
  // DOM is already loaded
  loadNavbar();
}
