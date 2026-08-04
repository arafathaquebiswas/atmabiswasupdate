const menuToggle = document.getElementById("menu-toggleId");
const sideNav = document.querySelector(".sidenav");
const closeBtn = document.getElementById("close-btn");
const sideDropdowns = document.querySelectorAll(".sidenav .sidedrop");
const sideNavLinks = document.querySelectorAll(
  ".sidenav a:not(.mainsidedrop a)"
);
const body = document.body;
const mobileHeader = document.querySelector(".mobile-header");

// Function to set body padding-top based on mobile header height
function setBodyPadding() {
  if (mobileHeader && window.innerWidth <= 768) {
    const headerHeight = mobileHeader.offsetHeight;
    body.style.paddingTop = headerHeight + "px";
  } else {
    body.style.paddingTop = "0";
  }
}

// Set padding on page load and resize
window.addEventListener("load", setBodyPadding);
window.addEventListener("resize", setBodyPadding);

// Toggle sidenav on menu button click
menuToggle.addEventListener("click", () => {
  sideNav.classList.toggle("active");
  menuToggle.classList.toggle("active");
});

// Close sidenav when close button is clicked
closeBtn.addEventListener("click", () => {
  sideNav.classList.remove("active");
  menuToggle.classList.remove("active");
});

// Prevent clicks inside sidenav from propagating
sideNav.addEventListener("click", (event) => {
  event.stopPropagation();
});

// Toggle dropdown menus in sidenav
sideDropdowns.forEach((dropdown) => {
  const mainDropdown = dropdown.querySelector(".mainsidedrop");

  mainDropdown.addEventListener("click", (event) => {
    event.preventDefault();
    event.stopPropagation();
    dropdown.classList.toggle("active");
  });
});

// Close sidenav only for normal nav links (not dropdowns)
sideNavLinks.forEach((link) => {
  link.addEventListener("click", () => {
    sideNav.classList.remove("active");
    menuToggle.classList.remove("active");
  });
});
