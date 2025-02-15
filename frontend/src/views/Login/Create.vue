<template>
  <div class="page-wrapper" :class="{ fadeOut: isAccountCreated }">
    <div class="create-login-container">
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
            <button type="button" @click="togglePasswordVisibility" class="password-toggle"
              aria-label="Toggle password visibility">
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
        phone_number: "",
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

        this.user = { name: "", email: "", password: "", phone_number: "" };
        this.errorList = {};

        this.isAccountCreated = true;

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
/* Background Styling */
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
  opacity: 1;
}

.page-wrapper.fadeOut {
  opacity: 0;
}

/* Container Design */
.create-login-container {
  max-width: 400px;
  padding: 40px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 1);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
  text-align: center;
}

/* Centered Logo */
.brand-logo {
  width: 120px;
  border-radius: 10%;
  object-fit: cover;
  display: block;
  margin: 0 auto 20px;
}

/* Heading */
h1 {
  text-align: center;
  color: #0a3992;
  font-size: 1.8rem;
  font-weight: bold;
  margin-bottom: 20px;
}

/* Form Group */
.form-group {
  margin-bottom: 15px;
  text-align: left;
}

label {
  display: block;
  font-weight: 600;
  margin-bottom: 5px;
  color: #0a3992;
}

/* Input Fields */
input {
  width: 100%;
  padding: 10px;
  border: 1px solid #aaa;
  border-radius: 5px;
  font-size: 1rem;
  background-color: white;
  color: #0a3992;
}

input:focus {
  border-color: #0a3992;
  outline: none;
}

/* Buttons */
button {
  width: 100%;
  padding: 12px;
  background-color: #0a3992;
  color: white;
  border: none;
  border-radius: 5px;
  font-size: 1rem;
  cursor: pointer;
  transition: 0.3s ease-in-out;
}

button:hover {
  background-color: #082a72;
}

/* Password Visibility Toggle */
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
  color: #0a3992;
  position: absolute;
  right: 0px;
  top: 50%;
  transform: translateY(-50%);
}

/* Alerts */
.alert {
  margin-top: 15px;
  padding: 10px;
  border-radius: 5px;
}

.alert-warning {
  background-color: #ffc107;
  color: #0e0e0e;
}

.alert-success {
  background-color: #28a745;
  color: white;
}
</style>
