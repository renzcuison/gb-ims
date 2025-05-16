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
          <h4>Release Item</h4>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <input v-model="stockSearchQuery" @input="filterItemList" class="form-control" type="text" id="stock-search"
              placeholder="Search by Item Name or Stock ID" />
          </div>

          <div class="item-table-scrollable">
            <table class="item-table">
              <thead>
                <tr>
                  <th>Item Name</th>
                  <th>Stock ID</th>
                  <th>On-hand</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in filteredItems" :key="item.id" @dblclick="selectItem(item)" class="item-table-row"
                  :title="'Double-click to add ' + item.item_name" tabindex="0">
                  <td>{{ item.item_name }}</td>
                  <td>{{ item.id }}</td>
                  <td>{{ item.on_hand }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <small class="text-muted item-table-hint">Double-click a row to add to selected items.</small>

          <div v-if="selectedItems.length > 0">
            <div class="border p-3 rounded mb-3">
              <div class="d-flex justify-content-between align-items-center">
                <h5>Item/s for Release</h5>
                <button class="btn btn-danger mb-3" @click="clearAllSelectedItems">Clear All</button>
              </div>
              <div v-for="(stock, index) in selectedItems" :key="stock.stock_id" class="mb-2 p-3 border rounded">
                <div class="d-flex justify-content-between align-items-center">
                  <span>{{ stock.item_name }}</span>
                  <button type="button" class="btn btn-danger ms-3" @click="removeSelectedItem(index)">Remove</button>
                </div>

                <div class="mb-3 mt-3">
                  <small><strong>Available Items: </strong></small>
                  <div class="sku-container">
                    <div v-for="(sku, skuIndex) in stock.skus" :key="skuIndex" class="form-check mt-2">
                      <div class="d-flex justify-content-between">
                        <div>
                          <input class="form-check-input" type="checkbox" :id="'sku-' + stock.stock_id + '-' + skuIndex"
                            :checked="stock.selectedSkus.includes(sku.sku)" @change="toggleSkuSelection(stock, sku)" />
                          <label class="form-check-label" :for="'sku-' + stock.stock_id + '-' + skuIndex">
                            {{ sku.sku }}
                          </label>
                        </div>
                        <div class="text-end">
                          <small>Stock ID: {{ sku.stock_id }}</small><br />
                          <small>Added At: {{ formatDate(sku.created_at) }}</small><br />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label for="dateReleased">Date Released</label>
            <input type="date" v-model="dateReleased" class="form-control" id="dateReleased" />
          </div>

          <div class="mb-3">
            <label for="receiverName">Receiver</label>
            <input type="text" v-model="receiverName" class="form-control" id="receiverName" />
          </div>

          <div class="mb-3">
            <label for="description">Description</label>
            <textarea v-model="descriptionText" class="form-control" id="description" rows="3"></textarea>
          </div>

          <RouterLink to="/stocks" class="btn btn-primary">Back</RouterLink>
          <button type="button" @click="saveStock" class="btn btn-primary float-end">Confirm</button>
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
  name: 'StocksOut',
  data() {
    return {
      stockSearchQuery: '',
      filteredItems: [],
      selectedItems: [],
      descriptionText: '',
      selectedReason: 'Sold',
      items: [],
      suppliers: [],
      dateReleased: '',
      receiverName: '',
    };
  },

  created() {
    this.fetchSuppliers().then(() => {
      this.fetchItems();
    });
  },

  methods: {
    toggleSkuSelection(stock, sku) {
      const skuIndex = stock.selectedSkus.indexOf(sku.sku);

      if (skuIndex !== -1) {
        stock.selectedSkus.splice(skuIndex, 1);
        console.log("Payload for DELETE SKU:", { stockId: stock.stock_id, skuId: sku.sku });
        this.removeSkuFromDatabase(stock.stock_id, sku.sku);
      } else {
        stock.selectedSkus.push(sku.sku);
        console.log("Payload for ADD SKU:", { stockId: stock.stock_id, skuId: sku.sku });
      }
    },

    removeSkuFromDatabase(stockId, skuId) {
      if (!skuId || !stockId) {
        console.log("Missing SKU ID or Stock ID:", { skuId, stockId });
        return;
      }

      console.log(`Removing SKU ${skuId} from stock ID ${stockId}`);

      axios.delete(`http://localhost:8001/api/stocks/${stockId}/skus/${skuId}`)
        .then(response => {
          console.log(`Successfully removed SKU ${skuId} from stock ID ${stockId}`, response);

          const stock = this.selectedItems.find(item => item.stock_id === stockId);
          if (stock) {
            stock.skus = stock.skus.filter(sku => sku.sku !== skuId);
          }
        })
        .catch(error => {
          console.error(`Error removing SKU ${skuId} for stock ID ${stockId}:`, error);
        });
    },

    fetchItems() {
      axios
        .get('http://localhost:8001/api/stocks')
        .then((res) => {
          this.items = res.data.stocks.map(stock => {
            // Find the supplier object using supplier_id
            const supplier = this.suppliers
              ? this.suppliers.find(s => s.id === stock.supplier_id)
              : null;
            return {
              ...stock,
              supplier: supplier || null,
              showTransactions: false,
              transactions: []
            };
          });
          this.filteredItems = [...this.items];
        })
        .catch((error) => console.error('Error fetching stocks:', error));
    },

    fetchSuppliers() {
      return axios.get('http://localhost:8001/api/suppliers')
        .then((res) => {
          this.suppliers = res.data.suppliers;
        })
        .catch((error) => console.error('Error fetching suppliers:', error));
    },

    filterItemList() {
      const query = this.stockSearchQuery.toLowerCase().trim();
      this.filteredItems = query
        ? this.items.filter(
          (stock) =>
            stock.item_name.toLowerCase().includes(query) ||
            stock.id.toString().includes(query)
        )
        : [...this.items];
    },

    selectItem(stock) {
      if (!this.selectedItems.some((selected) => selected.stock_id === stock.id)) {
        const selectedStock = this.items.find((s) => s.id === stock.id);


        let supplier_ids = [];
        if (Array.isArray(selectedStock.suppliers) && selectedStock.suppliers.length > 0) {
          // If suppliers is an array of objects
          supplier_ids = selectedStock.suppliers.map(s => s.id);
        } else if (selectedStock.supplier_id) {
          // Fallback if only a single supplier_id exists
          supplier_ids = [selectedStock.supplier_id];
        }

        const newItem = {
          stock_id: stock.id,
          item_name: stock.item_name,
          unit_of_measure: 'Pc',
          supplier_ids,
          quantity: 1,
          skus: selectedStock.skus || [],
          selectedSkus: [],
          category_id: stock.category_id || null,
          physical_count: stock.physical_count || 0,
          on_hand: stock.on_hand || 0,
          sold: stock.sold || 0,
          price_per_unit: stock.price_per_unit || 0,
          description: stock.description || '',
        };

        this.selectedItems.push(newItem);
      }
    },

    removeSelectedItem(index) {
      this.selectedItems.splice(index, 1);
    },

    clearAllSelectedItems() {
      this.selectedItems = [];
    },

    saveStock() {
      const requests = this.selectedItems.map((stock) => {
        const skusToRemove = stock.selectedSkus;
        const quantityToRemove = skusToRemove.length;

        skusToRemove.forEach((sku) => {
          this.removeSkuFromDatabase(stock.stock_id, sku);

          const stockLogPayload = {
            stock_id: stock.stock_id,
            sku: sku,
            qty: 1,
            reason: this.selectedReason,
            description: this.descriptionText || '',
            removed_at: new Date().toISOString(),
          };

          console.log(`Logging stock out for stock ID ${stock.stock_id}, SKU ${sku}:`, stockLogPayload);

          axios.post(`http://localhost:8001/api/stock-log`, stockLogPayload)
            .then(() => console.log(`Stock log recorded for SKU ${sku}`))
            .catch((error) => console.error(`Error logging stock out for SKU ${sku}:`, error));
        });

        const stockPayload = {
          stock_id: stock.stock_id,
          item_name: stock.item_name,
          category_id: stock.category_id,
          suppliers: stock.supplier_ids,
          unit_of_measure: stock.unit_of_measure,
          physical_count: Math.max(0, (stock.physical_count || 0) - quantityToRemove),
          on_hand: stock.on_hand,
          sold: this.selectedReason === 'Sold' ? (stock.sold || 0) + quantityToRemove : stock.sold,
          price_per_unit: stock.price_per_unit,
          buying_price: stock.buying_price || 0,
          description: stock.description || '',
          date_released: this.dateReleased,
          receiver: this.receiverName,
        };

        console.log(`Updating stock for stock ID ${stock.stock_id}:`, stockPayload);

        return axios.put(`http://localhost:8001/api/stocks/${stock.stock_id}`, stockPayload);
      });

      Promise.all(requests)
        .then(() => {
          console.log("All stock updates and logs have been processed successfully.");
          this.$router.push('/stocks');
        })
        .catch((error) => {
          console.error("Error processing stock out:", error);
        });
    },

    formatDate(date) {
      const options = { year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric', second: 'numeric' };
      return new Date(date).toLocaleDateString(undefined, options);
    }
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
  }
};
</script>

<style scoped>
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

.item-table-hint {
  display: block;
  /* Less top margin */
  margin-bottom: 15px;
  margin-left: 13px
    /* More bottom margin */
}

.item-table-scrollable {
  max-height: 220px;
  overflow-y: auto;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  background: #fafbfc;
  margin-bottom: 8px;
}

.item-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.item-table th,
.item-table td {
  padding: 8px 12px;
  border-bottom: 1px solid #e0e0e0;
  text-align: left;
}

.item-table th {
  background: #f3f6fa;
  font-weight: 600;
  position: sticky;
  top: 0;
  z-index: 1;
}

.item-table-row {
  cursor: pointer;
  transition: background 0.15s;
}

.item-table-row:hover,
.item-table-row:focus {
  background: #e6f0fa;
  outline: none;
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

.table {
  margin-top: 1rem;
}

.card-img-top {
  height: 200px;
  object-fit: cover;
}

.card-body {
  padding: 0.5rem;
}

.card-title {
  font-size: 1.2rem;
}

.card-text {
  font-size: 1rem;
}

.form-check {
  margin-bottom: 0.5rem;
}

.text-end {
  text-align: right;
}

.sku-container {
  max-height: 200px;
  overflow-y: auto;
  font-size: 0.9rem;
}

.sku-container .form-check {
  font-size: 0.8rem;
}

.alert {
  padding: 1rem;
  margin-top: 1rem;
  text-align: center;
  font-weight: bold;
}

.alert-success {
  background-color: #d4edda;
  color: #155724;
}

.alert-error {
  background-color: #f8d7da;
  color: #721c24;
}
</style>
