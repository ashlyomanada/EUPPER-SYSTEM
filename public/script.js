const allSideMenu = document.querySelectorAll("#sidebar .side-menu.top li a");

allSideMenu.forEach((item) => {
  const li = item.parentElement;

  item.addEventListener("click", function () {
    allSideMenu.forEach((i) => {
      i.parentElement.classList.remove("active");
    });
    li.classList.add("active");
  });
});

// TOGGLE SIDEBAR
const menuBar = document.querySelector("#content nav .bx.bx-menu");
const sidebar = document.getElementById("sidebar");
const logo = document.getElementById("logo");
const adminName = document.getElementById("adminName");

menuBar.addEventListener("click", function () {
  sidebar.classList.toggle("hide");
  if (sidebar.classList.contains("hide")) {
    logo.style.height = "38px";
    adminName.style.visibility = "hidden";
  } else {
    logo.style.height = "120px";
    adminName.style.visibility = "unset";
  }
});

const searchButton = document.querySelector(
  "#content nav form .form-input button"
);
const searchButtonIcon = document.querySelector(
  "#content nav form .form-input button .bx"
);
const searchForm = document.querySelector("#content nav form");

if (window.innerWidth < 768) {
  sidebar.classList.add("hide");
  logo.style.height = "38px";
  adminName.style.visibility = "hidden";
}

const switchMode = document.getElementById("switch-mode");
const main = document.getElementById("main");

switchMode.addEventListener("change", function () {
  if (this.checked) {
    document.body.classList.add("dark");
    main.style.backgroundColor = "#060714";
  } else {
    document.body.classList.remove("dark");
    main.style.backgroundColor = "#fbfbfb";
  }
});
