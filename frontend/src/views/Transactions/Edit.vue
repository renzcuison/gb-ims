<template>
  <div class="container mt-4">
    <div class="card mb-5">
      <div class="card-header">
        <h4>Edit Transaction</h4>
      </div>
      <div class="card-body">
        <ul class="alert alert-warning" v-if="Object.keys(errorList).length > 0">
          <li class="mb-0 ms-3" v-for="(error, index) in errorList" :key="index">
            {{ error[0] }}
          </li>
        </ul>
        <div class="mb-3">
          <label for="item_id">Item</label>
          <select id="item_id" v-model="model.transaction.item_id" class="form-select">
            <option value="" disabled>Select Item</option>
            <option v-for="item in items" :key="item.id" :value="item.id">
              {{ item.name }} (ID: {{ item.id }})
            </option>
          </select>
        </div>
        <div class="mb-3">
          <label for="transaction_type">Transaction Type</label>
          <select id="transaction_type" v-model="model.transaction.transaction_type" class="form-select">
            <option value="" disabled>Select Type</option>
            <option value="inbound">Inbound</option>
            <option value="outbound">Outbound</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="quantity">Quantity</label>
          <input type="text" id="quantity" v-model="model.transaction.quantity" class="form-control">
        </div>
        <div class="mb-3">
          <label for="transaction_date">Date</label>
          <input type="date" id="transaction_date" v-model="model.transaction.transaction_date" class="form-control">
        </div>
        <RouterLink to="/transactions" class="btn btn-primary float-end">Back</RouterLink>
        <button type="button" @click="updateTransaction" class="btn btn-primary">Save</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'TransactionEdit',
  data() {
    return {
      model: {
        transaction: {
          id: '',
          item_id: '',
          transaction_type: '',
          quantity: '',
          transaction_date: '',
        }
      },
      items: [],
      errorList: {}
    };
  },
  mounted() {
    this.fetchItems();
    this.getTransaction();
  },
  methods: {
    getTransaction() {
      const id = this.$route.params.id;
      axios.get(`http://localhost:8001/api/inventory_transactions/${id}`)
        .then(res => {
          this.model.transaction = res.data.transaction;
        })
        .catch(error => {
          console.error('Error fetching transaction:', error);
        });
    },
    updateTransaction() {
      const id = this.$route.params.id;
      axios.put(`http://localhost:8001/api/inventory_transactions/${id}`, this.model.transaction)
        .then(res => {
          alert(res.data.message);
          this.$router.push('/transactions');
        })
        .catch(error => {
          if (error.response) {
            if (error.response.status === 422) {
              this.errorList = error.response.data.errors;
            } else {
              console.error('Error:', error.response.data.message);
            }
          } else {
            console.error('Error:', error.message);
          }
        });
    },
    fetchItems() {
      axios.get('http://localhost:8001/api/items')
        .then(response => {
          this.items = response.data.items;
        })
        .catch(error => {
          console.error('Error fetching items:', error);
        });
    }
  }
};
</script>