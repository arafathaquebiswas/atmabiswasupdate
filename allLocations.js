window.onload = function () {
  getData();
};

function getData() {
  axios
    .get("locations.json")
    .then((response) => {
      const container = document.getElementById("storeId");
      const countEl = document.getElementById("branchCount");
      const branches = response.data;

      if (countEl) {
        countEl.textContent = branches.length + (branches.length === 1 ? " branch" : " branches");
      }

      branches.forEach((element) => {
        const link = document.createElement("a");

        link.href = "#";
        link.className = "ct-locator-item";
        link.setAttribute("data-lat", element.latitude);
        link.setAttribute("data-lng", element.longitude);
        link.dataset.search = [
          element.branch_name,
          element.address,
          element.district,
          element.division,
        ].join(" ").toLowerCase();

        link.innerHTML = `
          <div class="ct-locator-item-top">
            <span class="ct-locator-name">${element.branch_name}</span>
            <span class="ct-locator-code">#${element.code}</span>
          </div>
          <div class="ct-locator-item-row">
            <i class="fas fa-map-marker-alt"></i>
            <span>${element.address}</span>
          </div>
          <div class="ct-locator-item-row">
            <i class="fas fa-phone"></i>
            <span>${element.mobile}</span>
          </div>
          <div class="ct-locator-item-row ct-locator-item-meta">
            <i class="fas fa-location-dot"></i>
            <span>${element.division} &middot; ${element.district}</span>
          </div>
        `;

        link.addEventListener("click", (e) => {
          e.preventDefault();
          document.querySelectorAll('#storeId .ct-locator-item.active').forEach((el) => el.classList.remove('active'));
          link.classList.add('active');
          moveToLocation(element.latitude, element.longitude);
        });

        container.appendChild(link);
      });
    })
    .catch((e) => console.error('Failed to load branch locations:', e));
}
