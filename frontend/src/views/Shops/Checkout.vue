<template>
  <div class="container mt-4">
    <div class="card mb-5">
      <div class="card-header bg-dark text-white d-flex align-items-center">
        <button class="btn btn-light btn-sm me-3" @click="goBack">Back</button>
        <h4>Checkout</h4>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- Shipping Information Section -->
          <div class="col-md-6">
            <h5>Shipping Information</h5>
            <form>
              <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input
                  type="text"
                  class="form-control"
                  id="name"
                  v-model="shipping.name"
                  :class="{ 'is-invalid': errors.name }"
                  @blur="validateField('name')"
                />
                <div v-if="errors.name" class="invalid-feedback">{{ errors.name }}</div>
              </div>
              <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <input
                  type="text"
                  class="form-control"
                  id="address"
                  v-model="shipping.address"
                  :class="{ 'is-invalid': errors.address }"
                  @blur="validateField('address')"
                />
                <div v-if="errors.address" class="invalid-feedback">{{ errors.address }}</div>
              </div>
              <div class="mb-3">
                <label for="city" class="form-label">City</label>
                <input
                  type="text"
                  class="form-control"
                  id="city"
                  v-model="shipping.city"
                  :class="{ 'is-invalid': errors.city }"
                  @blur="validateField('city')"
                />
                <div v-if="errors.city" class="invalid-feedback">{{ errors.city }}</div>
              </div>
              <div class="mb-3">
                <label for="postalCode" class="form-label">Postal Code</label>
                <input
                  type="text"
                  class="form-control"
                  id="postalCode"
                  v-model="shipping.postalCode"
                  :class="{ 'is-invalid': errors.postalCode }"
                  @blur="validateField('postalCode')"
                />
                <div v-if="errors.postalCode" class="invalid-feedback">{{ errors.postalCode }}</div>
              </div>
              <div class="mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input
                  type="text"
                  class="form-control"
                  id="phone"
                  v-model="shipping.phone"
                  :class="{ 'is-invalid': errors.phone }"
                  @blur="validateField('phone')"
                />
                <div v-if="errors.phone" class="invalid-feedback">{{ errors.phone }}</div>
              </div>
            </form>
          </div>

          <!-- Order Summary Section -->
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

            <div class="mt-4">
              <h5>Choose Payment Method</h5>
              <div>
                <label>
                  <input type="radio" v-model="paymentMethod" value="cod" />
                  Cash on Delivery
                </label>
              </div>
              <div>
                <label>
                  <input type="radio" v-model="paymentMethod" value="gcash" />
                  GCash
                </label>
              </div>

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
      shipping: {
        name: '',
        address: '',
        city: '',
        postalCode: '',
        phone: '',
      },
      errors: {},
      paymentMethod: '',
    };
  },
  created() {
    const itemsQuery = this.$route.query.items;
    const ordersQuery = this.$route.query.orders;
    if (itemsQuery) {
      try {
        this.orders = JSON.parse(itemsQuery);
      } catch (error) {
        console.error('Error parsing selected items:', error);
      }
    }
    if (ordersQuery) {
      try {
        this.orders = JSON.parse(ordersQuery);
      } catch (error) {
        console.error('Error parsing selected orders:', error);
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
    validateField(field) {
      if (!this.shipping[field]) {
        this.errors[field] = `${field.replace(/([A-Z])/g, ' $1')} is required`;
      } else {
        delete this.errors[field];
      }
    },
    validateForm() {
      Object.keys(this.shipping).forEach((field) => this.validateField(field));
      return Object.keys(this.errors).length === 0; // Form is valid if there are no errors
    },
    placeOrder() {
      if (!this.validateForm()) {
        alert('Please fill in all required fields.');
        return;
      }
      if (!this.paymentMethod) {
        alert('Please select a payment method.');
        return;
      }
      this.$router.push({
        name: 'OrderPage',
        query: {
          orders: JSON.stringify(this.orders),
          shipping: JSON.stringify(this.shipping),
          paymentMethod: this.paymentMethod,
        },
      });
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
