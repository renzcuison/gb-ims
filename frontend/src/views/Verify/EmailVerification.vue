<template>
  <div>
    <h1>Email Verification</h1>
    <p v-if="isVerifying">Verifying your email...</p>
    <p v-if="verificationStatus === 'success'">Your email has been verified successfully!</p>
    <p v-if="verificationStatus === 'failure'">Verification failed. Please try again.</p>
    <p v-if="verificationStatus === 'no-token'">Authentication token is missing. Please log in again.</p>
    <p v-if="verificationStatus === 'invalid-url'">Unverified. Please check the email verification link.</p>
    <p v-if="verificationStatus === 'error'">An error occurred while verifying your email. Please try again later.</p>
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
    // Get the URL parameters
    const queryParams = new URLSearchParams(window.location.search);
    const verificationUrl = queryParams.get('url'); // Extract the URL parameter
    const signature = queryParams.get('signature'); // Extract the signature parameter
    
    console.log("Verification URL:", verificationUrl);
    console.log("Signature:", signature);

    // Check if both parameters are present
    if (verificationUrl && signature) {
      this.verifyEmail(verificationUrl, signature);
    } else {
      console.error("Missing verification URL or signature.");
      this.verificationStatus = 'invalid-url'; // Handle error if URL or signature is missing
    }
  },
  methods: {
    verifyEmail(verificationUrl, signature) {
      this.isVerifying = true;

      // Retrieve token from localStorage or sessionStorage
      const token = localStorage.getItem('authToken'); 

      if (!token) {
        this.verificationStatus = 'no-token';  // Show error if no token is found
        this.isVerifying = false;
        return;
      }

      // Use the fetch API to make the GET request
      fetch(`${decodeURIComponent(verificationUrl)}?signature=${signature}`, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        }
      })
        .then(response => {
          if (response.ok) {
            this.verificationStatus = 'success';  // Successful verification
          } else {
            throw new Error('Verification failed');  // Handle failed verification
          }
        })
        .catch(error => {
          console.error("Verification error:", error);
          this.verificationStatus = 'failure';  // Show failure status
        })
        .finally(() => {
          this.isVerifying = false;  // Stop verifying indicator
        });
    }
  }
};
</script>
