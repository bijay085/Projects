/* === CSS PATCHES (auto‑injected) === */
(() => {
  const css = `
:focus-visible{outline:2px solid var(--accent);outline-offset:2px;}
html{scroll-behavior:smooth;}
.tool-card.skeleton{animation:pulse 1.2s infinite ease-in-out;background:var(--skeleton-bg);border-radius:.75rem;height:180px;}
@keyframes pulse{0%,100%{opacity:.6}50%{opacity:1}}
.tool-thumb-wrapper{aspect-ratio:16/9;position:relative;}
.tool-thumb-wrapper img{object-fit:cover;width:100%;height:100%;}
`;
  const style = document.createElement("style");
  style.textContent = css;
  document.head.appendChild(style);
})();

/* ----------  CONSTANTS & DOM REFS  ---------- */
const DATA_FILES = [
  "data/tools.json",
  "data/bots.json",
  "data/checkers.json",
  "data/game.json",
  "data/others.json",
  "data/cookies.json",
  "data/methods.json",
  "data/membership.json"
];

const THEME_KEY   = "theme";
const SEARCH_KEY  = "search";
const SORT_KEY    = "sort";
const FILTER_KEY  = "filter";
const BANNER_KEY  = "hideBanner";
const RECENT_KEY  = "recentSearches";
const MAX_RECENTS = 5;

/* DOM */
const container        = document.getElementById("main-tool-list");
const filtersContainer = document.getElementById("filters");
const searchInput      = document.getElementById("searchInput");
const sortSelect       = document.getElementById("sortSelect");
const scrollToTopBtn   = document.getElementById("scrollToTopBtn");
const darkToggle       = document.getElementById("darkToggle");
const banner           = document.getElementById("announcement-banner");
const closeBanner      = document.getElementById("close-banner");
const navbarToggle     = document.getElementById("navbarToggle");
const navbarMenu       = document.getElementById("navbarMenu");
const imageModal       = document.getElementById("imageModal");
const autocompleteBox  = document.getElementById("autocompleteBox");

const offersList      = document.getElementById("offers-list");
const recommendedList = document.getElementById("recommended-list");
const limitedList     = document.getElementById("limited-list");

let allTools = [];

/* ----------  HELPERS  ---------- */
const debounce = (fn, delay) => {
  let t;
  return (...a) => {
    clearTimeout(t);
    t = setTimeout(() => fn(...a), delay);
  };
};

const escapeHTML = (s = "") =>
  s.replace(/&/g, "&amp;")
   .replace(/</g, "&lt;")
   .replace(/>/g, "&gt;")
   .replace(/"/g, "&quot;");

const nl2br = (txt = "") => txt.replace(/\n/g, "<br>");

function highlightMatch(text, matches, key) {
  const m = matches?.find((x) => x.key === key);
  if (!m?.indices?.length) return escapeHTML(text);

  // Merge adjacent/overlapping ranges
  const merged = m.indices
    .sort((a, b) => a[0] - b[0])
    .reduce((acc, [s, e]) => {
      if (!acc.length || s > acc[acc.length - 1][1] + 1) {
        acc.push([s, e]);
      } else {
        acc[acc.length - 1][1] = Math.max(acc[acc.length - 1][1], e);
      }
      return acc;
    }, []);

  // Filter out runs shorter than the minimum length
  const MIN_RUN = 2;
  const goodRuns = merged.filter(([s, e]) => e - s + 1 >= MIN_RUN);

  // Build the highlighted string
  let out = "", last = 0;
  for (const [start, end] of goodRuns) {
    out += escapeHTML(text.slice(last, start));
    out += `<mark>${escapeHTML(text.slice(start, end + 1))}</mark>`;
    last = end + 1;
  }
  return out + escapeHTML(text.slice(last));
}

function getShortDescription(tool, query = "") {
  const raw =
    tool.description ||
    (tool.long_description
      ? tool.long_description.split("\n")[0] + "…"
      : "No description available.");
  return query ? highlightMatch(raw, query) : escapeHTML(raw);
}

/* ----------  LAZY‑IMAGE SYSTEM ---------- */
const io = new IntersectionObserver(
  (entries) => {
    entries.forEach(({ target, isIntersecting }) => {
      if (isIntersecting) {
        target.src = target.dataset.src;
        io.unobserve(target);
      }
    });
  },
  { rootMargin: "100px" }
);

function smartImg(src, alt = "") {
  const fallbacks = [
    src,
    "/assets/placeholder.jpg",
    "../assets/placeholder.jpg",
    "assets/placeholder.jpg"
  ];
  const fbList = JSON.stringify(fallbacks).replace(/"/g, "&quot;");
  return `
    <img loading="lazy"
         data-src="${src}"
         data-fallbacks="${fbList}"
         data-fb-index="0"
         src="${fallbacks[3]}"
         alt="${escapeHTML(alt)}"
         onerror="
           (() => {
             const fbs = JSON.parse(this.dataset.fallbacks);
             let idx = Number(this.dataset.fbIndex) || 0;
             console.error('Image failed to load:', fbs[idx]);
             idx++;
             if (idx < fbs.length) {
               this.dataset.fbIndex = idx;
               this.src = fbs[idx];
             } else {
               console.error('All image fallbacks failed for:', fbs[0]);
               const div = document.createElement('div');
               div.className = 'no-image';
               div.textContent = this.alt || 'No image';
               this.replaceWith(div);
             }
           })()
         "
    >
  `.trim();
}

function activateLazyImages(root = document) {
  const images = root.querySelectorAll("img[data-src]");
  images.forEach((img) => {
    if (img.complete && img.naturalWidth > 0) {
      // If the image is already loaded, skip observing
      img.src = img.dataset.src;
    } else {
      io.observe(img);
      // Fallback: Load image manually if not loaded within 3 seconds
      setTimeout(() => {
        if (img.dataset.src && img.src === "assets/placeholder.jpg") {
          img.src = img.dataset.src;
        }
      }, 3000);
    }
  });
}

/* ----------  DARK MODE  ---------- */
if (darkToggle) {
  darkToggle.setAttribute("aria-label", "Toggle dark mode");
  darkToggle.setAttribute("title", "Toggle dark mode (D)");
  darkToggle.addEventListener("click", () => {
    document.body.classList.toggle("dark");
    localStorage.setItem(
      THEME_KEY,
      document.body.classList.contains("dark") ? "dark" : "light"
    );
  });
}
if (localStorage.getItem(THEME_KEY) === "dark")
  document.body.classList.add("dark");

document.addEventListener("keydown", (e) => {
  if (
    e.key.toLowerCase() === "d" &&
    !e.target.matches("input,textarea,[contenteditable]")
  )
    darkToggle?.click();
});

/* ----------  BANNER  ---------- */
if (banner && closeBanner && !localStorage.getItem(BANNER_KEY)) {
  setTimeout(() => banner.classList.remove("hidden"), 500);
  closeBanner.addEventListener("click", () => {
    banner.classList.add("hidden");
    localStorage.setItem(BANNER_KEY, true);
  });
}

/* ----------  AUTO REFRESH FOR MOBILE OR UNKNOWN DEVICES  ---------- */
if (/Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent) || !navigator.userAgent) {
  if (!sessionStorage.getItem("refreshed")) {
    sessionStorage.setItem("refreshed", "true");
    location.reload(true); // Force a hard refresh
  }
}

/* --------------------------------------------------
     RENDER UTIL THAT WORKS FOR *ANY* CONTAINER
   -------------------------------------------------- */
function renderTools(list, target = container) {
  target.className = "main-grid";

  if (!list.length) {
    target.replaceChildren(document.createElement("p").appendChild(document.createTextNode("No tools found.")));
    return;
  }

  // Build everything in a DocumentFragment
  const frag = document.createDocumentFragment();
  list.forEach((tool) => {
    const card = document.createElement("div");
    card.className = "tool-card fade-in";
    card.dataset.toolName = tool.name;

    let desc = highlightMatch(
      tool.description || "",
      tool._matches || [],
      "description"
    );
    desc = nl2br(desc); // Convert \n to <br> for card descriptions

    const name = highlightMatch(
      tool.name || "Unnamed",
      tool._matches || [],
      "name"
    );

    card.innerHTML = `
      <div class="tool-thumb-wrapper">
        ${getCardBadges(tool)}
        ${smartImg(tool.image || "assets/placeholder.jpg", tool.name).trim()}
      </div>
      <div class="tool-card-body">
        <h3 class="tool-title">${name}</h3>
        <p class="tool-desc">${desc}</p>
        <div class="tool-tags">
          ${(tool.tags || [])
            .map((t) => `<span class="tag">${escapeHTML(t)}</span>`)
            .join("")}
          ${tool.popular ? `<span class="tag">popular</span>` : ""}
          ${getRecentTags(tool)}
        </div>
      </div>`;
    frag.appendChild(card);
  });

  // Replace all children with one operation
  target.replaceChildren(frag);

  // Wire up lazy-loading
  activateLazyImages(target);
}

/* helper: render a list into any selector */
function renderInto(selector, list) {
  const el = document.querySelector(selector);
  if (el) renderTools(list, el);
}

/* ----------  LOAD DATA  ---------- */
async function loadData() {
  container.className = "main-grid";
  container.innerHTML = "<div class='tool-card skeleton'></div>".repeat(12);

  try {
    const data = await Promise.allSettled(
      DATA_FILES.map((u) =>
        fetch(u).then((r) => (r.ok ? r.json() : Promise.reject(r.statusText)))
      )
    );

    const merged = data
      .filter((result) => result.status === "fulfilled")
      .flatMap((result) => result.value);

    const seen = new Set();
    allTools = merged.filter((t) => {
      if (!t.name || !t.type) return false;
      const k = t.name.toLowerCase();
      if (seen.has(k)) return false;
      seen.add(k);
      return true;
    });

    // Insert static heading and separator for "Special Offers"
    const offersSection = document.getElementById("offers-section");
    offersSection.insertAdjacentHTML(
      "afterbegin",
      `<h2 class="section-heading">Special Offers</h2>`
    );
    offersSection.insertAdjacentHTML(
      "afterend",
      `<div class="animated-separator"></div>`
    );

    generateFilterButtons();
    applyFiltersAndRender();
    applyURLHash();
  } catch (err) {
    console.error("Error loading data:", err);
    container.innerHTML = "<p>Error loading data.</p>";
  }
}

function renderOrHide(list, wrapper, section) {
  if (list.length) {
    renderTools(list, wrapper);
    section.classList.remove("hidden");
  } else {
    section.classList.add("hidden");
  }
}

function populateSpecialSections() {
  const now = Date.now();

  /* OFFERS & DISCOUNTS */
  const offers = allTools.filter(t => {
    const hasKeyword = (t.keywords || []).includes("offer");
    const disc = t.discount && (!t.discount_expiry || new Date(t.discount_expiry) > now);
    const off  = t.offer    && (!t.offer_expiry    || new Date(t.offer_expiry)    > now);
    return hasKeyword || disc || off;
  });

  const offersList = document.getElementById("offers-list");
  renderOrHide(offers, offersList, document.getElementById("offers-section"));

  /* RECOMMENDED */
  const recommended = allTools.filter(t =>
    (t.keywords || []).includes("recommended")
  );
  renderOrHide(recommended, recommendedList, document.getElementById("recommended-section"));

  /* LIMITED‑TIME */
  const limited = allTools.filter(t =>
    (t.keywords || []).includes("limited") || t.stock === 1
  );
  renderOrHide(limited, limitedList, document.getElementById("limited-section"));
}

/* allow clicking cards in the extra lists */
["offers-list","recommended-list","limited-list"].forEach(id=>{
  const el = document.getElementById(id);
  el?.addEventListener("click", e=>{
    const c = e.target.closest(".tool-card");
    if(!c) return;
    const tool = allTools.find(t=>t.name===c.dataset.toolName);
    if(tool) showToolDetail(tool);
  });
});

/* =========  MOBILE SWIPE HINT  ========= */
function addSwipeHint(wrapperId) {
  const list = document.getElementById(wrapperId);
  const parent = list?.parentElement;
  if (!list || !parent) return;

  // Show the hint initially
  parent.classList.add("has-scroll-hint");

  // Hide after the user scrolls a bit (or after 6s as fallback)
  const hide = () => parent.classList.remove("has-scroll-hint");
  list.addEventListener(
    "scroll",
    () => {
      if (list.scrollLeft > 24) hide();
    },
    { passive: true }
  );

  setTimeout(hide, 6000); // Auto-fade in case they don't scroll
}

/* ========= APPLY SECTION SCRIPTS ========= */
function applySectionScripts() {
  const ids = ["offers-list", "recommended-list", "limited-list"];
  if (window.innerWidth > 480) {
    ids.forEach(addSwipeHint);
  }
}

// Run once on load
applySectionScripts();

// Re-run if the user resizes the window
window.addEventListener("resize", applySectionScripts);

/* ----------  SEARCH / FILTER / SORT  ---------- */
function runSearch(raw = "") {
  sessionStorage.setItem(SEARCH_KEY, raw);
  applyFiltersAndRender();
}

function applyFiltersAndRender() {
  const offersSection      = document.getElementById("offers-section");
  const recommendedSection = document.getElementById("recommended-section");
  const limitedSection     = document.getElementById("limited-section");

  const searchRaw = sessionStorage.getItem(SEARCH_KEY) || "";
  const sortKey   = sessionStorage.getItem(SORT_KEY)   || "name";
  const typeKey   = sessionStorage.getItem(FILTER_KEY) || "all";

  // Hide special sections when filtering anything but "all"
  if (typeKey !== "all") {
    offersSection?.classList.add("hidden");
    recommendedSection?.classList.add("hidden");
    limitedSection?.classList.add("hidden");
  } else {
    offersSection?.classList.remove("hidden");
    recommendedSection?.classList.remove("hidden");
    limitedSection?.classList.remove("hidden");
  }

  let list = [...allTools];
  if (typeKey !== "all")
    list = list.filter(
      (t) => (t.type || "").toLowerCase() === typeKey
    );

  if (searchRaw.trim()) {
    const fuse = new Fuse(getWeightedFuseList(), {
      includeScore: true,
      includeMatches: true,
      threshold: 0.3,
      distance: 100,
      ignoreLocation: true,
      minMatchCharLength: 2, // Ensure matches are at least 2 characters long
      keys: [
        { name: "name", weight: 1.0 },
        { name: "keywords", weight: 0.6 },
        { name: "tags", weight: 0.5 },
        { name: "description", weight: 0.3 },
        { name: "long_description", weight: 0.2 },
        { name: "type", weight: 0.1 },
        { name: "_boost", weight: 0.8 }
      ]
    });
    list = fuse
      .search(searchRaw.trim())
      .map(({ item, matches }) => ({ ...item, _matches: matches }));
  }

  switch (sortKey) {
    case "release_date":
      list.sort(
        (a, b) =>
          new Date(b.release_date) - new Date(a.release_date)
      );
      break;
    case "update_date":
      list.sort(
        (a, b) =>
          new Date(b.update_date) - new Date(a.update_date)
      );
      break;
    case "discount": {
      const now = Date.now();
      list = list
        .filter(
          (t) =>
            (t.discount &&
              (!t.discount_expiry ||
                new Date(t.discount_expiry) > now)) ||
            (t.offer &&
              (!t.offer_expiry ||
                new Date(t.offer_expiry) > now))
        )
        .sort(
          (a, b) =>
            (parseFloat(b.discount) || 0) -
            (parseFloat(a.discount) || 0)
        );
      break;
    }
    default: {
      const wk = 6048e5,
        now = Date.now();
      list.sort((a, b) => {
        const ar =
          now - new Date(a.release_date) < wk ||
          now - new Date(a.update_date) < wk;
        const br =
          now - new Date(b.release_date) < wk ||
          now - new Date(b.update_date) < wk;
        if (ar !== br) return br - ar;
        return (a.name || "").localeCompare(b.name || "");
      });
    }
  }

  renderTools(list);

  document
    .querySelectorAll("#filters button")
    .forEach((b) =>
      b.classList.toggle(
        "active",
        b.textContent.toLowerCase() === typeKey
      )
    );
  searchInput.value = searchRaw;
  sortSelect.value = sortKey;

  // Dynamically hide or show special sections
  if (typeKey === "all") {
    populateSpecialSections();
  }
}

/* ----------  BADGE HELPERS & CARD MARKUP ---------- */
function getCardBadges(tool) {
  const now = new Date();
  const offerEnd    = tool.offer_expiry    ? new Date(tool.offer_expiry)    : null;
  const discountEnd = tool.discount_expiry ? new Date(tool.discount_expiry) : null;

  const isOffer    = tool.offer    && (!offerEnd    || offerEnd    > now);
  const isDiscount = tool.discount && (!discountEnd || discountEnd > now);
  const isNumeric  = !isNaN(parseFloat(tool.discount));

  const out = [];
  if (isDiscount) {
    if (isNumeric) {
      out.push(`<span class="tool-badge discount-badge">-${tool.discount}%</span>`);
      if (discountEnd) {
        const left = formatTimeRemaining(tool.discount_expiry);
        if (left) {
          out.push(
            `<span class="tool-badge discount-badge" data-expiry="${tool.discount_expiry}">
              ${left}
            </span>`
          );
        }
      }
    } else {
      out.push(`<span class="tool-badge discount-badge">${escapeHTML(tool.discount)}</span>`);
    }
  }
  if (isOffer) out.push(`<span class="tool-badge offer-badge">${escapeHTML(tool.offer)}</span>`);
  return out.join("");
}

/* ----------  MAIN LIST CLICK‑THROUGH ---------- */
container?.addEventListener("click", e => {
  const card = e.target.closest(".tool-card");
  if (!card) return;
  const tool = allTools.find(t => t.name === card.dataset.toolName);
  if (tool) showToolDetail(tool);
});

/* ----------  FILTER BUTTONS ---------- */
function generateFilterButtons() {
  if (!filtersContainer) return;                     // ← added guard
  const types = [...new Set(
    allTools.map(t => (t.type || "").toLowerCase()).filter(Boolean)
  )];
  filtersContainer.innerHTML = "";
  filtersContainer.appendChild(createFilterBtn("All"));
  types.forEach(t => filtersContainer.appendChild(createFilterBtn(t)));
}

function createFilterBtn(label) {
  const b = document.createElement("button");
  b.textContent = label.charAt(0).toUpperCase() + label.slice(1);
  b.addEventListener("click", () => {
    sessionStorage.setItem(FILTER_KEY, label.toLowerCase());
    applyFiltersAndRender();
  });
  return b;
}

/* ----------  DETAIL VIEW ---------- */
function swapMainImage(thumb) {
  const main = document.querySelector('.tool-main-img');
  if (!main) return;

  const src = thumb.dataset.src || thumb.src;
  main.src = src;
  main.dataset.src = src;

  document.querySelectorAll('.tool-gallery img')
          .forEach(i => i.classList.remove('active'));
  thumb.classList.add('active');
}

function showToolDetail(tool, initial = false) {
  if (!initial) {
    location.hash = `tool=${encodeURIComponent(tool.name)}`;
    document.body.classList.add('detail-mode');
  }

  // Hide special sections
  document.getElementById("offers-section")?.classList.add("hidden");
  document.getElementById("recommended-section")?.classList.add("hidden");
  document.getElementById("limited-section")?.classList.add("hidden");

  container.className = 'detail-wrapper';
  container.innerHTML = `
    <div class="tool-detail fade-in">
      <div class="tool-detail-top">
        <button class="back-btn" onclick="clearHash()">← Back</button>
        <h2>${escapeHTML(tool.name)} ${getBadges(tool)}</h2>
      </div>

      <div class="tool-detail-content">
        <div class="tool-detail-left">
          ${smartImg(tool.image || 'assets/placeholder.jpg', tool.name)
             .replace('<img ', '<img class="tool-main-img" onclick="openImageModal(this.src)" ')}
          <div class="tool-gallery">
            ${(tool.images || []).map(img => smartImg(img, 'gallery')).join('')}
          </div>
          ${tool.video ? `<iframe src="${tool.video}" class="tool-video" allowfullscreen></iframe>` : ''}
        </div>

        <div class="tool-detail-right">
          <div class="tool-info">
            <p class="desc"><strong>Description:</strong><br>${escapeHTML(
              tool.long_description || tool.description || 'No description available.'
            ).replace(/\n/g, "<br>")}</p><br>

            ${renderPricing(tool)}
            ${tool.discount       ? `<p><strong>Discount:</strong> ${tool.discount}%</p><br>` : ''}
            ${tool.offer_expiry   ? `<p>⏳ Offer ends in ${daysLeft(tool.offer_expiry)} days</p><br>` : ''}
            <p><strong>Stock:</strong><br>${getStockStatus(tool.stock)}</p><br>
            <p><strong>Released:</strong><br>${escapeHTML(tool.release_date || 'N/A')}</p><br>
            <p><strong>Updated:</strong><br>${escapeHTML(tool.update_date  || 'N/A')}</p><br>

            <div style="display:flex;gap:1rem;flex-wrap:wrap;">
              <a href="${getContactLink(tool.contact)}" target="_blank" class="contact-btn">💬 Contact</a>
              <button class="requirements-btn" onclick="showRequirementsPopup('${escapeHTML(tool.name)}')">
                Requirements
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    ${renderRecommendations(tool)}
  `;

  // Force load images in the detail view
  const mainImg = document.querySelector('.tool-main-img');
  const galleryImgs = document.querySelectorAll('.tool-gallery img');
  if (mainImg && mainImg.dataset.src) mainImg.src = mainImg.dataset.src;

  galleryImgs.forEach(img => {
    if (img.dataset.src) img.src = img.dataset.src;
    img.style.cursor = 'pointer';
    img.addEventListener('click', () => swapMainImage(img));
  });

  // Scroll the detail card into view
  setTimeout(() => {
    const detail = document.querySelector('.tool-detail');
    if (detail) window.scrollTo({ top: detail.getBoundingClientRect().top + window.scrollY - 100 });
  }, 0);
}

function clearHash() {
  document.body.classList.remove("detail-mode");
  location.hash = "";
  window.scrollTo(0, 0);

  // Show special sections
  document.getElementById("offers-section")?.classList.remove("hidden");
  document.getElementById("recommended-section")?.classList.remove("hidden");
  document.getElementById("limited-section")?.classList.remove("hidden");

  applyFiltersAndRender();
}

/* ----------  HASH UTILS ---------- */
function applyURLHash() {
  const h = decodeURIComponent(location.hash).replace("#", "");
  if (h.startsWith("tool=")) {
    const name = h.slice(5).toLowerCase();
    const tool = allTools.find((t) => (t.name || "").toLowerCase() === name);
    if (tool) showToolDetail(tool, true);
  }
}

/* ----------  MISC UTILITIES ---------- */
function getRecentTags(t) {
  const now = Date.now(), wk = 6048e5;
  const tags = [];
  if (now - new Date(t.release_date) < wk) tags.push('<span class="tag">new</span>');
  if (now - new Date(t.update_date)  < wk) tags.push('<span class="tag">updated</span>');
  return tags.join(" ");
}
const getStockStatus = (v) =>
  typeof v === "number"
    ? v === 0
      ? "Not in stock"
      : `${v} in stock`
    : typeof v === "string"
    ? { unlimited: "Unlimited", "very limited": "Very limited" }[v.toLowerCase()] || v
    : "Need to contact owner";

function formatTimeRemaining(dateStr) {
  const diff = new Date(dateStr) - new Date();
  if (diff <= 0) return null;
  const m = Math.floor(diff / 60000),
    d = Math.floor(m / 1440),
    h = Math.floor((m % 1440) / 60),
    mins = m % 60;
  return `⏳ ${d ? d + "d " : ""}${h ? h + "h " : ""}${mins}m left`.trim();
}

function getBadges(tool) {
  const now = Date.now(),
    wk = 6048e5,
    offerEnd = tool.offer_expiry && new Date(tool.offer_expiry),
    discEnd = tool.discount_expiry && new Date(tool.discount_expiry);

  const out = [];
  if (now - new Date(tool.release_date) < wk) out.push('<span class="badge new-badge">NEW</span>');
  if (now - new Date(tool.update_date)  < wk) out.push('<span class="badge updated-badge">UPDATED</span>');

  const discActive = tool.discount && (!discEnd || discEnd > now);
  if (discActive) {
    const numeric = !isNaN(parseFloat(tool.discount));
    const lbl = numeric ? `-${tool.discount}%` : escapeHTML(tool.discount);
    out.push(`<span class="badge discount-badge">${lbl}</span>`);
    if (numeric && discEnd) {
      const c = formatTimeRemaining(tool.discount_expiry);
      if (c) out.push(`<span class="badge discount-badge">${c}</span>`);
    }
  }
  const offerActive = tool.offer && (!offerEnd || offerEnd > now);
  if (offerActive) out.push(`<span class="badge offer-badge">${escapeHTML(tool.offer)}</span>`);
  return out.sort((a, b) => (a.includes("NEW") ? -1 : b.includes("NEW") ? 1 : 0)).join("");
}

const daysLeft = (d) => Math.max(0, Math.ceil((new Date(d) - new Date()) / 864e5));

const getContactLink = (t) =>
  ({ telegram: "https://t.me/fmpChatBot", discord: "https://discord.gg/kfJfP3aNwC", email: "mailto:flamemodparadiscord@gmail.com" }[
    t?.toLowerCase()
  ] || "#");

function renderPricing(tool) {
  if (tool.pricing) {
    const li = Object.entries(tool.pricing)
      .map(([k, v]) => `<li>${escapeHTML(k)}: ${escapeHTML(v)}</li>`)
      .join("");
    return `<p><strong>Pricing:</strong></p><ul class="pricing-list">${li}</ul><br>`;
  }
  if (tool.price)
    return `<p><strong>Price:</strong><br>${escapeHTML(tool.price).replace(/\n/g, "<br>")}</p><br>`;
  return "";
}

function renderRecommendations(tool) {
  const rec = allTools
    .filter((t) => t.name !== tool.name && (t.type || "").toLowerCase() === (tool.type || "").toLowerCase())
    .slice(0, 6);
  if (!rec.length) return "";
  return `
    <section class="recommended-section fade-in">
      <h3>You may also like</h3>
      <div class="recommended-scroll">
        ${rec
          .map(
            (r) => `
          <div class="recommended-card" onclick='location.hash="tool=${encodeURIComponent(r.name)}"'>
            <img src="${r.image || 'assets/placeholder.jpg'}"
                 alt="${escapeHTML(r.name)}">
            <h4>${escapeHTML(r.name)}</h4>
            <p>${escapeHTML((r.description || r.long_description || "")
              .split("\n")[0] || "No description")}…</p>
          </div>`
          )
          .join("")}
      </div>
    </section>`;
}

/* ----------  REQUIREMENTS POPUP ---------- */
function showRequirementsPopup(name) {
  const box = document.getElementById("popupMessage"),
    txt = document.getElementById("popupText");
  const tool = allTools.find((t) => t.name === name);
  let msg =
    tool?.requirements || `Requirements for ${name}…\n\nPlease contact the owner.`;
  txt.innerHTML = escapeHTML(msg).replace(/\n/g, "<br>");
  box.classList.remove("hidden");
  setTimeout(() => box.classList.add("hidden"), 4000);
}

/* ----------  NAV & SCROLL ---------- */
/* ── NAV MENU ─────────────────────────────── */
if (navbarToggle && navbarMenu) {
  // open / close with ☰
  navbarToggle.addEventListener("click", () =>
    navbarMenu.classList.toggle("show-menu")
  );

  // close after any link (or anything inside a link) is tapped
  navbarMenu.addEventListener("click", e => {
    if (e.target.closest("a")) navbarMenu.classList.remove("show-menu");
  });
}

/* ── SCROLL‑TO‑TOP BUTTON ─────────────────── */
if (scrollToTopBtn) {
  window.addEventListener(
    "scroll",
    () => scrollToTopBtn.classList.toggle("show", scrollY > 300),
    { passive: true }
  );

  scrollToTopBtn.addEventListener("click", () =>
    window.scrollTo({ top: 0, behavior: "smooth" })
  );
}

/* ----------  AUTOCOMPLETE ---------- */
let selectedIndex = -1;
const addToRecents = (n) => {
  const r = [n, ...JSON.parse(localStorage.getItem(RECENT_KEY) || "[]").filter((x) => x !== n)].slice(
    0,
    MAX_RECENTS
  );
  localStorage.setItem(RECENT_KEY, JSON.stringify(r));
};
const getWeightedFuseList = () => {
  const r = JSON.parse(localStorage.getItem(RECENT_KEY) || "[]");
  return allTools.map((t) => ({ ...t, _boost: r.filter((x) => x === t.name).length }));
};
const updateSelectedItem = (it) =>
  it.forEach((el, i) => el.classList.toggle("selected", i === selectedIndex));
const renderAutocomplete = (res) => {
  autocompleteBox.innerHTML = res
    .map(({ item }) => `<div data-name="${escapeHTML(item.name)}">${escapeHTML(item.name)}</div>`)
    .join("");
  autocompleteBox.classList.remove("hidden");
  selectedIndex = -1;
  autocompleteBox.querySelectorAll("div").forEach((d) =>
    d.addEventListener("mousedown", () => {
      runSearch(d.dataset.name);
      autocompleteBox.classList.add("hidden");
    })
  );
};
const showRecentSearches = () => {
  const r = JSON.parse(localStorage.getItem(RECENT_KEY) || "[]");
  if (!r.length) return;
  autocompleteBox.innerHTML = r.map((n) => `<div data-name="${escapeHTML(n)}">${escapeHTML(n)}</div>`).join("");
  autocompleteBox.classList.remove("hidden");
  selectedIndex = -1;
  autocompleteBox.querySelectorAll("div").forEach((d) =>
    d.addEventListener("mousedown", () => {
      addToRecents(d.dataset.name);
      searchInput.value = d.dataset.name;
      sessionStorage.setItem(SEARCH_KEY, d.dataset.name);
      applyFiltersAndRender();
      autocompleteBox.classList.add("hidden");
    })
  );
};

const debouncedSearch = debounce(runSearch, 250);

searchInput?.addEventListener("input", () => {
  const q = searchInput.value.trim();
  if (!q) {
    autocompleteBox.classList.add("hidden");
    debouncedSearch("");
    return;
  }

  const fuse = new Fuse(getWeightedFuseList(), {
    includeScore: true,
    includeMatches: true,
    threshold: 0.4,
    ignoreLocation: true,
    minMatchCharLength: 2,
    keys: [
      { name: "name", weight: 0.4 },
      { name: "keywords", weight: 0.3 },
      { name: "tags", weight: 0.1 },
      { name: "type", weight: 0.1 },
      { name: "description", weight: 0.3 },
      { name: "long_description", weight: 0.2 },
      { name: "_boost", weight: 0.8 }
    ]
  });

  const results = fuse.search(q).slice(0, 5);
  results.length ? renderAutocomplete(results) : autocompleteBox.classList.add("hidden");
  debouncedSearch(q);
});

searchInput?.addEventListener("keydown", (e) => {
  const items = autocompleteBox.querySelectorAll("div");
  if (!items.length) return;

  if (e.key === "ArrowDown") {
    e.preventDefault();
    selectedIndex = (selectedIndex + 1) % items.length;
    updateSelectedItem(items);
  } else if (e.key === "ArrowUp") {
    e.preventDefault();
    selectedIndex = (selectedIndex - 1 + items.length) % items.length;
    updateSelectedItem(items);
  } else if (e.key === "Enter") {
    e.preventDefault();
    if (selectedIndex !== -1) items[selectedIndex].dispatchEvent(new Event("mousedown"));
    else debouncedSearch(searchInput.value);
    autocompleteBox.classList.add("hidden");
  }
});

searchInput?.addEventListener("focus", () => {
  if (!searchInput.value.trim()) showRecentSearches();
});

searchInput?.addEventListener("blur", () => { setTimeout(() => autocompleteBox.classList.add("hidden"), 150)});

/* ================= LIVE COUNTDOWN ================= */
function updateBadges() {
  const now = Date.now();
  document.querySelectorAll("[data-expiry]").forEach((el) => {
    const expiry = Date.parse(el.dataset.expiry);
    if (isNaN(expiry)) return;
    const diff = expiry - now;
    if (diff <= 0) {
      el.remove();
      return;
    }
    el.textContent = formatTimeRemaining(el.dataset.expiry);
  });
}
setInterval(updateBadges, 60_000);

/* ----------  HASH & MODAL ---------- */
window.addEventListener("hashchange", applyURLHash);

document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") closeImageModal();
});
imageModal?.addEventListener("click", (e) => {
  if (e.target === imageModal) closeImageModal();
});
function openImageModal(src) {
  const modal = document.getElementById("imageModal");
  const modalImg = document.getElementById("modalImage");
  if (modal && modalImg) {
    modalImg.src = src;
    modal.classList.remove("hidden");
  }
}
function closeImageModal() {
  const modal = document.getElementById("imageModal");
  if (modal) modal.classList.add("hidden");
}

/* ----------  SORT SELECT ---------- */
sortSelect?.addEventListener("change", () => {
  sessionStorage.setItem(SORT_KEY, sortSelect.value);
  applyFiltersAndRender();
});

/* ----------  SCROLL PROGRESS ---------- */
document.addEventListener("scroll", () => {
  const scrollProgress = document.getElementById("scrollProgress");
  const scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
  const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
  const scrollPercentage = (scrollTop / scrollHeight) * 100;
  scrollProgress.style.width = `${scrollPercentage}%`;
});

/* ----------  GO ---------- */
if (container) loadData();

// Smooth scrolling for internal links
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute("href"));
      if (target) {
        target.scrollIntoView({ behavior: "smooth", block: "start" });
      }
    });
  });
});
