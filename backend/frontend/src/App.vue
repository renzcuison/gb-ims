<script setup>
  import { RouterLink, RouterView } from 'vue-router'
  import HelloWorld from './components/HelloWorld.vue'
</script>

<template>
  <header>
    <div class="wrapper">
      <nav class="navbar mb-5 navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
          <RouterLink class="navbar-brand" to="/items">SNS CA-PH</RouterLink>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-3 mb-lg-0">
              <li class="nav-item">
                <RouterLink class="nav-link" to="/stocks">Stocks</RouterLink>
              </li>
              <li class="nav-item">
                <RouterLink class="nav-link" to="/transactions">Transactions</RouterLink>
              </li>
              <li class="nav-item">
                <RouterLink class="nav-link" to="/categories">Categories</RouterLink>
              </li>
              <li class="nav-item">
                <RouterLink class="nav-link" to="/suppliers">Suppliers</RouterLink>
              </li>
              <li class="nav-item">
                <RouterLink class="nav-link" to="/employees">Employees</RouterLink>
              </li>
              <li class="nav-item">
                <RouterLink class="nav-link" to="/customers">Customers</RouterLink>
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </div>
  </header>
  <RouterView />
</template>

<style scoped>
.nav-item {
  display: inline-block;
  margin-right: 10px;
}
</style>