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
    animateCounter("number3", branchCount,       4000);
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

  fetch("/backend/getBranchNumber.php")
    .then(res => res.json())
    .then(data => {
      const count = (data && typeof data.value === "number") ? data.value : 30;
      setupObserver(count);
    })
    .catch(() => setupObserver(30));
});
