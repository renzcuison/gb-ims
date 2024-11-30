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
              <th>Actions</th>
            </tr>
          </thead>
          <tbody v-if="filteredStocks.length > 0">
            <tr v-for="(stock, index) in filteredStocks" :key="index">
              <td>{{ stock.item_id }}</td>
              <td>{{ stock.item_name }}</td>
              <td>
                <div style="display: flex; align-items: center;">
                  <span :style="stock.quantity === 0 ? { color: 'red', fontWeight: 'bold' } : {}">
                    {{ stock.quantity }}
                  </span>
                  <RouterLink v-if="stock.quantity < 5 && stock.quantity > 0"
                    :to="{ name: 'stocksLowStock', params: { stockId: stock.id } }"
                    class="low-stock-link low-stock-alert ms-2">
                    ⚠ low stock
                  </RouterLink>
                  <RouterLink v-if="stock.quantity === 0"
                    :to="{ name: 'stocksLowStock', params: { stockId: stock.id } }"
                    class="low-stock-link no-stock-alert ms-2">
                    ⚠ out of stock
                  </RouterLink>
                </div>
              </td>
              <td>{{ stock.unit_of_measure }}</td>
              <td>{{ getSupplierName(stock.supplier_id) }}</td>
              <td>
                <span class="remark-text">
                  <div class="remark-container position-relative">
                    <div v-if="isEditing && editingStockId === stock.id">
                      <textarea v-model="editingRemark" class="form-control remark-textarea"></textarea>
                      <button class="btn btn-success float-end mt-2 ms-2" @click="saveRemark(stock.id)">Save</button>
                      <button class="btn btn-secondary float-end mt-2" @click="cancelEdit">Back</button>
                    </div>
                    <div v-else>
                      <div v-for="(line, lineIndex) in stock.formattedDescription"
                        :key="`${stock.id}-line-${lineIndex}`" class="remark-line d-flex align-items-center">

                        <input type="checkbox" v-if="line.text.includes('IN (Pending)')" v-model="line.isChecked"
                          class="me-1" :id="`checkbox-${lineIndex}`" />

                        <span class="description flex-grow-1" v-html="line.text"></span>
                      </div>

                      <!-- Confirm Button appears when checkbox is checked -->
                      <button
                        v-if="stock.formattedDescription.some(line => line.isChecked && line.text.includes('IN (Pending)'))"
                        class="btn btn-primary btn-sm position-absolute top-0 end-0 me-5" @click="confirmReturn(stock)">
                        Accept
                      </button>

                      <!-- Edit Button always visible at top-right -->
                      <button class="btn btn-success btn-sm position-absolute top-0 end-0"
                        @click="editRemark(stock.id, stock.description)">
                        Edit
                      </button>
                    </div>
                  </div>
                </span>
              </td>
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
      isEditing: false,
      editingRemark: '',
      editingStockId: null,
    }
  },
  mounted() {
    this.getStocks()
    this.getSuppliers()
  },
  methods: {
    getPendingReturns(description) {
      return description
        .split("\n")
        .filter(line => line.includes("IN (Pending)"));
    },

    confirmReturn(stock, returnLine) {
      const returnMatch = returnLine.text.match(/IN \(Pending\): (\d{2}\/\d{2}\/\d{4}, \d{1,2}:\d{2}:\d{2} [AP]M) - (.*)/);
      const returnTimestamp = returnMatch ? returnMatch[1] : "";
      const returnDetails = returnMatch ? returnMatch[2] : "";

      const quantityMatch = returnDetails.match(/\+(\d+)/);
      const quantityToAdd = quantityMatch ? parseInt(quantityMatch[1]) : 0;

      const updatedDescription = stock.descriptionWithTimestamp
        .split("\n")
        .filter(line => line !== returnLine.text)
        .join("\n")
        .concat(
          `\nIN ${returnTimestamp} - ${returnDetails}`
        );

      const updatedStock = {
        ...stock,
        quantity: stock.quantity + quantityToAdd,
        description: updatedDescription,
        descriptionWithTimestamp: updatedDescription,
      };

      axios
        .put(`http://localhost:8001/api/stocks/${stock.id}`, updatedStock)
        .then(res => {
          alert(res.data.message);
          this.getStocks();
        })
        .catch(error => {
          console.error("Error confirming return:", error);
          alert("Failed to confirm the return. Check the console for details.");
        });
    },

    cancelEdit() {
      this.isEditing = false;
      this.editingStockId = null;
      this.editingRemark = '';
    },

    getSupplierName(supplierId) {
      const supplier = this.suppliers.find(supplier => supplier.id === supplierId)
      return supplier ? supplier.supplier_name : 'Unknown'
    },

    getStocks() {
      axios.get('http://localhost:8001/api/stocks')
        .then(res => {
          this.stocks = res.data.stocks.map(stock => {
            const description = stock.description || 'No remark available';

            const descriptionWithTimestamp = description.startsWith('OUT') || description.startsWith('IN')
              ? description
              : `OUT ${new Date(stock.created_at).toLocaleString()} - ${description}`;

            stock.formattedDescription = descriptionWithTimestamp.split("\n").map(line => ({
              text: line,
              isChecked: false,
            }));

            stock.descriptionWithTimestamp = descriptionWithTimestamp;

            return stock;
          });
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
          this.filteredStocks = this.stocks.filter(stock => stock.item_id.toString().includes(query))
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

    formatDescription(description) {
      return description ? description.replace(/\n/g, '<br />') : 'No description';
    },

    editRemark(stockId, currentDescription) {
      this.isEditing = true;
      this.editingStockId = stockId;
      this.editingRemark = currentDescription;
    },

    saveRemark(stockId) {
      const updatedStock = this.stocks.find(stock => stock.id === stockId);
      updatedStock.description = this.editingRemark;

      axios.put(`http://localhost:8001/api/stocks/${stockId}`, updatedStock)
        .then((res) => {
          alert(res.data.message);
          this.isEditing = false;
          this.editingStockId = null;
          this.getStocks();
        })
        .catch((error) => {
          console.error('Error saving remark:', error);
        });
    }
  }
};
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

.remark-text {
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

.remark-textarea {
  width: 100%;
  height: 150px;
}
</style>
