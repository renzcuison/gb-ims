<template>
  <RouterView />
</template>

<script setup>
import { RouterView } from 'vue-router';
import { ref, watch, onMounted } from 'vue';
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
};
</script>
