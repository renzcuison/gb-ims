<template>
  <header class="navbar">
    <div class="navbar-brand">
      <RouterLink :to="{ path: '/shop' }" class="brand-text">GREATBUY</RouterLink>
      <RouterLink :to="{ path: '/shop' }" class="brand-text-follow">ORIGINALS</RouterLink>
    </div>
    <div class="navbar-center">
      <a href="/shop">SHOP</a>
      <a href="#brands-section">BRANDS</a>
      <a href="login">MY ACCOUNT</a>
      <a href="https://www.facebook.com/profile.php?id=100075567471861" target="_blank">ABOUT US</a>
    </div>
    <div class="navbar-right">
      <a href="stocks">
        <button v-if="isAdmin || isEmployee" class="icon-button">
          <img src="/star.png" alt="Bag" class="icon-image-star" />
        </button>
      </a>
      <button class="icon-button">
        <img src="/search.png" alt="Search" class="icon-image" />
      </button>
      <button class="icon-button" @click="() => window.location.href = '/order'">
        <img src="/bag.png" alt="Bag" class="icon-image" />
      </button>
    </div>
  </header>

  <div class="container mt-4">
    <div class="card mb-5">
      <div class="card-header text-white d-flex align-items-center">
        <button class="btn btn-light btn-sm me-3" @click="goBack">Back</button>
        <h4>Checkout</h4>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <h5>Customer Information</h5>
            <form>
              <!-- GCash Name or Full Name -->
              <div class="mb-3">
                <label for="name" class="form-label">
                  {{ paymentMethod === 'gcash' ? 'GCash Name' : 'Full Name' }}
                </label>
                <input type="text" class="form-control" id="name" v-model="shipping.name"
                  :class="{ 'is-invalid': errors.name }" @blur="validateField('name')" />
                <div v-if="errors.name" class="invalid-feedback">{{ errors.name }}</div>
              </div>

              <!-- Phone Number -->
              <div class="mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phone" v-model="shipping.phone"
                  :class="{ 'is-invalid': errors.phone }" @blur="validateField('phone')" />
                <div v-if="errors.phone" class="invalid-feedback">{{ errors.phone }}</div>
              </div>

              <!-- Reference Number (Only for GCash) -->
              <div v-if="paymentMethod === 'gcash'" class="mb-3">
                <label for="address" class="form-label">Reference Number</label>
                <input type="text" class="form-control" id="address" v-model="shipping.address"
                  :class="{ 'is-invalid': errors.address }" @blur="validateField('address')" />
                <div v-if="errors.address" class="invalid-feedback">{{ errors.address }}</div>
              </div>
            </form>
          </div>

          <div class="col-md-6">
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
                <tr v-for="order in orders" :key="order.item.id">
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

            <div class="mt-4">
              <h5>Choose Payment Method</h5>
              <label><input type="radio" v-model="paymentMethod" value="cod" /> Cash on Pickup</label>
              <label><input type="radio" v-model="paymentMethod" value="gcash" /> GCash</label>

              <div v-if="paymentMethod === 'gcash'" class="mt-3">
                <h6>GCash:</h6>
                <p>Janriz Christian U. Prado</p>
                <p>Davao City</p>
                <p>09167813365</p>
                <p>NOTE: Before placing the order, please put the reference number.</p>
              </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
              <button class="btn btn-primary" @click="placeOrder">Place Order</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      orders: [],
      shipping: { name: '', address: '', city: '', postalCode: '', phone: '' },
      shippingLabels: {
        name: "Full Name",
        phone: "Phone Number",
        address: "Reference Number",
      },
      errors: {},
      paymentMethod: '',
    };
  },
  created() {
    console.log("Raw query parameters:", this.$route.query);
    const ordersQuery = this.$route.query.orders;
    if (ordersQuery) {
      try {
        this.orders = JSON.parse(ordersQuery);
        console.log("Parsed orders:", this.orders);
      } catch (error) {
        console.error("Error parsing selected orders:", error);
      }
    }
  },
  computed: {
    totalPrice() {
      return this.orders
        .reduce(
          (acc, order) =>
            acc + Number(order.quantity) * Number(order.item.price_per_unit),
          0
        )
        .toFixed(2);
    },
  },
  methods: {
    calculateTotalPrice(order) {
      return (Number(order.quantity) * Number(order.item.price_per_unit)).toFixed(2);
    },
    validateField(field) {
      if (!this.shipping[field]) {
        this.errors[field] = `${this.shippingLabels[field]} is required`;
      } else {
        delete this.errors[field];
      }
    },
    validateForm() {
      this.errors = {}; // Clear previous errors

      if (!this.shipping.name) {
        this.errors.name = this.paymentMethod === 'gcash' ? 'GCash Name is required' : 'Full Name is required';
      }

      if (!this.shipping.phone) {
        this.errors.phone = 'Phone Number is required';
      }

      if (this.paymentMethod === 'gcash' && !this.shipping.address) {
        this.errors.address = 'Reference Number is required';
      }

      return Object.keys(this.errors).length === 0;
    },
    async placeOrder() {
      if (!this.validateForm()) {
        alert('Please fill in all required fields.');
        return;
      }
      if (!this.paymentMethod) {
        alert('Please select a payment method.');
        return;
      }

      const requestBody = {
        customer_name: this.shipping.name,
        shipping_address: this.shipping.address,
        city: this.shipping.city,
        postal_code: this.shipping.postalCode,
        phone: this.shipping.phone,
        payment_method: this.paymentMethod,
        total_price: this.totalPrice,
        stocks: this.orders.map(order => ({
          stock_id: order.item.id,
          quantity: order.quantity,
          price_per_unit: order.item.price_per_unit,
        })),
      };

      console.log("Sending order request:", requestBody);

      const token = localStorage.getItem('authToken');
      if (!token) {
        alert('You are not authenticated. Please login.');
        this.$router.push('/login');
        return;
      }

      try {
        const response = await axios.post(
          'http://localhost:8001/api/customer-orders',
          requestBody,
          {
            headers: {
              'Content-Type': 'application/json',
              Authorization: `Bearer ${token}`,
            },
            withCredentials: true,
          }
        );

        alert('Order placed successfully!');
        console.log(response.data);

        const now = new Date();
        const timestamp = now.toLocaleString();

        const allOrderTimes = JSON.parse(localStorage.getItem('orderTimestamps') || '{}');
        allOrderTimes[response.data.id] = timestamp;
        localStorage.setItem('orderTimestamps', JSON.stringify(allOrderTimes));

        this.$router.push({ name: 'OrderPage' });
      } catch (error) {
        if (error.response) {
          console.error('Error placing order:', error.response.data);
          alert('Failed to place order: ' + (error.response.data.error || 'Not enough stock.'));
        } else if (error.request) {
          console.error('Network error:', error.request);
          alert('Network error. Please try again.');
        } else {
          console.error('Unexpected error:', error.message);
          alert('An unexpected error occurred.');
        }
      }
    },

    goBack() {
      this.$router.go(-1);
    },
  },
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
}

.card {
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  border: none;
}

.card-header {
  background-color: #000000;
  color: white;
  padding: 20px 24px;
  font-size: 20px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 16px;
}

.card-header h4 {
  margin: 0;
}

.card-body {
  padding: 2rem;
}

.navbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background-color: white;
  color: #fff;
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
  font-family: 'Kantumruy Pro', sans-serif;
  font-size: 12px;
  color: black;
  text-decoration: none;
  transition: color 0.3s ease;
  margin-top: 5px;
  font-weight: 400;
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
  width: 14x;
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
  text-decoration: none;
  color: #0086E7;
  font-size: 24px;
  font-weight: normal;
  cursor: pointer;
}

.brand-text-follow {
  font-family: 'LibreCaslonDisplay-Regular';
  text-decoration: none;
  color: #0086E7;
  font-size: 12px;
  font-weight: normal;
  cursor: pointer;
  margin-top: 10px;
}

h5 {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 16px;
}

label.form-label {
  font-size: 13px;
  font-weight: 500;
}

input.form-control {
  font-size: 14px;
  border-radius: 6px;
  padding: 10px;
  border: 1px solid #ced4da;
}

input.form-control.is-invalid {
  border-color: #dc3545;
}

.invalid-feedback {
  font-size: 12px;
}

.table {
  font-size: 14px;
  border-collapse: collapse;
  width: 100%;
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
  font-weight: 600;
  font-size: 14px;
  border-radius: 50px;
  padding: 8px 20px;
}

.btn-primary {
  background-color: #000000;
  border: none;
}

.btn-primary:hover {
  background-color: #0086E7;
}

.btn-light {
  font-size: 12px;
  font-weight: 500;
  border-radius: 20px;
  padding: 5px 14px;
}

.mt-4 h5 {
  margin-top: 20px;
  font-weight: 600;
  font-size: 16px;
}

.mt-4 h6 {
  font-size: 14px;
  font-weight: 600;
}

.mt-4 p {
  font-size: 13px;
  margin-bottom: 6px;
}

@media (max-width: 768px) {
  .card-body {
    padding: 1.25rem;
  }

  .table th,
  .table td {
    font-size: 12px;
    padding: 10px;
  }

  .btn {
    font-size: 12px;
    padding: 6px 16px;
  }
}
</style>
