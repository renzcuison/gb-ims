<template>
  <div class="page-wrapper">
    <div class="overlay"></div>
    <div class="login-container">
      <img src="/src/assets/companylogo.jpg" alt="GreatBuy Logo" class="brand-logo" />
      <h1>Create an Account</h1>

      <ul class="alert alert-warning" v-if="Object.keys(errorList).length > 0">
        <li v-for="(error, index) in errorList" :key="index">
          {{ error[0] }}
        </li>
      </ul>

      <form @submit.prevent="handleCreateAccount">
        <div class="form-group">
          <label for="newUsername">Username</label>
          <input type="text" id="newUsername" v-model="user.name" required />
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" v-model="user.email" required />
        </div>

        <div class="form-group">
          <label for="phoneNumber">Phone Number</label>
          <input type="text" id="phoneNumber" v-model="user.phone_number" pattern="^(09\d{9}|\+639\d{9})$"
            title="Please enter a valid PH phone number (e.g., 09123456789 or +639123456789)" required />
        </div>

        <div class="form-group">
          <label for="newPassword">Password</label>
          <div class="password-container">
            <input :type="isPasswordVisible ? 'text' : 'password'" id="newPassword" v-model="user.password" required />
            <button type="button" @click="togglePasswordVisibility" class="password-toggle">
              <span v-if="isPasswordVisible">👁️‍🗨️</span>
              <span v-else>👁️</span>
            </button>
          </div>
        </div>

        <button type="submit">Create Account</button>
      </form>

      <p>
        Already have an account?
        <RouterLink class="r1" to="/login">Login here</RouterLink>
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: "CreateLoginView",
  data() {
    return {
      user: {
        name: "",
        email: "",
        password: "",
        phone_number: "",
      },
      isPasswordVisible: false,
      errorList: {},
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
            const errorData = await response.json();
            this.errorList = errorData.errors;
            throw new Error("Validation errors occurred.");
          }
          throw new Error("Failed to create account.");
        }

        alert("Account created successfully! Please check your email for a verification link.");
        this.user = { name: "", email: "", password: "", phone_number: "" };
        this.errorList = {};

        setTimeout(() => {
          this.$router.push("/login");
        }, 500);
      } catch (error) {
        console.error(error);
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
