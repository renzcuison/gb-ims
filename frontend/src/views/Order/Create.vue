<template>
  <div class="container mt-4">
    <div class="card">
      <div class="card-header bg-dark text-white">
        <h4>Create Order</h4>
      </div>
      <div class="card-body">
        <form @submit.prevent="createOrder">
          <!-- Customer Section -->
          <div class="mb-3">
            <label for="customer_id" class="form-label">Customer Name</label>
            <select
              class="form-control"
              id="customer_id"
              v-model="order.customer_id"
              :class="{ 'is-invalid': errors.customer_id }"
            >
              <option value="">...</option>
              <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                {{ customer.first_name }} {{ customer.last_name }}
              </option>
            </select>
            <div v-if="errors.customer_id" class="text-danger mt-1">{{ errors.customer_id }}</div>
          </div>

          <!-- Status Section -->
          <div class="mb-3">
            <label for="status" class="form-label">Order Status</label>
            <select
              class="form-control"
              id="status"
              v-model="order.status"
              :class="{ 'is-invalid': errors.status }"
            >
              <option value="Pending">Pending</option>
              <option value="Shipped">Shipped</option>
              <option value="Delivered">Delivered</option>
            </select>
            <div v-if="errors.status" class="text-danger mt-1">{{ errors.status }}</div>
          </div>

          <!-- Item Section -->
          <div class="mb-3">
            <label for="item_id" class="form-label">Item</label>
            <input
              type="text"
              class="form-control"
              :value="selectedItem ? selectedItem.name : ''"
              readonly
              placeholder="Select a product"
              :class="{'bg-light': !selectedItem, 'is-invalid': errors.item_id}"
            />
            <div v-if="errors.item_id" class="text-danger mt-1">{{ errors.item_id }}</div>
          </div>

          <!-- Quantity Section -->
          <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input
              type="number"
              class="form-control"
              id="quantity"
              v-model="order.quantity"
              :class="{ 'is-invalid': errors.quantity }"
              min="1"
            />
            <div v-if="errors.quantity" class="text-danger mt-1">{{ errors.quantity }}</div>
          </div>

          <!-- Buttons Section -->
          <div class="d-flex justify-content-between">
            <button type="button" class="btn btn-secondary" @click="openProductModal">
              {{ selectedItem ? 'Change Product' : 'Add Product' }}
            </button>
            <button
              type="button"
              class="btn btn-danger"
              @click="removeProduct"
              v-if="selectedItem"
            >
              Remove Product
            </button>
          </div>

          <!-- Submit Button -->
          <button type="submit" class="btn btn-primary mt-3">Submit Order</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Product Modal Component -->
  <ProductModal v-if="showProductModal" @close="closeProductModal" @select="handleProductSelect" />
</template>

<script>
import ProductModal from './productModal.vue';

export default {
  components: {
    ProductModal,
  },
  data() {
    return {
      order: {
        customer_id: '',
        item_id: null,
        quantity: 1,
        status: 'Pending',
      },
      items: [],
      customers: [],
      showProductModal: false,
      selectedItem: null,
      errors: {}, // For storing validation errors
    };
  },
  created() {
    this.fetchItems();
    this.fetchCustomers();
  },
  methods: {
    async fetchItems() {
      try {
        const response = await fetch('http://localhost:8001/api/items');
        if (!response.ok) {
          throw new Error('Failed to fetch items');
        }
        const data = await response.json();
        this.items = data.items;
      } catch (error) {
        console.error('There was an error fetching the items:', error);
      }
    },
    async fetchCustomers() {
      try {
        const response = await fetch('http://localhost:8001/api/customers');
        if (!response.ok) {
          throw new Error('Failed to fetch customers');
        }
        const data = await response.json();
        this.customers = data.customers;
      } catch (error) {
        console.error('There was an error fetching the customers:', error);
      }
    },
    openProductModal() {
      this.showProductModal = true;
    },
    closeProductModal() {
      this.showProductModal = false;
    },
    handleProductSelect(selectedItems) {
      if (selectedItems.length > 0) {
        this.selectedItem = selectedItems[0];
        this.order.item_id = this.selectedItem.id;
      }
      this.closeProductModal();
    },
    removeProduct() {
      this.selectedItem = null;
      this.order.item_id = null;
    },
    validateForm() {
      this.errors = {}; // Reset errors
      let isValid = true;

      // Validate customer
      if (!this.order.customer_id) {
        this.errors.customer_id = 'Please select a customer';
        isValid = false;
      }

      // Validate item
      if (!this.order.item_id) {
        this.errors.item_id = 'Please select an item';
        isValid = false;
      }

      // Validate quantity
      if (!this.order.quantity || this.order.quantity < 1) {
        this.errors.quantity = 'Quantity must be at least 1';
        isValid = false;
      }

      // Validate status
      if (!this.order.status) {
        this.errors.status = 'Please select a status';
        isValid = false;
      }

      return isValid;
    },
    async createOrder() {
      if (!this.validateForm()) {
        return; // Halt submission if form is invalid
      }

      try {
        const selectedCustomer = this.customers.find(
          (customer) => customer.id === this.order.customer_id
        );
        const customerName = selectedCustomer
          ? `${selectedCustomer.first_name} ${selectedCustomer.last_name}`
          : '';

        const response = await fetch('http://localhost:8001/api/orders', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            customer_id: this.order.customer_id,
            customer_name: customerName,
            item_id: this.order.item_id,
            quantity: this.order.quantity,
            status: this.order.status,
          }),
        });

        if (!response.ok) {
          throw new Error('Failed to create order');
        }

        this.$router.push('/orders');
      } catch (error) {
        console.error('Error creating order:', error);
      }
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

.is-invalid {
  border-color: #dc3545 !important;
}

.text-danger {
  font-size: 12px;
}

.btn {
  font-weight: 600;
}

.btn-secondary {
  min-width: 120px;
}

input.form-control[readonly] {
  cursor: pointer;
}

input.form-control.bg-light {
  background-color: #f8f9fa !important;
}
</style>
