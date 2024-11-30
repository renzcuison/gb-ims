<template>
  <div class="wrapper">
    <aside class="sidebar">
      <div class="sidebar-header">Menu</div>
      <ul class="nav flex-column">
        <li class="nav-item">
          <RouterLink :to="{ path: '/items' }" class="nav-link">
            <FontAwesomeIcon :icon="faBox" :size="iconSize" /> Items
          </RouterLink>
        </li>
        <li class="nav-item">
          <RouterLink :to="{ path: '/stocks' }" class="nav-link">
            <FontAwesomeIcon :icon="faWarehouse" :size="iconSize" /> Stocks
          </RouterLink>
        </li>
         <li class="nav-item">
          <RouterLink :to="{ path: '/shop' }" class="nav-link">
            <FontAwesomeIcon :icon="faShoppingCart" :size="iconSize" /> Order
          </RouterLink>
        </li> 
        <li class="nav-item">
          <RouterLink :to="{ path: '/categories' }" class="nav-link">
            <FontAwesomeIcon :icon="faList" :size="iconSize" /> Categories
          </RouterLink>
        </li>
        <li class="nav-item">
          <RouterLink :to="{ path: '/suppliers' }" class="nav-link">
            <FontAwesomeIcon :icon="faTruck" :size="iconSize" /> Suppliers
          </RouterLink>
        </li>
        <li class="divider"></li>
        <li class="nav-item">
          <RouterLink :to="{ path: '/employees' }" class="nav-link">
            <FontAwesomeIcon :icon="faUserTie" :size="iconSize" /> Employees
          </RouterLink>
        </li>
        <li class="nav-item">
          <RouterLink :to="{ path: '/customers' }" class="nav-link">
            <FontAwesomeIcon :icon="faUser" :size="iconSize" /> Customers
          </RouterLink>
        </li>
        <li class="divider"></li>
        <li class="nav-item">
          <button class="nav-link btn-signout" type="button" @click="handleLogout">
            <FontAwesomeIcon :icon="faSignOutAlt" :size="iconSize" /> Sign Out
          </button>
        </li>
      </ul>
    </aside>

    <main class="main-content">
      <RouterView />
    </main>
  </div>

  <header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
      <div class="container">
        <RouterLink class="navbar-brand":to="{ path: '/shop' }">GREATBUY ORIGINALS</RouterLink>
        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarSupportedContent"
          aria-controls="navbarSupportedContent"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>
      </div>
    </nav>
  </header>
</template>


<script setup>
import { RouterLink, RouterView } from 'vue-router';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faBox, faWarehouse, faShoppingCart, faList, faTruck, faUserTie, faUser, faSignOutAlt } from '@fortawesome/free-solid-svg-icons';
import { ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const isLoginPage = ref(route.name === 'login');
const isRegisterPage = ref(route.name === 'create-login'); // Assuming the register route name is 'create-login'

// Watch for changes in the route and update login and register states accordingly
watch(() => route.name, (newRoute) => {
  isLoginPage.value = newRoute === 'login';
  isRegisterPage.value = newRoute === 'create-login'; // Ensure this matches your actual route name
});

const handleLogout = () => {
  localStorage.removeItem('authToken');
  
  router.push('/login');
};
</script>

<style scoped>
.nav-link {
  display: flex;
  align-items: center;
  gap: 10px; 
  color: #fff;
  font-size: 1rem;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.nav-link:hover {
  background-color: #007bff;
  color: #fff;
}

.wrapper {
  display: flex;
  min-height: 100vh;
  padding-top: 56px; 
}

.sidebar {
  width: 250px;
  background-color: #333;
  color: #fff;
  padding: 20px;
  box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
  position: fixed; 
  height: 100vh; 
  top: 56px; 
  transition: transform 0.3s ease-in-out;
}

.sidebar-header {
  margin-bottom: 20px;
  font-size: 1.5rem;
  font-weight: bold;
  color: #fff;
}

.nav-item {
  margin-bottom: 15px;
}

.nav-link {
  color: #fff !important;
  font-size: 1rem;
  padding: 10px 15px;
  border-radius: 4px;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.nav-link:hover {
  color: #fff !important;
  background-color: #007bff;
}

.divider {
  margin: 15px 0;
  height: 1px;
  background-color: #555;
}

.btn-signout {
  background: none;
  border: none;
  color: #fff;
  font-size: 1rem;
  padding: 10px 15px;
  text-align: left;
  width: 100%;
  border-radius: 4px;
  transition: background-color 0.3s ease, color 0.3s ease;
  display: flex;
  align-items: center;
  gap: 10px;
}

.btn-signout:hover {
  background-color: #dc3545;
  color: #fff;
}

.main-content {
  flex-grow: 1;
  padding: 20px;
  margin-left: 250px; 
  transition: margin-left 0.3s ease;
}

.navbar {
  z-index: 999; 
  padding: 10px 15px;
}

.navbar-nav .nav-link {
  color: #fff !important;
  margin-right: 15px;
  transition: color 0.3s ease;
}

.navbar-nav .nav-link:hover {
  color: #00bcd4 !important;
}

@media (max-width: 768px) {
  .wrapper {
    flex-direction: column;
    padding-top: 100px; 
  }

  .sidebar {
    width: 100%;
    height: auto;
    box-shadow: none;
    position: relative;
    transform: translateX(-100%); 
  }

  .sidebar.open {
    transform: translateX(0); 
  }

  .main-content {
    padding: 10px;
    margin-left: 0;
  }
}
</style>
