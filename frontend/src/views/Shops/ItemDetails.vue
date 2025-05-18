<template>
  <div class="shop-view">
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
          <button class="icon-button">
            <img src="/star.png" alt="Bag" class="icon-image-star">
          </button>
        </a>
        <button class="icon-button">
          <img src="/search.png" alt="Search" class="icon-image">
        </button>
        <button class="icon-button" @click="goToOrder">
          <img src="/bag.png" alt="Bag" class="icon-image">
        </button>
      </div>
    </header>

    <section class="item-section">
      <nav class="breadcrumb">
        <a href="/shop">Home&nbsp;</a>/ {{ item.name }}
      </nav>

      <h1 class="item-title-centered">{{ item.name }}</h1>

      <div class="item-main">
        <div class="item-gallery">
          <div class="item-image"></div>
        </div>
        <div class="item-details">
          <p class="item-price">₱{{ item.price }}</p>
          <div class="item-description">
            <p>{{ item.description }}</p>
          </div>

          <p v-if="stockItem && stockItem.on_hand > 0">✅ In Stock</p>
          <p v-if="stockItem && stockItem.on_hand > 0">
            Available pieces: {{ stockItem.on_hand }}
          </p>
          <p v-else-if="stockItem && stockItem.on_hand === 0">
            ❌ Out of Stock
          </p>
          <p v-else>
            Loading stock info...
          </p>

          <div class="quantity-selector">
            <button class="quantity-btn" @click="decreaseQuantity" :disabled="isOutOfStock">-</button>
            <span class="quantity-display">{{ quantity }}</span>
            <button class="quantity-btn" @click="increaseQuantity"
              :disabled="isOutOfStock || quantity >= stockItem?.on_hand">
              +
            </button>
          </div>
          <div class="actions">
            <button class="buy-now" @click="buyNow" :disabled="isOutOfStock"
              :class="{ 'disabled-button': isOutOfStock }">
              Buy Now
            </button>

            <button class="add-to-cart" @click="createOrder" :disabled="isOutOfStock"
              :class="{ 'disabled-button': isOutOfStock }">
              Add to Cart
            </button>
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
      stockItem: null,
      quantity: 1,
      cart: [],
      customerOrderId: null,
    };
  },
  created() {
    this.fetchItemAndStockDetails();
    this.loadCart();
  },
  computed: {
    isOutOfStock() {
      return this.stockItem?.on_hand === 0;
    }
  },
  methods: {
    async fetchItemAndStockDetails() {
      const itemId = String(this.$route.params.id); // 🛠 Fix: Ensure ID is a string
      if (!itemId) {
        console.error("Item ID is missing");
        return;
      }

      try {
        const response = await fetch(`http://localhost:8001/api/stocks/${itemId}`);
        const data = await response.json();

        if (!data || !data.stock) {
          console.error("Stock not found");
          return;
        }

        const stock = data.stock;

        this.item = {
          id: String(stock.id),
          name: stock.item_name || "No name available",
          price: stock.price_per_unit || "0",
          description: stock.description || "No description available",
        };

        this.stockItem = {
          on_hand: Number(stock.on_hand),
        };
      } catch (error) {
        console.error("Error fetching stock details:", error);
      }
    },

    loadCart() {
      const savedCart = localStorage.getItem("cart");
      if (savedCart) {
        this.cart = JSON.parse(savedCart);
      }

      const savedOrderId = localStorage.getItem("customer_order_id");
      if (savedOrderId) {
        this.customerOrderId = savedOrderId;
      }
    },

    increaseQuantity() {
      if (this.stockItem && this.quantity < this.stockItem.on_hand) {
        this.quantity++;
      }
    },
    decreaseQuantity() {
      if (this.quantity > 1) {
        this.quantity--;
      }
    },
    goToOrder() {
      window.location.href = '/order';
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
    async createOrder() {
      if (!this.item.id) {
        alert("Error: Stock ID is missing.");
        return;
      }

      const customerOrderId = localStorage.getItem("customer_order_id");
      const token = localStorage.getItem("authToken");

      if (!customerOrderId || !token) {
        alert("Missing order ID or auth token. Please log in again.");
        return;
      }

      try {
        // 1️⃣ Fetch current cart items from backend
        const existingRes = await fetch(`http://localhost:8001/api/orders?customer_order_id=${customerOrderId}`, {
          headers: {
            Accept: "application/json",
            Authorization: `Bearer ${token}`,
          },
        });

        const existingOrders = await existingRes.json();

        // 2️⃣ Check if item already exists in cart
        const matchingOrder = existingOrders.find(order => order.stock_id === this.item.id);

        if (matchingOrder) {
          // 3️⃣ Item exists → update quantity
          const updatedQuantity = matchingOrder.quantity + this.quantity;

          const updateRes = await fetch(`http://localhost:8001/api/orders/${matchingOrder.id}`, {
            method: "PUT",
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: `Bearer ${token}`,
            },
            body: JSON.stringify({
              customer_order_id: customerOrderId,
              stock_id: this.item.id,
              quantity: updatedQuantity,
              price_per_unit: this.item.price,
            }),
          });

          if (!updateRes.ok) {
            const err = await updateRes.json();
            throw new Error(err.message || "Failed to update cart item.");
          }

          alert("Cart updated with additional quantity!");

        } else {
          // 4️⃣ Item doesn't exist → create new order
          const createRes = await fetch("http://localhost:8001/api/orders", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
              Authorization: `Bearer ${token}`,
            },
            body: JSON.stringify({
              customer_order_id: customerOrderId,
              stock_id: this.item.id,
              quantity: this.quantity,
            }),
          });

          if (!createRes.ok) {
            const error = await createRes.json();
            throw new Error(error.message || "Failed to add to cart.");
          }

          alert("Item added to cart!");
        }

      } catch (error) {
        console.error("❌ Cart operation failed:", error.message);
        alert("Error updating cart. Please try again.");
      }
    },
  },
};
</script>


<style scoped>
@font-face {
  font-family: 'LibreCaslonDisplay-Regular';
  src: url('/assets/LibreCaslonDisplay-Regular.ttf') format('truetype');
}

@font-face {
  font-family: 'Kantumruy Pro';
  src: url('/assets/KantumruyPro-VariableFont_wght.ttf') format('truetype');
  font-weight: 100 900;
  font-style: normal;
}

.shop-view {
  font-family: 'Kantumruy Pro', sans-serif;
  color: #000;
  background-color: white;
  margin: 0 auto;
  max-width: 1140px;
  padding: 0 20px;
}

.shop-header {
  background-color: white;
  border-bottom: 1px solid #ddd;
  max-width: 1140px;
  margin: 0 auto;
  padding: 10px 350px;
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

.menu {
  list-style: none;
  display: flex;
  justify-content: flex-start;
  align-items: center;
  gap: 40px;
  padding: 0;
  margin: 0;
}

.menu a {
  font-size: 12px;
  text-transform: uppercase;
  text-decoration: none;
  font-weight: 400;
  color: black;
  transition: color 0.3s ease;
}

.menu a:hover {
  color: #0086E7;
}

.cart-icon {
  position: relative;
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

.cart-count {
  position: absolute;
  top: -6px;
  right: -10px;
  background: #0086E7;
  color: white;
  font-size: 10px;
  padding: 2px 6px;
  border-radius: 50%;
}

.item-section {
  padding: 40px 11%;
}

/* Centered product title */
.item-title-centered {
  font-size: 26px;
  font-weight: bold;
  text-align: center;
  margin-bottom: 40px;
  color: black;
  text-transform: uppercase;
}

.breadcrumb {
  font-size: 12px;
  color: #999;
  margin-bottom: 20px;
  text-transform: uppercase;
}

.breadcrumb a {
  color: #0086E7;
  text-decoration: none;
}

.breadcrumb a:hover {
  text-decoration: underline;
}

/* Layout alignment for image + product info */
.item-main {
  display: flex;
  flex-wrap: wrap;
  gap: 40px;
  justify-content: center;
  align-items: flex-start;
  margin: 0 auto;
}

.item-image {
  width: 300px;
  height: 400px;
  background-image: url('/sample.jpg');
  background-size: cover;
  background-position: center;
  border: 1px solid #ccc;
  flex-shrink: 0;
}

.item-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  max-width: 400px;
  min-width: 280px;
}

.item-title {
  font-family: 'LibreCaslonDisplay-Regular';
  font-size: 24px;
  color: #0086E7;
  margin-bottom: 5px;
}

.item-price {
  font-size: 22px;
  font-weight: bold;
  margin: 10px 0 20px;
  color: #000;
}

.item-description p {
  font-size: 12px;
  color: #333;
  line-height: 1.6;
}

.quantity-selector {
  display: flex;
  align-items: center;
  gap: 15px;
  margin: 15px 0;
}

.quantity-btn {
  width: 40px;
  height: 40px;
  font-size: 18px;
  font-weight: bold;
  background-color: #f5f5f5;
  border: 1px solid #ccc;
  border-radius: 4px;
  cursor: pointer;
}

.quantity-display {
  font-size: 14px;
  font-weight: bold;
  width: 30px;
  text-align: center;
}

.actions {
  display: flex;
  gap: 10px;
  margin-top: 20px;
  flex-wrap: wrap;
}

.buy-now,
.add-to-cart {
  background-color: #000000;
  color: white;
  border: none;
  padding: 10px 20px;
  font-size: 12px;
  text-transform: uppercase;
  font-weight: bold;
  border-radius: 5px;
  cursor: pointer;
}

.buy-now:hover {
  background-color: #006ec4;
}

.add-to-cart:hover {
  background-color: #0086E7;
}

.disabled-button {
  background-color: #ccc !important;
  color: #666 !important;
  cursor: not-allowed !important;
  pointer-events: none;
  border: none;
}
</style>
