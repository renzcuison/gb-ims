<template>
    <div class="container mt-4">
      <div class="card">
        <div class="card-header bg-dark text-white">
          <h4>Edit Order</h4>
        </div>
        <div class="card-body">
          <form @submit.prevent="updateOrder">
            <div class="mb-3">
              <label for="customer_name" class="form-label">Customer Name</label>
              <input type="text" class="form-control" id="customer_name" v-model="order.customer_name" required>
            </div>
            <div class="mb-3">
              <label for="status" class="form-label">Order Status</label>
              <select class="form-control" id="status" v-model="order.status" required>
                <option value="Pending">Pending</option>
                <option value="Shipped">Shipped</option>
                <option value="Delivered">Delivered</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="item_id" class="form-label">Item</label>
              <select class="form-control" id="item_id" v-model="order.item_id" required>
                <option v-for="item in items" :value="item.item_id" :key="item.item_id">{{ item.name }}</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="quantity" class="form-label">Quantity</label>
              <input type="number" class="form-control" id="quantity" v-model="order.quantity" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Order</button>
          </form>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    data() {
      return {
        order: {
          customer_name: '',
          item_id: null,
          quantity: 1,
        },
        items: [],
      };
    },
    created() {
      this.fetchOrder();
      this.fetchItems();
    },
    methods: {
      fetchOrder() {
        const orderId = this.$route.params.id;
        axios.get(`http://localhost:8001/api/orders/${orderId}`)
          .then(response => {
            this.order = response.data;
          })
          .catch(error => {
            console.error("Error fetching order:", error);
          });
      },
      fetchItems() {
        axios.get('http://localhost:8001/api/items')
          .then(response => {
            this.items = response.data;
          })
          .catch(error => {
            console.error("Error fetching items:", error);
          });
      },
      updateOrder() {
        const orderId = this.$route.params.id;
        axios.put(`http://localhost:8001/api/orders/${orderId}`, this.order)
          .then(response => {
            this.$router.push('/orders');
          })
          .catch(error => {
            console.error("Error updating order:", error);
          });
      },
    },
  };
  </script>
  
  <style scoped>
  .container {
    max-width: 600px;
  }
  
  .card {
    border-radius: 10px;
  }
  
  .card-header {
    padding: 16px 24px;
    font-size: 20px;
    font-weight: 600;
  }
  
  .card-body {
    padding: 1.5rem;
  }
  
  .form-control {
    font-size: 14px;
  }
  
  .btn {
    font-weight: 600;
  }
  </style>
  