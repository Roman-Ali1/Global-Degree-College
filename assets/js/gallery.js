/**
 * Global Degree College – Gallery JS
 * Save to: assets/js/gallery.js
 * Handles: client-side category filtering, count update
 */

"use strict";

(function () {
  const filterBtns = Array.from(
    document.querySelectorAll(".gallery-filter-btn"),
  );
  const items = Array.from(document.querySelectorAll(".masonry-item"));
  const countEl = document.getElementById("visibleCount");

  if (!filterBtns.length || !items.length) return;

  const normalize = (value) =>
    String(value || "")
      .trim()
      .toLowerCase();

  const applyFilter = (filter) => {
    let visible = 0;
    const target = normalize(filter);

    items.forEach((item) => {
      const itemCategory = normalize(item.dataset.category || "");
      const match = target === "all" || itemCategory === target;

      if (match) {
        item.classList.remove("hidden");
        item.style.animation = "none";
        item.offsetHeight;
        item.style.animation = "";
        visible++;
      } else {
        item.classList.add("hidden");
      }
    });

    if (countEl) countEl.textContent = visible;

    const url = new URL(window.location.href);
    if (target === "all") {
      url.searchParams.delete("category");
    } else {
      url.searchParams.set("category", filter);
    }
    history.replaceState({}, "", url);
  };

  filterBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      filterBtns.forEach((b) => {
        b.classList.remove("active");
        b.setAttribute("aria-pressed", "false");
      });

      btn.classList.add("active");
      btn.setAttribute("aria-pressed", "true");
      applyFilter(btn.dataset.filter || "all");
    });
  });

  const urlCategory = new URLSearchParams(window.location.search).get(
    "category",
  );
  if (urlCategory) {
    const matchedBtn = filterBtns.find(
      (btn) => normalize(btn.dataset.filter) === normalize(urlCategory),
    );
    if (matchedBtn) {
      matchedBtn.click();
    } else {
      applyFilter("all");
    }
  }

  if (typeof lightbox !== "undefined") {
    lightbox.option({
      resizeDuration: 200,
      wrapAround: true,
      albumLabel: "Photo %1 of %2",
      fadeDuration: 300,
      imageFadeDuration: 300,
      positionFromTop: 80,
      disableScrolling: true,
    });
  }
})();
