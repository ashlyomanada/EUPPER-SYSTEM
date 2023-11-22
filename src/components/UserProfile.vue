<template>
  <div class="profile-container">
    <div class="cover-container">
      <img src="./img/cover.png" alt="" srcset="" class="cover-photo" />
    </div>
    <div class="img-container">
      <div class="img-left">
        <img src="./img/logo.png" alt="" id="profile-pic" />
        <h2 class="name">JOHN DOE HIDDLESTONE</h2>
      </div>
      <div class="img-right">
        <button class="edit-profile-btn">
          <i class="fa-solid fa-pencil fa-lg"></i>Edit profile
        </button>
      </div>
    </div>
    <div class="profile-description-container">
      <p><i class="fa-solid fa-briefcase fa-sm"></i>Office: Oriental Mindoro</p>
      <p><i class="fa-solid fa-phone fa-sm"></i>Phone No. 09546437378</p>
      <p>
        <i class="fa-solid fa-envelope fa-sm"></i>Email:
        johndoehiddlestone@gmail.com
      </p>
    </div>
  </div>
</template>

<script>
export default {};
</script>

<style>
.profile-container {
  height: 91vh;
  width: 100%;
  display: flex;
  flex-direction: column;
  position: relative;
  color: var(--dark);
}
.cover-container {
  position: absolute;
  display: flex;
  height: 45%;
  width: 100%;
  align-items: flex-end;
}
.cover-photo {
  height: 92%;
  width: 100%;
}
.img-container {
  display: flex;
  height: 38%;
  width: 100%;
  align-items: center;
  padding: 1rem;
  margin-top: 11.5rem;
  z-index: 1;
}
.img-left {
  display: flex;
  height: 100%;
  width: 75%;
  align-items: center;
  gap: 1rem;
}
.img-right {
  display: flex;
  height: 100%;
  width: 25%;
  align-items: center;
  justify-content: center;
}
.edit-profile-btn {
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  background: var(--blue);
  color: white;
}
#profile-pic {
  height: 90%;
  background-color: white;
  border-radius: 50%;
}
.profile-description-container {
  height: 38%;
  width: 100%;
  padding: 1rem;
  line-height: 2rem;
}
</style>
