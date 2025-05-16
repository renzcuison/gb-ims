<template>
  <div class="page-wrapper">
    <header class="navbar">
      <div class="navbar-brand">
        <RouterLink :to="{ path: '/shop' }" class="brand-text">GREATBUY</RouterLink>
        <RouterLink :to="{ path: '/shop' }" class="brand-text-follow">ORIGINALS</RouterLink>
      </div>
      <div class="navbar-right">
        <a href="shop">SHOP</a>
        <a href="https://www.facebook.com/profile.php?id=100075567471861" target="_blank">ABOUT US</a>
      </div>
    </header>


    <div class="login-container">
      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <h2 class="login-header">LOGIN</h2>
          <label for="name">USERNAME OR EMAIL ADDRESS</label>
          <input type="text" id="name" v-model="username" required />
        </div>
        <div class="form-group">
          <label for="password">PASSWORD</label>
          <div class="password-container">
            <input :type="isPasswordVisible ? 'text' : 'password'" id="password" v-model="password" required />
            <img v-if="password.length > 0" :src="isPasswordVisible ? '/eye-slash.png' : '/eye.png'"
              @click="togglePasswordVisibility" class="eye-icon" alt="Toggle Password" />
          </div>
        </div>
        <div class="form-group1 remember-me-container">
          <input class="chkbox" type="checkbox" id="rememberMe" v-model="rememberMe" />
          <label for="rememberMe" class="remember-me-label">REMEMBER ME</label>
          <span class="forgot-password" @click="handleForgotPassword">FORGOT PASSWORD</span>
        </div>
        <button type="submit">LOGIN</button>

        <button @click="redirectToRegister" type="button" class="register-btn">
          Don't have a google account? Register here
        </button>
        <button class="google" type="button" @click="handleGoogleSignIn">
          <img src="/google.png" alt="Google Icon" class="google-icon" />
          SIGN IN WITH GOOGLE
        </button>

      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: "LoginView",
  data() {
    return {
      username: "",
      password: "",
      isPasswordVisible: false,
      rememberMe: false,
    };
  },
  methods: {
    togglePasswordVisibility() {
      this.isPasswordVisible = !this.isPasswordVisible;
    },
    handleGoogleSignIn() {
      window.location.href = "http://localhost:8001/api/auth/redirect/google";
    },
    redirectToRegister() {
      this.$router.push("/register");
    },
    async handleLogin() {
      if (!this.username || !this.password) {
        alert("Please enter valid credentials.");
        return;
      }

      try {
        await fetch("http://localhost:8001/sanctum/csrf-cookie", {
          method: "GET",
          credentials: "include",
        });

        const response = await fetch("http://localhost:8001/api/login", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            name: this.username,
            password: this.password,
          }),
          credentials: "include",
        });

        const data = await response.json();

        if (response.ok) {
          localStorage.setItem("authToken", data.token);
          localStorage.setItem("user_role", data.role);
          localStorage.setItem("user_token", data.token);

          const userResponse = await fetch("http://localhost:8001/api/user", {
            method: "GET",
            headers: { Authorization: `Bearer ${data.token}` },
            credentials: "include",
          });

          const userData = await userResponse.json();

          window.location.reload();
        } else {
          alert(data.message || "Login failed. Please check your credentials.");
          window.location.reload();
        }
      } catch (error) {
        console.error("Login error:", error);
        alert("An error occurred while trying to log in. Please try again.");
        window.location.reload();
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

* {
  font-family: 'Kantumruy Pro', sans-serif;
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

.navbar-right {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-right: 10%;
  position: relative;
}

.navbar-right a {
  font-family: 'Kantumruy Pro', sans-serif;
  font-size: 12px;
  color: black;
  text-decoration: none;
  transition: color 0.3s ease;
  margin-top: 5px;
  font-weight: 400;
}

.brand-logo {
  width: 150px;
  border-radius: 10%;
  object-fit: cover;
  display: block;
  margin: 0 auto 40px;
}

.page-wrapper.fade-out {
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.5s ease;
}

.login-container {
  top: 30px;
  margin: 50px auto;
  position: relative;
  padding: 50px;
  border: 1px solid #e9e9e9;
  border-radius: 5px;
  background-color: white;
  width: 400px;
  height: 500px;
}

.login-container label {
  font-size: 12px;
}

h1 {
  text-align: center;
  color: black;
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 25px;
}

.login-header {
  font-weight: 600;
  margin-bottom: 30px;
  font-size: 24px;
}

.form-group {
  margin-bottom: 10px;
}

.form-group1 {
  margin-top: -15px;
}

label {
  display: block;
  margin-bottom: 5px;
  color: black;
}

input {
  width: 100%;
  margin: 5px 0 15px;
  background-color: rgb(238, 238, 238);
  border: 1px solid rgb(238, 238, 238);
  border-radius: 5px;
  color: black;
  font-size: 12px;
  padding: 5px 10px;
}

.chkbox {
  width: 5%;
  width: 12px;
  height: 12px;
}

.remember-me-container {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  width: 100%;
}

.remember-me-container input.chkbox {
  margin-right: 10px;
  margin-bottom: 10px;
}

.remember-me-label {
  font-size: 12px;
  color: black;
}

input:focus {
  border-color: rgb(237, 237, 237);
  outline: none;
}

.forgot-password {
  font-size: 12px;
  color: #000000;
  text-decoration: none;
  cursor: pointer;
  margin-left: 74px;
  margin-bottom: 5px;
}

.forgot-password:hover {
  text-decoration: underline;
}

button {
  width: 100%;
  padding: 10px;
  background-color: #ffffff;
  border: 1px solid #0086E7;
  border-radius: 5px;
  font-size: 12px;
  cursor: pointer;
  transition: background-color 0.3s ease;
  margin-top: 30px;
  appearance: none;
  color: #0086E7;
  font-weight: 500;
}

button:hover {
  background-color: #0086E7;
  color: white
}

p {
  margin-top: 20px;
  text-align: center;
  color: rgb(0, 0, 0);
}

RouterLink {
  color: rgb(0, 0, 0);
  text-decoration: none;
}

RouterLink:hover {
  text-decoration: underline;
}

.eye-icon {
  width: 12px;
  height: 12px;
  cursor: pointer;
  position: absolute;
  right: 10px;
  margin-top: 4px;
  transform: translateY(-50%);
}

.password-container {
  position: relative;
  display: flex;
  align-items: center;
}

.password-container input {
  width: 100%;
  padding-right: 30px;
}

input[type="password"]::-ms-reveal,
input[type="password"]::-ms-clear {
  display: none;
}

.seperator {
  display: flex;
  font-size: 12px;
  margin-top: 15px;
  margin-bottom: -15px;
  justify-content: center;
}

.register-btn {
  margin-top: 20px;
  padding: 10px;
  background-color: #0a3992;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.register-btn:hover {
  background-color: #082a72;
}

.google {
  width: 100%;
  padding: 10px;
  background-color: #ffffff;
  border: 1px solid #000000;
  border-radius: 5px;
  font-size: 12px;
  cursor: pointer;
  transition: background-color 0.3s ease;
  margin-top: 30px;
  appearance: none;
  color: #000000;
  font-weight: 500;
}

.google:hover {
  background-color: #000000;
  color: white;
}

.google .google-icon {
  width: 15px;
  height: 14px;
  margin-right: 5px;
}
</style>