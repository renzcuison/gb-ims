<template>
  <div class="verification-container">
    <h1>Email Verification</h1>

    <p v-if="isVerifying">🔄 Verifying your email...</p>
    <p v-else-if="verificationStatus === 'success'">
      ✅ Your email has been verified successfully! Redirecting...
    </p>
    <p v-else-if="verificationStatus === 'failure'">❌ Verification failed. Please try again.</p>
    <p v-else-if="verificationStatus === 'no-token'">⚠️ Authentication token is missing. Please log in again.</p>
    <p v-else-if="verificationStatus === 'invalid-url'">⚠️ Invalid or missing verification link.</p>
    <p v-else-if="verificationStatus === 'error'">🚫 An error occurred while verifying your email. Please try again
      later.</p>
  </div>
</template>

<script>
export default {
  data() {
    return {
      isVerifying: false,
      verificationStatus: null,
    };
  },
  mounted() {
    const queryParams = new URLSearchParams(window.location.search);
    const encodedUrl = queryParams.get('url');

    if (!encodedUrl) {
      this.verificationStatus = 'invalid-url';
      return;
    }

    const token = localStorage.getItem('authToken');

    if (!token) {
      this.verificationStatus = 'no-token';
      return;
    }

    this.verifyEmail(encodedUrl, token);
  },
  methods: {
    async verifyEmail(url, token) {
      this.isVerifying = true;

      try {
        const response = await fetch(decodeURIComponent(url), {
          method: 'GET',
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json',
          },
        });

        if (response.ok) {
          this.verificationStatus = 'success';

          // Redirect to homepage after 3 seconds
          setTimeout(() => {
            this.$router.push('/shop'); // Adjust this if your main page is different
          }, 3000);
        } else {
          this.verificationStatus = 'failure';
        }
      } catch (error) {
        console.error('Verification error:', error);
        this.verificationStatus = 'error';
      } finally {
        this.isVerifying = false;
      }
    },
  },
};
</script>

<style scoped>
.verification-container {
  max-width: 500px;
  margin: 2rem auto;
  padding: 1.5rem 2rem;
  text-align: center;
  font-family: Arial, sans-serif;
  border: 1px solid #ccc;
  border-radius: 12px;
  box-shadow: 0 0 12px rgba(0, 0, 0, 0.05);
}
</style>
