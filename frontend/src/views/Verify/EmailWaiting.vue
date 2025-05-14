<template>
    <div class="waiting-container">
        <h2>📩 Check your inbox!</h2>
        <p>We sent you a verification email.</p>
        <p>Waiting for verification...</p>
        <p v-if="error" class="error">{{ error }}</p>
        <p v-if="loading">⏳ Checking...</p>
    </div>
</template>

<script>
export default {
    data() {
        return {
            intervalId: null,
            loading: false,
            error: null,
        };
    },
    mounted() {
        this.intervalId = setInterval(this.checkVerification, 1000);
    },
    beforeUnmount() {
        clearInterval(this.intervalId);
    },
    methods: {
        async checkVerification() {
            const token = localStorage.getItem('authToken');

            if (!token) {
                this.$router.push('/login');
                return;
            }

            this.loading = true;

            try {
                const res = await fetch('http://localhost:8001/api/user', {
                    method: 'GET',
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                });

                if (res.ok) {
                    const user = await res.json();

                    if (user.email_verified_at) {
                        clearInterval(this.intervalId);
                        this.$router.push('/shop');
                    }
                } else if (res.status === 401) {
                    localStorage.removeItem('authToken');
                    this.$router.push('/login');
                } else {
                    this.error = 'Failed to check verification.';
                }
            } catch (err) {
                console.error(err);
                this.error = 'An error occurred while checking verification.';
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>

<style scoped>
.waiting-container {
    max-width: 500px;
    margin: 3rem auto;
    padding: 2rem;
    text-align: center;
    font-family: sans-serif;
    border: 2px dashed #ccc;
    border-radius: 10px;
}

.error {
    color: red;
    margin-top: 10px;
}
</style>