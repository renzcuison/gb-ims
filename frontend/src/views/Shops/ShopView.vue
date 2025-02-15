<template>
  <div class="shop-view">
    <!-- Header Section -->
    <header class="shop-header">
      <nav>
        <ul class="menu">
          <li><a href="/shop">SHOP</a></li>
          <!-- <li><a href="#">WELLNESS STUDY</a></li>
          <li><a href="#">SIZE CHART</a></li> -->
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

    <!-- Items Section -->
    <section class="items">
      <div
        class="item"
        v-for="item in items"
        :key="item.id"
        @click="goToItemDetails(item.id)"
        style="cursor: pointer;"
      >
        <!-- Placeholder box for item image -->
        <div class="placeholder-image">No Image</div>
        <p>{{ item.item_name }}</p>
        <p>Price: {{ item.price_per_unit }}</p>
      </div>
    </section>
  </div>
</template>

<script>
export default {
  data() {
    return {
      items: [],
      cart: [],
    };
  },
  computed: {
    cartQuantity() {
      return this.cart.reduce((total, item) => total + item.quantity, 0);
    },
  },
  created() {
    this.fetchItems();
    this.updateCartQuantity();
    
    const savedCart = localStorage.getItem("cart");
    if (savedCart) {
      this.cart = JSON.parse(savedCart);
    }
  },
  methods: {
    updateCartQuantity() {
      const savedCart = localStorage.getItem("cart");
      if (savedCart) {
        this.cart = JSON.parse(savedCart);
      }
    },

    async fetchItems() {
      try {
        const response = await fetch('http://localhost:8001/api/stocks');
        if (!response.ok) {
          throw new Error('Failed to fetch items');
        }
        const data = await response.json();
        this.items = data.stocks;
      } catch (error) {
        console.error('Error fetching items:', error);
      }
    },
    goToItemDetails(itemId) {
      this.$router.push({ name: 'ItemDetails', params: { id: itemId } });
    },
  },
};
</script>

<style scoped>
.shop-view {
  font-family: Arial, sans-serif;
  color: #000;
  text-align: center;
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

.cart-icon {
  font-size: 20px;
  position: relative;
}

.cart-count {
  background: red;
  color: white;
  font-size: 12px;
  border-radius: 50%;
  padding: 2px 6px;
  position: absolute;
  top: -10px;
  right: -10px;
}

.items {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin: 20px 0;
}

.item {
  text-align: center;
}

.placeholder-image {
  width: 200px;
  height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f0f0f0;
  color: #888;
  font-size: 14px;
  margin: 0 auto 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.item p {
  font-weight: bold;
}

.btn-add {
  background-color: #28a745;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 5px;
  cursor: pointer;
  font-weight: bold;
}

.btn-add:hover {
  background-color: #218838;
}
</style>
