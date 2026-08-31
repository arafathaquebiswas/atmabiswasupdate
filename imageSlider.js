let slider = document.querySelector(".slider .list");
let items = document.querySelectorAll(".slider .list .item");
let next = document.getElementById("next");
let prev = document.getElementById("prev");
let dots = document.querySelectorAll(".slider .dots li");

let lengthItems = items.length - 1;
let active = 0;

/* Slides 2+ ship with data-src instead of src so the browser cannot download
   them during the initial load. Previously all five were fetched at once -- the
   lazy attribute they carried does nothing for images sitting in the viewport
   container -- and those four downloads competed with the visible slide, which
   took 8.9s to arrive on Slow 4G as a result.
   A slide is hydrated one step before it is shown, so by the time it slides into
   view the image is already decoded and there is no blank frame. */
function hydrateSlide(index) {
  if (index < 0 || index >= items.length) return;
  const img = items[index].querySelector("img[data-src]");
  if (!img) return;                       // slide 1, or already hydrated

  const picture = img.parentElement && img.parentElement.tagName === "PICTURE"
    ? img.parentElement
    : null;

  // Sources must exist before src is set, or the browser commits to the
  // fallback before it has seen the WebP candidate.
  if (!picture && (img.dataset.srcsetWebp || img.dataset.srcset)) {
    const pic = document.createElement("picture");
    if (img.dataset.srcsetWebp) {
      const s = document.createElement("source");
      s.type = "image/webp";
      s.srcset = img.dataset.srcsetWebp;
      s.sizes = img.dataset.sizes || "100vw";
      pic.appendChild(s);
    }
    if (img.dataset.srcset) {
      const s = document.createElement("source");
      s.srcset = img.dataset.srcset;
      s.sizes = img.dataset.sizes || "100vw";
      pic.appendChild(s);
    }
    img.parentNode.insertBefore(pic, img);
    pic.appendChild(img);
  }

  img.src = img.dataset.src;
  delete img.dataset.src;                 // hydrate once
}

function reloadSlider() {
  slider.style.transform = `translateX(-${items[active].offsetLeft}px)`;

  // Current slide, plus the one after it so the next advance is already loaded.
  hydrateSlide(active);
  hydrateSlide(active + 1 <= lengthItems ? active + 1 : 0);

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

/* Slide 2 is needed 3s in. Fetch it once the page has finished its own critical
   work, so it is ready in time without ever contending with the LCP image. */
if (document.readyState === "complete") {
  hydrateSlide(1);
} else {
  window.addEventListener("load", () => hydrateSlide(1), { once: true });
}

let refreshInterval = setInterval(() => next.click(), 3000);

dots.forEach((dot, index) => {
  dot.addEventListener("click", () => {
    active = index;
    reloadSlider();
  });
});

window.onresize = reloadSlider;
