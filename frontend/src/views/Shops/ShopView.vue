<template>
  <div class="wrapper">
    <header class="navbar">
      <div class="navbar-brand">
        <RouterLink :to="{ path: '/shop' }" class="brand-text">GREATBUY</RouterLink>
        <RouterLink :to="{ path: '/shop' }" class="brand-text-follow">ORIGINALS</RouterLink>
      </div>
      <div class="navbar-center">
        <a href="shop">SHOP</a>
        <a href="/order">ORDERS</a>
        <a href="login">MY ACCOUNT</a>
        <a href="https://www.facebook.com/profile.php?id=100075567471861" target="_blank">ABOUT US</a>
      </div>
      <div class="navbar-right">
        <a href="stocks">
          <button v-if="isAdmin" class="icon-button">
            <img src="/star.png" alt="Bag" class="icon-image-star">
          </button>
        </a>
        <button class="icon-button">
          <img src="/search.png" alt="Search" class="icon-image">
        </button>
        <button class="icon-button" onclick="window.location.href='/orders'">
          <img src="/bag.png" alt="Bag" class="icon-image">
        </button>
      </div>
    </header>

    <div class="hero">
      <div class="hero">
        <div class="hero-text top-left">NEW ARRIVALS</div>
        <img src="/hero1.jpg" alt="Hero 1" class="hero-image">
        <img src="/hero2.jpg" alt="Hero 2" class="hero-image">
        <img src="/hero3.jpg" alt="Hero 3" class="hero-image">
        <div class="hero-text bottom-right">POLO RALPH LAUREN</div>
      </div>
    </div>

    <section id="brands-section" class="brands-section">
      <h2 class="brands-header">BRANDS</h2>
      <div class="arrows-container">
        <img src="/arrow.png" alt="Left Arrow" class="left-arrow">
        <img src="/arrow.png" alt="Right Arrow" class="right-arrow">
      </div>
      <div class="top-brands">
        <button v-for="brand in topBrands" :key="brand" @click="filterBrand(brand)"
          :class="{ active: selectedBrand === brand }" class="brand-button">
          {{ brand }}
        </button>
      </div>
      <div class="brands-container">
        <div class="brand-item">
          <img src="/nike.jpg" alt="Nike">
          <div class="brand-name">Nike</div>
        </div>
        <div class="brand-item">
          <img src="/nike.jpg" alt="Adidas">
          <div class="brand-name">Adidas</div>
        </div>
        <div class="brand-item">
          <img src="/nike.jpg" alt="Guess">
          <div class="brand-name">Guess</div>
        </div>
        <div class="brand-item">
          <img src="/nike.jpg" alt="TommyH">
          <div class="brand-name">TommyH</div>
        </div>
        <div class="brand-item">
          <img src="/nike.jpg" alt="BBW">
          <div class="brand-name">BBW</div>
        </div>
        <div class="brand-item">
          <img src="/nike.jpg" alt="Levi's">
          <div class="brand-name">Levis</div>
        </div>
        <div class="brand-item">
          <img src="/nike.jpg" alt="Crocs">
          <div class="brand-name">Crocs</div>
        </div>
        <div class="brand-item">
          <img src="/nike.jpg" alt="Gap">
          <div class="brand-name">Gap</div>
        </div>
      </div>
    </section>

    <section class="shop-all-section">
      <div class="shop-all-header">
        <h2 class="shop-all-title">SHOP ALL</h2>

        <div class="top-brands">
          <button v-for="brand in topBrands" :key="brand" @click="filterBrand(brand)"
            :class="{ active: selectedBrand === brand }" class="brand-button">
            {{ brand }}
          </button>
        </div>
      </div>
      <div class="search-container">
        <div v-if="!showSearch" class="search-icon-label" @click="toggleSearch">
          <img src="/search.png" alt="Search Icon" class="search-icon">
          <span class="search-text">Search</span>
        </div>

        <input ref="searchInput" v-show="showSearch" type="text" v-model="searchQuery" placeholder="Search for item"
          class="search-bar" @blur="hideSearch">
      </div>
    </section>

    <div class="shop-view">
      <section class="items">
        <div class="item" v-for="item in items" :key="item.id" @click="goToItemDetails(item.id)"
          style="cursor: pointer;">
          <div class="placeholder-image"></div>
          <p>{{ item.item_name }}</p>
          <p :class="{ 'out-of-stock': Number(item.on_hand) === 0 }" v-if="Number(item.on_hand) === 0">❌ Out of Stock
          </p>
          <p class="out-of-stock-price">₱{{ item.price_per_unit }}</p>
        </div>
      </section>
    </div>

    <footer class="shop-footer">
      <div class="footer-wrapper">
        <div class="footer-left">
          <h2 class="footer-header">CONTACT US</h2>
          <div class="contact-icons">
            <img src="/facebook.png" alt="Facebook" class="footer-icon">
            <img src="/email.png" alt="Email" class="footer-icon">
          </div>
        </div>
        <p class="dial-info">or dial us thru (082) 240 9707</p>
      </div>

      <div class="store-info">
        <h2 class="footer-header">VISIT OUR STORE</h2>
        <p>Simeon, De Jesus St, Poblacion District, Davao City, 8000 Davao del Sur</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import { ref, watch, onMounted, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from "axios";

axios.defaults.baseURL = "http://localhost:8001/api";
axios.defaults.withCredentials = true;
axios.defaults.headers.common["Authorization"] = `Bearer ${localStorage.getItem("authToken")}`;

const route = useRoute();
const router = useRouter();
const isLoginPage = ref(route.name === 'login');
const isRegisterPage = ref(route.name === 'create-login');
const username = ref("");
const isAdmin = ref(false);

const showSearch = ref(false);
const searchInput = ref(null);
const searchQuery = ref("");

const toggleSearch = () => {
  showSearch.value = !showSearch.value;

  nextTick(() => {
    if (searchInput.value) {
      if (showSearch.value) {
        searchInput.value.classList.add("search-bar-active");
        searchInput.value.focus();
      } else {
        searchInput.value.classList.remove("search-bar-active");
      }
    }
  });
};

const hideSearch = () => {
  showSearch.value = false;
};

const topBrands = ["Nike", "Adidas", "Guess", "TommyH"];
const selectedBrand = ref("Nike");
const filterBrand = (brand) => {
  selectedBrand.value = brand;
};

watch(() => route.name, (newRoute) => {
  isLoginPage.value = newRoute === 'login';
  isRegisterPage.value = newRoute === 'create-login';
});

const fetchUserData = async () => {
  try {
    const token = localStorage.getItem('authToken');

    if (!token) {
      console.error("No auth token found.");
      handleLogout();
      return;
    }

    const response = await fetch('http://localhost:8001/api/user', {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });

    if (response.status === 401) {
      console.error("Unauthorized: Token may be invalid.");
      handleLogout();
      return;
    }

    if (!response.ok) {
      throw new Error(`Failed to fetch user data. Status: ${response.status}`);
    }

    const data = await response.json();
    console.log("User data:", data);

    isAdmin.value = data.role && data.role.toLowerCase() === 'admin';
    username.value = data.name || "Admin";
  } catch (error) {
    console.error('Error fetching user data:', error);
    isAdmin.value = false;
  }
};

onMounted(async () => {
  await fetchUserData();
  console.log('isAdmin:', isAdmin.value);
});


const handleLogout = () => {
  localStorage.removeItem('authToken');
  router.push('/login');
  closeHamburgerDropdown();
};
</script>

<script>
export default {

  data() {
    return {
      items: [],
      cart: [],
      stockItem: null,
    };
  },

  computed: {
    cartQuantity() {
      return this.cart.reduce((total, item) => total + item.quantity, 0);
    },
  },

  created() {
    this.fetchItems();
    this.fetchItemAndStockDetails();
    this.updateCartQuantity();

    const savedCart = localStorage.getItem("cart");
    if (savedCart) {
      this.cart = JSON.parse(savedCart);
    }
  },

  methods: {
    async fetchItemAndStockDetails() {
      const itemId = this.$route.params.id;
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

        // ✅ Assign correct stock data including on_hand
        this.stockItem = {
          on_hand: Number(stock.on_hand),
        };
      } catch (error) {
        console.error("Error fetching stock details:", error);
      }
    },

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

        // Assuming each stock has item_name, on_hand, and price_per_unit
        this.items = data.stocks.map(stock => ({
          ...stock
        }));
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

html {
  scroll-behavior: smooth;
}

* {
  font-family: 'Kantumruy Pro', sans-serif;
}

.items {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-start;
  gap: 20px;
  margin: 25px 11%;
}

.placeholder-image {
  width: 250px;
  height: 350px;
  display: flex;
  background-image: url('/sample.jpg');
  background-size: cover;
  background-position: center;
  margin: 0 auto 10px;
  border: 1px solid #ffffff;
  flex-shrink: 0;
}

.item p.out-of-stock {
  /* color: rgb(184, 184, 184); */
  font-weight: 100;
}

.item p.out-of-stock-price {
  font-weight: bold;
}

.item p {
  margin: 3px 0;
  font-size: 12px;
  text-transform: uppercase;
}

.item p:nth-of-type(2) {
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

.navbar-right {
  display: flex;
  align-items: center;
  margin-right: 10%;
}

.icon-button {
  background: none;
  border: none;
  cursor: pointer;
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

.navbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background-color: rgb(255, 255, 255);
  color: #fff;
  position: relative;
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
  margin-top: 10px
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

.main-layout {
  display: flex;
  flex-grow: 1;
  color: black;
}

.content-area {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.hero {
  display: flex;
  width: 100%;
  height: calc(100vh - 50px);
  overflow: hidden;
}

.hero-image {
  flex: 1;
  object-fit: cover;
  width: 100%;
  height: 100%;
}

.main-content {
  padding: 20px;
  background-color: #fff;
  flex-grow: 1;
}

.hero-text {
  position: absolute;
  color: white;
  font-size: 36px;
  font-weight: bold;
}

.top-left {
  top: 60px;
  left: 20px;
}

.bottom-right {
  bottom: 5px;
  right: 20px;
}

.brands-section {
  text-align: center;
  padding: 30px 0;
  background-color: white;
  position: relative;
}

.brands-header {
  margin-top: 10px;
  font-size: 24px;
  font-weight: 600;
  color: black;
  text-transform: uppercase;
  margin-bottom: 0;
}

.top-brands-wrapper {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  position: relative;
}

.top-brands {
  display: flex;
  justify-content: center;
  gap: 10px;
  margin-top: 10px;
}

.brand-button {
  background: none;
  border: none;
  color: black;
  font-size: 12px;
  font-weight: normal;
  cursor: pointer;
  transition: 0.2s ease;
}

.brand-button.active {
  font-weight: bold;
  text-decoration: underline;
}

.arrows-container {
  position: absolute;
  width: 100%;
  display: flex;
  justify-content: space-between;
  transform: translateY(-50%);
  pointer-events: none;
}


.left-arrow,
.right-arrow {
  cursor: pointer;
  width: 35px;
  height: 35px;
  pointer-events: all;
}


.left-arrow {
  margin-left: 11%;
  transform: scaleX(-1);
}


.right-arrow {
  margin-right: 11%;
}

.brands-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 40px;
  justify-content: center;
  margin: 40px auto 0;
  margin-left: 11%;
  margin-right: 11%;
}

.brand-item {
  position: relative;
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.2s ease-in-out;
}

.brand-item img {
  width: 100%;
  height: auto;
  display: block;
  transition: opacity 0.3s ease-in-out;
}

.brand-name {
  position: absolute;
  bottom: 0;
  width: 100%;
  padding: 90px;
  background: rgba(0, 0, 0, 0.25);
  color: white;
  font-size: 20px;
  font-weight: bold;
  text-align: center;
  transition: transform 0.2s ease-in-out;
}

.brand-item:hover img {
  opacity: 0.8;
}

.brand-item:hover .brand-name {
  transform: scale(1.1);
}

.brand-item:active {
  transform: scale(0.95);
}

.shop-all-section {
  display: flex;
  justify-content: space-between;
  background-color: white;
  padding: 10px 11%;
  margin-bottom: -20px;
}

.shop-all-header {
  display: flex;
  align-items: center;
  gap: 20px;
}

.shop-all-title {
  font-size: 24px;
  font-weight: bold;
  margin-top: 10px;
}

.search-container {
  display: flex;
  align-items: center;
  gap: 10px;
}

.search-icon {
  width: 12px;
  height: 12px;
  cursor: pointer;
}

.search-text {
  font-size: 12px;
  margin-left: 5px;
  cursor: pointer;
}

.search-bar {
  width: 265px;
  height: 40px;
  padding: 10px;
  font-size: 12px;
  border: 1px solid #ccc;
  outline: none;
  opacity: 0;
  transform: scale(0.9);
  transition: opacity 0.3s ease, transform 0.2s ease;
  display: block;
  visibility: hidden;
}

.search-bar-active {
  opacity: 1 !important;
  transform: scale(1);
  visibility: visible;
}

.shop-footer {
  background-color: #ffffff;
  padding: 30px 11%;
  border-top: 1px solid rgb(229, 229, 229);
  width: 100%;
  display: flex;
  flex-direction: row;
  align-items: flex-start;
  justify-content: space-between;
}

.footer-left {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-top: 10px;
}

.footer-header {
  font-size: 24px;
  font-weight: bold;
  white-space: nowrap;
}

.store-info {
  margin-top: 10px;
  text-align: right;
  font-size: 14px;
  color: black;
  white-space: nowrap;
}

.contact-icons {
  display: flex;
  justify-content: center;
  gap: 15px;
}

.footer-icon {
  width: 25px;
  height: 25px;
  object-fit: cover;
  margin-top: -9px;
}

.dial-info {
  font-size: 14px;
  color: black;
  margin-left: 0%;
  white-space: nowrap;
}

.footer-wrapper {
  display: flex;
  flex-direction: column;
}
</style>