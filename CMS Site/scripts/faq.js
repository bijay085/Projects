document.addEventListener("DOMContentLoaded", () => {
  const faqContainer = document.getElementById("faq-container");
  const openAllBtn = document.getElementById("openAllBtn");
  const closeAllBtn = document.getElementById("closeAllBtn");

  // Fetch the JSON data for FAQs
  fetch("../json/faq.json")
    .then((response) => {
      if (!response.ok) {
        throw new Error(`Network response was not ok: ${response.status}`);
      }
      return response.json();
    })
    .then((faqData) => {
      if (!Array.isArray(faqData) || !faqData.length) {
        faqContainer.innerHTML = "<p>No FAQ items available at this time.</p>";
        return;
      }

      // Render each FAQ item
      faqData.forEach((item, index) => {
        // Provide safe defaults if fields are missing
        const questionText = item.question || "Untitled Question";
        const answerText = item.answer || "No answer provided.";
        const faqId = `faq-${index}`; // e.g. "faq-0"

        // Create the container for each Q&A
        const faqItem = document.createElement("div");
        faqItem.classList.add("faq-item");
        faqItem.setAttribute("aria-expanded", "false");

        // Create the question element
        const questionEl = document.createElement("h2");
        questionEl.classList.add("faq-question");
        questionEl.id = faqId;
        questionEl.textContent = questionText;

        // Create the answer element
        const answerEl = document.createElement("p");
        answerEl.classList.add("faq-answer");
        answerEl.textContent = answerText;

        // Toggle event — no scrolling is performed
        questionEl.addEventListener("click", () => {
          const isOpen = faqItem.classList.toggle("open");
          answerEl.classList.toggle("visible");
          faqItem.setAttribute("aria-expanded", isOpen);

          // Highlight the currently expanded FAQ item
          document.querySelectorAll(".faq-item").forEach((item) => {
            item.classList.remove("highlight");
          });
          if (isOpen) {
            faqItem.classList.add("highlight");
          }

          // (Optional) If you want to reflect the open item in the URL:
          if (isOpen) {
            location.hash = faqId;
          } else {
            history.replaceState(null, "", " "); // remove the hash
          }
        });

        // Combine elements and append to container
        faqItem.appendChild(questionEl);
        faqItem.appendChild(answerEl);
        faqContainer.appendChild(faqItem);
      });

      // Check if URL has a hash indicating a specific FAQ item to open
      applyURLHash();
    })
    .catch((error) => {
      console.error("Error fetching FAQ data:", error);
      faqContainer.innerHTML = "<p>Could not load FAQs at this time.</p>";
    });

  // "Open All" / "Close All" buttons (if present)
  openAllBtn?.addEventListener("click", () => {
    document.querySelectorAll(".faq-item").forEach((item) => {
      item.classList.add("open");
      item.setAttribute("aria-expanded", "true");
      item.querySelector(".faq-answer").classList.add("visible");
    });
  });

  closeAllBtn?.addEventListener("click", () => {
    document.querySelectorAll(".faq-item").forEach((item) => {
      item.classList.remove("open");
      item.setAttribute("aria-expanded", "false");
      item.querySelector(".faq-answer").classList.remove("visible");
    });
    // Remove the hash from URL so user doesn't come back to a forced open state
    history.replaceState(null, null, " ");
  });

  // Open FAQ item if URL contains a hash (e.g., #faq-0)
  function applyURLHash() {
    const hash = location.hash.replace("#", "");
    if (!hash) return;

    const questionEl = document.getElementById(hash);
    if (questionEl && questionEl.classList.contains("faq-question")) {
      const parent = questionEl.parentElement;
      parent.classList.add("open");
      parent.setAttribute("aria-expanded", "true");
      parent.querySelector(".faq-answer").classList.add("visible");
      // (Optional) if you want to scroll to it:
      questionEl.scrollIntoView({ behavior: "smooth", block: "center" });
    }
  }
});