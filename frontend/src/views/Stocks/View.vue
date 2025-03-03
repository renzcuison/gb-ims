<template>
  <div class="container mt-4">
    <div class="card mb-5">
      <div class="card-header">
        <h4>
          Stocks
          <RouterLink to="/stocks/uncreate" class="btn btn-primary float-end">Out</RouterLink>
          <RouterLink to="/stocks/in" class="btn btn-primary float-end me-2">In</RouterLink>
          <RouterLink to="/stocks/create" class="btn btn-primary float-end me-2">Add Item</RouterLink>
        </h4>
      </div>

      <div class="card-body">
        <div class="d-flex">
          <div class="flex-grow-1 me-3">
            <input type="text" v-model="searchQuery" @input="filterStocks" class="form-control"
              placeholder="Enter Search Query" />
          </div>

          <div class="d-flex align-items-center">
            <label class="form-check-label me-1 mb-0 d-inline">Sort</label>
            <label class="form-check-label me-2 mb-0 d-inline">by:</label>
            <select v-model="sortOption" class="form-select" @change="applySort">
              <option value="alpha-asc">Alphabetical: A-Z</option>
              <option value="alpha-desc">Alphabetical: Z-A</option>
              <option value="date-desc">Date: Recent to Old</option>
              <option value="date-asc">Date: Old to Recent</option>
              <option value="quantity-asc">Quantity: Low to High</option>
              <option value="quantity-desc">Quantity: High to Low</option>
              <option value="price-asc">Price: Low to High</option>
              <option value="price-desc">Price: High to Low</option>
              <option value="low-stock">Low on Stock</option>
              <option value="all">View All</option>
            </select>
          </div>
        </div>

        <div class="mb-3">
          <div>
            <label class="form-check-label search-by-label">Search By:</label>
            <label class="form-check-label search-by-label">
              <input type="radio" v-model="searchBy" value="id" class="form-check-input" @change="filterStocks" />
              ID
            </label>
            <label class="form-check-label search-by-label">
              <input type="radio" v-model="searchBy" value="name" class="form-check-input" @change="filterStocks" />
              Item Name
            </label>
          </div>
        </div>

        <table class="table table-bordered">
          <thead>
            <tr>
              <th>ID</th>
              <th>Item</th>
              <th>Unit</th>
              <th>Quantity</th>
              <th>Description</th>
              <th>Price /unit</th>
              <th>•</th>
            </tr>
          </thead>
          <tbody v-if="filteredStocks.length > 0">
            <template v-for="(stock, index) in filteredStocks" :key="index">
              <tr @click="selectRow(stock.id, $event)" :class="{ 'selected-row': stock.id === selectedStockId }">
                <td class="text-muted">{{ stock.id }}</td>
                <td>
                  <div class="d-flex align-items-center">
                    <strong>{{ stock.item_name }}</strong>
                    <button class="btn btn-link btn-sm ms-2" @click.stop="toggleSkuVisibility(stock)">
                      {{ stock.showSkus ? "▲" : "▼" }}
                    </button>
                  </div>
                  <div class="text-muted small">
                    {{ getSupplierName(stock.supplier_id) }}
                  </div>
                  <div class="text-muted small">
                    {{ stock.category ? stock.category.category_name : "N/A" }}
                  </div>
                  <div v-if="stock.showSkus" class="mt-2">
                    <div v-if="stock.skus.length === 0" class="text-muted small">
                      No stock.
                    </div>
                    <div v-else>
                      <div class="text-muted small">
                        <strong>Stock(s): {{ stock.skus.length }}</strong>
                      </div>
                      <ul class="list-unstyled small">
                        <li v-for="(sku, skuIndex) in stock.skus" :key="sku.id" class="d-flex justify-content-between">
                          <span class="sku-text">{{ skuIndex + 1 }}. {{ sku.sku }}</span>
                        </li>
                      </ul>
                    </div>
                  </div>
                  <div class="text-muted small text-end mt-2">
                    <span class="logs-text" @click.stop="fetchStockLogs(stock.id)">→ log</span>
                  </div>
                </td>
                <td>{{ stock.unit_of_measure }}</td>
                <td>
                  <div>
                    <div>
                      On Hand:
                      <span>
                        {{ stock.on_hand }}
                        <RouterLink v-if="stock.on_hand < 5"
                          :to="{ name: 'stocksLowStock', params: { stockId: stock.id } }" class="low-stock-link ms-2">
                          ⚠
                          {{ stock.on_hand === 0 ? "out of stock" : "low stock" }}
                        </RouterLink>
                      </span>
                    </div>
                    <div class="qty-container">
                      <div class="qty-text">Physical Count: {{ stock.physical_count }}</div>
                      <div class="qty-text">Sold: {{ stock.sold }}</div>
                    </div>
                  </div>
                </td>
                <td>
                  <textarea v-model="stock.description" class="form-control" @change="saveDescription(stock)"
                    placeholder="" rows="3"></textarea>
                </td>
                <td>{{ formatPrice(stock.price_per_unit) }}</td>
                <td>
                  <button v-if="stock.id === selectedStockId" type="button" @click="editStock(stock.id)"
                    class="btn btn-success me-2">
                    Edit
                  </button>
                  <button v-if="stock.id === selectedStockId" type="button" @click="deleteStock(stock.id)"
                    class="btn btn-danger">
                    Delete
                  </button>
                </td>
              </tr>
              <transition name="fade-slide">
                <tr v-if="expandedStockIds.includes(stock.id)">
                  <td colspan="7">
                    <div class="transaction-log-container">
                      <p class="transaction-log-title">Transaction Log:</p>

                      <p v-if="!stockLogs[stock.id] || stockLogs[stock.id].length === 0" class="no-logs">
                        No stock logs available.
                      </p>

                      <table v-if="stockLogs[stock.id] && stockLogs[stock.id].length > 0"
                        class="table table-sm transaction-table">
                        <thead>
                          <tr>
                            <th>Date</th>
                            <th>Item ID</th>
                            <th>SKU</th>
                            <th>Quantity</th>
                            <th>Reason</th>
                            <th>Description</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="log in stockLogs[stock.id]" :key="log.id">
                            <td>{{ formatDate(log.created_at) }}</td>
                            <td>{{ log.stock_id }}</td>
                            <td>{{ log.sku || 'N/A' }}</td>
                            <td>{{ log.qty }}</td>
                            <td>{{ log.reason }}</td>
                            <td>{{ log.description || '' }}</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </td>
                </tr>
              </transition>
            </template>
          </tbody>

          <tbody v-else>
            <tr>
              <td colspan="6">.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'StocksView',
  data() {
    return {
      username: '',
      stock: [],
      stocks: [],
      suppliers: [],
      searchQuery: '',
      searchBy: 'id',
      filteredStocks: [],
      sortOption: 'all',
      isEditing: false,
      editingRemark: '',
      editingStockId: null,
      selectedStockId: null,
      expandedStockIds: [],
      editableDescription: '',
      stockLogs: {},
      transactionPopupVisible: false
    }
  },
  mounted() {
    this.getStocks()
    this.getSuppliers()
  },
  methods: {
    fetchStockLogs(stockId) {

      console.log("Fetching logs for stock ID:", stockId);

      axios.get(`http://localhost:8001/api/stock-log`)
        .then(response => {

          console.log("Stock Log Response:", response.data);

          const filteredLogs = response.data.stock_logs.filter(log => log.stock_id === stockId);

          this.stockLogs[stockId] = filteredLogs;

          const index = this.expandedStockIds.indexOf(stockId);
          if (index === -1) {
            this.expandedStockIds.push(stockId);
          } else {
            this.expandedStockIds.splice(index, 1);
          }

          console.log(`Expanded stock logs:`, this.expandedStockIds);
        })
        .catch(error => {
          console.error(`Failed to fetch stock logs:`, error);
        });
    },
    editDescription(stockId, currentDescription) {
      this.editingStockId = stockId;
      this.editableDescription = currentDescription;
    },

    saveDescription(stock) {
      console.log('Saving description for stock:', stock);
      const { id, item_name, description, category_id, supplier_id, unit_of_measure, physical_count, on_hand, sold, price_per_unit } = stock;
      const payload = {
        id,
        item_name,
        description,
        category_id,
        supplier_id,
        unit_of_measure,
        physical_count,
        on_hand,
        sold,
        price_per_unit
      };

      console.log('Payload:', payload);

      axios
        .put(`http://localhost:8001/api/stocks/${stock.id}`, payload)
        .then((response) => {
          console.log('Response from server:', response);
        })
        .catch((error) => {
          console.error('Error updating stock:', error.response || error.message);
        });
    },

    cancelEdit() {
      this.editingStockId = null;
      this.editableDescription = '';
    },

    toggleTransactionDetails(stock) {
      if (!stock.showTransactions) {
        this.fetchTransactions(stock.id);
      }
      stock.showTransactions = !stock.showTransactions;
    },

    fetchTransactions(stockId) {
      const stock = this.stocks.find(s => s.id === stockId);
      if (stock && !stock.transactions.length) {
        axios.get(`/api/transactions?stock_id=${stockId}`)
          .then(response => {
            stock.transactions = response.data.transactions || [];
          })
          .catch(error => {
            console.error(`Failed to fetch transactions for stock ID ${stockId}:`, error);
          });
      }
    },

    showTransactionPopup(transaction) {
      this.currentTransaction = transaction;
      this.transactionPopupVisible = true;
    },

    closeTransactionPopup() {
      this.transactionPopupVisible = false;
      this.currentTransaction = null;
    },

    formatDate(date) {
      return new Date(date).toLocaleString();
    },

    selectRow(stockId, event) {
      if (event.target.tagName === 'TEXTAREA') {
        event.stopPropagation();
        return;
      }
      this.selectedStockId = this.selectedStockId === stockId ? null : stockId;
    },

    deleteStock(stockId) {
      const confirmDelete = confirm('Are you sure you want to delete this stock item?');
      if (confirmDelete) {
        axios.delete(`http://localhost:8001/api/stocks/${stockId}`)
          .then(res => {
            this.getStocks();
            alert('Stock item deleted successfully.');
          })
          .catch(error => {
            console.error('Error deleting stock:', error);
            alert('Failed to delete the stock item.');
          });
      }
    },

    fetchSkus(stockId) {
      console.log(`Fetching SKUs for stock ID: ${stockId}`);

      const stock = this.stocks.find(item => item.id === stockId);

      if (stock) {
        console.log(`Fetching SKUs for ${stock.item_name}`);

        axios.get(`http://localhost:8001/api/stocks/${stockId}`)
          .then(response => {
            stock.skus = response.data.stock.skus;
            console.log("Fetched SKUs:", [...stock.skus]);
          })
          .catch(error => {
            console.error(`Error fetching SKUs for stock ID ${stockId}:`, error);
          });
      }
    },

    getSupplierName(supplierId) {
      const supplier = this.suppliers.find(supplier => supplier.id === supplierId)
      return supplier ? supplier.supplier_name : 'Unknown'
    },

    getStocks() {
      axios.get('http://localhost:8001/api/stocks')
        .then(res => {
          this.stocks = res.data.stocks.map(stock => ({
            ...stock,
            showTransactions: false,
            transactions: []
          }));
          this.filteredStocks = [...this.stocks];
          this.applySort();
        })
        .catch(error => {
          console.error('Error fetching stocks:', error);
        });
    },

    getSuppliers() {
      axios.get('http://localhost:8001/api/suppliers')
        .then(res => {
          this.suppliers = res.data.suppliers
        })
        .catch(error => {
          console.error('Error fetching suppliers:', error)
        })
    },

    filterStocks() {
      const query = this.searchQuery.toLowerCase().trim()

      if (!query) {
        this.filteredStocks = [...this.stocks]
      } else {
        if (this.searchBy === 'id') {
          this.filteredStocks = this.stocks.filter(stock => stock.id.toString().includes(query))
        } else if (this.searchBy === 'name') {
          this.filteredStocks = this.stocks.filter(stock => stock.item_name.toLowerCase().includes(query))
        }
      }

      this.applySort();
    },

    toggleSkuVisibility(stock) {
      stock.showSkus = !stock.showSkus;
      if (stock.showSkus && !stock.skus.length) {
        this.fetchSkus(stock.id);
      }
    },

    applySort() {
      switch (this.sortOption) {
        case 'alpha-asc':
          this.filteredStocks.sort((a, b) => a.item_name.localeCompare(b.item_name));
          break;
        case 'alpha-desc':
          this.filteredStocks.sort((a, b) => b.item_name.localeCompare(a.item_name));
          break;
        case 'date-desc':
          this.filteredStocks.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
          break;
        case 'date-asc':
          this.filteredStocks.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
          break;
        case 'quantity-asc':
          this.filteredStocks.sort((a, b) => a.on_hand - b.on_hand);
          break;
        case 'quantity-desc':
          this.filteredStocks.sort((a, b) => b.on_hand - a.on_hand);
          break;
        case 'price-asc':
          this.filteredStocks.sort((a, b) => a.price_per_unit - b.price_per_unit);
          break;
        case 'price-desc':
          this.filteredStocks.sort((a, b) => b.price_per_unit - a.price_per_unit);
          break;
        case 'low-stock':
          this.filteredStocks = this.filteredStocks.filter(stock => stock.on_hand < 5);
          break;
        default:
          break;
      }
    },

    formatPrice(price) {
      return `₱${price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")}`;
    },
  }
}
</script>

<style scoped>
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