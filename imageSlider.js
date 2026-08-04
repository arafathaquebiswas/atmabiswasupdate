let slider = document.querySelector(".slider .list");
let items = document.querySelectorAll(".slider .list .item");
let next = document.getElementById("next");
let prev = document.getElementById("prev");
let dots = document.querySelectorAll(".slider .dots li");

let lengthItems = items.length - 1;
let active = 0;

function reloadSlider() {
  slider.style.transform = `translateX(-${items[active].offsetLeft}px)`;

  document.querySelector(".slider .dots li.active").classList.remove("active");
  dots[active].classList.add("active");
  clearInterval(refreshInterval);
  refreshInterval = setInterval(() => next.click(), 3000);
}

next.onclick = () => {
  active = active + 1 <= lengthItems ? active + 1 : 0;
  reloadSlider();
};

prev.onclick = () => {
  active = active - 1 >= 0 ? active - 1 : lengthItems;
  reloadSlider();
};

let refreshInterval = setInterval(() => next.click(), 3000);

dots.forEach((dot, index) => {
  dot.addEventListener("click", () => {
    active = index;
    reloadSlider();
  });
});

window.onresize = reloadSlider;
