const carousel = document.querySelector(".carousel");
const prev = document.querySelector(".prev");
const next = document.querySelector(".next");
const images = Array.from(document.querySelectorAll(".carousel img"));
const title = document.getElementById("club-title");
const description = document.getElementById("club-description");
const logo = document.getElementById("club-logo");

const activities = [
  {
    title: "ULAB Computer Society",
    description:
      "ULAB Computer Society is a club of IT-savvy students. It hopes to be a club where students interested in technologies can share their knowledge and be benefited from each other by collaboration. ULAB ComSoc is also keen to provide a platform for developing leadership skills.",
    logo: "https://via.placeholder.com/150",
  },
  {
    title: "ULAB Debating Club",
    description:
      "ULAB Debating Club helps students develop their oratory and reasoning skills through debate competitions and practice sessions.",
    logo: "https://via.placeholder.com/150",
  },
  {
    title: "ULAB Debating Club",
    description:
      "ULAB Debating Club helps students develop their oratory and reasoning skills through debate competitions and practice sessions.",
    logo: "https://via.placeholder.com/150",
  },
  {
    title: "ULAB Debating Club",
    description:
      "ULAB Debating Club helps students develop their oratory and reasoning skills through debate competitions and practice sessions.",
    logo: "https://via.placeholder.com/150",
  },
  {
    title: "ULAB Debating Club",
    description:
      "ULAB Debating Club helps students develop their oratory and reasoning skills through debate competitions and practice sessions.",
    logo: "https://via.placeholder.com/150",
  },
  {
    title: "ULAB Debating Club",
    description:
      "ULAB Debating Club helps students develop their oratory and reasoning skills through debate competitions and practice sessions.",
    logo: "https://via.placeholder.com/150",
  },
  {
    title: "ULAB Debating Club",
    description:
      "ULAB Debating Club helps students develop their oratory and reasoning skills through debate competitions and practice sessions.",
    logo: "https://via.placeholder.com/150",
  },
  {
    title: "ULAB Robotics Club",
    description:
      "ULAB Robotics Club focuses on innovation and hands-on learning in the field of robotics and automation.",
    logo: "https://via.placeholder.com/150",
  },
  {
    title: "ULAB Sports Club",
    description:
      "ULAB Sports Club promotes physical fitness and teamwork through various sports activities and tournaments.",
    logo: "https://via.placeholder.com/150",
  },
];

let currentIndex = 0;

function updateCarousel() {
  const offset = -currentIndex * 110;
  carousel.style.transform = `translateX(calc(50% + ${offset}px))`;

  images.forEach((img, index) => {
    img.classList.toggle("selected", index === currentIndex);
  });

  prev.classList.toggle("disabled", currentIndex === 0);
  next.classList.toggle("disabled", currentIndex === images.length - 1);
}

function updateContent(index) {
  const activity = activities[index];
  title.textContent = activity.title;
  description.textContent = activity.description;
  logo.src = activity.logo;
}

prev.addEventListener("click", () => {
  if (currentIndex > 0) {
    currentIndex--;
    updateContent(currentIndex);
    updateCarousel();
  }
});

next.addEventListener("click", () => {
  if (currentIndex < images.length - 1) {
    currentIndex++;
    updateContent(currentIndex);
    updateCarousel();
  }
});

function autoChange() {
  currentIndex = (currentIndex + 1) % activities.length;
  updateContent(currentIndex);
  updateCarousel();
}

setInterval(autoChange, 2000);

updateCarousel();
