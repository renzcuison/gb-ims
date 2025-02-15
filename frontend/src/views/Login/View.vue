<template>
  <div class="page-wrapper">
    <div class="overlay"></div>
    <div class="login-container">
      <img src="/src/assets/companylogo.jpg" alt="GreatBuy Logo" class="brand-logo" />
      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label for="name">Username</label>
          <input type="text" id="name" v-model="username" required />
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <div class="password-container">
            <input :type="isPasswordVisible ? 'text' : 'password'" id="password" v-model="password" required />
            <button type="button" @click="togglePasswordVisibility" class="password-toggle">
              <span v-if="isPasswordVisible">👁️‍🗨️</span>
              <span v-else>👁️</span>
            </button>
          </div>
        </div>
        <button type="submit">Login</button>
      </form>
      <p>
        Don't have an account?
        <RouterLink class="r1" to="/login/create">Register here</RouterLink>
      </p>
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
    };
  },
  methods: {
    async handleLogin() {
      if (!this.username || !this.password) {
        alert("Please enter valid credentials.");
        return;
      }

      try {
        await fetch("http://localhost:8001/sanctum/csrf-cookie", {
          method: "GET",
          credentials: "same-origin",
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
          credentials: "same-origin",
        });

        const data = await response.json();

        if (response.ok) {
          // ✅ Login success: Store token and redirect
          localStorage.setItem("authToken", data.token);

          // Trigger fade effect only on successful login
          const pageWrapper = document.querySelector(".page-wrapper");
          pageWrapper.classList.add("fade-out");

          // Redirect after fade effect
          setTimeout(() => {
            this.$router.push("/items");
          }, 500);
        } else {
          // ❌ Login failed: Show error message and prevent redirection
          alert(data.message || "Login failed. Please check your credentials.");
        }
      } catch (error) {
        console.error("Login error:", error);
        alert("An error occurred while trying to log in. Please try again.");
      }
    }



  },
};
</script>


<style scoped>
.page-wrapper {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: url("/images/login_img.png") no-repeat center center/cover;
  display: flex;
  justify-content: center;
  align-items: center;
}

.overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  backdrop-filter: blur(5px);
  background: rgba(255, 255, 255, 0);
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
  margin: 50px auto;
  position: relative;
  padding: 50px;
  border: 1px solid #ddd;
  border-radius: 10px;
  background-color: white;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

h1 {
  text-align: center;
  color: black;
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 25px;
}

.form-group {
  margin-bottom: 15px;
}

label {
  display: block;
  margin-bottom: 5px;
  color: black;
}

input {
  width: 100%;
  padding: 10px;
  margin: 5px 0 15px;
  background-color: white;
  border: 1px solid black;
  border-radius: 5px;
  color: black;
  font-size: 1rem;
}

input:focus {
  border-color: black;
  outline: none;
}

button {
  width: 100%;
  padding: 10px;
  background-color: #0a3992;
  color: white;
  border: none;
  border-radius: 5px;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

button:hover {
  background-color: #e0a800;
}

p {
  margin-top: 20px;
  text-align: center;
  color: black;
}

RouterLink {
  color: black;
  text-decoration: none;
}

RouterLink:hover {
  text-decoration: underline;
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

.password-toggle {
  background: none;
  border: none;
  width: 20%;
  font-size: 1.2rem;
  cursor: pointer;
  color: black;
  position: absolute;
  right: 0px;
  top: 50%;
  transform: translateY(-60%);
}
</style>
