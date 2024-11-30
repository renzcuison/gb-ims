<template>
    <div class="container mt-4">
      <div class="card mb-5">
        <div class="card-header bg-dark text-white d-flex align-items-center">
          <button class="btn btn-light btn-sm me-3" @click="goBack">Back</button>
          <h4>Order Details</h4>
        </div>
        <div class="card-body">
          <div>
            <h5>Shipping Information</h5>
            <ul>
              <li><strong>Name:</strong> {{ shipping.name }}</li>
              <li><strong>Address:</strong> {{ shipping.address }}</li>
              <li><strong>City:</strong> {{ shipping.city }}</li>
              <li><strong>Postal Code:</strong> {{ shipping.postalCode }}</li>
              <li><strong>Phone:</strong> {{ shipping.phone }}</li>
            </ul>
          </div>
  
          <div class="mt-4">
            <h5>Order Summary</h5>
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Item</th>
                  <th>Quantity</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in orders" :key="order.id">
                  <td>{{ order.item.name }}</td>
                  <td>{{ order.quantity }}</td>
                  <td>₱{{ calculateTotalPrice(order) }}</td>
                </tr>
              </tbody>
            </table>
            <div class="d-flex justify-content-between mt-3">
              <h6>Total Price:</h6>
              <h6>₱{{ totalPrice }}</h6>
            </div>
          </div>
  
          <div class="mt-4">
            <h5>Payment Method</h5>
            <p>{{ paymentMethod === 'gcash' ? 'GCash' : 'Cash on Delivery' }}</p>
            <div v-if="paymentMethod === 'gcash'" class="mt-2">
              <h6>GCash Details:</h6>
              <p>Janriz Christian U. Prado</p>
              <p>Davao City</p>
              <p>09167813365</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        orders: [],
        shipping: {
          name: '',
          address: '',
          city: '',
          postalCode: '',
          phone: '',
        },
        paymentMethod: '',
      };
    },
    created() {
      const queryData = this.$route.query;
      if (queryData) {
        try {
          this.orders = JSON.parse(queryData.orders || '[]');
          this.shipping = JSON.parse(queryData.shipping || '{}');
          this.paymentMethod = queryData.paymentMethod || '';
        } catch (error) {
          console.error('Error parsing query data:', error);
        }
      }
    },
    computed: {
      totalPrice() {
        return this.orders
          .reduce((acc, order) => acc + order.quantity * order.item_price_per_unit, 0)
          .toFixed(2);
      },
    },
    methods: {
      calculateTotalPrice(order) {
        return (order.quantity * order.item_price_per_unit).toFixed(2);
      },
      goBack() {
        this.$router.go(-1);
      },
    },
  };
  </script>
  
  <style scoped>
  .container {
    max-width: 1000px;
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
  
  .table {
    margin-top: 20px;
    width: 100%;
  }
  
  .table th,
  .table td {
    padding: 12px 15px;
    text-align: center;
  }
  
  .table thead {
    background-color: #1e2a37;
    color: white;
  }
  
  .table tbody tr:hover {
    background-color: #f8f9fa;
  }
  
  .table tbody td {
    border-top: 1px solid #ddd;
  }
  </style>
  