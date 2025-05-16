<template>
    <header class="navbar">
        <div class="navbar-brand">
            <RouterLink :to="{ path: '/shop' }" class="brand-text">GREATBUY</RouterLink>
            <RouterLink :to="{ path: '/shop' }" class="brand-text-follow">DATABASE</RouterLink>
        </div>
        <div class="navbar-right">
            <img src="/profile.jpg" alt="User Profile" class="icon-image-profile" @click="toggleDropdown">
            <span class="user-name">admin</span>
            <button class="icon-button" @click="toggleDropdown">
                <img src="/drop.png" alt="Dropdown" class="icon-image">
            </button>

            <div v-if="dropdownVisible" class="dropdown-menu">
                <ul>
                    <li>
                        <button @click="handleLogout" class="dropdown-item">Logout</button>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <div class="wrapper">
        <div class="sidebar">
            <nav>
                <ul>
                    <li>
                        <router-link to="/stocks" active-class="router-link-active"
                            exact-active-class="router-link-active"
                            :class="{ 'router-link-active': $route.path.startsWith('/stocks') }">
                            <img src="/inventory.png" alt="Inventory" class="sidebar-icon" />
                            INVENTORY
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/suppliers" active-class="router-link-active">
                            <img src="/supplier.png" alt="Suppliers" class="sidebar-icon"> SUPPLIERS
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/categories" active-class="router-link-active">
                            <img src="/category.png" alt="Categories" class="sidebar-icon"> CATEGORIES
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/customers" active-class="router-link-active">
                            <img src="/customer1.png" alt="Customers" class="sidebar-icon"> CUSTOMERS
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/employees" active-class="router-link-active">
                            <img src="/employees.png" alt="Employees" class="sidebar-icon"> EMPLOYEES
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/admin/orders" active-class="router-link-active">
                            <img src="/order.png" alt="Orders" class="sidebar-icon"> ORDERS
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/shop" active-class="router-link-active">
                            <img src="/shop.png" alt="Shop" class="sidebar-icon"> SHOP
                        </router-link>
                    </li>
                </ul>
            </nav>
        </div>

        <div class="container mt-5">
            <h2 class="mb-4 text-center">Admin - Customers Orders Table</h2>

            <div v-if="orders.length === 0" class="alert alert-info text-center">
                No orders available.
            </div>

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
                    <tr v-for="order in orders" :key="order.id">
                        <td>{{ order.order_code }}</td>
                        <td>{{ order.customer_name }}</td>
                        <td>₱{{ parseFloat(order.total_price).toFixed(2) }}</td>
                        <td>{{ order.quantity || 'N/A' }}</td>
                        <td>{{ order.item_name || 'N/A' }}</td> <!-- ✅ NEW -->
                        <td>
                            <select v-model="order.status" @change="updateOrderStatus(order)"
                                class="form-select form-select-sm" :disabled="order.status === 'Approved'">
                                <option v-for="status in statusOptions" :key="status" :value="status">
                                    {{ status }}
                                </option>
                            </select>
                        </td>
                        <td>{{ order.payment_method === 'gcash' ? 'GCash' : 'COD' }}</td>
                        <td>{{ order.orderTime || 'N/A' }}</td>
                        <td>
                            <template v-if="order.payment_method === 'gcash'">
                                <button class="btn btn-sm text-white" :style="{
                                    backgroundColor: order.status === 'Approved' ? 'green' : '#0086E7',
                                    cursor: order.status === 'Approved' ? 'default' : 'pointer'
                                }" :disabled="order.status === 'Approved'" @click="verifyPayment(order)">
                                    {{ order.status === 'Approved' ? 'Verified' : 'Verify Payment' }}
                                </button>
                            </template>
                            <template v-else-if="order.payment_method === 'cod'">
                                <!-- No action -->
                            </template>
                        </td>
                    </tr>
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
            statusOptions: [
                'Pending',
                'Approved',
                'Cancelled',
                'Processing',
                'Shipped',
                'Delivered',
                'Refunded',
                'On Hold',
            ],
        };
    },
    async created() {
        this.fetchOrders();
    },
    methods: {
        async fetchOrders() {
            try {
                const response = await fetch('http://localhost:8001/api/customer-orders');
                const data = await response.json();

                if (response.ok) {
                    const timestampMap = JSON.parse(localStorage.getItem('orderTimestamps') || '{}');

                    data.forEach(order => {
                        // 🕒 Timestamp (optional)
                        if (timestampMap[order.id]) {
                            order.orderTime = timestampMap[order.id];
                        }

                        // 🧾 Item Name logic
                        if (Array.isArray(order.orders) && order.orders.length > 0) {
                            const itemNames = order.orders.map(o => o.stock?.item_name).filter(Boolean);

                            order.item_name = itemNames.length === 1
                                ? itemNames[0]
                                : 'Multiple Items';
                        } else {
                            order.item_name = 'N/A';
                        }

                        // 🔢 Quantity logic
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

        async verifyPayment(order) {
            order.status = 'Approved';
            try {
                await this.updateOrderStatus(order);
                alert('Payment verified and order approved.');
            } catch (error) {
                console.error('Error verifying payment:', error);
                alert('Failed to verify payment.');
            }
        },
        async updateOrderStatus(order) {
            try {
                await fetch(`http://localhost:8001/api/customer-orders/${order.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ status: order.status }),
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
    width: 10x;
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

.router-link-active {
    background: transparent;
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

.router-link-active:hover {
    transform: none !important;
}

.table th,
.table td {
    vertical-align: middle;
}

.form-select {
    min-width: 120px;
}
</style>
