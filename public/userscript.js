document.querySelectorAll("#sidebar .side-menu.top li a").forEach((item) => {
  const li = item.parentElement;

  item.addEventListener("click", function () {
    document.querySelectorAll("#sidebar .side-menu.top li a").forEach((i) => {
      i.parentElement.classList.remove("active");
    });
    li.classList.add("active");
  });
});

// TOGGLE SIDEBAR

document
  .querySelector("#content nav .bx.bx-menu")
  .addEventListener("click", function () {
    document.getElementById("sidebar").classList.toggle("hide");
    if (document.getElementById("sidebar").classList.contains("hide")) {
      document.getElementById("logo2").style.height = "38px";
      document.getElementById("adminName2").style.visibility = "hidden";
    } else {
      document.getElementById("logo2").style.height = "120px";
      document.getElementById("adminName2").style.visibility = "unset";
    }
  });

if (window.innerWidth < 768) {
  document.getElementById("sidebar").classList.add("hide");
  document.getElementById("logo2").style.height = "38px";
  document.getElementById("adminName2").style.visibility = "hidden";
}

document.getElementById("switch-mode").addEventListener("change", function () {
  if (this.checked) {
    document.body.classList.add("dark");
    document.getElementById("usermain").style.backgroundColor = "#060714";
  } else {
    document.body.classList.remove("dark");
    document.getElementById("usermain").style.backgroundColor = "#fbfbfb";
  }
});

document.getElementById("profile").addEventListener("click", () => {
  document.querySelectorAll("#sidebar .side-menu.top li a").forEach((item) => {
    const li = item.parentElement;
    li.classList.remove("active");
  });
  document.getElementById("userprof").classList.add("active");
});
