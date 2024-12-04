<template>
  <div class="container">
    <div class="card mb-5">
      <div class="card-header">
        <h4>
          Transactions
          <RouterLink to="/transactions/create" class="btn btn-primary float-end">Add</RouterLink>
        </h4>
      </div>
      <div class="card-body">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>ID</th>
              <th>Items</th>
              <th>Transaction Type</th>
              <th>Quantity</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody v-if="transactions.length > 0">
            <tr v-for="transaction in transactions" :key="transaction.id">
              <td>{{ transaction.id }}</td>
              <td>
                <ul>
                  <li v-for="item in transaction.items" :key="item.id">
                    {{ item.item ? item.item.name + ' (ID: ' + item.item.id + ')' : 'N/A' }} - Quantity: {{ item.quantity }}
                  </li>
                </ul>
              </td>
              <td>{{ transaction.transaction_type }}</td>
              <td>{{ transaction.items.reduce((total, item) => total + item.quantity, 0) }}</td>
              <td>{{ new Date(transaction.transaction_date).toLocaleDateString() }}</td>
              <td>
                <RouterLink :to="{ path: '/transactions/' + transaction.id + '/edit' }" class="btn btn-success">Edit</RouterLink>
                <button type="button" @click="deleteTransaction(transaction.id)" class="btn btn-danger">Delete</button>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="6">↺</td>
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
  name: 'TransactionsView',
  data() {
    return {
      transactions: [],
    };
  },
  mounted() {
    this.getTransactions();
  },
  methods: {
    getTransactions() {
      axios.get('http://localhost:8001/api/inventory_transactions')
        .then(res => {
          this.transactions = res.data.transactions;
        })
        .catch(error => {
          console.error('Error fetching transactions:', error);
        });
    },
    deleteTransaction(id) {
      if (confirm('Confirm action: DELETE')) {
        axios.delete(`http://localhost:8001/api/inventory_transactions/${id}`)
          .then(res => {
            alert(res.data.message);
            this.getTransactions();
          })
          .catch(error => {
            console.error('Error deleting transaction:', error);
          });
      }
    },
  },
};
</script>
