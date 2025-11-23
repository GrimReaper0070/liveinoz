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

      // Setup mobile menu events
      setupMobileMenuEvents();
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

          // Create welcome text link
          const welcomeLink = document.createElement("a");
          welcomeLink.href = "dashboard.html";
          welcomeLink.className = "welcome-text";
          welcomeLink.textContent = `Welcome, ${data.user.name}`;
          userInfo.appendChild(welcomeLink);

          // Create logout link with proper structure
          const logoutLink = document.createElement("a");
          logoutLink.href = "logout.php";
          logoutLink.className = "auth-link";

          const logoutIconSpan = document.createElement("span");
          logoutIconSpan.className = "icon";
          logoutIconSpan.textContent = "🚪";

          const logoutTextSpan = document.createElement("span");
          logoutTextSpan.textContent = "Logout";

          logoutLink.appendChild(logoutIconSpan);
          logoutLink.appendChild(logoutTextSpan);

          userInfo.appendChild(logoutLink);
          navMenu.appendChild(userInfo);
          // Update chat link for authenticated user
          updateChatLink(true);


        } else {
          // User is not logged in, add login link
          const loginLink = document.createElement("a");
          loginLink.href = "login.html";
          loginLink.className = "auth-link";

          // Create proper structure with icon and text spans
          const iconSpan = document.createElement("span");
          iconSpan.className = "icon";
          iconSpan.textContent = "🔑";

          const textSpan = document.createElement("span");
          textSpan.textContent = "Login";

          loginLink.appendChild(iconSpan);
          loginLink.appendChild(textSpan);

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

          // Create proper structure with icon and text spans
          const adminIconSpan = document.createElement("span");
          adminIconSpan.className = "icon";
          adminIconSpan.textContent = "🔒";

          const adminTextSpan = document.createElement("span");
          adminTextSpan.textContent = "Admin Panel";

          adminLink.appendChild(adminIconSpan);
          adminLink.appendChild(adminTextSpan);

          // Insert before the user-info div if it exists, otherwise append
          const userInfo = navMenu.querySelector('.user-info');
          if (userInfo) {
            navMenu.insertBefore(adminLink, userInfo);
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
    '.main-menu a[href="chat-landing.html"]'
  );
  if (chatLink) {
    if (isAuthenticated) {
      chatLink.href = "chat-landing.html";
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

// Mobile Menu Toggle Functionality
function toggleMenu() {
  const menuToggle = document.getElementById('menuToggle');
  const mainMenu = document.getElementById('mainMenu');
  const menuOverlay = document.getElementById('menuOverlay');

  if (menuToggle && mainMenu && menuOverlay) {
    menuToggle.classList.toggle('active');
    mainMenu.classList.toggle('active');
    menuOverlay.classList.toggle('active');
  }
}

// Close menu when clicking overlay or a link (mobile only)
function setupMobileMenuEvents() {
  const menuToggle = document.getElementById('menuToggle');
  const menuOverlay = document.getElementById('menuOverlay');
  const menuLinks = document.querySelectorAll('.main-menu a');

  if (menuToggle) {
    menuToggle.addEventListener('click', toggleMenu);
  }

  if (menuOverlay) {
    menuOverlay.addEventListener('click', toggleMenu);
  }

  // Close menu when clicking a link (mobile only)
  menuLinks.forEach(link => {
    link.addEventListener('click', () => {
      if (window.innerWidth <= 768) {
        toggleMenu();
      }
    });
  });
}

// Load the navbar when the DOM is fully loaded
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", loadNavbar);
} else {
  // DOM is already loaded
  loadNavbar();
}
