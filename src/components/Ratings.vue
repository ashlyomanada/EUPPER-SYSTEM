<template>
  <div class="table-data">
    <div class="order">
      <div id="evaluation-ratings">
        <div>
          <h3>Unit Performance Evaluation Rating</h3>
          <h4>R1 - DPRM</h4>
        </div>
        <div class="table-options">
          <div class="options-control">
            Select User:<select class="month" name="month">
              <option value="1">R1</option>
            </select>
            Select Table:
            <select class="month" name="month">
              <option value="1">PPO CPO LEVEL</option>
              <option value="2">RMFB PMFC LEVEL</option>
              <option value="3">MPS CPS LEVEL</option>
            </select>
            <button class="find"><i class="bx bx-search"></i>Find</button>
          </div>
        </div>
      </div>
      <table>
        <thead>
          <tr>
            <th>Update</th>
            <th>Month</th>
            <th>Year</th>
            <th>Occidental Mindoro</th>
            <th>Oriental Mindoro</th>
            <th>Marinduque</th>
            <th>Romblon</th>
            <th>Palawan</th>
            <th>Puerto Princesa</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {};
</script>

<style>
.evaluation-ratings {
  display: flex;
  align-items: center;
  grid-gap: 16px;
  margin-bottom: 24px;
  flex-direction: column;
}
.table-options {
  width: 100%;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  padding: 1rem;
}
.options-control {
  display: flex;
  gap: 1rem;
}
</style>
