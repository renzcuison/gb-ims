<template>
  <div class="container mt-4">
    <div class="card mb-5">
      <div class="card-header">
        <h4>Stock In</h4>
      </div>
      <div class="card-body">
        <ul
          class="alert alert-warning"
          v-if="Object.keys(errorList).length > 0"
        >
          <li
            class="mb-0 ms-3"
            v-for="(error, index) in errorList"
            :key="index"
          >
            {{ error[0] }}
          </li>
        </ul>

        <div class="mb-3">
          <label for="item_id">Item Name</label>
          <select v-model="model.item_id" class="form-control" required>
            <option value="" disabled>Select an item</option>
            <option v-for="item in items" :key="item.id" :value="item.id">
              {{ item.name }}
            </option>
            <option disabled v-if="items.length === 0">
              No items available
            </option>
          </select>
        </div>

        <div class="mb-3">
          <label for="quantity">Quantity</label>
          <input
            type="number"
            v-model="model.quantity"
            class="form-control"
            required
          />
        </div>

        <div class="mb-3">
          <label for="unit_of_measure">Unit of Measure</label>
          <select v-model="model.unit_of_measure" class="form-control" required>
            <option v-for="unit in units" :key="unit" :value="unit">
              {{ unit }}
            </option>
          </select>
        </div>

        <div class="mb-3">
          <label for="supplier_id">Supplier</label>
          <select
            v-model="model.supplier_id"
            @change="updateSupplierName"
            class="form-control"
            required
          >
            <option value="" disabled>Select a supplier</option>
            <option
              v-for="supplier in suppliers"
              :key="supplier.id"
              :value="supplier.id"
            >
              {{ supplier.supplier_name }}
            </option>
            <option disabled v-if="suppliers.length === 0">
              No suppliers available
            </option>
          </select>
        </div>


        <div class="mb-3">
          <label for="description">Description</label>
          <textarea v-model="model.description" class="form-control"></textarea>
        </div>

        <RouterLink to="/stocks" class="btn btn-primary float-end"
          >Back</RouterLink
        >
        <button type="button" @click="saveStock" class="btn btn-primary">
          Add
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "StocksCreate",
  data() {
    return {
      errorList: "",
      model: {
        item_id: "",
        quantity: "",
        unit_of_measure: "piece",
        supplier_id: "",
        description: "",
      },
      supplierName: "",
      items: [],
      suppliers: [],
      units: ["Pc", "Box", "Kg", "G", "Liter", "Ml", "Meter", "Cm", "Bundle"],
    };
  },
  created() {
    this.fetchItems();
    this.fetchSuppliers();
  },
  methods: {
    fetchItems() {
      axios
        .get("http://localhost:8001/api/items")
        .then((res) => {
          this.items = res.data.items || res.data; // Adjust this line based on your API response
          console.log("Items fetched:", this.items);
        })
        .catch((error) => {
          console.error("Error fetching items:", error);
        });
    },
    fetchSuppliers() {
      axios
        .get("http://localhost:8001/api/suppliers")
        .then((res) => {
          this.suppliers = res.data.suppliers || res.data; // Adjust this line based on your API response
          console.log("Suppliers fetched:", this.suppliers);
        })
        .catch((error) => {
          console.error("Error fetching suppliers:", error);
        });
    },
    updateSupplierName() {
      console.log("Suppliers:", this.suppliers);
      console.log("Selected Supplier ID:", this.model.supplier_id);

      const selectedSupplier = this.suppliers.find((supplier) => {
        console.log("Checking supplier:", supplier); // Log each supplier
        return supplier.id === this.model.supplier_id; // Ensure type matches
      });

      this.supplierName = selectedSupplier
        ? selectedSupplier.supplier_name
        : "";
      console.log("Selected Supplier Name:", this.supplierName);
    },
    saveStock() {
      // Find the selected item to retrieve its name
      const selectedItem = this.items.find(
        (item) => item.id === this.model.item_id
      );
      const itemName = selectedItem ? selectedItem.name : "";

      // Set item_name in the model for the API request
      const requestData = {
        item_name: itemName, // Assuming your API requires this field
        item_id: this.model.item_id,
        quantity: this.model.quantity,
        unit_of_measure: this.model.unit_of_measure,
        supplier_id: this.model.supplier_id,
        description: this.model.description,
      };

      console.log("Model data being sent:", requestData); // Log the model data

      axios
        .post("http://localhost:8001/api/stocks", requestData)
        .then((res) => {
          alert(res.data.message);
          this.model = {
            item_id: "",
            quantity: "",
            unit_of_measure: "piece",
            supplier_id: "",
            description: "",
          };
          this.supplierName = "";
          this.errorList = "";
          this.$router.push("/stocks");
        })
        .catch((error) => {
          if (error.response) {
            console.log("Error response:", error.response); // Log the full error response
            if (error.response.status === 422) {
              // Log specific validation errors
              console.error("Validation errors:", error.response.data.errors);
              this.errorList = error.response.data.errors;
            } else {
              console.log("Error", error.message);
            }
          }
        });
    },
  },
};
</script>
