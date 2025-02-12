<template>
  <div class="wrapper">
    <!-- Top Navbar -->
    <header class="navbar">
      <div class="navbar-brand">
        <RouterLink :to="{ path: '/shop' }" class="brand-text">GreatBuy</RouterLink>
        <img src="/src/assets/companylogo.jpg" alt="GreatBuy Logo" class="brand-logo" />
      </div>
      <div class="hamburger-menu" @click="toggleHamburgerMenu">
        <span></span>
        <span></span>
        <span></span>
      </div>
      <div v-if="hamburgerDropdownVisible" class="hamburger-dropdown">
        <button class="dropdown-item" @click="handleLogout">
          <FontAwesomeIcon :icon="faSignOutAlt" style="margin-right: 8px;" />
          Logout
        </button>
      </div>
    </header>

    <div class="main-layout">
      <!-- Sidebar -->
      <aside class="sidebar">
        <div class="user-info">
          <p>access: <span>{{ username }}</span></p>
          <h2>Hello, <span>{{ username }}</span>!</h2>
          <div class="activity-report">
            <h3>Activity Report</h3>
            <p>Details about the user's recent activities.</p>
          </div>
        </div>
      </aside>

      <!-- Main Content Area -->
      <div class="content-area">
        <!-- Horizontal Nav -->
        <nav class="horizontal-nav">
          <!-- Dropdown for Stocks -->
          <div class="nav-card dropdown">
            <button @click="toggleDropdown" class="dropdown-btn">
              <div>
                <img src="/src/assets/warehouse.png" alt="Warehouse Icon" class="icon-image" />
              </div>
              <span>INVENTORY</span>
            </button>
            <div v-if="dropdownVisible" class="dropdown-menu">
              <RouterLink @click="closeDropdown" :to="{ path: '/items' }" class="dropdown-item">Items</RouterLink>
              <RouterLink @click="closeDropdown" :to="{ path: '/stocks' }" class="dropdown-item">Stocks</RouterLink>
              <RouterLink @click="closeDropdown" :to="{ path: '/suppliers' }" class="dropdown-item">Suppliers
              </RouterLink>
              <RouterLink @click="closeDropdown" :to="{ path: '/categories' }" class="dropdown-item">Category
              </RouterLink>
            </div>
          </div>
          <!-- Other Nav Cards -->
          <RouterLink :to="{ path: '/customers' }" class="nav-card">
            <div>
              <img src="/src/assets/rating.png" alt="customers" class="icon-image" />
            </div>
            <span>CUSTOMERS</span>
          </RouterLink>
          <RouterLink :to="{ path: '/employees' }" class="nav-card">
            <div>
              <img src="/src/assets/officer.png" alt="employees" class="icon-image" />
            </div>
            <span>EMPLOYEES</span>
          </RouterLink>
          <RouterLink :to="{ path: '/order' }" class="nav-card">
            <div>
              <img src="/src/assets/logistics.png" alt="orders" class="icon-image" />
            </div>
            <span>ORDERS</span>
          </RouterLink>
          <RouterLink :to="{ path: '/shop' }" class="nav-card">
            <div>
              <img src="/src/assets/shopping.png" alt="shop" class="icon-image" />
            </div>
            <span>SHOP</span>
          </RouterLink>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
          <RouterView />
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { RouterLink, RouterView } from 'vue-router';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faSignOutAlt } from '@fortawesome/free-solid-svg-icons';
import { ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const isLoginPage = ref(route.name === 'login');
const isRegisterPage = ref(route.name === 'create-login');
const dropdownVisible = ref(false);
const hamburgerDropdownVisible = ref(false);


watch(() => route.name, (newRoute) => {
  isLoginPage.value = newRoute === 'login';
  isRegisterPage.value = newRoute === 'create-login';
});

const toggleDropdown = () => {
  dropdownVisible.value = !dropdownVisible.value;
};

const closeDropdown = () => {
  dropdownVisible.value = false;
};

const handleLogout = () => {
  localStorage.removeItem('authToken');
  router.push('/login');
  closeHamburgerDropdown();
};

const toggleHamburgerMenu = () => {
  hamburgerDropdownVisible.value = !hamburgerDropdownVisible.value;
};

</script>

<style scoped>
/* Global Wrapper */
.wrapper {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background-color: #f5f5f5;
  font-family: Verdana, Geneva, Tahoma, sans-serif;
}

.brand-text {
  text-decoration: none;
  color: white;
  font-size: 1.5rem;
  font-weight: normal;
  cursor: pointer;
}

.icon-image {
  width: 40px;
  /* Set the desired width */
  height: 40px;
  /* Set the desired height */
  object-fit: contain;
  /* Ensures the image scales without distortion */
}

/* Navbar (Top) */
.navbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background-color: #0a3992;
  color: #fff;
  padding: 10px 20px;
  position: relative;
}

.navbar-content {
  display: flex;
  align-items: center;
  width: 100%;
  position: relative;
}

.brand-logo {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  height: auto;
  width: 80px;
  border-radius: 10%;
  object-fit: cover;
}

.hamburger-menu {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  width: 30px;
  /* Width of the hamburger icon */
  height: 20px;
  /* Height of the hamburger icon */
  cursor: pointer;
}

.hamburger-menu span {
  display: block;
  height: 3px;
  /* Thickness of the lines */
  background-color: white;
  /* Match the navbar's color scheme */
  border-radius: 3px;
  transition: all 0.3s ease;
  /* Smooth animation */
}

/* Optional: Add a hover effect */
.hamburger-menu:hover span {
  background-color: #ccc;
  /* Change color on hover */
}

.hamburger-dropdown {
  position: absolute;
  top: 100%;
  right: 20px;
  background-color: #0a3992;
  color: white;
  box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
  border-radius: 8px;
  padding: 10px 0;
  z-index: 1000;
}

.hamburger-dropdown .dropdown-item {
  display: block;
  padding: 10px 15px;
  text-align: center;
  font-size: 1rem;
  color: white;
  text-decoration: none;
  background-color: transparent;
  border: none;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.hamburger-dropdown .dropdown-item:hover {
  background-color: #4a148c;
}

/* Sidebar */
.sidebar {
  width: 250px;
  background-color: #ffffff;
  color: #fff;
  padding: 20px;
  text-align: left;
  flex-shrink: 0;
  outline: 3px solid #0a3992;
  /* Adds an outline with a color */
  outline-offset: -2px;
  /* Adjusts the distance between the element and its outline */
  box-shadow: 0px 6px 25px rgba(0, 0, 0, 0.2);
}

.user-info p {
  color: black;
}

.user-info h2 {
  font-size: 1.5rem;
  color: black;
}

.user-info .activity-report {
  margin-top: 20px;
  color: black;
}

/* Content Layout */
.main-layout {
  display: flex;
  flex-grow: 1;
  color: black;
  box-shadow: 6px 0px 25px rgba(0, 0, 0, 0.2);
}

.content-area {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

/* Horizontal Navigation */
.horizontal-nav {
  display: flex;
  justify-content: space-around;
  gap: 20px;
  background-color: #11095c;
  padding: 20px 0;
  flex-wrap: wrap;
  /* Allow cards to wrap on smaller screens */
}

.nav-card {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  background-color: #ffffff;
  color: #000;
  text-decoration: none;
  width: 320px;
  height: 80px;
  border-radius: 10px;
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
  font-size: 1rem;
  font-weight: bold;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  margin-bottom: 15px;
  padding: 10px 15px;
  gap: 15px;
}

.nav-card:hover {
  transform: scale(1.05);
  box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.3);
  background-color: #d1d1ff;
}

.nav-card span {
  font-size: 1rem;
  font-weight: bold;
}

.card-icon {
  font-size: 2rem;
  margin-bottom: 0;
  /* Remove bottom margin */
  color: #11095c;
  /* Match your color scheme */
}

/* Dropdown Menu */
.dropdown {
  position: relative;
}

.dropdown-btn {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 20px;
  /* Adds spacing between the icon and text */
  padding: 10px 15px;
  /* Adds padding around the button content */
  font-size: 1rem;
  background-color: transparent;
  border: none;
  color: inherit;
  cursor: pointer;
  text-align: center;
  width: 100%;
  /* Ensures the button spans the width of its container */
}

.dropdown-menu {
  position: absolute;
  top: 130%;
  left: 50%;
  transform: translateX(-50%);
  background-color: #11095c;
  border-radius: 10px;
  box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
  padding: 10px 0;
  z-index: 10;
  width: 150px;
}

.dropdown-item {
  display: block;
  padding: 10px 15px;
  color: #fff;
  text-decoration: none;
  font-size: 0.9rem;
  text-align: center;
  transition: background-color 0.3s ease;
}

.dropdown-item:hover {
  background-color: #4a148c;
  border-radius: 5px;
}

/* Main Content */
.main-content {
  padding: 20px;
  background-color: #fff;
  flex-grow: 1;
}

/* Responsive Styles */
@media (max-width: 768px) {
  .wrapper {
    flex-direction: column;
  }

  .sidebar {
    width: 100%;
    height: auto;
  }

  .main-layout {
    flex-direction: column;
  }

  .horizontal-nav {
    justify-content: center;
    /* Center nav cards on small screens */
    gap: 15px;
    /* Reduce gap between cards on smaller screens */
  }

  .nav-card {
    width: 100px;
    /* Smaller size for small screens */
    height: 100px;
    font-size: 0.9rem;
    /* Smaller font size */
  }

  /* Ensure dropdown menu is centered */
  .dropdown-menu {
    width: 130px;
    left: 50%;
    transform: translateX(-50%);
  }
}
</style>