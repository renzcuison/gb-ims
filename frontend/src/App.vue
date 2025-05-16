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

const handleLogout = () => {
  localStorage.removeItem('authToken');
  localStorage.removeItem('user_token');
  localStorage.removeItem('user_role');
  router.push('/login');
};

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
  const urlParams = new URLSearchParams(window.location.search);
  const token = urlParams.get('token');
  const role = urlParams.get('role');

  if (token) {

    localStorage.setItem('authToken', token);
    localStorage.setItem('user_token', token);
    if (role) {
      localStorage.setItem('user_role', role);
    }

    try {
      const res = await fetch('http://localhost:8001/api/user', {
        method: 'GET',
        headers: {
          Authorization: `Bearer ${token}`,
        },
        credentials: 'include',
      });

      const user = await res.json();
      console.log('OAuth User:', user);

      window.history.replaceState({}, document.title, '/shop');


      if (role === 'admin') {
        router.replace('/stocks');
      } else {
        router.replace('/shop');
      }
    } catch (err) {
      console.error('Failed to fetch user after OAuth login', err);
      router.replace('/login');
    }
  } else {
    await fetchUserData();
  }

  console.log('isAdmin:', isAdmin.value);
});
</script>
