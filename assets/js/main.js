/**
 * Future Vision College – Global JavaScript
 * Save to: assets/js/main.js
 */

"use strict";

// ── AOS Init ──────────────────────────────────────────────────
AOS.init({
  duration: 700,
  easing: "ease-out-cubic",
  once: true,
  offset: 60,
});

// ── Navbar: shrink on scroll ──────────────────────────────────
const mainNav = document.getElementById("mainNav");
if (mainNav) {
  const handleNavScroll = () => {
    if (window.scrollY > 80) {
      mainNav.classList.add("scrolled");
    } else {
      mainNav.classList.remove("scrolled");
    }
  };
  window.addEventListener("scroll", handleNavScroll, { passive: true });
  handleNavScroll();
}

// ── Back to Top ───────────────────────────────────────────────
const backToTopBtn = document.getElementById("backToTop");
if (backToTopBtn) {
  window.addEventListener(
    "scroll",
    () => {
      if (window.scrollY > 400) {
        backToTopBtn.classList.add("visible");
      } else {
        backToTopBtn.classList.remove("visible");
      }
    },
    { passive: true },
  );

  backToTopBtn.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  });
}

// ── Admission Alert Bar: dismiss ─────────────────────────────
const admissionAlert = document.getElementById("admissionAlert");
if (admissionAlert) {
  const closeAlert = admissionAlert.querySelector('[data-dismiss="alert"]');
  if (closeAlert) {
    closeAlert.addEventListener("click", () => {
      admissionAlert.style.display = "none";
      sessionStorage.setItem("fvc_alert_dismissed", "1");
    });
  }
  // Keep dismissed across page loads (session only)
  if (sessionStorage.getItem("fvc_alert_dismissed")) {
    admissionAlert.style.display = "none";
  }
}

// ── Stats Counter Animation ───────────────────────────────────
function animateCounter(el, target, duration = 2000) {
  const start = performance.now();
  const startVal = 0;

  const tick = (now) => {
    const elapsed = now - start;
    const progress = Math.min(elapsed / duration, 1);
    // Ease out cubic
    const eased = 1 - Math.pow(1 - progress, 3);
    const current = Math.floor(startVal + eased * (target - startVal));

    el.textContent = current.toLocaleString();

    if (progress < 1) {
      requestAnimationFrame(tick);
    } else {
      el.textContent = target.toLocaleString();
    }
  };

  requestAnimationFrame(tick);
}

// Trigger counter when stats section enters viewport
const statNumbers = document.querySelectorAll("[data-counter]");
if (statNumbers.length > 0) {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting && !entry.target.dataset.animated) {
          entry.target.dataset.animated = "true";
          const target = parseInt(entry.target.dataset.counter, 10);
          animateCounter(entry.target, target);
        }
      });
    },
    { threshold: 0.4 },
  );

  statNumbers.forEach((el) => observer.observe(el));
}

// ── Smooth scroll for anchor links ───────────────────────────
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    const target = document.querySelector(this.getAttribute("href"));
    if (target) {
      e.preventDefault();
      const offset = 80; // Height of sticky nav
      const top =
        target.getBoundingClientRect().top + window.pageYOffset - offset;
      window.scrollTo({ top, behavior: "smooth" });
    }
  });
});

// ── Auto-dismiss Bootstrap alerts after 5s ───────────────────
document.querySelectorAll(".alert.alert-dismissible").forEach((alert) => {
  setTimeout(() => {
    const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
    if (bsAlert) bsAlert.close();
  }, 5000);
});

// ── Lazy load images ──────────────────────────────────────────
if ("loading" in HTMLImageElement.prototype) {
  document.querySelectorAll("img[data-src]").forEach((img) => {
    img.src = img.dataset.src;
  });
} else {
  // Fallback: IntersectionObserver
  const imgObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const img = entry.target;
        img.src = img.dataset.src;
        imgObserver.unobserve(img);
      }
    });
  });
  document
    .querySelectorAll("img[data-src]")
    .forEach((img) => imgObserver.observe(img));
}
// ── Course Filter Tabs ────────────────────────────────────────
const filterTabs = document.querySelectorAll(".filter-tab");
if (filterTabs.length > 0) {
  const attachCourseFilter = () => {
    filterTabs.forEach((tab) => {
      tab.addEventListener("click", () => {
        // Update active state
        filterTabs.forEach((t) => {
          t.classList.remove("active");
          t.setAttribute("aria-pressed", "false");
        });
        tab.classList.add("active");
        tab.setAttribute("aria-pressed", "true");

        const filter = (tab.dataset.filter || "all").trim().toLowerCase();
        const items = document.querySelectorAll(".course-grid-item");
        let visible = 0;

        items.forEach((item) => {
          const itemFilter = (item.dataset.filter || "").trim().toLowerCase();
          const match = filter === "all" || itemFilter === filter;
          item.classList.toggle("hidden", !match);
          if (match) visible++;
        });

        const noResults = document.getElementById("noFilterResults");
        if (noResults) {
          noResults.classList.toggle("d-none", visible > 0);
        }
      });
    });
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", attachCourseFilter);
  } else {
    attachCourseFilter();
  }
}
// ── Admissions Form ───────────────────────────────────────────

// Gender option visual toggle
document.querySelectorAll('.gender-option').forEach(option => {
    option.addEventListener('click', () => {
        document.querySelectorAll('.gender-option').forEach(o => o.classList.remove('selected'));
        option.classList.add('selected');
        option.querySelector('input[type="radio"]').checked = true;
    });
});

// Program option visual toggle
document.querySelectorAll('.program-option').forEach(option => {
    option.addEventListener('click', () => {
        document.querySelectorAll('.program-option').forEach(o => o.classList.remove('selected'));
        option.classList.add('selected');
        option.querySelector('input[type="radio"]').checked = true;
    });
});

// Live percentage calculator
const obtainedInput   = document.getElementById('obtained_marks');
const totalInput      = document.getElementById('total_marks');
const percentageDisplay = document.getElementById('percentageDisplay');

function updatePercentage() {
    if (!obtainedInput || !totalInput || !percentageDisplay) return;
    const obtained = parseFloat(obtainedInput.value) || 0;
    const total    = parseFloat(totalInput.value)    || 0;
    if (total > 0 && obtained > 0) {
        const pct = ((obtained / total) * 100).toFixed(1);
        percentageDisplay.textContent = pct + '%';
        percentageDisplay.style.color = pct >= 60 ? '#38a169' : '#e53e3e';
    } else {
        percentageDisplay.textContent = '—';
        percentageDisplay.style.color = '';
    }
}

if (obtainedInput) obtainedInput.addEventListener('input', updatePercentage);
if (totalInput)    totalInput.addEventListener('input', updatePercentage);

// CNIC auto-formatter: type digits, dashes added automatically
const cnicInput = document.getElementById('cnic_bform');
if (cnicInput) {
    cnicInput.addEventListener('input', function () {
        let val = this.value.replace(/[^0-9]/g, '');
        if (val.length > 5)  val = val.slice(0,5)  + '-' + val.slice(5);
        if (val.length > 13) val = val.slice(0,13) + '-' + val.slice(13);
        this.value = val.slice(0, 15);
    });
}

// Prevent double-submit
const admissionForm = document.getElementById('admissionForm');
if (admissionForm) {
    admissionForm.addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
        }
    });
}