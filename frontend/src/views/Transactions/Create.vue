<template>
  <div class="container mt-4">
    <div class="card mb-5">
      <div class="card-header">
        <h4>New Transaction</h4>
      </div>
      <div class="card-body">
        <ul class="alert alert-warning" v-if="Object.keys(errorList).length > 0">
          <li class="mb-0 ms-3" v-for="(error, index) in errorList" :key="index">
            {{ error[0] }}
          </li>
        </ul>

        <div class="mb-3">
          <label for="transaction_type">Transaction Type</label>
          <select id="transaction_type" v-model="model.transaction_type" class="form-select">
            <option value="" disabled selected>Select Type</option>
            <option value="inbound">Inbound</option>
            <option value="outbound">Outbound</option>
          </select>
        </div>

        <div class="mb-3">
          <label for="transaction_date">Date</label>
          <input type="date" id="transaction_date" v-model="model.transaction_date" class="form-control">
        </div>

        <div v-for="(item, index) in model.items" :key="index" class="mb-3">
          <label for="item_id">Item</label>
          <select v-model="item.item_id" class="form-select mb-3">
            <option value="" disabled selected>Select Item</option>
            <option v-for="itemOption in items" :key="itemOption.id" :value="itemOption.id">
              {{ itemOption.name }} (ID: {{ itemOption.id }})
            </option>
          </select>

          <label for="quantity">Quantity</label>
          <input type="text" v-model="item.quantity" class="form-control mb-3">

          <button 
            v-if="index > 0" 
            type="button" 
            @click="removeItem(index)" 
            class="btn btn-danger">Remove</button>
        </div>

        <button type="button" @click="addItem" class="btn btn-secondary mb-3">Add Another Item</button>

        <div class="d-flex justify-content-between">
          <RouterLink to="/transactions" class="btn btn-primary">Back</RouterLink>
          <button type="button" @click="saveTransaction" class="btn btn-primary">Add</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'TransactionCreate',
  data() {
    return {
      errorList: {},
      model: {
        transaction_type: '',
        transaction_date: '',
        items: [{ item_id: '', quantity: '' }]
      },
      items: []
    };
  },
  mounted() {
    this.fetchItems();
  },
  methods: {
    addItem() {
      this.model.items.push({ item_id: '', quantity: '' });
    },
    removeItem(index) {
      if (index > 0) {
        this.model.items.splice(index, 1);
      }
    },
    saveTransaction() {
      const transactionData = {
        transaction_type: this.model.transaction_type,
        transaction_date: this.model.transaction_date,
        items: this.model.items // Ensure items are included here
      };

      axios.post('http://localhost:8001/api/inventory_transactions', transactionData)
        .then(res => {
          alert(res.data.message);
          this.model = {
            transaction_type: '',
            transaction_date: '',
            items: [{ item_id: '', quantity: '' }]
          };
          this.errorList = {};
          this.$router.push('/transactions');
        })
        .catch(error => {
          if (error.response) {
            console.error('Error response:', error.response.data);
            if (error.response.status === 422) {
              this.errorList = error.response.data.errors; // Populate errors for display
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
