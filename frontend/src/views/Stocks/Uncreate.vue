<template>
  <div class="container mt-4">
    <div class="card mb-5">
      <div class="card-header">
        <h4>Stock Out</h4>
      </div>
      <div class="card-body">
        <!-- Error Messages -->
        <ul class="alert alert-warning" v-if="Object.keys(errorList).length > 0">
          <li class="mb-0 ms-3" v-for="(error, index) in errorList" :key="index">
            {{ error[0] }}
          </li>
        </ul>

        <!-- Searchable Item List -->
        <div class="mb-3">
          <input type="text" v-model="stockSearchQuery" @input="filterItemList" placeholder="Search by ID or Item Name"
            class="form-control mb-3" />
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>ID</th>
                <th>Item Name</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in filteredItems" :key="item.id">
                <td>{{ item.id }}</td>
                <td>{{ item.name }}</td>
                <td>
                  <button class="btn btn-sm btn-primary" @click="selectItem(item)">Select</button>
                </td>
              </tr>
              <tr v-if="filteredItems.length === 0 && items.length > 0">
                <td colspan="4" class="text-center">↺</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Selected Items -->
        <div>
          <div v-if="selectedItems.length > 0">
            <div class="border p-3 rounded mb-3">
              <div class="d-flex justify-content-between align-items-center">
                <h5>Item/s Selected</h5>
                <button class="btn btn-danger mb-3" @click="clearAllSelectedItems">Clear All</button>
              </div>
              <div v-for="(item, index) in selectedItems" :key="item.item_id" class="mb-2 p-3 border rounded">
                <div class="d-flex justify-content-between align-items-center">
                  <span>{{ item.name }}</span>
                  <div class="d-flex align-items-center">
                    <select v-model="item.unit_of_measure" class="form-select me-2" style="width: auto;">
                      <option v-for="unit in units" :key="unit" :value="unit">{{ unit }}</option>
                    </select>
                    <button type="button" class="btn btn-outline-secondary" @click="adjustQuantity(index, -1)"
                      :disabled="item.quantity <= 1">-</button>
                    <input type="number" v-model.number="item.quantity" class="form-control d-inline-block w-auto mx-2"
                      min="1" style="width: 80px" @input="validateQuantity(index)" />
                    <button type="button" class="btn btn-outline-secondary" @click="adjustQuantity(index, 1)">+</button>
                    <button type="button" class="btn btn-danger ms-3" @click="removeSelectedItem(index)">Remove</button>
                  </div>
                </div>

                <div class="mt-1">
                  <small><strong>Supplier:</strong> {{ item.supplier_name || 'Unknown' }}</small><br />
                  <small><strong>Contact:</strong> {{ item.contact_name || 'Unknown' }}</small>
                </div>
              </div>
            </div>
          </div>

          <div v-else>
            <p class="text-muted">No items selected</p>
          </div>
        </div>

        <!-- Reason and Description -->
        <div class="mb-3">
          <label for="reason">Reason</label>
          <select v-model="selectedReason" class="form-control">
            <option value="sold">Sold</option>
            <option value="expired">Expired</option>
            <option value="damaged">Damaged</option>
            <option value="other">Other</option>
          </select>
        </div>

        <div class="mb-3">
          <label for="description">Description</label>
          <textarea v-model="descriptionText" placeholder="" class="form-control"></textarea>
        </div>

        <!-- Buttons -->
        <RouterLink to="/stocks" class="btn btn-primary float-end">Back</RouterLink>
        <button type="button" @click="saveStock" class="btn btn-primary">Confirm</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "StocksOut",
  data() {
    return {
      errorList: "",
      stockSearchQuery: "",
      filteredItems: [],
      selectedItems: [],
      descriptionText: "",
      selectedReason: "",
      items: [],
      suppliers: [],
      units: ["Pc", "Box", "Kg", "G", "Liter", "Ml", "Meter", "Cm", "Bundle"],
      timestamp: "",
    };
  },

  created() {
    this.fetchItems();
    this.fetchSuppliers();
  },

  methods: {
    fetchItems() {
      axios.get("http://localhost:8001/api/items")
        .then((res) => {
          this.items = res.data.items;
          this.filteredItems = [...this.items];
        })
        .catch((error) => console.error("Error fetching items:", error));
    },

    fetchSuppliers() {
      axios.get("http://localhost:8001/api/suppliers")
        .then((res) => {
          this.suppliers = res.data.suppliers;
        })
        .catch((error) => console.error("Error fetching suppliers:", error));
    },

    filterItemList() {
      const query = this.stockSearchQuery.toLowerCase().trim();
      this.filteredItems = query
        ? this.items.filter(item => item.name.toLowerCase().includes(query) || item.id.toString().includes(query))
        : [...this.items];
    },

    selectItem(item) {
      if (!this.selectedItems.some(selected => selected.item_id === item.id)) {
        const stock = this.items.find(stock => stock.id === item.id);
        const supplier = this.suppliers.find(supplier => supplier.id === stock?.supplier_id);

        this.selectedItems.push({
          ...item,
          item_id: item.id,
          quantity: 1,
          unit_of_measure: "Pc",
          supplier_id: stock?.supplier_id || null,
          supplier_name: supplier?.supplier_name || "Unknown",
          contact_name: supplier?.contact_name || "Unknown",
        });
      }
    },

    removeSelectedItem(index) {
      this.selectedItems.splice(index, 1);
    },

    clearAllSelectedItems() {
      this.selectedItems = [];
    },

    adjustQuantity(index, amount) {
      const item = this.selectedItems[index];
      if (item.quantity + amount >= 1) {
        item.quantity += amount;
      }
    },

    validateQuantity(index) {
      const item = this.selectedItems[index];
      if (item.quantity < 1) {
        item.quantity = 1;
      }
    },

    saveStock() {
      if (this.selectedItems.length === 0) {
        alert("Please select at least one item to stock out.");
        return;
      }

      if (!this.selectedReason) {
        alert("Please select a reason for stock out.");
        return;
      }

      if (!this.descriptionText) {
        this.descriptionText = "No additional details provided";
      }

      this.timestamp = new Date().toLocaleString();

      const requests = this.selectedItems.map(item => {
        const newRemark = `OUT ${this.timestamp} - (${this.selectedReason.toUpperCase()}) ${this.descriptionText.trim()} -${item.quantity}`;

        return axios.get("http://localhost:8001/api/stocks")
          .then((res) => {
            const existingStock = res.data.stocks.find(
              stock => stock.item_id === item.item_id && stock.unit_of_measure === item.unit_of_measure
            );

            if (existingStock) {
              if (item.quantity > existingStock.quantity) {
                throw new Error(`Insufficient stock for ${item.name}`);
              }

              const updatedPayload = {
                ...existingStock,
                quantity: existingStock.quantity - item.quantity,
                description: existingStock.description
                  ? `${existingStock.description}\n${newRemark}`
                  : newRemark,
              };

              return axios.put(`http://localhost:8001/api/stocks/${existingStock.id}`, updatedPayload);
            } else {
              throw new Error(`No stock found for ${item.name}`);
            }
          })
          .catch((error) => {
            console.error(`Error processing item ${item.name}:`, error.message);
          });
      });

      Promise.allSettled(requests)
        .then(results => {
          const errors = results.filter(result => result.status === "rejected");
          if (errors.length > 0) {
            alert("Some items could not be processed. Check the console for details.");
          } else {
            alert("Stock Out completed successfully!");
            this.resetForm();
            this.$router.push("/stocks");
          }
        });
    },

    resetForm() {
      this.selectedItems = [];
      this.descriptionText = "";
      this.selectedReason = "";
    },
  },
};
</script>
