<template>
  <header class="navbar">
    <div class="navbar-brand">
      <RouterLink :to="{ path: '/shop' }" class="brand-text">GREATBUY</RouterLink>
      <RouterLink :to="{ path: '/shop' }" class="brand-text-follow">DATABASE</RouterLink>
    </div>
    <div class="navbar-right">
      <img src="/profile.jpg" alt="User Profile" class="icon-image-profile" @click="toggleDropdown">
      <span class="user-name">admin</span>
      <button class="icon-button" @click="toggleDropdown">
        <img src="/drop.png" alt="Dropdown" class="icon-image">
      </button>

      <div v-if="dropdownVisible" class="dropdown-menu">
        <ul>
          <li>
            <button @click="handleLogout" class="dropdown-item">Logout</button>
          </li>
        </ul>
      </div>
    </div>
  </header>

  <div class="wrapper">
    <div class="sidebar">
      <nav>
        <ul>
          <li>
            <router-link to="/stocks" active-class="router-link-active" exact-active-class="router-link-active"
              :class="{ 'router-link-active': $route.path.startsWith('/stocks') }">
              <img src="/inventory.png" alt="Inventory" class="sidebar-icon" />
              INVENTORY
            </router-link>
          </li>
          <li>
            <router-link to="/suppliers" active-class="router-link-active">
              <img src="/supplier.png" alt="Suppliers" class="sidebar-icon"> SUPPLIERS
            </router-link>
          </li>
          <li>
            <router-link to="/categories" active-class="router-link-active">
              <img src="/category.png" alt="Categories" class="sidebar-icon"> CATEGORIES
            </router-link>
          </li>
          <li>
            <router-link to="/customers" active-class="router-link-active">
              <img src="/customer1.png" alt="Customers" class="sidebar-icon"> CUSTOMERS
            </router-link>
          </li>
          <li>
            <router-link to="/employees" active-class="router-link-active">
              <img src="/employees.png" alt="Employees" class="sidebar-icon"> EMPLOYEES
            </router-link>
          </li>
          <li>
            <router-link to="/orders" active-class="router-link-active">
              <img src="/order.png" alt="Orders" class="sidebar-icon"> ORDERS
            </router-link>
          </li>
          <li>
            <router-link to="/shop" active-class="router-link-active">
              <img src="/shop.png" alt="Shop" class="sidebar-icon"> SHOP
            </router-link>
          </li>
        </ul>
      </nav>
    </div>

    <div class="content">
    </div>

    <div class="container mt-4">
      <div class="card mb-5">
        <div class="card-header">
          <h4>Stock In</h4>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="supplier">Supplier</label>
            <select v-model="selectedSupplier" class="form-control" id="supplier">
              <option value="" disabled></option>
              <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                {{ supplier.supplier_name }}
              </option>
            </select>
          </div>

          <div class="mb-3">
            <label for="date">Date</label>
            <input type="date" v-model="dateInput" class="form-control" id="date" />
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
            <textarea v-model="descriptionText" class="form-control" id="description" rows="3"></textarea>
          </div>

          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Product</th>
                <th>Cost Price</th>
                <th>Quantity</th>
                <th>Serialized</th>
                <th></th>
              </tr>
            </thead>
            <td>
              <button class="btn btn-success" @click="addRow">+</button>
            </td>
            <tbody v-if="items.length > 0">
              <tr v-for="(item, index) in items" :key="index">
                <td>
                  <div class="d-flex">
                    <select v-model="item.id" class="form-control" @change="updateSelectedItem(item, index)">
                      <option value="" disabled>Select Product</option>
                      <option v-for="product in filteredItems" :key="product.id" :value="product.id">
                        {{ product.item_name }}
                      </option>
                    </select>
                    <button class="btn btn-primary ml-2" @click="openProductModal(index)">Select</button>
                  </div>
                </td>
                <td>
                  <input type="text" class="form-control" v-model="item.buying_price"
                    @input="handleBuyingPriceInput($event, index)" @blur="formatBuyingPrice(item, index)"
                    placeholder="Enter Cost Price" />
                </td>
                <td>
                  <div class="input-group">
                    <input type="number" v-model="item.quantity" class="form-control"
                      @input="handleQuantityInput($event, index)" @blur="validateQuantity(index)" min="1" step="1"
                      placeholder="Enter Quantity" />
                  </div>
                </td>
                <td>
                  <input type="checkbox" v-model="item.sku" />
                </td>
                <td>
                  <button class="btn btn-danger" @click="removeItem(index)">x</button>
                </td>
              </tr>
            </tbody>
            <tbody v-else>
              <tr>
                <td colspan="6" class="text-center">No items added.</td>
              </tr>
            </tbody>
          </table>

          <div v-if="showModal" class="modal-overlay">
            <div class="modal-content">
              <button class="close-button" @click="closeModal">×</button>
              <h5>Select Product</h5>

              <div class="mb-3">
                <input v-model="stockSearchQuery" @input="filterItemList" class="form-control" type="text"
                  id="stock-search" placeholder="Search by Item Name or Stock ID" />
              </div>

              <div v-for="(item, index) in filteredItems" :key="item.id" class="card p-2 m-2"
                style="width: 18rem; cursor: pointer" @click="selectProduct(item)">
                <img class="card-img-top" :src="item.image_url || ''" alt="Item image" />
                <div class="card-body">
                  <h5 class="card-title">{{ item.item_name }}</h5>
                  <p class="card-text">Item ID: {{ item.id }}</p>
                </div>
              </div>
            </div>
          </div>

          <RouterLink to="/stocks" class="btn btn-primary">Back</RouterLink>
          <button type="button" @click="saveStock" class="btn btn-primary float-end">Add</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();
const dropdownVisible = ref(false);
const username = ref("");

const toggleDropdown = () => {
  dropdownVisible.value = !dropdownVisible.value;
};

const handleLogout = () => {
  localStorage.removeItem('authToken');
  router.push('/login');
  closeHamburgerDropdown();
  dropdownVisible.value = false;
};

const fetchUserData = async () => {
  try {
    const token = localStorage.getItem('authToken');
    if (!token) {
      console.error("No auth token found.");
      handleLogout();
      return;
    }

    const response = await fetch('http://localhost:8001/api/user', {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });

    if (response.status === 401) {
      console.error("Unauthorized: Token may be invalid.");
      handleLogout();
      return;
    }

    if (!response.ok) {
      throw new Error(`Failed to fetch user data. Status: ${response.status}`);
    }

    const data = await response.json();
    console.log("User data:", data);

    username.value = data.name || "Admin";
  } catch (error) {
    console.error('Error fetching user data:', error);
    username.value = "Admin";
  }
};

onMounted(() => {
  fetchUserData();
});
</script>

<script>
import axios from 'axios';

export default {
  name: 'StocksIn',
  data() {
    return {
      showModal: false,
      selectedModalItem: null,
      stockSearchQuery: '',
      filteredItems: [],
      selectedItems: [],
      descriptionText: '',
      selectedReason: 'stock-in',
      selectedSupplier: '',
      selectedProductIndex: null,
      items: [],
      suppliers: [],
      units: ['Pc', 'Box', 'Kg', 'G', 'Liter', 'Ml', 'Meter', 'Cm', 'Bundle'],
      dateInput: '',
    };
  },

  created() {
    this.fetchItems();
    this.fetchSuppliers();
  },

  methods: {
    handleBuyingPriceInput(event, index) {
      let input = event.target.value;
      input = String(input).replace(/[^0-9.]/g, "");

      if ((input.match(/\./g) || []).length > 1) {
        input = input.slice(0, input.lastIndexOf("."));
      }

      this.items[index].buying_price = input;
    },

    formatBuyingPrice(item, index) {
      let value = String(item.buying_price);
      value = value.replace(/[^0-9.]/g, "");

      let formattedValue = parseFloat(value);
      if (isNaN(formattedValue)) {
        formattedValue = 0;
      }

      this.items[index].buying_price = "₱ " + formattedValue.toLocaleString("en-PH", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    },

    openProductModal(index) {
      this.selectedProductIndex = index;
      this.showModal = true;
    },

    closeModal() {
      this.showModal = false;
      this.selectedProductIndex = null;
    },

    addRow() {
      this.items.push({
        id: '',
        item_name: '',
        quantity: 1,
        sku: false,
        buying_price: '0', // Default to '0'
        price_per_unit: '0', // Default to '0'
        skus: [],
        unit_of_measure: 'Pc',
      });
    },

    updateSelectedItem(item, index) {
      const selectedProduct = this.filteredItems.find(product => product.id === item.id);
      if (selectedProduct) {
        this.items[index].item_name = selectedProduct.item_name;
        this.items[index].buying_price = selectedProduct.buying_price || '';
      }
    },

    selectProduct(product) {
      const selectedProductIndex = this.selectedProductIndex;
      if (selectedProductIndex !== null) {
        const selectedRow = this.items[selectedProductIndex];
        selectedRow.id = product.id;
        selectedRow.stock_id = product.id;
        selectedRow.item_name = product.item_name;
        selectedRow.buying_price = product.buying_price || '0'; // Default to '0' if undefined
        selectedRow.price_per_unit = product.price_per_unit || '0'; // Default to '0' if undefined
        selectedRow.category_id = product.category_id || null; // Assign category_id from the product
        this.selectedItems.push(selectedRow);
        this.closeModal();
      }
    },

    removeItem(index) {
      this.items.splice(index, 1);
    },

    fetchItems() {
      axios
        .get('http://localhost:8001/api/stocks')
        .then((res) => {
          this.filteredItems = res.data.stocks.map(stock => ({
            ...stock,
            showTransactions: false,
            transactions: [],
            unit_of_measure: stock.unit_of_measure || 'Pc',
          })).sort((a, b) => a.id.localeCompare(b.id));

          this.items = [];
          console.log("Fetched Items for dropdown/modal:", this.filteredItems);
        })
        .catch((error) => console.error('Error fetching stocks:', error));
    },

    fetchSuppliers() {
      axios
        .get('http://localhost:8001/api/suppliers')
        .then((res) => {
          this.suppliers = res.data.suppliers;
        })
        .catch((error) => console.error('Error fetching suppliers:', error));
    },

    filterItemList() {
      console.log("Search Query:", this.stockSearchQuery);
      console.log("Items before filtering:", this.items);

      const query = this.stockSearchQuery.toLowerCase().trim();
      this.filteredItems = query
        ? this.items.filter((stock) => {
          const itemName = stock.item_name?.toLowerCase() || '';
          const stockId = stock.id?.toString() || '';
          return itemName.includes(query) || stockId.includes(query);
        })
        : [...this.items];

      console.log("Filtered Items after filtering:", this.filteredItems);
    },

    selectItem(stock) {
      if (!this.selectedItems.some((selected) => selected.stock_id === stock.id)) {
        const supplier = this.suppliers.find(s => s.id === this.selectedSupplier);

        const newItem = {
          stock_id: stock.id,
          item_name: stock.item_name,
          unit_of_measure: 'Pc',
          supplier_id: supplier?.id || null,
          supplier_name: supplier?.supplier_name || 'Unknown',
          category_id: stock.category_id || null,
          quantity: 1,
          skus: [],
          physical_count: stock.physical_count || 0,
          on_hand: stock.on_hand || 0,
          sold: stock.sold || 0,
          price_per_unit: stock.price_per_unit || 0,
          buying_price: stock.buying_price || 0,
          description: stock.description || '',
        };

        this.selectedItems.push(newItem);
        console.log("Added selected stock item:", newItem);
        this.addSku(this.selectedItems.length - 1);
      }
    },

    removeSelectedItem(index) {
      this.selectedItems.splice(index, 1);
    },

    clearAllSelectedItems() {
      this.selectedItems = [];
    },

    updateSkusForQuantity(index) {
      const stock = this.items[index];
      if (!stock) {
        console.error(`Stock not found at index ${index}`);
        return;
      }

      const newQuantity = parseInt(stock.quantity, 10);

      if (newQuantity <= 0) {
        stock.quantity = 1;
        return;
      }

      const currentSkuCount = stock.skus.length;

      if (currentSkuCount < newQuantity) {
        const additionalSkus = newQuantity - currentSkuCount;
        this.generateAdditionalSkus(index, additionalSkus);
      }

      if (currentSkuCount > newQuantity) {
        stock.skus.splice(newQuantity);
      }
    },

    generateAdditionalSkus(index, additionalCount) {
      const stock = this.items[index];
      if (!stock) {
        console.error(`Stock not found at index ${index}`);
        return;
      }

      for (let i = 0; i < additionalCount; i++) {
        const supplier = (stock.supplier_name || 'Unknown').substring(0, 3).toUpperCase();
        const itemName = (stock.item_name || 'Unknown').substring(0, 3).toUpperCase();
        const randomNumber = Math.floor(100 + Math.random() * 900);
        const unitOfMeasure = (stock.unit_of_measure || 'Pc').toUpperCase();
        const sku = `${supplier}-${itemName}-${randomNumber}-${unitOfMeasure}`;

        stock.skus.push(sku);
      }
    },

    addSku(index) {
      const stock = this.selectedItems[index];
      const quantity = stock.quantity;

      if (quantity <= 0) return;

      this.generateAdditionalSkus(index, quantity);
    },

    saveStock() {
      if (this.selectedItems.length === 0) {
        console.error("No items selected to save.");
        alert("Please add items before saving.");
        return;
      }

      console.log("saveStock method called");
      console.log("Selected Items:", this.selectedItems);

      this.selectedItems.forEach((stock) => {
        if (!stock.stock_id) {
          console.error("Stock ID is undefined for item:", stock);
          alert("Stock ID is missing for one or more items. Please check your data.");
          return;
        }

        const payload = {
          stock_id: stock.stock_id,
          item_name: stock.item_name,
          quantity: parseInt(stock.quantity, 10) || 0,
          buying_price: parseFloat(stock.buying_price.replace(/[^\d.-]/g, '')) || 0,
          price_per_unit: parseFloat(stock.price_per_unit.replace(/[^\d.-]/g, '')) || 0,
          skus: Array.from(stock.skus),
          supplier_id: this.selectedSupplier || null,
          transaction_type: stock.transaction_type || 'in',
          unit_of_measure: stock.unit_of_measure || 'Pc',
          reason: stock.reason || 'stock-in',
          date: stock.date || new Date().toISOString().split('T')[0],
          description: stock.description || '',
          category_id: stock.category_id || null,
          physical_count: parseInt(stock.physical_count, 10) || 0,
          on_hand: parseInt(stock.on_hand, 10) || 0,
          sold: parseInt(stock.sold, 10) || 0,
        };

        console.log(`Payload for stock ID ${stock.stock_id}:`, payload);

        axios.put(`http://localhost:8001/api/stocks/${stock.stock_id}`, payload)
          .then(response => {
            console.log(`Stock updated successfully for stock ID ${stock.stock_id}`, response.data);
            this.$router.push('/stocks');
          })
          .catch(error => {
            console.error("Error updating stock:", error.response ? error.response.data : error.message);
          });
      });
    },

    validatePayload(payload) {
      if (!payload.stock_id) {
        throw new Error("Stock ID is required.");
      }
      if (!payload.item_name) {
        throw new Error("Item name is required.");
      }
      if (isNaN(payload.quantity) || payload.quantity < 0) {
        throw new Error("Quantity must be a non-negative number.");
      }
      if (isNaN(payload.buying_price) || payload.buying_price < 0) {
        throw new Error("Buying price must be a non-negative number.");
      }
      if (isNaN(payload.price_per_unit) || payload.price_per_unit < 0) {
        throw new Error("Price per unit must be a non-negative number.");
      }
      if (!payload.transaction_type) {
        throw new Error("Transaction type is required.");
      }
      if (!payload.unit_of_measure) {
        throw new Error("Unit of measure is required.");
      }
      if (!payload.reason) {
        throw new Error("Reason is required.");
      }
      if (!payload.date) {
        throw new Error("Date is required.");
      }
      return true;
    },

    toggleTransactionDetails(stock) {
      if (!stock.showTransactions) {
        this.fetchTransactions(stock.id);
      }
      stock.showTransactions = !stock.showTransactions;
    },

    fetchTransactions(stockId) {
      const stock = this.items.find(s => s.id === stockId);
      if (stock && !stock.transactions.length) {
        axios
          .get(`/api/transactions?stock_id=${stockId}`)
          .then((response) => {
            stock.transactions = response.data.transactions || [];
          })
          .catch((error) => {
            console.error(`Failed to fetch transactions for stock ID ${stockId}:`, error);
          });
      }
    },

    handleQuantityInput(event, index) {
      let input = event.target.value;
      input = input.replace(/[^0-9]/g, "");
      this.items[index].quantity = input;

      if (parseInt(input, 10) > 1) {
        this.items[index].sku = true;
        this.updateSkusForQuantity(index);
      } else {
        this.items[index].sku = false;
        this.items[index].skus = [];
      }
    },

    validateQuantity(index) {
      if (this.items[index].quantity <= 0) {
        this.items[index].quantity = 1;
      }
    },

    allowOnlyNumbers(event) {
      const charCode = event.which ? event.which : event.keyCode;
      if (charCode < 48 || charCode > 57) {
        event.preventDefault();
      }
    },
  },
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.modal-content {
  background-color: white;
  padding: 20px;
  border-radius: 10px;
  width: 300px;
}

.close-button {
  font-size: 20px;
  color: red;
  cursor: pointer;
  border: none;
  background: none;
}

.table {
  margin-top: 1rem;
}

@font-face {
  font-family: 'LibreCaslonDisplay-Regular';
  src: url('/assets/LibreCaslonDisplay-Regular.ttf') format('truetype');
}

@font-face {
  font-family: 'Kantumruy Pro';
  src: url('/assets/KantumruyPro-VariableFont_wght.ttf') format('truetype');
  font-weight: 100 900;
  font-style: normal;
}

* {
  font-family: 'Kantumruy Pro', sans-serif;
}

.wrapper {
  background-color: #F4F4F4;
  min-height: 93vh;
  display: flex;
}

.card {
  margin-left: 25px;
  margin-right: 12px;
}

.table-responsive {
  overflow-x: auto;
}

.table {
  width: 100%;
}

.navbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background-color: rgb(255, 255, 255);
  color: #fff;
  position: relative;
  height: 50px;
  padding: 0 20px;
  position: sticky;
  top: 0;
  z-index: 1000;
  margin-bottom: -1px;
}

.navbar-brand {
  display: flex;
  align-items: center;
}

.brand-text {
  font-family: 'LibreCaslonDisplay-Regular';
  text-decoration: none;
  color: #0086E7;
  font-size: 24px;
  font-weight: normal;
  cursor: pointer;
}

.brand-text-follow {
  font-family: 'LibreCaslonDisplay-Regular';
  text-decoration: none;
  color: #0086E7;
  font-size: 12px;
  font-weight: normal;
  cursor: pointer;
  margin-top: 10px
}

.icon-button {
  background: none;
  border: none;
  cursor: pointer;
}

.navbar-right {
  display: flex;
  align-items: center;
  gap: 10px;
  position: relative;
}

.icon-image {
  width: 10x;
  height: 10px;
  margin-top: 3px;
  margin-left: -10px;
}

.icon-image-profile {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  object-fit: cover;
}

.user-name {
  color: black;
  font-size: 13px;
  margin-top: 3px;
}

.dropdown-menu {
  min-width: 0;
  max-width: none;
  width: 100px !important;
  background-color: #ffffff;
  border-radius: 5px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  position: absolute;
  top: 100%;
  right: 0;
  overflow: hidden;
  display: block;
}

.dropdown-item {
  text-align: left;
  color: #7d7d7d;
  cursor: pointer;
  width: 100%;
  font-size: 12px;
  padding: 8px 10px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  background-color: transparent;
  border: none;
}

.dropdown-item:hover {
  background-color: #ffffff;
}

.dropdown-menu ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.sidebar {
  width: 300px;
  height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  background: linear-gradient(180deg, #0086E7 50%, #004B81 100%);
  padding: 20px;
}

.sidebar nav ul {
  list-style: none;
  padding: 0;
  margin-top: 30%;
  margin-left: 15%;
}

.sidebar nav ul li {
  display: flex;
  align-items: center;
  padding: 20px;
  position: relative;
  transition: background 0.3s;
}

.sidebar-icon {
  width: 16px;
  height: 16px;
  margin-right: 10px;
  margin-bottom: 2px;
  filter: brightness(0) invert(1);
}

.router-link-active {
  background: transparent;
}

.router-link-active::before {
  content: "";
  position: absolute;
  top: 0;
  left: -60px;
  width: 5px;
  height: 100%;
  background: white;
  transform: scale(1.1);
  z-index: 1;
}

.router-link-active::after {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 500%;
  height: 100%;
  background: #ffffff1c;
  z-index: -1;
  transform: scale(1.1);
  transition: transform 0.2s ease-in-out;
}

.router-link-active:hover {
  transform: none !important;
}

.sidebar nav ul li:not(.router-link-active):hover {
  transform: scale(1.05);
  transition: transform 0.2s ease-in-out;
}

.sidebar nav ul li a {
  color: white;
  text-decoration: none;
  font-size: 18px;
  font-weight: 200;
}

.content {
  margin-left: 250px;
  padding: 20px;
}

.low-stock-link {
  text-decoration: none;
  color: grey;
  display: inline;
  font-size: 10px;
  margin-left: 10px;
}

.low-stock-link:hover {
  text-decoration: underline !important;
}

.low-stock-alert {
  color: grey;
  display: flex;
  align-items: center;
  font-size: 10px;
  margin-left: 10px;
}

.search-by-label {
  margin-left: 14px;
  font-size: 12px;
}

.form-select {
  color: grey;
  font-size: 12px;
}

.form-check-label {
  font-size: 12px;
}

.form-control {
  font-size: 12px;
}

.timestamp {
  color: green;
}

.description {
  color: grey;
}

.low-stock-alert.no-stock {
  color: red;
  font-weight: bold;
}

.selected-row {
  background-color: #d1e7ff;
  font-weight: bold;
  border-left: 4px solid #e7d837;
  cursor: pointer;
}

.text-muted {
  font-size: 12px;
}

.btn-link {
  padding: 0;
  font-size: 12px;
  cursor: pointer;
}

.btn-success {
  background-color: green;
  border-color: green;
}

.description {
  color: grey;
  font-size: 12px;
}

.sku-text {
  font-size: 12px;
}

.sku-list li {
  margin-bottom: 5px;
}

.sku-list .text-muted {
  font-size: 11px;
}

.transaction-row {
  background-color: #f9f9f9;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal-content {
  background: #fff;
  padding: 20px;
  border-radius: 4px;
  max-width: 400px;
  width: 100%;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.modal-content h5 {
  margin-bottom: 15px;
}

.modal-content p {
  margin-bottom: 10px;
}

.modal-content .btn {
  display: block;
  margin-left: auto;
}

textarea.form-control {
  resize: vertical;
  font-size: 12px;
}

.log-row {
  background-color: #f9f9f9;
}

.log-row h6 {
  margin-top: 10px;
  font-size: 14px;
  font-weight: bold;
}

.log-row .table {
  margin-top: 10px;
}

.logs-text {
  font-size: 10px;
  color: gray;
  cursor: pointer;
  text-decoration: none;
  transition: text-decoration 0.2s ease-in-out, color 0.2s ease-in-out;
}

.logs-text:hover {
  text-decoration: underline;
  color: black;
}

.transaction-log-container {
  padding: 15px;
  border-radius: 5px;
  background-color: #f8f9fa;
}

.transaction-log-title {
  font-weight: bold;
  font-size: 14px;
  margin-bottom: 8px;
}

.transaction-table {
  font-size: 12px;
  width: 100%;
}

.transaction-table th {
  background-color: #e9ecef;
  text-align: left;
  padding: 6px;
  font-size: 12px;
}

.transaction-table td {
  padding: 6px;
  border-bottom: 1px solid #dee2e6;
}

.transaction-table tbody tr:hover {
  background-color: #f1f3f5;
}

.no-logs {
  font-size: 12px;
  color: #6c757d;
  text-align: center;
  padding: 8px;
}

.expanded-log-row {
  background-color: #f8f9fa;
}

td {
  position: relative;
}

.qty-container {
  position: absolute;
  bottom: 2px;
  display: flex;
  flex-direction: column;
}

.qty-text {
  font-size: 10px;
  cursor: pointer;
  text-decoration: none;
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
