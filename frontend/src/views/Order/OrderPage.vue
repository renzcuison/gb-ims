<template>
  <div class="container mt-4">
    <header class="navbar">
      <div class="navbar-brand">
        <RouterLink to="/shop" class="brand-text">GREATBUY</RouterLink>
        <RouterLink to="/shop" class="brand-text-follow">ORIGINALS</RouterLink>
      </div>
      <div class="navbar-center">
        <a href="/shop">SHOP</a>
        <a href="#brands-section">BRANDS</a>
        <a href="/login">MY ACCOUNT</a>
        <a href="https://www.facebook.com/profile.php?id=100075567471861" target="_blank">ABOUT US</a>
      </div>
      <div class="navbar-right">
        <a v-if="isAdmin" href="/stocks" class="icon-button">
          <img src="/star.png" alt="Star" class="icon-image-star">
        </a>
        <button class="icon-button">
          <img src="/search.png" alt="Search" class="icon-image">
        </button>
        <button class="icon-button" @click="$router.push('/order')">
          <img src="/bag.png" alt="Bag" class="icon-image">
        </button>
      </div>
    </header>

    <div v-if="orders.length === 0" class="alert alert-warning text-center mt-4">
      No Order Found
      <br />
      <button class="btn btn-primary mt-3" @click="goToShop">Go to Shop</button>
    </div>

    <div v-for="order in orders" :key="order.id" class="card mb-5">
      <div class="card-header">
        <h4 class="mb-0 d-flex align-items-center">
          Order Details
          <div class="ms-3 order-status-wrapper" v-if="isAdmin">
            <select v-model="order.status" @change="updateOrderStatus(order)" class="form-select status-select">
              <option v-for="status in statusOptions" :key="status" :value="status">{{ status }}</option>
            </select>
          </div>
        </h4>
      </div>

      <div class="card-body">
        <div>
          <!-- <h5>Order Information</h5> -->
          <ul>
            <li><strong>Order Code:</strong> {{ order.order_code }}</li>
            <li><strong>{{ order.payment_method === 'gcash' ? 'GCash Name' : 'Name' }}:</strong> {{ order.customer_name
            }}</li>
            <li><strong>Phone:</strong> {{ order.phone }}</li>

            <li v-if="order.payment_method === 'gcash'">
              <strong>Reference Number:</strong> {{ order.shipping_address }}
            </li>

            <li v-if="order.payment_method === 'cod' && order.shipping_address">
              <strong>Address:</strong> {{ order.shipping_address }}
            </li>
            <li v-if="order.payment_method === 'cod' && order.city">
              <strong>City:</strong> {{ order.city }}
            </li>
            <li v-if="order.payment_method === 'cod' && order.postal_code">
              <strong>Postal Code:</strong> {{ order.postal_code }}
            </li>
          </ul>
        </div>

        <div v-if="order.orderTime" class="mb-3">
          <strong>Order Placed At:</strong> {{ order.orderTime }}
        </div>

        <div class="mt-4">
          <h5>Order Summary</h5>
          <table class="table">
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
          <p>{{ order.payment_method === 'gcash' ? 'GCash' : 'Cash on Pickup' }}</p>
        </div>


        <div class="mt-4 text-end" v-if="!isAdmin">
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
      orders: [],
      statusOptions: [
        'Pending',
        'Approved',
        'Cancelled',
        'Refunded',
      ]
    };
  },
  computed: {
    isAdmin() {
      return localStorage.getItem('user_role') === 'admin';
    }
  },
  async created() {
    console.log("User role:", localStorage.getItem('user_role'));
    console.log("isAdmin:", this.isAdmin);
    this.fetchOrders();
  },

  methods: {
    async fetchOrders() {
      try {
        const token = localStorage.getItem('authToken');
        const response = await fetch('http://localhost:8001/api/customer-orders', {
          credentials: 'include',
          headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`,
          }
        });

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
          console.error('Failed to fetch orders:', data);
        }
      } catch (error) {
        console.error('Network error:', error);
      }
    },
    goToShop() {
      this.$router.push('/shop');
    },
    async cancelOrder(orderId) {
      const token = localStorage.getItem('authToken');

      try {
        const response = await fetch(`http://localhost:8001/api/customer-orders/${orderId}`, {
          method: 'DELETE',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
          },
          credentials: 'include',
        });

        if (!response.ok) {
          throw new Error(`Cancel order failed: ${response.statusText}`);
        }

        alert('Order canceled successfully!');

        // Re-fetch orders here:
        await this.fetchOrders();

      } catch (error) {
        console.error('Cancel order failed:', error);
        alert('Failed to cancel order.');
      }
    },
    async updateOrderStatus(order) {
      try {
        await fetch(`http://localhost:8001/api/customer-orders/${order.id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ status: order.status }),
        });
      } catch (error) {
        console.error("Error updating status:", error);
      }
    },
  }
};
</script>


<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@300;400;700&display=swap');

* {
  font-family: 'Kantumruy Pro', sans-serif;
}

.container {
  max-width: 1000px;
  margin: 0 auto;
  padding: 20px;
  margin-bottom: 200px;
}

.card {
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  border: none;
}

.card-header {
  background-color: #000;
  color: white;
  padding: 20px 24px;
  font-size: 20px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 16px;
}

.card-body {
  padding: 2rem;
}

.navbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background-color: white;
  height: 50px;
  padding: 0 20px;
  position: sticky;
  top: 0;
  z-index: 1000;
  margin-bottom: -1px;
}

.navbar-brand {
  display: flex;
  align-items: center;
  margin-left: 10%;
}

.navbar-center {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 30px;
}

.navbar-center a {
  font-size: 12px;
  color: black;
  text-decoration: none;
  font-weight: 400;
  margin-top: 5px;
}

.navbar-center a:hover {
  color: #0086E7;
}

.navbar-right {
  display: flex;
  align-items: center;
  margin-right: 10%;
}

.icon-image {
  width: 14px;
  height: 14px;
}

.icon-image-star {
  width: 16px;
  height: 16px;
  margin-bottom: 1px;
}

.icon-button {
  background: none;
  border: none;
  cursor: pointer;
}

.brand-text {
  font-family: 'LibreCaslonDisplay-Regular';
  color: #0086E7;
  font-size: 24px;
  text-decoration: none;
}

.brand-text-follow {
  font-family: 'LibreCaslonDisplay-Regular';
  color: #0086E7;
  font-size: 12px;
  margin-top: 10px;
}

h5 {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 16px;
}

.table {
  font-size: 14px;
  width: 100%;
  border-collapse: collapse;
}

.table th,
.table td {
  text-align: center;
  padding: 12px 15px;
}

.table thead {
  background-color: #1e2a37;
  color: white;
}

.table tbody tr:nth-child(even) {
  background-color: #f9f9f9;
}

.table tbody tr:hover {
  background-color: #f1f1f1;
}

.btn {
  font-size: 14px;
  font-weight: 600;
  border-radius: 50px;
  padding: 8px 20px;
}

.btn-primary {
  background-color: #000;
  border: none;
}

.btn-primary:hover {
  background-color: #0086E7;
}

.status-select {
  border-radius: 20px;
  padding: 4px 12px;
  font-size: 14px;
}

.status-label {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 14px;
}
</style>
