<template>
  <div>
    <h1>Email Verification</h1>
    <p v-if="isVerifying">Verifying your email...</p>
    <p v-if="verificationStatus === 'success'">Your email has been verified successfully!</p>
    <p v-if="verificationStatus === 'already-verified'">Your email is already verified.</p>
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
    // Retrieve token from localStorage or sessionStorage
    const token = localStorage.getItem("authToken");

    if (!token) {
      this.verificationStatus = "no-token"; // Show error if no token is found
      return;
    }

    // Fetch the authenticated user's data
    fetch("http://localhost:8001/api/user", {
      method: "GET",
      headers: {
        Authorization: `Bearer ${token}`,
        "Content-Type": "application/json",
      },
    })
      .then((response) => response.json())
      .then((user) => {
        if (user.email_verified_at) {
          console.log("Email is already verified. Skipping verification.");
          this.verificationStatus = "already-verified";
          return;
        }

        // Get the URL parameters if verification is still needed
        const queryParams = new URLSearchParams(window.location.search);
        const verificationUrl = queryParams.get("url");
        const signature = queryParams.get("signature");

        console.log("Verification URL:", verificationUrl);
        console.log("Signature:", signature);

        if (verificationUrl && signature) {
          this.verifyEmail(verificationUrl, signature);
        } else {
          console.error("Missing verification URL or signature.");
          this.verificationStatus = "invalid-url";
        }
      })
      .catch((error) => {
        console.error("Error fetching user:", error);
        this.verificationStatus = "error";
      });
  },
  methods: {
    verifyEmail(verificationUrl, signature) {
      this.isVerifying = true;

      // Retrieve token from localStorage
      const token = localStorage.getItem("authToken");

      if (!token) {
        this.verificationStatus = "no-token"; // Show error if no token is found
        this.isVerifying = false;
        return;
      }

      // Use the fetch API to make the GET request
      fetch(`${decodeURIComponent(verificationUrl)}?signature=${signature}`, {
        method: "GET",
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "application/json",
        },
      })
        .then((response) => {
          if (response.ok) {
            this.verificationStatus = "success"; // Successful verification
          } else {
            throw new Error("Verification failed"); // Handle failed verification
          }
        })
        .catch((error) => {
          console.error("Verification error:", error);
          this.verificationStatus = "failure"; // Show failure status
        })
        .finally(() => {
          this.isVerifying = false; // Stop verifying indicator
        });
    },
  },
};
</script>
