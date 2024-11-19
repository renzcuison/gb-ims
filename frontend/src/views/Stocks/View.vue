<template>
  <div class="container mt-4">
    <div class="card mb-5">
      <div class="card-header">
        <h4>
          Stocks
          <RouterLink to="/stocks/uncreate" class="btn btn-primary float-end">Out</RouterLink>
          <RouterLink to="/stocks/in" class="btn btn-primary float-end me-2">In</RouterLink>
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
            <select v-model="sortOption" class="form-select" @change="applySearchAndSort">
              <option value="alpha-asc">Alphabetical: A-Z</option>
              <option value="alpha-desc">Alphabetical: Z-A</option>
              <option value="date-desc">Date: Recent to Old</option>
              <option value="date-asc">Date: Old to Recent</option>
              <option value="quantity-asc">Quantity: Low to High</option>
              <option value="quantity-desc">Quantity: High to Low</option>
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
              <th>Item Name</th>
              <th>Quantity</th>
              <th>Unit</th>
              <th>Supplier</th>
              <th>Remark</th>
              <th>Time Added</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody v-if="filteredStocks.length > 0">
            <tr v-for="(stock, index) in filteredStocks" :key="index">
              <td>{{ stock.id }}</td>
              <td>{{ stock.item_name }}</td>
              <td>
                <div style="display: flex; align-items: center;">
                  <span>{{ stock.quantity }}</span>
                  <RouterLink v-if="stock.quantity < 5" :to="{ name: 'stocksLowStock', params: { stockId: stock.id } }"
                    class="low-stock-link low-stock-alert">⚠ low stock</RouterLink>
                </div>
              </td>
              <td>{{ stock.unit_of_measure }}</td>
              <td>{{ getSupplierName(stock.supplier_id) }}</td>
              <td>{{ stock.description }}</td>
              <td>{{ new Date(stock.created_at).toLocaleString() }}</td>
              <td>
                <button type="button" @click="deleteStock(stock.id)" class="btn btn-danger">Delete</button>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="8">↺</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'StocksView',
  data() {
    return {
      stocks: [],
      suppliers: [],
      searchQuery: '',
      searchBy: 'id',
      filteredStocks: [],
      sortOption: 'all',
    }
  },
  mounted() {
    this.getStocks()
    this.getSuppliers()
  },
  methods: {
    getSupplierName(supplierId) {
      const supplier = this.suppliers.find(supplier => supplier.id === supplierId)
      return supplier ? supplier.supplier_name : 'Unknown'
    },

    getStocks() {
      axios.get('http://localhost:8001/api/stocks')
        .then(res => {
          this.stocks = res.data.stocks
          this.filteredStocks = [...this.stocks]
          this.applySort()
        })
        .catch(error => {
          console.error('Error fetching stocks:', error)
        })
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

      this.applySort()
    },

    applySort() {
      let sortedStocks = [...this.filteredStocks]

      if (this.sortOption === 'low-stock') {
        sortedStocks = sortedStocks.filter(stock => stock.quantity < 5)
      } else if (this.sortOption === 'date-desc') {
        sortedStocks.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
      } else if (this.sortOption === 'date-asc') {
        sortedStocks.sort((a, b) => new Date(a.created_at) - new Date(b.created_at))
      } else if (this.sortOption === 'alpha-asc') {
        sortedStocks.sort((a, b) => a.item_name.localeCompare(b.item_name))
      } else if (this.sortOption === 'alpha-desc') {
        sortedStocks.sort((a, b) => b.item_name.localeCompare(a.item_name))
      } else if (this.sortOption === 'quantity-asc') {
        sortedStocks.sort((a, b) => a.quantity - b.quantity)
      } else if (this.sortOption === 'quantity-desc') {
        sortedStocks.sort((a, b) => b.quantity - a.quantity)
      }

      this.filteredStocks = sortedStocks
    },

    applySearchAndSort() {
      this.filterStocks()
    },

    resetSearch() {
      this.searchQuery = ''
      this.filteredStocks = [...this.stocks]
      this.applySort()
    },

    deleteStock(id) {
      if (confirm('Confirm action: DELETE')) {
        axios.delete(`http://localhost:8001/api/stocks/${id}`)
          .then(res => {
            alert(res.data.message)
            this.getStocks()
          })
          .catch(error => {
            console.error('Error deleting stock:', error)
          })
      }
    },
  }
}
</script>

<style scoped>
.low-stock-link {
  text-decoration: none;
  cursor: pointer;
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
</style>
