<template>
  <div class="container mt-4">
    <div class="card mb-5">
      <div class="card-header">
        <h4 class="cart-title">
          Cart
          <RouterLink to="/shop" class="btn shop-more-btn float-end">
            Shop More
          </RouterLink>
        </h4>
      </div>
      <div class="card-body">
        <table class="table cart-table">
          <thead>
            <tr>
              <th>Select</th>
              <th>Item</th>
              <th>Quantity</th>
              <th>Price Per Unit</th>
              <th>Total Price</th>
            </tr>
          </thead>
          <tbody v-if="orders.length > 0">
            <tr v-for="order in orders" :key="order.id">
              <td>
                <input type="checkbox" v-model="selectedItems" :value="order.id" class="checkbox" />
              </td>
              <td>{{ order.stock ? order.stock.item_name : "Loading..." }}</td>
              <td>
                <div class="quantity-controls">
                  <button @click="updateQuantity(order, -1)" class="btn decrement-btn">-</button>
                  <span class="quantity-display">{{ order.quantity }}</span>
                  <button @click="updateQuantity(order, 1)" class="btn increment-btn">+</button>
                </div>
              </td>
              <td>₱{{ order.price_per_unit }}</td>
              <td>₱{{ calculateTotalPrice(order) }}</td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="5" class="no-orders">No Items in Cart</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Remove Selected Button -->
      <div class="remove-selected-footer" v-if="selectedItems.length > 0">
        <button class="btn remove-selected-btn" @click="deleteSelectedOrders">
          Remove Selected Items
        </button>
      </div>

      <!-- Checkout Button -->
      <div class="checkout-footer">
        <button class="btn checkout-btn" @click="proceedToCheckout">
          Proceed to Checkout
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      orders: [],
      selectedItems: [],
    };
  },
  created() {
    this.fetchOrders();
  },
  methods: {
    async fetchOrders() {
      const customerOrderId = localStorage.getItem("customer_order_id");
      if (!customerOrderId) {
        console.warn("No customer order ID found.");
        return;
      }

      try {
        const response = await fetch(`http://localhost:8001/api/orders?customer_order_id=${customerOrderId}`);
        this.orders = await response.json();
      } catch (error) {
        console.error("Error fetching orders:", error);
      }
    },

    async updateQuantity(order, delta) {
      const newQuantity = order.quantity + delta;
      if (newQuantity < 1) return;

      try {
        const response = await fetch(`http://localhost:8001/api/orders/${order.id}`, {
          method: "PUT",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify({
            customer_order_id: order.customer_order_id,
            stock_id: order.stock_id,
            quantity: newQuantity,
            price_per_unit: order.price_per_unit,
          }),
        });

        if (!response.ok) throw new Error("Failed to update quantity.");

        this.orders = this.orders.map(o => (o.id === order.id ? { ...o, quantity: newQuantity } : o));
      } catch (error) {
        console.error("Error updating quantity:", error);
      }
    },

    calculateTotalPrice(order) {
      return (order.quantity * order.price_per_unit).toFixed(2);
    },

    async deleteSelectedOrders() {
      if (!confirm("Are you sure you want to remove selected items?")) return;

      try {
        await Promise.all(
          this.selectedItems.map(async (id) => {
            await fetch(`http://localhost:8001/api/orders/${id}`, {
              method: "DELETE",
              headers: { "Content-Type": "application/json" },
            });
          })
        );

        this.orders = this.orders.filter(order => !this.selectedItems.includes(order.id));
        this.selectedItems = [];
        alert("Selected items removed.");
      } catch (error) {
        console.error("Error removing selected items:", error);
        alert("Error deleting items.");
      }
    },

    async proceedToCheckout() {
      if (this.selectedItems.length === 0) {
        alert("Please select at least one item to proceed to checkout.");
        return;
      }

      const checkedOutOrders = this.orders.filter(order => this.selectedItems.includes(order.id));
      this.$router.push({
        name: "Checkout",
        query: { orders: encodeURIComponent(JSON.stringify(checkedOutOrders)) },
      });
    },
  },
};
</script>

<style scoped>
.container {
  max-width: 1100px;
  margin: auto;
}


.card {
  border: none;
  border-radius: 15px;
  box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.card-header {
  background: linear-gradient(135deg, #2c3e50, #4ca1af);
  color: white;
  padding: 20px;
  font-size: 22px;
  font-weight: bold;
}

.cart-title {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.shop-more-btn {
  background-color: white;
  color: #007bff;
  border: none;
  border-radius: 50px;
  font-weight: bold;
  padding: 8px 15px;
  transition: background 0.3s ease, color 0.3s ease;
}

.shop-more-btn:hover {
  background-color: #007bff;
  color: white;
}

/* Table Design */
.cart-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

.cart-table thead {
  background-color: #1e2a37;
  color: white;
}

.cart-table th,
.cart-table td {
  padding: 15px;
  text-align: center;
}

.cart-table tbody tr {
  border-bottom: 1px solid #ddd;
  transition: background 0.2s ease;
}

.cart-table tbody tr:hover {
  background-color: #f8f9fa;
}

.quantity-controls {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  /* Space between buttons and quantity */
}

.btn-quantity {
  width: 35px;
  height: 35px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.3s ease;
}

.btn-quantity:hover {
  background-color: #0056b3;
}

.quantity-display {
  font-size: 18px;
  font-weight: bold;
}

.decrement-btn,
.increment-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #1e2a37;
  /* Dark modern tone */
  color: white;
  font-size: 16px;
  font-weight: bold;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: none;
  transition: all 0.3s ease;
}

.decrement-btn:hover,
.increment-btn:hover {
  background-color: #007bff;
  /* Highlight color on hover */
  color: white;
}

.decrement-btn span,
.increment-btn span {
  margin: 0;
  padding: 0;
}

/* Remove Button */
/* Remove Selected Button */
.remove-selected-footer {
  background-color: #f8d7da;
  padding: 15px;
  text-align: center;
  border-top: 1px solid #ddd;
}

.remove-selected-btn {
  background-color: #dc3545;
  color: white;
  padding: 10px 20px;
  font-size: 16px;
  font-weight: bold;
  border-radius: 50px;
  border: none;
  transition: background 0.3s ease;
}

.remove-selected-btn:hover {
  background-color: #c82333;
}

/* Checkout Footer */
.checkout-footer {
  background-color: #f7f7f7;
  padding: 15px;
  text-align: right;
  border-top: 1px solid #ddd;
}

.checkout-btn {
  background-color: #28a745;
  color: white;
  padding: 10px 20px;
  font-size: 18px;
  font-weight: bold;
  border-radius: 50px;
  border: none;
  transition: background 0.3s ease;
}

.checkout-btn:hover {
  background-color: #218838;
}

/* No Orders */
.no-orders {
  font-size: 18px;
  color: #6c757d;
  text-align: center;
}

/* Responsive Design */
@media (max-width: 768px) {
  .cart-title {
    flex-direction: column;
    text-align: center;
    gap: 10px;
  }

  .cart-table th,
  .cart-table td {
    font-size: 14px;
  }

  .btn-quantity {
    width: 30px;
    height: 30px;
  }

  .checkout-btn {
    font-size: 16px;
    padding: 8px 15px;
  }
}
</style>