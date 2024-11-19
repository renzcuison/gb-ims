<template>
  <div class="container mt-4">
    <div class="card mb-5">
      <div class="card-header">
        <h4>
          Stocks
          <RouterLink to="/stocks/out" class="btn btn-primary float-end">Out</RouterLink>
          <RouterLink to="/stocks/create" class="btn btn-primary float-end">In</RouterLink>
        </h4>
      </div>
      <div class="card-body">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>ID</th>
              <th>Item Name</th>
              <th>Quantity</th>
              <th>Unit</th>
              <th>Supplier</th>
              <th>Description</th>
              <th>Time</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody v-if="stocks.length > 0">
            <tr v-for="(stock, index) in stocks" :key="index">
              <td>{{ stock.id }}</td>
              <td>{{ stock.item_name }}</td>
              <td>{{ stock.quantity }}</td>
              <td>{{ stock.unit_of_measure }}</td>
              <td>{{ getSupplierName(stock.supplier_id) }}</td>
              <td>{{ stock.description }}</td>
              <td>{{ formatTimestamp(stock.time) }}</td>
              <td>
                <RouterLink :to="{ path: '/stocks/' + stock.id + '/edit' }" class="btn btn-success">Edit</RouterLink>
                <button type="button" @click="deleteStock(stock.id)" class="btn btn-danger">Delete</button>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="8">↺ No stocks available</td>
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
      suppliers: [], // Add suppliers array
    }
  },
  mounted() {
    this.getStocks()
    this.getSuppliers() // Fetch suppliers on mount
  },
  methods: {
    getStocks() {
      const url = 'http://localhost:8001/api/stocks'; // Make sure this URL is correct
      console.log("Fetching stocks from:", url);
      axios.get(url)
        .then(res => {
          this.stocks = res.data.stocks; // Ensure that 'stocks' is the correct key in the response
        })
        .catch(error => {
          console.error('Error fetching stocks:', error);
        });
    },
    getSuppliers() {
      const url = 'http://localhost:8001/api/suppliers'; // Adjust the URL as needed
      axios.get(url)
        .then(res => {
          this.suppliers = res.data.suppliers; // Store suppliers
        })
        .catch(error => {
          console.error('Error fetching suppliers:', error);
        });
    },
    getSupplierName(supplierId) {
      const supplier = this.suppliers.find(s => s.id === supplierId);
      return supplier ? supplier.supplier_name : 'Unknown Supplier';
    },
    formatTimestamp(timestamp) {
      const date = new Date(timestamp);
      return isNaN(date) ? 'Invalid Date' : date.toLocaleString(); // Format the timestamp
    },
    deleteStock(id) {
      if (confirm('Confirm action: DELETE')) {
        axios.delete(`http://localhost:8001/api/stocks/${id}`)
          .then(res => {
            alert(res.data.message);
            this.getStocks();
          })
          .catch(error => {
            console.error('Error deleting stock:', error);
          });
      }
    }
  }
}
</script>
