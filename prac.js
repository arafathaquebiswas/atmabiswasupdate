document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.querySelector(".search-bar input");
  const filterButtons = document.querySelectorAll(".filter-btn");
  const noticeCards = document.querySelectorAll(".notice-card");
  let currentCategory = "all";

  // Search functionality
  searchInput.addEventListener("input", function (e) {
    const searchTerm = e.target.value.toLowerCase();

    noticeCards.forEach((card) => {
      const title = card
        .querySelector(".notice-title")
        .textContent.toLowerCase();
      const description = card
        .querySelector(".notice-description")
        .textContent.toLowerCase();
      const category = card.dataset.category;

      const matchesSearch =
        title.includes(searchTerm) || description.includes(searchTerm);
      const matchesCategory =
        currentCategory === "all" || category === currentCategory;

      card.style.display = matchesSearch && matchesCategory ? "block" : "none";
    });
  });

  // Filter functionality
  filterButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const category = this.dataset.category;

      // Update active button
      filterButtons.forEach((btn) => btn.classList.remove("active"));
      this.classList.add("active");

      currentCategory = category;

      // Filter cards
      noticeCards.forEach((card) => {
        const cardCategory = card.dataset.category;
        const searchTerm = searchInput.value.toLowerCase();
        const title = card
          .querySelector(".notice-title")
          .textContent.toLowerCase();
        const description = card
          .querySelector(".notice-description")
          .textContent.toLowerCase();

        const matchesSearch =
          title.includes(searchTerm) || description.includes(searchTerm);
        const matchesCategory = category === "all" || cardCategory === category;

        card.style.display =
          matchesSearch && matchesCategory ? "block" : "none";
      });
    });
  });

  // Expandable description functionality
  noticeCards.forEach((card) => {
    const description = card.querySelector(".notice-description");
    const fullText = description.textContent;
    const shortText = fullText.substring(0, 150) + "...";

    if (fullText.length > 150) {
      description.textContent = shortText;

      const readMoreBtn = document.createElement("button");
      readMoreBtn.textContent = "Read More";
      readMoreBtn.className = "notice-btn read-more-btn";
      readMoreBtn.style.backgroundColor = "transparent";
      readMoreBtn.style.color = "var(--primary-color)";
      readMoreBtn.style.padding = "0.5rem 0";

      readMoreBtn.addEventListener("click", function () {
        if (description.textContent === shortText) {
          description.textContent = fullText;
          this.textContent = "Show Less";
        } else {
          description.textContent = shortText;
          this.textContent = "Read More";
        }
      });

      description.parentNode.insertBefore(readMoreBtn, description.nextSibling);
    }
  });
});
