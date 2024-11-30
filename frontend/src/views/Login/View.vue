<template>
  <div class="page-wrapper">
    <div class="login-container">
      <h1>Login</h1>
      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label for="name">Name</label>
          <input type="text" id="name" v-model="username" required />
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <div class="password-container">
            <input
              :type="isPasswordVisible ? 'text' : 'password'"
              id="password"
              v-model="password"
              required
            />
            <button
              type="button"
              @click="togglePasswordVisibility"
              class="password-toggle"
              aria-label="Toggle password visibility"
            >
              👁️
            </button>
          </div>
        </div>
        <button type="submit">Login</button>
      </form>
      <p>
        Don't have an account?
        <RouterLink to="/login/create">Register here</RouterLink>
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
        const pageWrapper = document.querySelector('.page-wrapper');
    pageWrapper.classList.add('fade-out');
      if (this.username && this.password) {
        try {
          // Step 1: Request the CSRF token
          await fetch("http://localhost:8001/sanctum/csrf-cookie", {
            method: "GET",
            credentials: "same-origin", 
          });

          // Step 2: Send the login request
          const response = await fetch("http://localhost:8001/api/login", {
            method: "POST", // Using POST since we're sending credentials
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
            // Login successful, store the token
            localStorage.setItem("authToken", data.token);

            // Redirect to the main page (e.g., /items)
            this.$router.push("/items");
          } else {
            alert(data.message || "Login failed. Please check your credentials.");
          }
        } catch (error) {
          console.error("Login error:", error);
          alert("An error occurred while trying to log in. Please try again.");
        }
      } else {
        alert("Please enter valid credentials.");
      }
    },
    
    togglePasswordVisibility() {
      this.isPasswordVisible = !this.isPasswordVisible;
    },
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
  background-color: #121212; 
  display: flex;
  justify-content: center;
  align-items: center;
  opacity: 1;
  transition: opacity 0.5s ease; 
}
.page-wrapper.fade-out {
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.5s ease;
}

/* Login Box */
.login-container {
  max-width: 400px;
  margin: 50px auto;
  padding: 20px;
  border: 1px solid #ddd;
  border-radius: 5px;
  background-color: #0e0e0e; 
  color: #ffc107;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

h1 {
  text-align: center;
  color: #ffc107;
  font-size: 2rem;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 15px;
}

label {
  display: block;
  margin-bottom: 5px;
  color: #ffc107;
}

input {
  width: 100%;
  padding: 10px;
  margin: 5px 0 15px;
  background-color: #333;
  border: 1px solid #555;
  border-radius: 5px;
  color: #ffc107;
  font-size: 1rem;
}

input:focus {
  border-color: #ffc107;
  outline: none;
}

button {
  width: 100%;
  padding: 10px;
  background-color: #ffc107;
  color: #0e0e0e;
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
  color: #fff;
}

RouterLink {
  color: #ffc107;
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
  color: #ffc107;
  position: absolute;
  right: 0px; 
  top: 50%;
  transform: translateY(-60%); 
}
</style>
