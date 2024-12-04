<template>
  <div class="page-wrapper" :class="{ fadeOut: isAccountCreated }">
    <div class="create-login-container">
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
          <input
            type="text"
            id="phoneNumber"
            v-model="user.phone_number"
            pattern="^(09\d{9}|\+639\d{9})$"
            title="Please enter a valid PH phone number (e.g., 09123456789 or +639123456789)"
            required
          />
        </div>
        <div class="form-group">
          <label for="newPassword">Password</label>
          <div class="password-container">
            <input
              :type="isPasswordVisible ? 'text' : 'password'"
              id="newPassword"
              v-model="user.password"
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
        <button type="submit">Create Account</button>
      </form>
      <div v-if="isAccountCreated" class="alert alert-success">
        <p>Your account was created successfully! Please check your email for a verification link.</p>
      </div>
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
        phone_number: "",  // New phone number field
      },
      isPasswordVisible: false,
      isAccountCreated: false,
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

        const data = await response.json();
        alert("Account created successfully! Please check your email for a verification link.");

        // Reset form fields and errors
        this.user = { name: "", email: "", password: "", phone_number: "" };
        this.errorList = {};

        // Trigger fade effect
        this.isAccountCreated = true;

        // Redirect to login page after a delay
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
  background-color: #121212;
  display: flex;
  justify-content: center;
  align-items: center;
  opacity: 1;
  transition: opacity 0.5s ease;
}

.page-wrapper.fadeOut {
  opacity: 0;
}

.create-login-container {
  max-width: 400px;
  padding: 20px;
  border: 1px solid #ddd;
  border-radius: 5px;
  background-color: #0e0e0e;
  color: #ffc107;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
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
  width: 20%;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  color: #ffc107;
  position: absolute;
  right: 0px;
  top: 50%;
  transform: translateY(-60%);
}

.alert.alert-success {
  margin-top: 20px;
  background-color: #28a745;
  color: white;
  padding: 10px;
  border-radius: 5px;
}
</style>

