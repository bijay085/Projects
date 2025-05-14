document.addEventListener("DOMContentLoaded", () => {
  const cardWrapper = document.getElementById("contact-card-wrapper");

  // Optional fallback icon if 'icon' is missing
  const FALLBACK_ICON = "assets/icons/default-contact.png";

  // Fetch the contact data from contact.json
  fetch("../json/contact.json")
    .then(response => {
      if (!response.ok) {
        throw new Error(`Network response was not ok: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      // Clear existing content
      cardWrapper.innerHTML = "";

      if (!Array.isArray(data) || !data.length) {
        cardWrapper.innerHTML = "<p>No contact methods found.</p>";
        return;
      }

      // Create a flip card for each contact method
      data.forEach(item => {
        const {
          icon = FALLBACK_ICON, // fallback icon
          type = "Contact",
          title = "Untitled",
          description = "",
          link = "#",
          buttonText = "Contact"
        } = item;

        // Main flip card container
        const flipCard = document.createElement("div");
        flipCard.classList.add("flip-card");

        // Inner container
        const flipCardInner = document.createElement("div");
        flipCardInner.classList.add("flip-card-inner");

        // ========== FRONT SIDE ==========
        const frontSide = document.createElement("div");
        frontSide.classList.add("flip-card-front");
        frontSide.title = type; // Add tooltip with the contact type

        const frontImg = document.createElement("img");
        frontImg.src = icon;
        frontImg.alt = `${type} Icon`;

        const frontTitle = document.createElement("h3");
        frontTitle.textContent = title;

        const frontDesc = document.createElement("p");
        frontDesc.textContent = description;

        frontSide.appendChild(frontImg);
        frontSide.appendChild(frontTitle);
        frontSide.appendChild(frontDesc);

        // ========== BACK SIDE ==========
        const backSide = document.createElement("div");
        backSide.classList.add("flip-card-back");

        const backTitle = document.createElement("h3");
        backTitle.textContent = title;

        const backDesc = document.createElement("p");
        backDesc.textContent = description;

        const backLink = document.createElement("a");
        backLink.href = link;
        backLink.target = "_blank";
        backLink.textContent = buttonText;

        backSide.appendChild(backTitle);
        backSide.appendChild(backDesc);
        backSide.appendChild(backLink);

        // ========== Assemble Flip Card ==========
        flipCardInner.appendChild(frontSide);
        flipCardInner.appendChild(backSide);
        flipCard.appendChild(flipCardInner);
        cardWrapper.appendChild(flipCard);

        // For mobile/non-hover devices, toggle 'flipped' on click
        flipCard.addEventListener("click", () => {
          flipCardInner.classList.toggle("flipped");
        });

        // (Optional) If you want to prevent the flip if user only clicks the link on the back:
        backLink.addEventListener("click", event => {
          event.stopPropagation(); // ensures link opens without flipping card again
        });
      });
    })
    .catch(error => {
      console.error("Error loading contact data:", error);
      cardWrapper.innerHTML =
        "<p>Unable to load contact information at this time.</p>";
    });
});
