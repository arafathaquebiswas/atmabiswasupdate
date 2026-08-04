document.addEventListener("DOMContentLoaded", function () {
  var currentHref = location.href;

  document.querySelectorAll(".navbar-band .bottom-row a").forEach(function (item) {
    if (item.href === currentHref) item.classList.add("active");
  });

  document.querySelectorAll(".navbar-band .top-row .bars a").forEach(function (item) {
    if (item.href === currentHref) item.classList.add("active");
  });
});
