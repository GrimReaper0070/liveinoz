document.addEventListener("DOMContentLoaded", function () {
  const stateItems = document.querySelectorAll(".state-item");

  // Shuffle the state items to randomize the order
  const shuffledItems = Array.from(stateItems).sort(() => Math.random() - 0.5);

  let delay = 0;

  shuffledItems.forEach((item) => {
    setTimeout(() => {
      const neon = item.querySelector(".neon-outline");
      // Set the animation property to start the flicker effect
      neon.style.animation = `flicker 0.5s infinite alternate`;
    }, delay);

    // Add 3.1 seconds (3100 ms) for the next state
    delay += 700;
  });
});
