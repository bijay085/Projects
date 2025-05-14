/* ========= Flame Mod Paradise – sw.js (v2) ========= */

const PRECACHE        = "fmp-precache-v1";   // install‑time assets
const RUNTIME_IMG     = "fmp-img-v1";        // runtime images
const RUNTIME_JSON    = "fmp-json-v1";       // runtime data
const urlsToCache = [
  "/", "/index.html", "/styles/main.css", "/scripts/main.js",
  "/manifest.json",
  "/assets/logo.png", "/assets/icons/icon-192.png", "/assets/icons/icon-512.png",
  "/data/tools.json", "/data/bots.json", "/data/checkers.json",
  "/data/game.json", "/data/others.json", "/data/cookies.json", "/data/methods.json"
];

/* ---------- INSTALL (pre‑cache shell) ---------- */
self.addEventListener("install", (e) => {
  e.waitUntil(caches.open(PRECACHE).then((c) => c.addAll(urlsToCache)));
  self.skipWaiting();
});

/* ---------- ACTIVATE (clean old) ---------- */
self.addEventListener("activate", (e) => {
  const keep = [PRECACHE, RUNTIME_IMG, RUNTIME_JSON];
  e.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => !keep.includes(k)).map((k) => caches.delete(k)))
    )
  );
  self.clients.claim();
});

/* ---------- FETCH ---------- */
self.addEventListener("fetch", (e) => {
  const req = e.request;
  const url = new URL(req.url);

  if (req.method !== "GET") return;                 // ignore POST/PUT …

  /* 1 IMAGES – cache‑FIRST */
  if (/\.(png|jpe?g|webp|gif|svg)$/i.test(url.pathname)) {
    e.respondWith(
      caches.open(RUNTIME_IMG).then(async (cache) => {
        const cached = await cache.match(req);
        if (cached) return cached;                  // hit
        const fresh = await fetch(req);             // miss
        cache.put(req, fresh.clone());              // store
        return fresh;
      })
    );
    return;                                         // stop here
  }

  /* 2 JSON – network‑FIRST */
  if (url.pathname.endsWith(".json")) {
    e.respondWith(
      fetch(req)
        .then((resp) => {
          const clone = resp.clone();
          caches.open(RUNTIME_JSON).then((cache) => cache.put(req, clone));
          return resp;
        })
        .catch(() => caches.match(req))
    );
    return;
  }

  /* 3 Everything else – try network, fall back to cache */
  e.respondWith(
    fetch(req).catch(() => caches.match(req))
  );
});
