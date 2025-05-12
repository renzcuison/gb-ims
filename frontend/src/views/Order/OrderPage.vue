<template>
  <div class="container mt-4">
    <div class="shop-view">
      <!-- Header Section -->
      <header class="shop-header">
        <nav>
          <ul class="menu">
            <li><a href="/shop">SHOP</a></li>
            <li><a href="/order">ORDERS</a></li>
            <li><a href="#">MY ACCOUNT</a></li>
            <li><a href="#">ABOUT US</a></li>
            <li>
              <RouterLink to="/orders" class="cart-icon">
                🛒 <span class="cart-count">{{ cartQuantity }}</span>
              </RouterLink>
            </li>
          </ul>
        </nav>
      </header>
    </div>

    <div v-if="orders.length === 0" class="alert alert-warning text-center">
      No Order Found
      <br />
      <button class="btn btn-primary mt-2" @click="goToShop">Go to Shop</button>
    </div>

    <div v-for="order in orders" :key="order.id" class="card mb-5">
      <div class="card-header bg-dark text-white d-flex align-items-center">
        <h4>
          Order Details
          <span v-if="order.status" class="ms-3 px-2 py-1 text-sm rounded-pill" :class="{
            'bg-warning text-dark': order.status === 'Pending',
            'bg-success text-white': order.status === 'Approved',
            'bg-danger text-white': order.status === 'Cancelled',
            'bg-secondary text-white': !['Pending', 'Approved', 'Cancelled'].includes(order.status)
          }">
            {{ order.status }}
          </span>
          <span v-else class="ms-3 px-2 py-1 text-sm rounded-pill bg-secondary text-white">
            Unknown
          </span>
        </h4>
      </div>

      <div class="card-body">
        <div>
          <h5>Shipping Information</h5>
          <ul>
            <li><strong>Order Code:</strong> {{ order.order_code }}</li>
            <li><strong>Name:</strong> {{ order.customer_name }}</li>
            <li><strong>Address:</strong> {{ order.shipping_address }}</li>
            <li><strong>City:</strong> {{ order.city }}</li>
            <li><strong>Postal Code:</strong> {{ order.postal_code }}</li>
            <li><strong>Phone:</strong> {{ order.phone }}</li>
          </ul>
        </div>

        <div v-if="order.orderTime" class="mb-3">
          <strong>Order Placed At:</strong> {{ order.orderTime }}
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
              <tr v-for="orderItem in order.orders" :key="orderItem.id">
                <td>{{ orderItem.stock.item_name }}</td>
                <td>{{ orderItem.quantity }}</td>
                <td>₱{{ (orderItem.quantity * orderItem.price_per_unit).toFixed(2) }}</td>
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
      cartQuantity: 0,
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
          const timestampMap = JSON.parse(localStorage.getItem('orderTimestamps') || '{}');
          data.forEach(order => {
            if (timestampMap[order.id]) {
              order.orderTime = timestampMap[order.id];
            }
          });
          this.orders = data;
        } else {
          console.error('No orders found.');
        }
      } catch (error) {
        console.error('Network error:', error);
      }
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

          const data = await response.json();

          if (response.ok) {
            this.orders = this.orders.filter(order => order.id !== orderId);
            alert("Order canceled successfully.");
            this.fetchOrders();
          } else {
            alert(`Failed to cancel order: ${data.message || 'Unknown error'}`);
          }
        } catch (error) {
          console.error("Error deleting order:", error);
          alert("Network error. Please try again.");
        }
      }
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

.shop-header {
  padding: 20px 0;
}

.menu {
  list-style: none;
  display: flex;
  justify-content: center;
  gap: 20px;
  padding: 0;
  margin: 0;
}

.menu li {
  display: inline-block;
}

.menu a {
  text-decoration: none;
  color: black;
  font-weight: bold;
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
