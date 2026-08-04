document.addEventListener("DOMContentLoaded", function () {
  function animateCounter(id, end, duration) {
    const el = document.getElementById(id);
    if (!el) return;
    let startTimestamp = null;
    const step = (timestamp) => {
      if (!startTimestamp) startTimestamp = timestamp;
      const progress = Math.min((timestamp - startTimestamp) / duration, 1);
      el.innerText = Math.floor(progress * end);
      if (progress < 1) window.requestAnimationFrame(step);
    };
    window.requestAnimationFrame(step);
  }

  animateCounter("number1", 1500, 2000);
  animateCounter("number2", 100, 2000);
  animateCounter("number3", 30, 2000);
  animateCounter("number4", 500, 2000);
});
