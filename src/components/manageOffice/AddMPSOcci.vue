<template>
  <div class="table-data">
    <div class="order">
      <div class="rating-header py-3">
        <div class="d-flex align-items-center justify-content-between">
          <h4>Manage Occidental Mindoro Offices</h4>
          <button class="btn btn-primary" @click="openAddOffice">
            Add Office
          </button>
        </div>
      </div>

      <table>
        <thead>
          <tr>
            <th>Offices</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="RMFBOffice in RMFBOffices" :key="RMFBOffice">
            <td>{{ formatOfficeName(RMFBOffice) }}</td>
            <td>
              <div class="d-flex gap-2">
                <button class="btn btn-warning" @click="editOffice(RMFBOffice)">
                  <i class="fa-solid fa-pen fa-lg"></i>
                  <span>Edit</span>
                </button>
                <button
                  class="btn btn-danger"
                  @click="deleteColumn(RMFBOffice)"
                >
                  <i class="fa-solid fa-trash-can"></i>
                  <span>Delete</span>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Add Office Modal -->
  <div
    class="modal fade"
    id="addOfficeModal"
    tabindex="-1"
    aria-labelledby="addOfficeModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div
        class="modal-content"
        style="background: var(--light); color: var(--dark)"
      >
        <div class="modal-header">
          <h5 class="modal-title" id="addOfficeModalLabel">
            Add Occidental Mindoro Office
          </h5>
        </div>
        <div class="modal-body">
          <form @submit.prevent="insertColumnRmfb">
            <div class="mb-3">
              <label for="newOfficeName" class="form-label"
                >New Office Name</label
              >
              <input
                type="text"
                class="form-control"
                id="newOfficeName"
                v-model="newColumnName"
                required
                style="background: var(--light); color: var(--dark)"
              />
            </div>
            <div class="d-flex justify-content-end">
              <button type="submit" class="btn btn-success">
                Add PPO Office
              </button>
              <button
                type="button"
                class="btn btn-warning ms-2"
                data-bs-dismiss="modal"
              >
                Close
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Office Modal -->
  <div
    class="modal fade"
    id="editOfficeModal"
    tabindex="-1"
    aria-labelledby="editOfficeModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div
        class="modal-content"
        style="background: var(--light); color: var(--dark)"
      >
        <div class="modal-header">
          <h5 class="modal-title" id="editOfficeModalLabel">Edit Office</h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="officeNameEdit" class="form-label">Office:</label>
            <input
              type="text"
              class="form-control"
              id="officeNameEdit"
              v-model="columnValueEdit"
              style="background: var(--light); color: var(--dark)"
              required
            />
          </div>
          <div class="d-flex justify-content-end">
            <button class="btn btn-success" @click="saveColumn">Save</button>
            <button
              type="button"
              class="btn btn-warning ms-2"
              data-bs-dismiss="modal"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Confirmation Modal for Deletion -->
  <div
    class="modal fade"
    id="confirmDeleteModal"
    tabindex="-1"
    aria-labelledby="confirmDeleteModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div
        class="modal-content"
        style="background: var(--light); color: var(--dark)"
      >
        <div class="modal-header">
          <h5 class="modal-title" id="confirmDeleteModalLabel">
            Confirm Deletion
          </h5>
        </div>
        <div class="modal-body">
          <p>
            Are you sure you want to delete
            {{ formatOfficeName(columnToDelete) }}?
          </p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
            Cancel
          </button>
          <button type="button" class="btn btn-danger" @click="confirmDelete">
            Delete
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Success Modal -->
  <div
    class="modal fade"
    id="successModal"
    tabindex="-1"
    aria-labelledby="successModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-body text-center">
          <img class="checkImg" src="./img/check2.gif" alt="Success" />
          <h4 class="alertContent">Successfully Added New Office</h4>
          <button class="btn btn-primary" data-bs-dismiss="modal">Okay</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Success Modal -->
  <div
    class="modal fade"
    id="successModal2"
    tabindex="-1"
    aria-labelledby="successModal2Label"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-body text-center">
          <img class="checkImg" src="./img/check2.gif" alt="Success" />
          <h4 class="alertContent">Successfully Deleted Office</h4>
          <button class="btn btn-primary" data-bs-dismiss="modal">Okay</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Success Modal -->
  <div
    class="modal fade"
    id="successModal3"
    tabindex="-1"
    aria-labelledby="successModal3Label"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-body text-center">
          <img class="checkImg" src="./img/check2.gif" alt="Success" />
          <h4 class="alertContent">Successfully Edited Office</h4>
          <button class="btn btn-primary" data-bs-dismiss="modal">Okay</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { Modal } from "bootstrap";

export default {
  data() {
    return {
      newColumnName: "",
      RMFBOffices: "",
      columnValue: "",
      columnValueEdit: "",
      columnToDelete: null, // Store the column to delete
    };
  },

  mounted() {
    this.getOfficePPO();
  },

  methods: {
    formatOfficeName(officeName) {
      return officeName ? officeName.replace(/_/g, " ") : "";
    },

    openAddOffice() {
      const addOfficeModal = new Modal(
        document.getElementById("addOfficeModal")
      );
      addOfficeModal.show();
    },

    editOffice(officeName) {
      this.columnValue = officeName;
      this.columnValueEdit = officeName.replace(/_/g, " ");
      const editOfficeModal = new Modal(
        document.getElementById("editOfficeModal")
      );
      editOfficeModal.show();
    },

    async saveColumn() {
      try {
        const columnValueEdit = this.columnValueEdit.replace(/\s+/g, "_");
        const response = await axios.post("/updateColumnOcci", {
          OldColumnName: this.columnValue,
          NewColumnName: columnValueEdit,
        });

        if (response.status === 200) {
          this.columnValueEdit = "";
          const editOfficeModal = Modal.getInstance(
            document.getElementById("editOfficeModal")
          );
          if (editOfficeModal) {
            editOfficeModal.hide();
          }
          const successModal = new Modal(
            document.getElementById("successModal3")
          );
          successModal.show();
          setTimeout(() => {
            successModal.hide();
          }, 5000);

          this.getOfficePPO();
        }
      } catch (e) {
        console.log(e);
      }
    },

    deleteColumn(officeName) {
      this.columnToDelete = officeName;
      const confirmDeleteModal = new Modal(
        document.getElementById("confirmDeleteModal")
      );
      confirmDeleteModal.show();
    },

    async confirmDelete() {
      try {
        const response = await axios.post("/deleteColumnOcci", {
          ColumnName: this.columnToDelete,
        });

        if (response.status === 200) {
          const successModal = new Modal(
            document.getElementById("successModal2")
          );
          successModal.show();
          setTimeout(() => {
            successModal.hide();
          }, 5000);
          this.getOfficePPO();
        }
      } catch (e) {
        console.log(e);
      } finally {
        const confirmDeleteModal = Modal.getInstance(
          document.getElementById("confirmDeleteModal")
        );
        if (confirmDeleteModal) {
          confirmDeleteModal.hide();
        }
      }
    },

    async insertColumnRmfb() {
      try {
        const columnName = this.newColumnName.trim();
        const sanitizedColumnName = columnName.replace(/\s+/g, "_");
        const response = await axios.post("/addColumnOcci", {
          columnName: sanitizedColumnName,
        });

        if (response.status === 200) {
          this.newColumnName = "";
          const addOfficeModal = Modal.getInstance(
            document.getElementById("addOfficeModal")
          );
          addOfficeModal.hide();
          const successModal = new Modal(
            document.getElementById("successModal")
          );
          successModal.show();
          setTimeout(() => {
            successModal.hide();
          }, 5000);
          this.getOfficePPO();
        }
      } catch (e) {
        console.log(e);
      }
    },

    async getOfficePPO() {
      try {
        const response = await axios.get("/displayColumnsOcci");
        this.RMFBOffices = response.data.columns;
      } catch (e) {
        console.log(e);
      }
    },
  },
};
</script>
