document.addEventListener("DOMContentLoaded", function () {
  const currentYear = new Date().getFullYear();

  function animateCounter(id, end, duration, suffix) {
    const el = document.getElementById(id);
    if (!el || el.dataset.animated) return;
    el.dataset.animated = "1";
    let startTimestamp = null;
    const step = (timestamp) => {
      if (!startTimestamp) startTimestamp = timestamp;
      const progress = Math.min((timestamp - startTimestamp) / duration, 1);
      el.innerText = Math.floor(progress * end) + (suffix || "");
      if (progress < 1) window.requestAnimationFrame(step);
    };
    window.requestAnimationFrame(step);
  }

  function runAllCounters(branchCount) {
    animateCounter("number1", 1500,              7000);
    animateCounter("number2", 100,               5500, "K");
    // Skipped when the count is unknown, so the card keeps the text it has
    // rather than animating to a number nobody can vouch for.
    if (typeof branchCount === "number") {
      animateCounter("number3", branchCount,     4000);
    }
    animateCounter("number4", currentYear - 1994, 4000);
  }

  function setupObserver(branchCount) {
    const section = document.querySelector(".Numbercontainer");
    if (!section) return;
    if ("IntersectionObserver" in window) {
      const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
          runAllCounters(branchCount);
          observer.disconnect();
        }
      }, { threshold: 0.2 });
      observer.observe(section);
    } else {
      runAllCounters(branchCount);
    }
  }

  // The branch count comes from the database on every load and is never
  // hardcoded here. The old fallback of 30 meant an unreachable endpoint
  // quietly published a made-up figure indistinguishable from a real one.
  // null is passed through instead and the branch counter is skipped.
  fetch("/backend/getBranchNumber.php")
    .then(res => res.json())
    .then(data => {
      const value = data && data.value;
      setupObserver(typeof value === "number" ? value : null);
    })
    .catch(() => setupObserver(null));
});
