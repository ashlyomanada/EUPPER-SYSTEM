<template>
  <nav>
    <i class="bx bx-menu"></i>
    <a href="#" class="nav-link">PRO MIMAROPA E-UPER SYSTEM</a>
    <div class="nav-items">
      <input type="checkbox" id="switch-mode" hidden />
      <label for="switch-mode" class="switch-mode"></label>
      <a href="#" class="notification">
        <i class="bx bxs-bell"></i>
        <span class="num">8</span>
      </a>
      <a href="#" class="profile">
        <img src="img/people.png" />
      </a>
    </div>
  </nav>
</template>

<script>
export default {};
</script>

<style>
.nav-items {
  width: 20%;
  display: flex;
  justify-content: space-evenly;
}
@media screen and (max-width: 600px) {
  .nav-items {
    width: 60%;
    display: flex;
    justify-content: space-evenly;
  }
}
</style>
