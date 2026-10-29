document.addEventListener("DOMContentLoaded", function () {
  const currentYear = new Date().getFullYear();
  const FOUNDING_YEAR = 1991;

  function animateCounter(id, end, duration, suffix) {
    const el = document.getElementById(id);
    if (!el || el.dataset.animated) return;
    el.dataset.animated = "1";
    let startTimestamp = null;
    const step = (timestamp) => {
      if (!startTimestamp) startTimestamp = timestamp;
      const progress = Math.min((timestamp - startTimestamp) / duration, 1);
      // Separators so a figure like 54880 reads as 54,880. Numbers below a
      // thousand are unaffected, so the other counters render as before.
      el.innerText = Math.floor(progress * end).toLocaleString("en-US") + (suffix || "");
      if (progress < 1) window.requestAnimationFrame(step);
    };
    window.requestAnimationFrame(step);
  }

  // stats holds the admin-editable figures; branchCount and officeCount are
  // counted live from the branches and regional_offices tables. None is written
  // into this file any more -- the employee and client numbers used to be
  // literals here, so correcting a staff count meant editing JavaScript and
  // redeploying the site.
  function runAllCounters(stats, branchCount, officeCount) {
    // No "K" suffix: the figure is now the real client count rather than a
    // rounded 100K, so appending K would read as 54,880 thousand.
    if (stats && typeof stats.served_clients === "number") {
      animateCounter("stat-clients", stats.served_clients, 5500);
    }
    if (stats && typeof stats.employees === "number") {
      animateCounter("stat-employees", stats.employees, 7000);
    }
    // Skipped when a count is unknown, so the card keeps the text it has
    // rather than animating to a number nobody can vouch for.
    if (typeof branchCount === "number") {
      animateCounter("stat-branches", branchCount, 4000);
    }
    if (typeof officeCount === "number") {
      animateCounter("stat-offices", officeCount, 4000);
    }
    // ATMABISWAS was founded in 1991 -- the page title, the h1, aboutus.php,
    // founder.php, eve.php, generalbody.php and seo.php all say so. This line
    // was the only place in the project reading 1994, and it undercounted the
    // organisation by three years. Derived from the current year rather than
    // stored, so it rolls over on its own every 1 January.
    animateCounter("stat-years", currentYear - FOUNDING_YEAR, 4000);
  }

  function setupObserver(stats, branchCount, officeCount) {
    const section = document.querySelector(".Numbercontainer");
    if (!section) return;
    if ("IntersectionObserver" in window) {
      const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
          runAllCounters(stats, branchCount, officeCount);
          observer.disconnect();
        }
      }, { threshold: 0.2 });
      observer.observe(section);
    } else {
      runAllCounters(stats, branchCount, officeCount);
    }
  }

  // Every figure in this row comes from the database on load; none is written
  // into this file. The branch counter once fell back to a hardcoded 30, which
  // meant an unreachable endpoint quietly published a made-up number
  // indistinguishable from a real one. Nothing falls back to an invented value
  // now: a failure resolves to null and that one counter is skipped while the
  // others still animate.
  //
  // All requests are awaited before any counter runs, so the row animates once
  // to final values instead of visibly correcting itself afterwards.
  const asJson = url => fetch(url).then(r => r.json()).catch(() => null);
  const countOf = data => data && typeof data.value === "number" ? data.value : null;

  Promise.all([
    asJson("/backend/getHomepageStats.php"),
    asJson("/backend/getBranchNumber.php"),
    asJson("/backend/getRegionalOfficeNumber.php")
  ]).then(([stats, branch, offices]) => {
    setupObserver(stats, countOf(branch), countOf(offices));
  });
});
