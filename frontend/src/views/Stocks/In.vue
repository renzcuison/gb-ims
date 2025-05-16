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
              <option value="" disabled>Select Supplier</option>
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

          <table class="table table-striped table-hover align-middle text-center">
            <thead class="table-primary">
              <tr>
                <th>
                  <button class="btn btn-success btn-sm" @click="addRow">Add</button>
                </th>
                <th class="product-column">Product</th>
                <th class="cost-price-column">Cost Price</th>
                <th class="quantity-column">Quantity</th>
                <th>ITEM SN</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in items" :key="index">
                <td class="numbering">{{ index + 1 + "." }}</td>
                <td>
                  <div class="d-flex align-items-center">
                    <select v-model="item.id" class="form-select"
                      @change="(e) => { console.log('Dropdown changed:', e.target.value); updateSelectedItem(item, index); }">
                      <option value="" disabled>Select Product</option>
                      <option v-for="product in filteredItems" :key="product.id" :value="product.id">
                        {{ product.item_name }}
                      </option>
                    </select>
                    <button class="btn btn-sm btn-primary ms-2" @click="openProductModal(index)">Select</button>
                  </div>
                </td>
                <td>
                  <input type="text" class="form-control text-end cost-price-input" v-model="item.buying_price"
                    @input="handleBuyingPriceInput($event, index)" @blur="formatBuyingPrice(item, index)"
                    placeholder="" />
                </td>
                <td>
                  <input type="number" v-model="item.quantity" class="form-control text-center quantity-input"
                    @input="handleQuantityInput($event, index)" @blur="validateQuantity(index)" min="1" step="1" />
                </td>
                <td>
                  <div class="serial-inputs-container">
                    <div v-for="n in item.quantity" :key="n" class="serial-input-row">
                      <label class="serial-label">#{{ n }}</label>
                      <input type="text" v-model="item.serial_numbers[n - 1]" placeholder="Serial #"
                        class="serial-input" @input="updateSkuWithSerial(index, n - 1)" />
                      <span v-if="item.skus && item.skus[n - 1]" class="sku-text">
                        {{ item.skus[n - 1] }}
                      </span>
                    </div>
                  </div>
                </td>
                <td>
                  <button v-if="index > 0" class="btn btn-sm btn-danger" @click="removeItem(index)">Remove</button>
                </td>
              </tr>
            </tbody>
          </table>

          <div v-if="showModal" class="modal-overlay">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Select Product</h5>
                <button class="close-button" @click="closeModal">×</button>
              </div>
              <div class="modal-body">
                <div class="search-container mb-3">
                  <input v-model="stockSearchQuery" @input="filterItemList" class="form-control" type="text"
                    id="stock-search" placeholder="Search by Item Name or Stock ID" />
                </div>
                <div class="product-list">
                  <div v-for="(item, index) in filteredItems" :key="item.id" class="product-card"
                    @click="selectProduct(item)">
                    <img class="product-image" :src="item.image_url || ''" alt="" />
                    <div class="product-details">
                      <h6 class="product-title">{{ item.item_name }}</h6>
                      <p class="product-id">Item ID: {{ item.id }}</p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button class="btn btn-secondary" @click="closeModal">Cancel</button>
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
      selectedSupplier: null,
      selectedProductIndex: null,
      items: [],
      suppliers: [],
      units: ['Pc', 'Box', 'Kg', 'G', 'Liter', 'Ml', 'Meter', 'Cm', 'Bundle'],
      dateInput: '',
    };
  },

  created() {
    this.fetchItems().then(() => {
      this.addRow();
    });
    this.fetchSuppliers();
  },

  watch: {
    selectedSupplier(newSupplierId) {
      console.log("Selected Supplier ID:", newSupplierId);

      const selectedSupplier = this.suppliers.find(supplier => supplier.id === newSupplierId);
      console.log("Selected Supplier:", selectedSupplier);

      this.items.forEach(item => {
        item.supplier_name = selectedSupplier ? selectedSupplier.supplier_name : 'Unknown';
      });

      this.selectedItems.forEach(item => {
        item.supplier_name = selectedSupplier ? selectedSupplier.supplier_name : 'Unknown';
      });

      console.log("Updated items and selectedItems with supplier name:", this.items, this.selectedItems);
    },
  },

  watch: {
    items: {
      deep: true,
      handler(newItems) {
        newItems.forEach((item, index) => {
          if (item.quantity > 1 && !item.sku) {
            item.sku = true;
            this.updateSkusForQuantity(index);
          } else if (item.quantity <= 1 && item.sku) {
            item.sku = false;
            item.skus = [];
          }
        });
      },
    },
  },

  methods: {
    updateSkuWithSerial(index, serialIndex) {
      const item = this.items[index];
      const serial = item.serial_numbers[serialIndex] || '';
      const supplier = (item.supplier_name || 'Unknown').substring(0, 3).toUpperCase();
      const itemName = (item.item_name || 'Unknown').substring(0, 3).toUpperCase();
      const unitOfMeasure = (item.unit_of_measure || 'Pc').toUpperCase();
      const sku = `${supplier}-${itemName}-${serial}-${unitOfMeasure}`;
      item.skus[serialIndex] = sku;
    },

    getStocks() {
      axios.get('http://localhost:8001/api/stocks')
        .then(res => {
          this.items = res.data.stocks.map(stock => ({
            ...stock,
            showTransactions: false,
            transactions: [],
            unit_of_measure: stock.unit_of_measure || 'Pc',
          }));
          console.log("Fetched stocks:", this.items);
        })
        .catch(error => {
          console.error('Error fetching stocks:', error);
        });
    },

    handleSerializedChange(index) {
      const item = this.items[index];

      if (item.sku) {
        if (item.quantity <= 0) {
          item.quantity = 1;
        }
        this.updateSkusForQuantity(index);
        console.log(`SKUs generated for item ${item.item_name} because the checkbox is checked.`);
      } else {
        item.skus = [];
        console.log(`SKUs cleared for item ${item.item_name} because the checkbox is unchecked.`);
      }
    },

    updateQuantities(index) {
      const item = this.items[index];
      const quantity = parseInt(item.quantity, 10);

      if (isNaN(quantity) || quantity <= 0) {
        item.quantity = 1; // could be the possible error
        return;
      }

      item.physical_count += quantity;
      item.on_hand += quantity;

      console.log(`Updated quantities for item ${item.item_name}:`, {
        physical_count: item.physical_count,
        on_hand: item.on_hand,
      });
    },

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
      if (this.items.length === 0) {
        console.error("Items array is empty. Add rows before opening the modal.");
        return;
      }

      this.selectedProductIndex = index;
      this.showModal = true;
    },

    closeModal() {
      this.showModal = false;
      this.selectedProductIndex = null;
    },

    addRow() {
      const supplier = this.suppliers.find(s => s.id === this.selectedSupplier);

      this.items.push({
        id: '',
        item_name: '',
        quantity: 1,
        physical_count: 0,
        on_hand: 0,
        buying_price: '',
        price_per_unit: '0',
        skus: [],
        serial_numbers: [],
        unit_of_measure: 'Pc',
        supplier_name: supplier?.supplier_name || 'Unknown',
      });

      console.log("Added new row with supplier name:", supplier?.supplier_name || 'Unknown');
    },

    updateSelectedItem(item, index) {
      console.log("Dropdown value changed for item:", item);

      const selectedProduct = this.filteredItems.find(product => product.id === item.id);
      const selectedSupplier = this.suppliers.find(s => s.id === this.selectedSupplier);

      if (selectedProduct) {
        console.log("Selected Product from dropdown:", selectedProduct);

        this.items[index].item_name = selectedProduct.item_name;
        this.items[index].price_per_unit = selectedProduct.price_per_unit || '0';
        this.items[index].category_id = selectedProduct.category_id || null;
        this.items[index].unit_of_measure = selectedProduct.unit_of_measure || 'Pc';
        this.items[index].description = selectedProduct.description || '';
        this.items[index].stock_id = selectedProduct.id;
        this.items[index].physical_count = selectedProduct.physical_count || 0;
        this.items[index].on_hand = selectedProduct.on_hand || 0;
        this.items[index].transaction_type = "in";
        this.items[index].supplier_name = selectedSupplier?.supplier_name || 'Unknown';

        if (!this.items[index].quantity || this.items[index].quantity <= 0) {
          this.items[index].quantity = 1;
        }

        if (this.items[index].sku) {
          this.updateSkusForQuantity(index);
        }

        const existingItem = this.selectedItems.find(selected => selected.stock_id === selectedProduct.id);
        if (!existingItem) {
          const newItem = {
            stock_id: selectedProduct.id,
            item_name: selectedProduct.item_name,
            unit_of_measure: selectedProduct.unit_of_measure,
            supplier_id: this.selectedSupplier,
            supplier_name: this.suppliers.find(s => s.id === this.selectedSupplier)?.supplier_name || 'Unknown',
            category_id: selectedProduct.category_id,
            skus: [...this.items[index].skus],
            physical_count: this.items[index].physical_count || 0,
            on_hand: this.items[index].on_hand || 0,
            sold: 0,
            price_per_unit: selectedProduct.price_per_unit,
            buying_price: this.items[index].buying_price || '',
            description: selectedProduct.description || '',
            quantity: this.items[index].quantity || 1,
            transaction_type: "in",
          };

          this.selectedItems.push(newItem);
          console.log("Updated selectedItems:", this.selectedItems);
        }
      } else {
        console.error("No product found for the selected ID:", item.id);
      }
    },

    selectProduct(product) {
      const selectedProductIndex = this.selectedProductIndex;
      if (selectedProductIndex === null || selectedProductIndex === undefined) {
        console.error("Selected Product Index is not set.");
        return;
      }

      const selectedRow = this.items[selectedProductIndex];
      if (!selectedRow) {
        console.error("Selected row is undefined. Ensure items array is populated.");
        return;
      }

      selectedRow.id = product.id;
      selectedRow.stock_id = product.id;
      selectedRow.item_name = product.item_name;
      selectedRow.price_per_unit = product.price_per_unit || '0';
      selectedRow.category_id = product.category_id || null;
      selectedRow.physical_count = product.physical_count || 0;
      selectedRow.on_hand = product.on_hand || 0;
      selectedRow.transaction_type = "in";

      console.log("Selected Product:", selectedRow);
      this.selectedItems.push(selectedRow);
      console.log("Updated selectedItems:", this.selectedItems);
      this.closeModal();
    },

    fetchItems() {
      return axios
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
        .catch((error) => {
          console.error('Error fetching stocks:', error);
        });
    },

    fetchSuppliers() {
      axios
        .get('http://localhost:8001/api/suppliers')
        .then((res) => {
          console.log("Fetched Suppliers:", res.data.suppliers);
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

        console.log("Selected Supplier in selectItem:", supplier);

        const newItem = {
          stock_id: stock.id,
          item_name: stock.item_name,
          unit_of_measure: stock.unit_of_measure,
          supplier_id: supplier?.id || null,
          supplier_name: supplier?.supplier_name || 'Unknown',
          category_id: stock.category_id,
          skus: [],
          physical_count: stock.physical_count,
          on_hand: stock.on_hand,
          sold: stock.sold,
          price_per_unit: stock.price_per_unit,
          buying_price: stock.buying_price,
          description: stock.description || '',
        };

        console.log("New Item with Supplier Name:", newItem);

        this.selectedItems.push(newItem);
        console.log("Updated selectedItems:", this.selectedItems);
        this.addSku(this.selectedItems.length - 1);
      }
    },

    removeSelectedItem(index) {
      this.selectedItems.splice(index, 1);
    },

    clearAllSelectedItems() {
      this.selectedItems = [];
    },

    removeItem(index) {
      if (index >= 0 && index < this.items.length) {
        this.items.splice(index, 1);
        console.log(`Removed item at index ${index}. Updated items:`, this.items);
      } else {
        console.error(`Invalid index ${index}. Unable to remove item.`);
      }
    },

    updateSkusForQuantity(index) {
      const item = this.items[index];
      if (!item) return;

      let quantity = parseInt(item.quantity, 10);
      if (isNaN(quantity) || quantity <= 0) {
        quantity = 1;
        item.quantity = 1;
      }

      if (!Array.isArray(item.serial_numbers)) item.serial_numbers = [];
      if (!Array.isArray(item.skus)) item.skus = [];

      while (item.serial_numbers.length < quantity) item.serial_numbers.push('');
      while (item.skus.length < quantity) item.skus.push('');
      if (item.serial_numbers.length > quantity) item.serial_numbers.length = quantity;
      if (item.skus.length > quantity) item.skus.length = quantity;
    },

    generateAdditionalSkus(index, additionalCount) {
      const item = this.items[index];
      if (!item) {
        console.error(`Item not found at index ${index}`);
        return;
      }

      if (!item.supplier_name || item.supplier_name === 'Unknown') {
        const supplier = this.suppliers.find(s => s.id === this.selectedSupplier);
        item.supplier_name = supplier?.supplier_name || 'Unknown';
      }

      console.log("Supplier Name in generateAdditionalSkus:", item.supplier_name);

      for (let i = 0; i < additionalCount; i++) {
        const supplier = (item.supplier_name || 'Unknown').substring(0, 3).toUpperCase();
        const itemName = (item.item_name || 'Unknown').substring(0, 3).toUpperCase();
        const randomNumber = Math.floor(100 + Math.random() * 900);
        const unitOfMeasure = (item.unit_of_measure || 'Pc').toUpperCase();
        const sku = `${supplier}-${itemName}-${randomNumber}-${unitOfMeasure}`;

        item.skus.push(sku);
      }

      console.log(`Generated SKUs for item ${item.item_name}:`, item.skus);
    },

    addSku(index) {
      const stock = this.selectedItems[index];
      const quantity = stock.on_hand;

      if (quantity <= 0) return;

      this.generateAdditionalSkus(index, quantity);
    },

    saveStock() {
      console.log("Selected Items before saving:", this.selectedItems);

      if (this.selectedItems.length === 0) {
        console.error("No items selected to save.");
        alert("Please add items before saving.");
        return;
      }

      if (!this.selectedSupplier) {
        alert("Please select a supplier.");
        return;
      }

      this.selectedItems.forEach(selected => {
        const match = this.items.find(item => item.stock_id === selected.stock_id);
        if (match) {
          selected.quantity = match.quantity;
          selected.skus = [...match.skus];
        }
      });

      this.selectedItems.forEach((stock) => {
        const payload = {
          stock_id: stock.stock_id,
          item_name: stock.item_name,
          buying_price: parseFloat(stock.buying_price.replace(/[^\d.-]/g, '')) || 0,
          price_per_unit: parseFloat(stock.price_per_unit.replace(/[^\d.-]/g, '')) || 0,
          skus: Array.from(stock.skus),
          suppliers: [this.selectedSupplier],
          transaction_type: stock.transaction_type || 'in',
          unit_of_measure: stock.unit_of_measure || 'Pc',
          reason: stock.reason || (stock.transaction_type === 'in' ? 'stock-in' : 'stock-out'),
          date: stock.date || new Date().toISOString().split('T')[0],
          description: stock.description || '',
          category_id: stock.category_id,
          physical_count: parseInt(stock.physical_count || 0, 10) + parseInt(stock.quantity || 0, 10),
          on_hand: parseInt(stock.on_hand || 0, 10) + parseInt(stock.quantity || 0, 10),
          sold: parseInt(stock.sold || 0, 10),
          date_released: new Date().toISOString().split('T')[0],
          receiver: 'N/A',
        };

        console.log(`Payload for stock ID ${stock.stock_id}:`, payload);

        axios.put(`http://localhost:8001/api/stocks/${stock.stock_id}`, payload)
          .then((response) => {
            console.log(`Stock updated successfully for stock ID ${stock.stock_id}`, response.data);
            this.$router.push('/stocks');
          })
          .catch((error) => {
            if (error.response && error.response.data && error.response.data.errors) {
              console.error("Validation errors:", error.response.data.errors);

              const messages = Object.values(error.response.data.errors)
                .map(arr => arr.join('\n'))
                .join('\n');
              alert("Validation Error(s):\n" + messages);
            } else {
              // Log any other error
              console.error("Error saving stock:", error);
              alert("An unknown error occurred.");
            }
          });
      });

      this.selectedItems.forEach((item) => {
        item.quantity = 1;
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

      let value = parseInt(input, 10);
      if (isNaN(value) || value < 1) {
        value = 1;
      }

      this.items[index].quantity = value;

      console.log(`Updated quantity for item ${this.items[index].item_name}:`, this.items[index].quantity);

      this.updateSkusForQuantity(index);
    },

    validateQuantity(index) {
      const item = this.items[index];

      if (!item.quantity || parseInt(item.quantity, 10) <= 0) {
        item.quantity = 1;
      }

      console.log(`Validated quantity for item ${item.item_name}:`, item.quantity);
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
.serial-inputs-container {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-top: 4px;
}

.serial-input-row {
  display: flex;
  align-items: center;
  gap: 6px;
}

.serial-label {
  font-size: 11px;
  color: #888;
  width: 22px;
  text-align: right;
}

.serial-input {
  width: 90px;
  font-size: 12px;
  padding: 2px 6px;
  border: 1px solid #ccc;
  border-radius: 3px;
}

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
  border-radius: 10px;
  width: 400px;
  max-width: 90%;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  background-color: #007bff;
  color: white;
}

.modal-title {
  font-size: 18px;
  font-weight: bold;
  margin: 0;
}

.close-button {
  font-size: 20px;
  color: white;
  cursor: pointer;
  border: none;
  background: none;
  margin-bottom: 15px;
}

.modal-body {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.search-container {
  display: flex;
  justify-content: center;
}

.product-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-height: 300px;
  overflow-y: auto;
}

.product-card {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.2s, box-shadow 0.2s;
}

.product-card:hover {
  background-color: #f8f9fa;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.product-image {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 5px;
}

.product-details {
  display: flex;
  flex-direction: column;
}

.product-title {
  font-size: 14px;
  font-weight: bold;
  margin: 0;
}

.product-id {
  font-size: 12px;
  color: #6c757d;
  margin: 0;
}

.modal-footer {
  padding: 4px 5px;
  display: flex;
  justify-content: flex-end;
  background-color: #f1f1f1;
}

.btn {
  padding: 8px 15px;
  font-size: 14px;
  border-radius: 5px;
  border: none;
  cursor: pointer;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background-color: #5a6268;
}

.cost-price-column {
  width: 15%;
}

.quantity-column {
  width: 10%;
}

.cost-price-input {
  max-width: 100px;
}

.quantity-input {
  max-width: 80px;
}

.form-select {
  width: 100%;
  padding-right: 20px;
  background-repeat: no-repeat;
  background-position: right 10px center;
  background-size: 7px;
  appearance: none;
}

.form-select:focus {
  outline: none;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

input[type="checkbox"] {
  width: 20px;
  height: 10px;
  transform: scale(1.3);
  cursor: pointer;
}

.quantity-input::-webkit-inner-spin-button,
.quantity-input::-webkit-outer-spin-button {
  opacity: 1;
}

.numbering {
  font-size: 12px;
}

table {
  margin-top: 1rem;
  width: 100%;
}

.table th,
.table td {
  vertical-align: middle;
  padding: 0.75rem;
}

.table th {
  background-color: #f8f9fa;
  font-weight: bold;
  text-transform: uppercase;
  font-size: 14px;
}

.table-striped tbody tr:nth-of-type(odd) {
  background-color: #f9f9f9;
}

.table-hover tbody tr:hover {
  background-color: #f1f1f1;
}

.table-primary {
  background-color: #007bff;
  color: white;
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
