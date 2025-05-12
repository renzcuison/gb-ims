<template>
  <div class="container mt-4">
    <div class="card mb-5">
      <div class="card-header bg-dark text-white d-flex align-items-center">
        <button class="btn btn-light btn-sm me-3" @click="goBack">Back</button>
        <h4>Checkout</h4>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <h5>Shipping Information</h5>
            <form>
              <div v-for="(label, key) in shippingLabels" :key="key" class="mb-3">
                <label :for="key" class="form-label">{{ label }}</label>
                <input type="text" class="form-control" :id="key" v-model="shipping[key]"
                  :class="{ 'is-invalid': errors[key] }" @blur="validateField(key)" />
                <div v-if="errors[key]" class="invalid-feedback">{{ errors[key] }}</div>
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
              <label><input type="radio" v-model="paymentMethod" value="cod" /> Cash on Delivery</label>
              <label><input type="radio" v-model="paymentMethod" value="gcash" /> GCash</label>

              <div v-if="paymentMethod === 'gcash'" class="mt-3">
                <h6>GCash:</h6>
                <p>Janriz Christian U. Prado</p>
                <p>Davao City</p>
                <p>09167813365</p>
                <p>Please send the deposit slip/tracking number to jcuprado@gmail.com</p>
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
export default {
  data() {
    return {
      orders: [],
      shipping: { name: '', address: '', city: '', postalCode: '', phone: '' },
      shippingLabels: {
        name: "Full Name",
        address: "Address",
        city: "City",
        postalCode: "Postal Code",
        phone: "Phone Number"
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
      return this.orders.reduce((acc, order) =>
        acc + (Number(order.quantity) * Number(order.item.price_per_unit)), 0
      ).toFixed(2);
    }
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
      Object.keys(this.shipping).forEach(this.validateField);
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

      try {
        const response = await fetch('http://localhost:8001/api/customer-orders', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(requestBody),
        });

        console.log("Response:", response);
        const result = await response.json();

        if (response.ok) {
          alert('Order placed successfully!');
          console.log(result);
          const now = new Date();
          const month = String(now.getMonth() + 1).padStart(2, '0');
          const day = String(now.getDate()).padStart(2, '0');
          const year = now.getFullYear();
          const time = now.toLocaleTimeString();
          const timestamp = `${month}/${day}/${year}, ${time}`;
          const allOrderTimes = JSON.parse(localStorage.getItem('orderTimestamps') || '{}');
          allOrderTimes[result.id] = timestamp;
          localStorage.setItem('orderTimestamps', JSON.stringify(allOrderTimes));
          this.$router.push({ name: 'OrderPage' });
        } else {
          console.error('Error placing order:', result);
          alert('Failed to place order. Not enough stock.');
        }
      } catch (error) {
        console.error('Network error:', error);
        alert('Network error. Please try again.');
      }
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
  border-collapse: collapse;
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

.table tbody tr:nth-child(even) {
  background-color: #f9f9f9;
}

.table tbody td {
  border-top: 1px solid #ddd;
}

.btn {
  font-weight: 600;
}

.btn-primary {
  background-color: #007bff;
  color: white;
  border-radius: 50px;
}

.btn-primary:hover {
  background-color: #0056b3;
  color: white;
}

@media (max-width: 768px) {
  .card-body {
    padding: 1rem;
  }

  .table th,
  .table td {
    font-size: 12px;
  }

  .btn {
    font-size: 12px;
    padding: 5px 12px;
  }
}
</style>