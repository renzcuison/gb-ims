<template>
  <div class="container mt-4">
    <div class="card mb-5">
      <div class="card-header">
        <h4>Stock In</h4>
      </div>
      <div class="card-body">

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
                  <button class="btn btn-sm btn-primary" @click="selectItem(item)">
                    Select
                  </button>
                </td>
              </tr>
              <tr v-if="filteredItems.length === 0 && items.length > 0">
                <td colspan="4" class="text-center">↺</td>
              </tr>
            </tbody>
          </table>
        </div>

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


        <div class="mb-3">
          <label for="reason">Reason</label>
          <select v-model="selectedReason" class="form-control">
            <option value="stock-in">Add</option>
            <option value="return">Return</option>
            <option value="other">Other</option>
          </select>
        </div>

        <div class="mb-3">
          <label for="description">Description</label>
          <textarea v-model="descriptionText" class="form-control"></textarea>
        </div>

        <RouterLink to="/stocks" class="btn btn-primary float-end">Back</RouterLink>
        <button type="button" @click="saveStock" class="btn btn-primary">Add</button>
      </div>
    </div>
  </div>
</template>


<script>
import axios from 'axios';

export default {
  name: 'StocksIn',
  data() {
    return {
      errorList: '',
      stockSearchQuery: '',
      filteredItems: [],
      selectedItems: [],
      descriptionText: '',
      selectedReason: 'stock-in',
      items: [],
      suppliers: [],
      units: ['Pc', 'Box', 'Kg', 'G', 'Liter', 'Ml', 'Meter', 'Cm', 'Bundle'],
      model: {
        item_id: '',
        quantity: 1,
        unit_of_measure: '',
        supplier_id: '',
        description: '',
      },
    };
  },

  created() {
    this.fetchItems();
    this.fetchSuppliers();
  },

  methods: {
    fetchItems() {
      axios.get('http://localhost:8001/api/items')
        .then((res) => {
          this.items = res.data.items;
          this.filteredItems = [...this.items];
        })
        .catch((error) => console.error('Error fetching items:', error));
    },

    fetchSuppliers() {
      axios.get('http://localhost:8001/api/suppliers')
        .then((res) => {
          this.suppliers = res.data.suppliers;
        })
        .catch((error) => console.error('Error fetching suppliers:', error));
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
          unit_of_measure: 'Pc',
          supplier_id: stock?.supplier_id || null,
          supplier_name: supplier?.supplier_name || 'Unknown',
          contact_name: supplier?.contact_name || 'Unknown',
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
      console.log("Save Stock method triggered!");

      if (this.selectedItems.length === 0) {
        alert("Please select at least one item to stock in.");
        return;
      }

      if (!this.selectedReason) {
        alert("Please select a reason for stock in.");
        return;
      }

      if (!this.descriptionText) {
        this.descriptionText = "No additional details provided";
      }

      this.timestamp = new Date().toLocaleString();

      const requests = this.selectedItems.map((item) => {
        const isReturn = this.selectedReason === "return";

        const newRemark = isReturn
          ? `IN (Pending): ${this.timestamp} - (${this.selectedReason.toUpperCase()}) ${this.descriptionText.trim()} +${item.quantity}`
          : `IN ${this.timestamp} - (${this.selectedReason.toUpperCase()}) ${this.descriptionText.trim()} +${item.quantity}`;

        if (isNaN(item.quantity) || item.quantity <= 0) {
          console.error(`Invalid quantity for item ${item.name}`);
          return Promise.reject(new Error("Invalid quantity"));
        }

        console.log(`Processing item ${item.name}`);

        return axios
          .get("http://localhost:8001/api/stocks")
          .then((res) => {
            console.log("Existing stock data fetched:", res.data);

            const existingStock = res.data.stocks.find(
              (stock) =>
                stock.item_id === item.item_id &&
                stock.unit_of_measure === item.unit_of_measure
            );

            if (existingStock) {
              console.log(`Updating existing stock for item ${item.name}...`);
              const updatedPayload = {
                ...existingStock,
                quantity: isReturn
                  ? existingStock.quantity 
                  : existingStock.quantity + parseInt(item.quantity),
                description: existingStock.description
                  ? `${existingStock.description}\n${newRemark}`
                  : newRemark,
              };

              return axios.put(
                `http://localhost:8001/api/stocks/${existingStock.id}`,
                updatedPayload
              );
            } else {
              console.log(`No existing stock found for ${item.name}. Creating new stock...`);
              const newStockPayload = {
                item_id: item.item_id,
                item_name: item.name,
                quantity: isReturn ? 0 : parseInt(item.quantity), 
                unit_of_measure: item.unit_of_measure,
                supplier_id: item.supplier_id,
                description: newRemark,
              };

              return axios.post("http://localhost:8001/api/stocks", newStockPayload);
            }
          })
          .catch((error) => {
            if (error.response && error.response.status === 404) {
              console.warn("No existing stock records found. Creating new stock...");

              const newStockPayload = {
                item_id: item.item_id,
                item_name: item.name,
                quantity: isReturn ? 0 : parseInt(item.quantity),
                unit_of_measure: item.unit_of_measure,
                supplier_id: item.supplier_id,
                description: newRemark,
              };

              return axios.post("http://localhost:8001/api/stocks", newStockPayload);
            } else {
              console.error(`Error processing item ${item.name}:`, error.message);
              return Promise.reject(error);
            }
          });
      });

      Promise.allSettled(requests)
        .then((results) => {
          const errors = results.filter((result) => result.status === "rejected");
          if (errors.length > 0) {
            console.error("Errors occurred while saving stock:", errors);
            alert("Some items could not be processed. Check the console for details.");
          } else {
            alert("Stock In completed successfully!");
            this.resetForm();
            this.$router.push("/stocks");
          }
        });
    },

    resetForm() {
      this.selectedItems = [];
      this.descriptionText = '';
      this.selectedReason = 'stock-in';
    },
  },
};
</script>
