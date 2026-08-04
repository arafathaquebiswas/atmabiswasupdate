const form = document.querySelector("form");
const nextBtn = form ? form.querySelector(".nextBtn") : null;
const backBtn = form ? form.querySelector(".backBtn") : null;

if (nextBtn) {
  nextBtn.addEventListener("click", () => {
    const allInput = form.querySelectorAll("input");
    allInput.forEach((input) => {
      if (input.value != "") {
        form.classList.add("secActive");
      } else {
        form.classList.remove("secActive");
      }
    });
  });
}

if (backBtn) {
  backBtn.addEventListener("click", () => form.classList.remove("secActive"));
}
