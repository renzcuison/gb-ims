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
              <th>Actions</th>
            </tr>
          </thead>
          <tbody v-if="orders.length > 0">
            <tr v-for="order in orders" :key="order.id">
              <td>
                <input
                  type="checkbox"
                  v-model="selectedItems"
                  :value="order"
                  class="checkbox"
                />
              </td>
              <td>{{ order.item ? order.item.name : 'Unknown' }}</td>
              <td>
                <div class="quantity-controls">
                  <button
                    @click="updateQuantity(order, -1)"
                    class="btn decrement-btn"
                  >
                    <span>-</span>
                  </button>
                  <span class="quantity-display">{{ order.quantity }}</span>
                  <button
                    @click="updateQuantity(order, 1)"
                    class="btn increment-btn"
                  >
                    <span>+</span>
                  </button>
                </div>
              </td>
              <td>₱{{ order.item.price_per_unit }}</td>
              <td>₱{{ calculateTotalPrice(order) }}</td>
              <td>
                <button
                  @click="deleteOrder(order.id)"
                  class="btn remove-btn"
                >
                  Remove
                </button>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="6" class="no-orders">No orders found</td>
            </tr>
          </tbody>
        </table>
      </div>
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
  watch: {
    selectedItems(newValue) {
      console.log("Selected Items: ", newValue); 
    }
  },
  methods: {
    fetchOrders() {
      fetch("http://localhost:8001/api/orders")
        .then((response) => response.json())
        .then((data) => {
          this.orders = data;
        })
        .catch((error) => {
          console.error("Error fetching orders:", error);
        });
    },

    updateQuantity(order, delta) {
      if (order.quantity + delta >= 1) {
        order.quantity += delta;
      }
    },

    calculateTotalPrice(order) {
      return (order.quantity * order.item_price_per_unit).toFixed(2);
    },

    deleteOrder(orderId) {
      if (confirm("Are you sure you want to remove this item?")) {
        fetch(`http://localhost:8001/api/orders/${orderId}`, {
          method: "DELETE",
          headers: {
            "Content-Type": "application/json",
          },
        })
          .then((response) => {
            if (!response.ok) {
              throw new Error("Failed to remove the item.");
            }
            this.orders = this.orders.filter((order) => order.id !== orderId);
            alert("Item Removed.");
          })
          .catch((error) => {
            console.error("Error removing the item:", error);
            alert("Error deleting item");
          });
      }
    },

    proceedToCheckout() {
      if (this.selectedItems.length === 0) {
        alert("Please select at least one item to proceed to checkout.");
        return;
      }
      this.$router.push({
        name: "Checkout",
        query: { items: JSON.stringify(this.selectedItems) },
      });
    },
  },
};
</script>


<style scoped>
/* General Container */
.container {
  max-width: 1100px;
  margin: auto;
}

/* Card Styling */
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
  gap: 10px; /* Space between buttons and quantity */
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
  background-color: #1e2a37; /* Dark modern tone */
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
  background-color: #007bff; /* Highlight color on hover */
  color: white;
}

.decrement-btn span,
.increment-btn span {
  margin: 0;
  padding: 0;
}

/* Remove Button */
.remove-btn {
  background-color: #ff6b6b;
  color: white;
  border: none;
  border-radius: 5px;
  padding: 5px 10px;
  font-weight: bold;
  transition: background 0.3s ease;
}

.remove-btn:hover {
  background-color: #ff4a4a;
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
