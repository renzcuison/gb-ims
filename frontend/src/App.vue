<template>
  <div class="wrapper">
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
      <aside class="sidebar" :class="{ 'collapsed': sidebarClosed }">
        <button class="toggle-btn" @click="sidebarClosed = !sidebarClosed">
          {{ sidebarClosed ? '>' : '<' }} </button>
            <div class="user-info">
              <p>Access: admin<span>{{ username }}</span></p>
              <h2>Hello, admin<span>{{ username }}</span>!</h2>
              <div class="activity-report">
                <p>Details about the user's recent activities.</p>
              </div>
            </div>
      </aside>

      <div class="content-area">
        <nav class="horizontal-nav">
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
const sidebarClosed = ref(false);


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
.wrapper {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background-color: #f5f5f5;
  font-family: Verdana, Geneva, Tahoma, sans-serif;
}

.icon-image {
  width: 40px;
  height: 40px;
  object-fit: contain;
}

.navbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background-color: #0a3992;
  color: #fff;
  padding: 12px 20px;
  position: relative;
  height: 60px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.navbar-brand {
  display: flex;
  align-items: center;
}

.brand-text {
  text-decoration: none;
  color: white;
  font-size: 20px;
  transition: color 0.3s ease-in-out;
  font-weight: normal;
  cursor: pointer;
}

.brand-text:hover {
  color: #f5d742;
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
  width: 70px;
  border-radius: 10%;
  object-fit: cover;
}

.hamburger-menu {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  width: 20px;
  height: 17px;
  cursor: pointer;
  transition: transform 0.3s ease;
}

.hamburger-menu span {
  display: block;
  height: 3px;
  background-color: white;
  border-radius: 3px;
  transition: all 0.3s ease;
}

.hamburger-menu:hover span {
  background-color: #ccc;
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

.sidebar {
  width: 250px;
  background-color: #ffffff;
  color: #fff;
  padding: 20px;
  text-align: left;
  flex-shrink: 0;
  outline: 3px solid #0a3992;
  outline-offset: -2px;
  box-shadow: 0px 6px 25px rgba(0, 0, 0, 0.2);
  transition: width 0.3s ease-in-out;

  position: fixed;
  top: 60px;
  bottom: 0;
  left: 0;
}

.sidebar.collapsed {
  transform: translateX(-250px);
}

.content-area {
  margin-left: 250px;
}

.sidebar.collapsed+.content-area {
  margin-left: 0;
}

.toggle-btn {
  position: absolute;
  top: 45%;
  left: 105%;
  background-color: #fff;
  color: grey;
  border: none;
  padding: 2px 5px;
  cursor: pointer;
  border-radius: 50%;
  box-shadow: 0px 2px 6px rgba(0, 0, 0, 0.2);
  font-size: 10px;
}

.toggle-btn:hover {
  background-color: #f5d742;
}

.user-info p {
  font-size: 0.9rem;
  font-weight: normal;
  margin-bottom: 5px;
  color: grey;
}

.user-info h2 {
  font-size: 1.2rem;
  color: black;
}

.user-info .activity-report {
  margin-top: 20px;
  color: black;
}

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

.horizontal-nav {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 20px;
  background-color: #11095c;
  padding: 20px;
}

.nav-card {
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  background-color: #ffffff;
  color: #000;
  text-decoration: none;
  width: 250px;
  height: 100px;
  border-radius: 10px;
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
  font-size: 1rem;
  font-weight: bold;
  transition: all 0.3s ease;
  padding: 10px;
  gap: 10px;
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
  color: #11095c;
}

.dropdown {
  position: relative;
  /* z-index: 1; */
}

.dropdown-btn {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 20px;
  padding: 10px 15px;
  font-size: 1rem;
  background-color: transparent;
  border: none;
  color: inherit;
  cursor: pointer;
  text-align: center;
  width: 100%;
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

.main-content {
  padding: 20px;
  background-color: #fff;
  flex-grow: 1;
}

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
    gap: 15px;
  }

  .nav-card {
    width: 100px;
    height: 100px;
    font-size: 0.9rem;
  }

  .dropdown-menu {
    width: 130px;
    left: 50%;
    transform: translateX(-50%);
  }
}
</style>