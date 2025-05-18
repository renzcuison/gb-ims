<template>
    <header class="navbar">
        <div class="navbar-brand">
            <RouterLink :to="{ path: '/shop' }" class="brand-text">GREATBUY</RouterLink>
            <RouterLink :to="{ path: '/shop' }" class="brand-text-follow">DATABASE</RouterLink>
        </div>
        <div class="navbar-right">
            <img src="/profile.jpg" alt="User Profile" class="icon-image-profile" @click="toggleDropdown">
            <span class="user-name">{{ username }}</span>
            <button class="icon-button" @click="toggleDropdown">
                <img src="/drop.png" alt="Dropdown" class="icon-image">
            </button>
            <div v-if="dropdownVisible" class="dropdown-menu">
                <ul>
                    <li><button @click="handleLogout" class="dropdown-item">Logout</button></li>
                </ul>
            </div>
        </div>
    </header>

    <div class="wrapper">
        <div class="sidebar">
            <nav>
                <ul>
                    <li><router-link to="/stocks"
                            :class="{ 'router-link-active': $route.path.startsWith('/stocks') }"><img
                                src="/inventory.png" alt="Inventory" class="sidebar-icon" /> INVENTORY</router-link>
                    </li>
                    <li><router-link to="/suppliers" active-class="router-link-active"><img src="/supplier.png"
                                class="sidebar-icon"> SUPPLIERS</router-link></li>
                    <li><router-link to="/categories" active-class="router-link-active"><img src="/category.png"
                                class="sidebar-icon"> CATEGORIES</router-link></li>
                    <li><router-link to="/customers" active-class="router-link-active"><img src="/customer1.png"
                                class="sidebar-icon"> CUSTOMERS</router-link></li>
                    <li><router-link to="/employees" active-class="router-link-active"><img src="/employees.png"
                                class="sidebar-icon"> EMPLOYEES</router-link></li>
                    <li><router-link to="/admin/orders" active-class="router-link-active"><img src="/order.png"
                                class="sidebar-icon"> CUSTOMER ORDERS</router-link></li>
                    <li><router-link to="/shop" active-class="router-link-active"><img src="/shop.png"
                                class="sidebar-icon"> SHOP</router-link></li>
                </ul>
            </nav>
        </div>

        <div class="container mt-5">
            <h2 class="mb-4 text-center">Admin - Customers Orders Table</h2>

            <div v-if="orders.length === 0" class="alert alert-info text-center">No orders available.</div>

            <table v-else class="table table-bordered table-hover align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Order Code</th>
                        <th>Customer Name</th>
                        <th>Total Price</th>
                        <th>Quantity</th>
                        <th>Item Name</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Order Placed At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="order in orders" :key="order.id">
                        <tr @click="toggleRow(order.id)" style="cursor: pointer">
                            <td>{{ order.order_code }}</td>
                            <td>{{ order.customer_name }}</td>
                            <td>₱{{ parseFloat(order.total_price).toFixed(2) }}</td>
                            <td>{{ order.quantity || 'N/A' }}</td>
                            <td>{{ order.item_name || 'N/A' }}</td>
                            <td @click.stop>
                                <select v-model="order.status" @change="updateOrderStatus(order)"
                                    class="form-select form-select-sm" :disabled="!canManuallyChangeStatus(order)">
                                    <option v-for="status in statusOptions" :key="status" :value="status">{{ status }}
                                    </option>
                                </select>
                            </td>
                            <td>{{ order.payment_method === 'gcash' ? 'GCash' : 'Cash on Pickup' }}</td>
                            <td>{{ order.orderTime || 'N/A' }}</td>
                            <td @click.stop>
                                <template v-if="order.payment_method === 'gcash'">
                                    <div>
                                        <button class="btn btn-sm text-white mb-1" :style="{
                                            backgroundColor: order.status === 'Approved' ? 'green' : '#0086E7',
                                            cursor: order.status === 'Approved' ? 'default' : 'pointer'
                                        }" :disabled="order.status === 'Approved'" @click="verifyPayment(order)">
                                            {{ order.status === 'Approved' ? 'Verified' : 'Verify Payment' }}
                                        </button>
                                        <div class="text-sm mt-1">
                                            Verified by:
                                            <span v-if="order.verified_by">{{ order.verified_by }}</span>
                                            <span v-else class="text-muted">Unverified</span>
                                        </div>
                                        <button v-if="order.status === 'Approved'"
                                            class="btn btn-outline-danger btn-sm mt-1" @click="undoVerification(order)">
                                            Undo Verification
                                        </button>
                                    </div>
                                </template>
                                <!-- ✅ Show Unverify Button if Approved -->
                                <div v-if="order.payment_method === 'gcash' && order.status === 'Approved'"
                                    class="mt-3">
                                    <button class="btn btn-outline-danger btn-sm"
                                        @click.stop="togglePaymentStatus(order)">
                                        Unverify Payment
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="expandedOrders.includes(order.id)">
                            <td colspan="9">
                                <div class="p-3 text-start bg-light rounded">
                                    <p><strong>{{ order.payment_method === 'gcash' ? 'GCash Name' : 'Customer Name'
                                            }}:</strong> {{
                                                order.customer_name }}</p>
                                    <p><strong>Phone:</strong> {{ order.phone }}</p>
                                    <template v-if="order.payment_method === 'gcash'">
                                        <p><strong>Reference Number:</strong> {{ order.shipping_address }}</p>
                                    </template>
                                    <p><strong>Payment Method:</strong> {{ order.payment_method === 'gcash' ? 'GCash' :
                                        'Cash on Pickup' }}</p>
                                    <template v-if="order.orders && order.orders.length > 1">
                                        <p><strong>Items Ordered:</strong></p>
                                        <ul class="mb-2">
                                            <li v-for="(item, index) in order.orders" :key="index">
                                                {{ item.stock?.item_name }} - Qty: {{ item.quantity }}
                                            </li>
                                        </ul>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            orders: [],
            expandedOrders: [],
            statusOptions: ['Pending', 'Approved', 'Cancelled', 'Refunded'],
            username: '', // Instead of pulling from localStorage
            dropdownVisible: false
        };
    },
    async created() {
        await this.fetchUserData();  // 👈 fetch username first
        await this.fetchOrders();    // 👈 then fetch orders
    },
    methods: {
        toggleDropdown() {
            this.dropdownVisible = !this.dropdownVisible;
        },

        handleLogout() {
            localStorage.removeItem('authToken');
            this.$router.push('/login');
        },

        async fetchUserData() {
            try {
                const token = localStorage.getItem('authToken');
                if (!token) throw new Error("No token");

                const response = await fetch('http://localhost:8001/api/user', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (response.status === 401) {
                    this.handleLogout();
                    return;
                }

                const data = await response.json();
                this.username = data.name || 'Admin';
            } catch (error) {
                console.error("Error fetching user data:", error);
                this.username = 'Admin';
            }
        },

        async fetchOrders() {
            try {
                const token = localStorage.getItem('authToken');
                const response = await fetch('http://localhost:8001/api/customer-orders', {
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`,
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    const timestampMap = JSON.parse(localStorage.getItem('orderTimestamps') || '{}');

                    data.forEach(order => {
                        if (timestampMap[order.id]) {
                            order.orderTime = timestampMap[order.id];
                        }

                        if (Array.isArray(order.orders) && order.orders.length > 0) {
                            const itemNames = order.orders.map(o => o.stock?.item_name).filter(Boolean);
                            order.item_name = itemNames.length === 1 ? itemNames[0] : 'Multiple Items';
                        } else {
                            order.item_name = 'N/A';
                        }

                        if (Array.isArray(order.orders)) {
                            order.quantity = order.orders.reduce((sum, o) => sum + (o.quantity || 0), 0);
                        } else {
                            order.quantity = 'N/A';
                        }
                    });

                    this.orders = data;
                } else {
                    console.error('No orders found.');
                }
            } catch (error) {
                console.error('Network error:', error);
            }
        },

        toggleRow(orderId) {
            const i = this.expandedOrders.indexOf(orderId);
            if (i > -1) {
                this.expandedOrders.splice(i, 1);
            } else {
                this.expandedOrders.push(orderId);
            }
        },

        async verifyPayment(order) {
            const username = localStorage.getItem('username') || 'Admin';
            order.status = 'Approved';
            order.verified_by = username;

            try {
                await this.updateOrderStatus(order);
                alert('Payment verified and order approved.');
            } catch (error) {
                console.error('Error verifying payment:', error);
                alert('Failed to verify payment.');
            }
        },


        async undoVerification(order) {
            order.status = 'Pending';
            order.verified_by = null;

            try {
                await this.updateOrderStatus(order);
                alert('Verification undone.');
            } catch (error) {
                console.error('Error undoing verification:', error);
                alert('Failed to undo verification.');
            }
        },


        async updateOrderStatus(order) {
            try {
                const token = localStorage.getItem('authToken');
                await fetch(`http://localhost:8001/api/customer-orders/${order.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`,
                    },
                    body: JSON.stringify({
                        status: order.status,
                        verified_by: order.verified_by || null
                    }),
                });
            } catch (error) {
                console.error("Error updating status:", error);
            }
        },
        
        async cancelOrder(orderId) {
            if (confirm('Are you sure you want to cancel this order?')) {
                try {
                    const response = await fetch(`http://localhost:8001/api/customer-orders/${orderId}`, {
                        method: 'DELETE',
                    });
                    if (response.ok) {
                        this.orders = this.orders.filter(order => order.id !== orderId);
                        alert('Order canceled.');
                    } else {
                        alert('Failed to cancel order.');
                    }
                } catch (error) {
                    console.error('Error canceling order:', error);
                }
            }
        },

        canManuallyChangeStatus(order) {
            return order.status !== 'Approved';
        },

        async togglePaymentStatus(order) {
            const confirmed = confirm('Are you sure you want to unverify this payment?');
            if (!confirmed) return;

            const token = localStorage.getItem('authToken');
            try {
                const response = await fetch(`http://localhost:8001/api/customer-orders/${order.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`,
                    },
                    body: JSON.stringify({ status: 'Pending' }),
                });

                if (!response.ok) throw new Error('Failed to revert payment status.');

                order.status = 'Pending';
                this.orders = [...this.orders];

                alert('Order status reverted to Pending.');
            } catch (error) {
                console.error('Error:', error);
                alert('Unverify failed.');
            }
        },
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

.container {
    max-width: 1200px;
    margin: 0 auto;
}

h2 {
    font-family: 'Kantumruy Pro', sans-serif;
    font-weight: 700;
}

.navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background-color: rgb(255, 255, 255);
    color: #fff;
    position: relative;
    height: 50px;
    padding: 0 20px;
    position: sticky;
    top: 0;
    z-index: 1000;
    margin-bottom: -1px;
}

.navbar-brand {
    display: flex;
    align-items: center;
}

.brand-text {
    font-family: 'LibreCaslonDisplay-Regular';
    text-decoration: none;
    color: #0086E7;
    font-size: 24px;
    font-weight: normal;
    cursor: pointer;
}

.brand-text-follow {
    font-family: 'LibreCaslonDisplay-Regular';
    text-decoration: none;
    color: #0086E7;
    font-size: 12px;
    font-weight: normal;
    cursor: pointer;
    margin-top: 10px
}

.icon-button {
    background: none;
    border: none;
    cursor: pointer;
}

.icon-image {
    width: 10px;
    height: 10px;
    margin-top: 3px;
    margin-left: -10px;
}

.icon-image-profile {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    object-fit: cover;
}

.user-name {
    color: black;
    font-size: 13px;
    margin-top: 3px;
}

.navbar-right {
    display: flex;
    align-items: center;
    gap: 10px;
    position: relative;
}

.wrapper {
    background-color: #F4F4F4;
    min-height: 93vh;
    display: flex;
}

.sidebar {
    width: 300px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    background: linear-gradient(180deg, #0086E7 50%, #004B81 100%);
    padding: 20px;
}

.sidebar nav ul {
    list-style: none;
    padding: 0;
    margin-top: 30%;
    margin-left: 15%;
}

.sidebar nav ul li {
    display: flex;
    align-items: center;
    padding: 20px;
    position: relative;
    transition: background 0.3s;
}

.sidebar-icon {
    width: 16px;
    height: 16px;
    margin-right: 10px;
    margin-bottom: 2px;
    filter: brightness(0) invert(1);
}

.sidebar nav ul li a {
    color: white;
    text-decoration: none;
    font-size: 18px;
    font-weight: 200;
}

.router-link-active::before {
    content: "";
    position: absolute;
    top: 0;
    left: -60px;
    width: 5px;
    height: 100%;
    background: white;
    transform: scale(1.1);
    z-index: 1;
}

.router-link-active::after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 500%;
    height: 100%;
    background: #ffffff1c;
    z-index: -1;
    transform: scale(1.1);
    transition: transform 0.2s ease-in-out;
}

.table th,
.table td {
    vertical-align: middle;
}

tr:hover {
    background-color: #f2f2f2;
}

.form-select {
    min-width: 120px;
}
</style>
