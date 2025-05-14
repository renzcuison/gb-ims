<template>
  <div>
    <header class="navbar">
      <div class="navbar-brand">
        <RouterLink :to="{ path: '/shop' }" class="brand-text">GREATBUY</RouterLink>
        <RouterLink :to="{ path: '/shop' }" class="brand-sub">ORIGINALS</RouterLink>
      </div>
      <div class="navbar-links">
        <a href="/shop">SHOP</a>
        <a href="https://www.facebook.com/profile.php?id=100075567471861" target="_blank">ABOUT US</a>
      </div>
    </header>

    <div class="login-wrapper">
      <form @submit.prevent="handleCreateAccount" class="login-form">
        <h2 class="login-header">CREATE ACCOUNT</h2>

        <ul class="alert" v-if="Object.keys(errorList).length > 0">
          <li v-for="(error, index) in errorList" :key="index">{{ error[0] }}</li>
        </ul>

        <div class="form-group">
          <label>USERNAME</label>
          <input type="text" v-model="user.name" required />
        </div>

        <div class="form-group">
          <label>EMAIL</label>
          <input type="email" v-model="user.email" required />
        </div>

        <div class="form-group">
          <label>PHONE NUMBER</label>
          <input type="text" v-model="user.phone_number" pattern="^(09\d{9}|\+639\d{9})$" title="Enter valid PH number"
            required />
        </div>

        <div class="form-group">
          <label>PASSWORD</label>
          <div class="password-wrapper">
            <input :type="isPasswordVisible ? 'text' : 'password'" v-model="user.password" required />
            <img v-if="user.password" :src="isPasswordVisible ? '/eye-slash.png' : '/eye.png'"
              @click="togglePasswordVisibility" class="eye-icon" alt="Toggle" />
          </div>
        </div>

        <button type="submit">REGISTER</button>

        <button class="google" type="button" @click="handleGoogleSignIn">
          <img src="/google.png" alt="Google Icon" class="google-icon" />
          SIGN IN WITH GOOGLE
        </button>

        <button class="redirect-button" @click="$router.push('/login')">Already have an account? Login here</button>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: "CreateAccountView",
  data() {
    return {
      user: {
        name: "",
        email: "",
        password: "",
        phone_number: "",
      },
      errorList: {},
      isPasswordVisible: false,
    };
  },
  methods: {
    async handleCreateAccount() {
      try {
        const response = await fetch("http://localhost:8001/api/users", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(this.user),
        });

        if (!response.ok) {
          if (response.status === 422) {
            const data = await response.json();
            this.errorList = data.errors;
            return;
          }
          throw new Error("Something went wrong.");
        }

        alert("Account created successfully! Please check your email.");
        this.user = { name: "", email: "", password: "", phone_number: "" };
        this.errorList = {};
        this.$router.push("/login");
      } catch (error) {
        console.error(error);
      }
    },
    togglePasswordVisibility() {
      this.isPasswordVisible = !this.isPasswordVisible;
    },
    handleGoogleSignIn() {
      window.location.href = "http://localhost:8001/api/auth/redirect/google";
    }

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

body {
  background-color: #ffffff;
}

.navbar {
  width: 100%;
  height: 60px;
  background-color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 10%;
  position: fixed;
  top: 0;
  left: 0;
  z-index: 100;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.navbar-brand {
  display: flex;
  align-items: center;
}

.brand-text {
  font-size: 22px;
  color: #0086E7;
  font-weight: bold;
  text-decoration: none;
}

.brand-sub {
  font-size: 10px;
  color: #0086E7;
  margin-left: 5px;
  text-decoration: none;
  margin-top: 10px;
}

.navbar-links a {
  font-size: 12px;
  color: black;
  margin-left: 20px;
  text-decoration: none;
}

.login-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  padding-top: 100px;
  min-height: 100vh;
  background-color: #ffffff;
}

.login-form {
  background: white;
  padding: 30px;
  width: 100%;
  max-width: 350px;
  border-radius: 6px;
  box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
}

.login-form h2 {
  text-align: center;
  margin-bottom: 20px;
  font-weight: 600;
  font-size: 20px;
}

.alert {
  list-style: none;
  padding: 10px;
  background-color: #ffe6e6;
  color: #d8000c;
  margin-bottom: 15px;
  border-radius: 4px;
}

.form-group {
  margin-bottom: 15px;
}

label {
  display: block;
  font-size: 11px;
  margin-bottom: 5px;
  font-weight: 600;
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

.password-wrapper {
  position: relative;
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

button {
  width: 100%;
  padding: 8px;
  background-color: #ffffff;
  border: 1px solid #0086E7;
  border-radius: 5px;
  font-size: 11px;
  cursor: pointer;
  transition: background-color 0.3s ease;
  margin-top: 20px;
  appearance: none;
  color: #0086E7;
  font-weight: 500;
}

button:hover {
  background-color: #0086E7;
  color: white;
}

.divider {
  text-align: center;
  margin-top: 20px;
  font-size: 11px;
  color: #000000;
}

.google {
  width: 100%;
  padding: 8px;
  background-color: #ffffff;
  border: 1px solid #000000;
  border-radius: 5px;
  font-size: 11px;
  cursor: pointer;
  transition: background-color 0.3s ease;
  margin-top: 15px;
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

.redirect-button {
  display: block;
  width: 100%;
  padding: 8px;
  background-color: #ffffff;
  border: 1px solid #0086E7;
  border-radius: 5px;
  font-size: 11px;
  color: #0086E7;
  cursor: pointer;
  text-align: center;
  margin-top: 15px;
  font-weight: 500;
}

.redirect-button:hover {
  background-color: #0086E7;
  color: white;
}
</style>
