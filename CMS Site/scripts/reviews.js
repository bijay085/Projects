document.addEventListener("DOMContentLoaded", () => {
  const categoryOverview = document.getElementById("categoryOverview");
  const categoryDetail = document.getElementById("categoryDetail");
  const backButton = document.getElementById("backButton");
  const detailTitle = document.getElementById("detailTitle");
  const detailPhotosDiv = document.getElementById("detailPhotos");

  let groupedReviews = {};

  // Fetch reviews data from json/reviews.json
  fetch("../json/reviews.json")
    .then((response) => {
      if (!response.ok) {
        throw new Error(`Failed to load reviews data. Status: ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      if (!Array.isArray(data) || data.length === 0) {
        categoryOverview.innerHTML = "<p>No reviews available at this time.</p>";
        return;
      }

      // New compact format: each category contains an array of images
      groupedReviews = {};
      data.forEach((item) => {
        const cat = item.category || "Others";
        groupedReviews[cat] = item.images || [];
      });

      buildCategoryOverview();
    })
    .catch((error) => {
      console.error("Error loading reviews:", error);
      categoryOverview.innerHTML = "<p>Sorry, no reviews are available at this time.</p>";
    });

  // Build the category overview (stacked collage)
  function buildCategoryOverview() {
    categoryOverview.innerHTML = "";
    Object.keys(groupedReviews).forEach((category) => {
      const images = groupedReviews[category] || [];

      const catCard = document.createElement("div");
      catCard.classList.add("category-card");

      const collage = document.createElement("div");
      collage.classList.add("stacked-collage");

      // Use up to 3 images for the collage
      const coverImages = images.slice(0, 3);
      coverImages.forEach((imgSrc, idx) => {
        const img = document.createElement("img");
        img.src = imgSrc || "../assets/placeholder.jpg";
        img.alt = `${category} cover ${idx + 1}`;
        img.onerror = () => {
          img.src = "../assets/placeholder.jpg";
        };
        img.classList.add(`stacked-${idx + 1}`);
        collage.appendChild(img);
      });

      const label = document.createElement("h3");
      label.textContent = `${category} (${images.length} images)`; // Updated to include image count

      catCard.appendChild(collage);
      catCard.appendChild(label);

      // Click event => show detail for this category
      catCard.addEventListener("click", () => {
        showCategoryDetail(category);
      });

      categoryOverview.appendChild(catCard);
    });
  }

  // Show full detail gallery for a selected category
  function showCategoryDetail(category) {
    categoryOverview.classList.add("hidden");
    categoryDetail.classList.remove("hidden");
    detailTitle.textContent = category;
    detailPhotosDiv.innerHTML = "";

    const images = groupedReviews[category] || [];
    if (images.length === 0) {
      detailPhotosDiv.innerHTML = "<p>No images found for this category.</p>";
      return;
    }

    images.forEach((src) => {
      const img = document.createElement("img");
      img.src = src || "../assets/placeholder.jpg";
      img.alt = `${category} full image`;
      img.onerror = () => {
        img.src = "../assets/placeholder.jpg";
      };
      detailPhotosDiv.appendChild(img);
    });
  }

  // "Back" button returns to overview
  backButton.addEventListener("click", () => {
    categoryDetail.classList.add("hidden");
    categoryOverview.classList.remove("hidden");
  });

  // Add a single event listener to detailPhotosDiv to open modal on image click
  detailPhotosDiv.addEventListener("click", (e) => {
    if (e.target && e.target.tagName === "IMG") {
      openImageModal(e.target.src, e.target.alt);
    }
  });

  // Function to open modal with enlarged image and background blur
  function openImageModal(src, alt) {
    // Create modal overlay
    const modal = document.createElement("div");
    modal.classList.add("image-modal");

    // Create modal content container
    const modalContent = document.createElement("div");
    modalContent.classList.add("image-modal-content");

    // Create the enlarged image element
    const largeImg = document.createElement("img");
    largeImg.src = src;
    largeImg.alt = alt;
    largeImg.onerror = () => {
      largeImg.src = "../assets/placeholder.jpg";
    };

    // Append the image to modal content, then to modal
    modalContent.appendChild(largeImg);
    modal.appendChild(modalContent);
    document.body.appendChild(modal);

    // Close modal when clicking outside the image
    modal.addEventListener("click", (event) => {
      if (event.target === modal) {
        document.body.removeChild(modal);
      }
    });
  }
});
