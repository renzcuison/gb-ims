<template>
  <div class="shop-view">
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

    <section class="item-section">
      <nav class="breadcrumb">
        <a href="/shop">Home</a> / {{ item.name }}
      </nav>

      <div class="item-main">
        <div class="item-image"></div>
        <div class="item-info">
          <h1 class="item-title">{{ item.name }}</h1>
          <p class="item-price">₱{{ item.price }}</p>
          <div class="item-description">
            <p>{{ item.description }}</p>
          </div>
          <div class="actions">
            <div class="quantity-selector">
              <button class="quantity-btn" @click="decreaseQuantity">-</button>
              <span class="quantity-display">{{ quantity }}</span>
              <button class="quantity-btn" @click="increaseQuantity">+</button>
            </div>
            <button class="buy-now" @click="buyNow">Buy Now</button>
            <button class="add-to-cart" @click="addToCart">Add to Cart</button>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
export default {
  data() {
    return {
      item: {},
      quantity: 1,
      customerOrderId: null,
    };
  },
  computed: {
    cartQuantity() {
      return this.quantity;
    },
  },
  created() {
    this.fetchItemDetails();
    this.initCustomerOrderId(); // 🔥 This was missing!
  },
  methods: {
    initCustomerOrderId() {
      const storedId = localStorage.getItem("customer_order_id");
      if (storedId) {
        this.customerOrderId = storedId;
      } else {
        const newId = `order_${Date.now()}`;
        localStorage.setItem("customer_order_id", newId);
        this.customerOrderId = newId;
      }
    },

    async fetchItemDetails() {
      const itemId = this.$route.params.id;
      if (!itemId) {
        console.error("Item ID is missing");
        return;
      }

      try {
        const response = await fetch(`http://localhost:8001/api/stocks/${itemId}`);
        const data = await response.json();

        if (!data || !data.stock) {
          console.error("Item not found in the response");
          return;
        }

        this.item = {
          id: String(data.stock.id),
          name: data.stock.item_name || "No name available",
          price: data.stock.price_per_unit || "0",
          description: data.stock.description || "No description available",
        };
      } catch (error) {
        console.error("Error fetching item details:", error);
      }
    },

    increaseQuantity() {
      this.quantity++;
    },
    decreaseQuantity() {
      if (this.quantity > 1) {
        this.quantity--;
      }
    },

    async addToCart() {
      if (!this.item.id || !this.customerOrderId) {
        console.error("Missing item ID or customer order ID.");
        return;
      }

      try {
        const response = await fetch("http://localhost:8001/api/orders", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            customer_order_id: this.customerOrderId,
            stock_id: this.item.id,
            quantity: this.quantity,
            price_per_unit: Number(this.item.price),
          }),
        });

        if (!response.ok) throw new Error("Failed to add item to cart.");

        alert("Item added to cart!");
      } catch (error) {
        console.error("Error adding item to cart:", error);
      }
    },

    buyNow() {
      if (!this.item.id) {
        console.error("Item ID is missing in buyNow function.");
        return;
      }

      const orderData = [
        {
          item: {
            id: this.item.id,
            name: this.item.name,
            price_per_unit: Number(this.item.price),
          },
          quantity: this.quantity,
        },
      ];

      this.$router.push({
        path: "/checkout",
        query: {
          orders: JSON.stringify(orderData),
        },
      });
    },
  },
};
</script>

<style scoped>
.shop-view {
  font-family: 'Roboto', sans-serif;
  color: #2d3436;
  text-align: center;
  max-width: 1200px;
  margin: 0 auto;
}

.shop-header {
  padding: 20px 0;
  background-color: #f7f7f7;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

.menu {
  list-style: none;
  display: flex;
  justify-content: center;
  gap: 30px;
  margin: 0;
  padding: 0;
}

.menu a {
  text-decoration: none;
  font-weight: bold;
  font-size: 16px;
  color: #34495e;
  transition: color 0.3s ease;
}

.menu a:hover {
  color: #2980b9;
}

.cart-icon {
  position: relative;
}

.cart-count {
  background: #e74c3c;
  color: #fff;
  font-size: 12px;
  border-radius: 50%;
  padding: 2px 6px;
  position: absolute;
  top: -10px;
  right: -10px;
}

.item-section {
  margin: 40px 0;
}

.breadcrumb {
  font-size: 14px;
  color: #95a5a6;
  margin-bottom: 20px;
}

.breadcrumb a {
  text-decoration: none;
  color: #2980b9;
}

.breadcrumb a:hover {
  text-decoration: underline;
}

.item-main {
  display: flex;
  gap: 40px;
  align-items: flex-start;
  margin-bottom: 40px;
}

.quantity-selector {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  margin: 20px 0;
}

.quantity-btn {
  width: 50px;
  height: 50px;
  background-color: #ecf0f1;
  color: #2c3e50;
  font-size: 20px;
  font-weight: bold;
  border: 2px solid #bdc3c7;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.quantity-btn:hover {
  background-color: #bdc3c7;
  color: #fff;
}

.quantity-btn:active {
  transform: scale(0.95);
}

.quantity-display {
  font-size: 20px;
  font-weight: bold;
  padding: 0 10px;
  min-width: 40px;
  text-align: center;
  color: #2c3e50;
}

.item-image {
  flex: 1;
  height: 400px;
  background: linear-gradient(135deg, #11095c, #0a3992);
  border-radius: 12px;
}

.item-info {
  flex: 1;
  text-align: left;
}

.item-title {
  font-size: 28px;
  font-weight: 600;
  color: #2c3e50;
}

.item-price {
  font-size: 24px;
  font-weight: bold;
  color: #e74c3c;
  margin: 10px 0 20px;
}

.options {
  margin: 20px 0;
}

.actions {
  display: flex;
  gap: 15px;
  margin-top: 20px;
}

.buy-now {
  background: linear-gradient(135deg, #27ae60, #2ecc71);
  padding: 12px 30px;
  font-size: 16px;
  border: none;
  border-radius: 8px;
  color: white;
  cursor: pointer;
  transition: box-shadow 0.3s ease;
}

.buy-now:hover {
  box-shadow: 0 4px 10px rgba(39, 174, 96, 0.5);
}

.add-to-cart {
  background: linear-gradient(135deg, #2980b9, #3498db);
  padding: 12px 30px;
  font-size: 16px;
  border: none;
  border-radius: 8px;
  color: white;
  cursor: pointer;
  transition: box-shadow 0.3s ease;
}

.add-to-cart:hover {
  box-shadow: 0 4px 10px rgba(41, 128, 185, 0.5);
}

.size-chart {
  background: #fff;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

.size-chart h3 {
  font-size: 20px;
  margin-bottom: 10px;
}

.size-chart table {
  width: 100%;
  border-collapse: collapse;
}

.size-chart th,
.size-chart td {
  padding: 10px;
  text-align: center;
  border: 1px solid #ddd;
}
</style>