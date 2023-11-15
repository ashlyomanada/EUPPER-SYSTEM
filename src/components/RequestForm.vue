<template>
  <div class="table-data">
    <div class="order">
      <div class="head">
        <h3>Request Form</h3>
      </div>
      <form class="form" id="form">
        <input type="text" placeholder="Username" class="input" />
        <input type="text" placeholder="Sender" class="input" />
        <textarea placeholder="Type message"></textarea>
        <button type="submit">Submit</button>
      </form>
    </div>
  </div>
</template>

<script>
export default {};
</script>

<style>
.form {
  position: relative;
  display: flex;
  align-items: flex-start;
  flex-direction: column;
  gap: 10px;
  width: 100%;
  background: var(--light);
  padding: 20px;
  border-radius: 10px;
}

.form .title {
  color: royalblue;
  font-size: 30px;
  font-weight: 600;
  letter-spacing: -1px;
  line-height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.form input {
  outline: 0;
  border: 1px solid rgb(219, 213, 213);
  padding: 8px 14px;
  border-radius: 8px;
  width: 100%;
  height: 50px;
  color: var(--dark);
}

.form textarea {
  border-radius: 8px;
  height: 100px;
  width: 100%;
  resize: none;
  outline: 0;
  padding: 8px 14px;
  border: 1px solid rgb(219, 213, 213);
  color: var(--dark);
}

.form button {
  align-self: flex-end;
  padding: 8px;
  outline: 0;
  border: 0;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  background-color: royalblue;
  color: #fff;
  cursor: pointer;
}
@media screen and (max-width: 600px) {
  .form {
    width: 100vw;
  }
}
</style>
