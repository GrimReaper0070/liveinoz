// Function to load the footer
function loadFooter() {
  // Check if the placeholder element exists
  const placeholder = document.getElementById("footer-placeholder");
  if (!placeholder) {
    console.error("Footer placeholder not found");
    return;
  }

  // Fetch the footer HTML
  fetch("footer.html")
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      return response.text();
    })
    .then((data) => {
      // Insert the footer into the placeholder
      placeholder.innerHTML = data;
    })
    .catch((error) => {
      console.error("Error loading footer:", error);
      placeholder.innerHTML =
        '<footer class="footer-section"><div class="footer-container"><p>Error loading footer</p></div></footer>';
    });
}

// Load the footer when the DOM is fully loaded
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", loadFooter);
} else {
  // DOM is already loaded
  loadFooter();
}
