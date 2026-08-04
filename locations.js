document.addEventListener("DOMContentLoaded", function () {

  // -----------------------------------------------------------------------
  // Division select — load options from DB via AJAX, then wire branch filter
  // -----------------------------------------------------------------------
  const divisionSelect = document.getElementById("divisionSelect");

  function loadDivisions() {
    if (!divisionSelect) return;

    fetch("Action/get_divisions.php")
      .then(function (res) {
        if (!res.ok) throw new Error("HTTP " + res.status);
        return res.json();
      })
      .then(function (divisions) {
        divisionSelect.innerHTML = "";

        const defaultOpt = document.createElement("option");
        defaultOpt.value = "";
        defaultOpt.textContent = "Select Division";
        divisionSelect.appendChild(defaultOpt);

        if (!Array.isArray(divisions) || divisions.length === 0) {
          defaultOpt.textContent = "No divisions available";
          return;
        }

        divisions.forEach(function (div) {
          const opt = document.createElement("option");
          opt.value = div;
          opt.textContent = div;
          divisionSelect.appendChild(opt);
        });
      })
      .catch(function () {
        divisionSelect.innerHTML =
          '<option value="">Could not load divisions</option>';
      });
  }

  loadDivisions();

  // -----------------------------------------------------------------------
  // Branch filter — fires when a division is selected
  // -----------------------------------------------------------------------
  if (divisionSelect) {
    divisionSelect.addEventListener("change", function () {
      const division = this.value;
      if (!division) return;

      const tablebody = document.getElementById("table-body");
      if (!tablebody) return;

      tablebody.innerHTML =
        `<tr><td colspan="4" style="text-align:center;padding:20px;color:#666;">
           Loading branches…
         </td></tr>`;

      const xhr = new XMLHttpRequest();
      xhr.open("POST", "Action/get_branches.php", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

      xhr.onload = function () {
        if (xhr.status !== 200) {
          tablebody.innerHTML =
            `<tr><td colspan="4" style="text-align:center;color:#c00;">
               Request failed (${xhr.status})
             </td></tr>`;
          return;
        }
        try {
          const data = JSON.parse(xhr.responseText);
          tablebody.innerHTML = "";

          if (!Array.isArray(data) || data.length === 0) {
            tablebody.innerHTML =
              `<tr><td colspan="4" style="text-align:center;padding:20px;color:#666;">
                 No branches found for this division.
               </td></tr>`;
            return;
          }

          data.forEach(function (b) {
            const row = document.createElement("tr");
            row.innerHTML =
              `<td data-label="Branch Name">${b.branch_name || "N/A"}</td>
               <td data-label="Address">${b.address || "N/A"}</td>
               <td data-label="Division">${b.division || "N/A"}</td>
               <td data-label="District">${b.district || "N/A"}</td>`;
            tablebody.appendChild(row);
          });
        } catch (e) {
          tablebody.innerHTML =
            `<tr><td colspan="4" style="text-align:center;color:#c00;">Error parsing response.</td></tr>`;
        }
      };

      xhr.onerror = function () {
        if (tablebody) {
          tablebody.innerHTML =
            `<tr><td colspan="4" style="text-align:center;color:#c00;">Network error.</td></tr>`;
        }
      };

      xhr.send("division=" + encodeURIComponent(division));
    });
  }

  // -----------------------------------------------------------------------
  // Toggle — HQ & Liaison Office card
  // -----------------------------------------------------------------------
  const toggleBtn  = document.getElementById("toggle-btn");
  const contactCard = document.getElementById("contactCard");

  if (toggleBtn && contactCard) {
    toggleBtn.addEventListener("click", function () {
      const opening = !contactCard.classList.contains("active");
      contactCard.classList.toggle("active");
      if (opening) setTimeout(() => smoothScroll(contactCard), 100);
    });
  }

  // -----------------------------------------------------------------------
  // Toggle — Branches section
  // -----------------------------------------------------------------------
  const filterbtn  = document.getElementById("filterbutton");
  const filterField = document.getElementById("filterbars");

  if (filterbtn && filterField) {
    filterbtn.addEventListener("click", function () {
      const opening = !filterField.classList.contains("active");
      filterField.classList.toggle("active");
      if (opening) setTimeout(() => smoothScroll(filterField), 100);
    });
  }

  // -----------------------------------------------------------------------
  // Regional Offices — load from Action/get_regional_offices.php
  // -----------------------------------------------------------------------
  const newcard       = document.getElementById("newcard");
  const regSection    = document.getElementById("reg");
  const newtablebody  = document.getElementById("newtable");

  if (newcard && regSection && newtablebody) {
    newcard.addEventListener("click", function () {
      const opening = !regSection.classList.contains("active");
      regSection.classList.toggle("active");

      if (!opening) {
        newtablebody.innerHTML = "";
        return;
      }

      setTimeout(() => smoothScroll(regSection), 100);
      loadRegionalData();
    });
  }

  function loadRegionalData() {
    if (!newtablebody) return;

    newtablebody.innerHTML =
      `<tr><td colspan="4" style="text-align:center;padding:20px;color:#666;">
         Loading regional offices…
       </td></tr>`;

    fetch("Action/get_regional_offices.php")
      .then(function (res) {
        if (!res.ok) throw new Error("HTTP " + res.status);
        return res.json();
      })
      .then(function (data) {
        newtablebody.innerHTML = "";

        if (!Array.isArray(data) || data.length === 0) {
          newtablebody.innerHTML =
            `<tr><td colspan="4" style="text-align:center;padding:20px;color:#666;">
               No regional offices found.
             </td></tr>`;
          return;
        }

        data.forEach(function (o) {
          const row = document.createElement("tr");
          row.innerHTML =
            `<td data-label="Region Name">${o.region_name || "N/A"}</td>
             <td data-label="Address">${o.address || "N/A"}</td>
             <td data-label="Designation">${o.designation || "N/A"}</td>
             <td data-label="Phone">${o.phone || "N/A"}</td>`;
          newtablebody.appendChild(row);
        });
      })
      .catch(function () {
        newtablebody.innerHTML =
          `<tr><td colspan="4" style="text-align:center;color:#c00;">
             Error loading regional offices.
           </td></tr>`;
      });
  }

  // -----------------------------------------------------------------------
  // Escape key — close all open sections
  // -----------------------------------------------------------------------
  document.addEventListener("keydown", function (e) {
    if (e.key !== "Escape") return;
    if (contactCard && contactCard.classList.contains("active")) {
      contactCard.classList.remove("active");
    }
    if (filterField && filterField.classList.contains("active")) {
      filterField.classList.remove("active");
    }
    if (regSection && regSection.classList.contains("active")) {
      regSection.classList.remove("active");
      if (newtablebody) newtablebody.innerHTML = "";
    }
  });

  // -----------------------------------------------------------------------
  // Accessibility — focus outline on toggle buttons
  // -----------------------------------------------------------------------
  document.querySelectorAll(".toggle-btn").forEach(function (btn) {
    btn.addEventListener("focus", function () {
      this.style.outline = "2px solid #0073e6";
      this.style.outlineOffset = "2px";
    });
    btn.addEventListener("blur", function () {
      this.style.outline = "none";
    });
  });

  function smoothScroll(el) {
    if (el) el.scrollIntoView({ behavior: "smooth", block: "start" });
  }
});
