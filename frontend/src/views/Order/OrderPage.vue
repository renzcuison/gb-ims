<template>
  <div class="container mt-4">
    <div v-if="orders.length === 0" class="alert alert-warning text-center">
      No Order Found
      <br />
      <button class="btn btn-primary mt-2" @click="goToShop">Go to Shop</button>
    </div>

    <div v-for="order in orders" :key="order.id" class="card mb-5">
      <div class="card-header bg-dark text-white d-flex align-items-center">
        <button class="btn btn-light btn-sm me-3" @click="goBack">Back</button>
        <h4>Order Details - {{ order.order_code }}</h4>
      </div>
      <div class="card-body">
        <div>
          <h5>Shipping Information</h5>
          <ul>
            <li><strong>Name:</strong> {{ order.customer_name }}</li>
            <li><strong>Address:</strong> {{ order.shipping_address }}</li>
            <li><strong>City:</strong> {{ order.city }}</li>
            <li><strong>Postal Code:</strong> {{ order.postal_code }}</li>
            <li><strong>Phone:</strong> {{ order.phone }}</li>
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
              <tr v-for="item in order.items" :key="item.id">
                <td>{{ item.name }}</td>
                <td>{{ item.quantity }}</td>
                <td>₱{{ item.total_price }}</td>
              </tr>
            </tbody>
          </table>
          <div class="d-flex justify-content-between mt-3">
            <h6>Total Price:</h6>
            <h6>₱{{ order.total_price }}</h6>
          </div>
        </div>

        <div class="mt-4">
          <h5>Payment Method</h5>
          <p>{{ order.payment_method === 'gcash' ? 'GCash' : 'Cash on Delivery' }}</p>
          <div v-if="order.payment_method === 'gcash'" class="mt-2">
            <h6>GCash Details:</h6>
            <p>Janriz Christian U. Prado</p>
            <p>Davao City</p>
            <p>09167813365</p>
          </div>
        </div>

        <!-- Cancel Order Button -->
        <div class="mt-4 text-end">
          <button class="btn btn-danger" @click="cancelOrder(order.id)">Cancel Order</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      orders: []
    };
  },
  async created() {
    this.fetchOrders();
  },
  methods: {
    async fetchOrders() {
      try {
        const response = await fetch('http://localhost:8001/api/customer-orders');
        const data = await response.json();
        if (response.ok) {
          this.orders = data;
        } else {
          console.error('No orders found.');
        }
      } catch (error) {
        console.error('Network error:', error);
      }
    },
    goBack() {
      this.$router.go(-1);
    },
    goToShop() {
      this.$router.push('/shop');
    },
    async cancelOrder(orderId) {
      if (confirm("Are you sure you want to cancel this order?")) {
        try {
          const response = await fetch(`http://localhost:8001/api/customer-orders/${orderId}`, {
            method: 'DELETE',
          });

          if (response.ok) {
            this.orders = this.orders.filter(order => order.id !== orderId);
            alert("Order canceled successfully.");
          } else {
            alert("Failed to cancel order.");
          }
        } catch (error) {
          console.error("Error deleting order:", error);
        }
      }
    }
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

  