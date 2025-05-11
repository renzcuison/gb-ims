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
          <h4>Add Item</h4>
        </div>
        <div class="card-body">
          <ul class="alert alert-warning" v-if="Object.keys(errorList).length > 0">
            <li class="mb-0 ms-3" v-for="(error, index) in errorList" :key="index">
              {{ error[0] }}
            </li>
          </ul>

          <div v-for="(stock, index) in model.stocks" :key="index" class="mb-4">
            <div class="mb-3">
              <label for="item_name">
                <strong>STOCK #{{ index + 1 }}</strong>
              </label>
              <input type="text" v-model="stock.item_name" class="form-control"
                :class="{ 'is-invalid': isDuplicate(stock.item_name, index) }" placeholder="Item Name" />
              <div v-if="isDuplicate(stock.item_name, index)" class="text-danger">
                Duplicate item name.
              </div>
            </div>

            <div class="mb-3">
              <label for="category_id">Category</label>
              <select v-model="stock.category_id" class="form-select">
                <option value="" disabled selected></option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.category_name }}
                </option>
              </select>
            </div>

            <div class="mb-3">
              <label for="unit_of_measure">Unit of Measure</label>
              <select v-model="stock.unit_of_measure"
                @blur="checkDuplicate(stock.item_name, stock.unit_of_measure, index)" class="form-select">
                <option value="" disabled selected></option>
                <option v-for="unit in units" :key="unit" :value="unit">{{ unit }}</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="price_per_unit">Retail Price</label>
              <input type="text" v-model="stock.price_per_unit" @input="handlePriceInput($event, index)"
                @blur="formatPrice(stock, index)" class="form-control" placeholder="Enter Price" />
            </div>

            <button v-if="index === model.stocks.length - 1" type="button" @click="addStock"
              class="btn btn-secondary me-3">
              Add Another Stock
            </button>

            <button v-if="index > 0" type="button" @click="removeStock(index)" class="btn btn-danger">
              Remove Stock
            </button>
          </div>

          <RouterLink to="/stocks" class="btn btn-primary float">Back</RouterLink>
          <button type="button" @click="saveStocks" class="btn btn-primary float-end">
            Confirm
          </button>
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
import axios from "axios";

export default {
  name: "StockCreate",
  data() {
    return {
      isProfiling: true,
      errorList: {},
      model: {
        stocks: [
          {
            item_name: "",
            description: "",
            category_id: "",
            suppliers: ['Unknown'],
            unit_of_measure: "",
            price_per_unit: "",
            buying_price: 0,
            physical_count: 0,
            on_hand: 0,
            sold: 0,
          },
        ],
      },
      categories: [],
      suppliers: [],
      units: ['Pc', 'Box', 'Kg', 'G', 'Liter', 'Ml', 'Meter', 'Cm', 'Bundle'],
      checkTimeout: null,
      duplicateItems: [],
    };
  },
  mounted() {
    this.fetchCategories();
  },
  methods: {
    isDuplicate(name, currentIndex) {
      const normalizedName = name.trim().toLowerCase();
      if (!normalizedName) return false;
      return this.model.stocks.some(
        (stock, index) =>
          index !== currentIndex &&
          stock.item_name.trim().toLowerCase() === normalizedName
      );
    },

    hasDuplicate() {
      return this.model.stocks.some((stock, index) => this.isDuplicate(stock.item_name, stock.unit_of_measure, index));
    },

    checkDuplicate(itemName, unitOfMeasure, index) {
      if (!itemName.trim() || !unitOfMeasure.trim()) {
        delete this.errorList[`${itemName}-${unitOfMeasure}`];
        return false;
      }

      clearTimeout(this.checkTimeout);
      this.checkTimeout = setTimeout(() => {
        axios
          .get(`http://localhost:8001/api/stocks/check-duplicate`, {
            params: { item_name: itemName, unit_of_measure: unitOfMeasure },
          })
          .then((response) => {
            if (response.data.exists) {
              this.errorList[`${itemName}-${unitOfMeasure}`] = "This stock already exists.";
            } else {
              delete this.errorList[`${itemName}-${unitOfMeasure}`];
            }
          })
          .catch((error) => {
            console.error("Error checking duplicate:", error);
          });
      }, 300);
      return false;
    },

    addStock() {
      this.model.stocks.push({
        item_name: "",
        description: "",
        category_id: "",
        supplier_id: "",
        unit_of_measure: "",
        price_per_unit: "",
        buying_price: "",
        physical_count: 0,
        on_hand: 0,
        sold: 0,
      });
    },

    removeStock(index) {
      this.model.stocks.splice(index, 1);
    },

    saveStocks() {
      const savePromises = this.model.stocks.map((stock) => {
        const payload = {
          ...stock,
          suppliers: this.isProfiling ? [] : stock.suppliers.filter((supplier) => supplier !== "Unknown"),
          price_per_unit: parseFloat(stock.price_per_unit.replace(/[^0-9.]/g, "")),
        };

        console.log("isProfiling:", this.isProfiling);
        console.log("Payload for saving stock:", payload);

        return axios.post("http://localhost:8001/api/stocks", payload, {
          params: { is_profiling: this.isProfiling },
        })
          .then((response) => {
            console.log("Stock saved successfully:", response.data);
          })
          .catch((error) => {
            console.error("Error saving stock:", error);
            throw error;
          });
      });

      Promise.allSettled(savePromises)
        .then((results) => {
          const failed = results.filter((result) => result.status === "rejected");

          if (failed.length > 0) {
            console.error("Some stocks could not be saved due to errors:");
            failed.forEach((failedResult) => {
              console.error(failedResult.reason);
            });
            alert("Some stocks could not be saved due to errors. Check the console for details.");
          } else {
            alert("All stocks saved successfully.");
            this.$router.push("/stocks");
          }
        });
    },

    fetchCategories() {
      axios
        .get("http://localhost:8001/api/categories")
        .then((response) => {
          this.categories = response.data.categories;
        })
        .catch((error) => {
          console.error("Error fetching categories:", error);
        });
    },

    handlePriceInput(event, index) {
      let input = event.target.value;
      input = String(input).replace(/[^0-9.]/g, "");

      if ((input.match(/\./g) || []).length > 1) {
        input = input.slice(0, input.lastIndexOf('.'));
      }

      this.model.stocks[index].price_per_unit = input;
    },

    formatPrice(stock, index) {
      let value = String(stock.price_per_unit);
      value = value.replace(/[^0-9.]/g, '');

      let formattedValue = parseFloat(value);
      if (isNaN(formattedValue)) {
        formattedValue = 0;
      }

      stock.price_per_unit = '₱ ' + formattedValue.toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    },
  },
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

<style>
.is-invalid {
  border-color: red !important;
}
</style>
