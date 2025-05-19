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
                        <router-link to="/customers" active-class="router-link-active">
                            <img src="/customer1.png" alt="SalesReport" class="sidebar-icon"> REPORTS
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/employees" active-class="router-link-active">
                            <img src="/employees.png" alt="Employees" class="sidebar-icon"> EMPLOYEES
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/sales-report" active-class="router-link-active">
                            <img src="/dollar.png" alt="SalesReport" class="sidebar-icon">SALES
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/admin/orders" active-class="router-link-active">
                            <img src="/order.png" alt="Orders" class="sidebar-icon"> ORDERS
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/stocks/logs" active-class="router-link-active">
                            <img src="/file.png" alt="Logs" class="sidebar-icon"> LOGS
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
    </div>
    <div class="content">
        <h1>Sales Report</h1>

        <div>
            <button @click="changePeriod('day')" :class="{ active: currentPeriod === 'day' }">Daily</button>
            <button @click="changePeriod('week')" :class="{ active: currentPeriod === 'week' }">Weekly</button>
            <button @click="changePeriod('month')" :class="{ active: currentPeriod === 'month' }">Monthly</button>
            <button @click="changePeriod('year')" :class="{ active: currentPeriod === 'year' }">Yearly</button>
        </div>

        <div class="report-section">
            <h2>Overview</h2>
            <p>Reporting period: {{ reportPeriod }}</p>
            <div class="summary">
                Total Revenue: ₱{{ totalRevenue.toFixed(2) }}
            </div>
        </div>

        <div class="report-section">
            <h2>Sales by Quantity</h2>
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th class="numeric">Total Quantity Sold</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="itemQuantity in salesByQuantity" :key="itemQuantity.name">
                            <td>{{ itemQuantity.name }}</td>
                            <td class="numeric">{{ itemQuantity.totalQuantity }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="report-section">
            <h2>Sales by Item</h2>
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th class="numeric">Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="itemSales in salesByItem" :key="itemSales.name">
                            <td>{{ itemSales.name }}</td>
                            <td class="numeric">₱{{ itemSales.revenue.toFixed(2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            currentPeriod: 'month',
            reportPeriod: this.getReportPeriod('month'),
            completedOrders: [],
            dropdownVisible: false,
            username: '',
        };
    },
    async mounted() {
        await this.fetchCompletedOrders();
        this.fetchUserData();
    },
    computed: {
        filteredOrders() {
            if (!this.completedOrders) {
                return [];
            }
            const now = new Date();
            let startDate, endDate;

            if (this.currentPeriod === 'day') {
                startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                startDate.setHours(0, 0, 0, 0);
                endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                endDate.setHours(23, 59, 59, 999);
                return this.completedOrders.filter(order => new Date(order.created_at) >= startDate && new Date(order.created_at) <= endDate);
            } else if (this.currentPeriod === 'week') {
                const dayOfWeek = now.getDay();
                const diff = now.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1);
                startDate = new Date(now.setDate(diff));
                startDate.setHours(0, 0, 0, 0);
                endDate = new Date(startDate);
                endDate.setDate(endDate.getDate() + 6);
                endDate.setHours(23, 59, 59, 999);
                return this.completedOrders.filter(order => new Date(order.created_at) >= startDate && new Date(order.created_at) <= endDate);
            } else if (this.currentPeriod === 'month') {
                startDate = new Date(now.getFullYear(), now.getMonth(), 1);
                startDate.setHours(0, 0, 0, 0);
                endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                endDate.setHours(23, 59, 59, 999);
                return this.completedOrders.filter(order => new Date(order.created_at) >= startDate && new Date(order.created_at) <= endDate);
            } else if (this.currentPeriod === 'year') {
                startDate = new Date(now.getFullYear(), 0, 1);
                startDate.setHours(0, 0, 0, 0);
                endDate = new Date(now.getFullYear(), 11, 31);
                endDate.setHours(23, 59, 59, 999);
                return this.completedOrders.filter(order => new Date(order.created_at) >= startDate && new Date(order.created_at) <= endDate);
            }
            return this.completedOrders;
        },
        salesByQuantity() {
            const quantitySold = {};
            this.filteredOrders.forEach(order => {
                if (Array.isArray(order.orders)) {
                    order.orders.forEach(item => {
                        const itemName = item.stock?.item_name;
                        const quantity = parseInt(item.quantity);
                        if (itemName && !isNaN(quantity)) {
                            if (!quantitySold[itemName]) {
                                quantitySold[itemName] = 0;
                            }
                            quantitySold[itemName] += quantity;
                        }
                    });
                }
            });
            return Object.entries(quantitySold).map(([name, totalQuantity]) => ({ name, totalQuantity })).sort((a, b) => b.totalQuantity - a.totalQuantity);
        },
        totalRevenue() {
            let total = 0;
            this.filteredOrders.forEach(order => {
                total += parseFloat(order.total_price);
            });
            return total;
        },
        salesByItem() {
            const itemSales = {};
            this.filteredOrders.forEach(order => {
                if (Array.isArray(order.orders)) {
                    order.orders.forEach(item => {
                        const itemName = item.stock?.item_name;
                        const price = parseFloat(item.price_per_unit);
                        const quantity = parseInt(item.quantity);
                        if (itemName && !isNaN(price) && !isNaN(quantity)) {
                            if (!itemSales[itemName]) {
                                itemSales[itemName] = {
                                    name: itemName,
                                    revenue: 0,
                                };
                            }
                            itemSales[itemName].revenue += quantity * price;
                        }
                    });
                }
            });
            return Object.values(itemSales).sort((a, b) => b.revenue - a.revenue);
        },
    },
    methods: {
        async fetchCompletedOrders() {
            try {
                const token = localStorage.getItem('authToken');
                const response = await axios.get('http://localhost:8001/api/customer-orders?status=Completed', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                    },
                });
                console.log('Completed Orders Response:', response.data);
                this.completedOrders = response.data;
            } catch (error) {
                console.error('Error fetching completed orders:', error);
            }
        },
        async fetchUserData() {
            try {
                const token = localStorage.getItem('authToken');
                if (!token) {
                    console.error("No auth token found.");
                    this.handleLogout();
                    return;
                }

                const response = await fetch('http://localhost:8001/api/user', {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (response.status === 401) {
                    console.error("Unauthorized: Token may be invalid.");
                    this.handleLogout();
                    return;
                }

                if (!response.ok) {
                    throw new Error(`Failed to fetch user data. Status: ${response.status}`);
                }

                const data = await response.json();
                console.log("User data:", data);
                this.username = data.name || "Admin";
            } catch (error) {
                console.error('Error fetching user data:', error);
                this.username = "Admin";
            }
        },
        changePeriod(period) {
            this.currentPeriod = period;
            this.reportPeriod = this.getReportPeriod(period);
        },
        getReportPeriod(period) {
            const now = new Date();
            if (period === 'day') {
                return now.toLocaleDateString();
            } else if (period === 'week') {
                const dayOfWeek = now.getDay();
                const diff = now.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1);
                const start = new Date(now.setDate(diff));
                const end = new Date(start);
                end.setDate(end.getDate() + 6);
                return `${start.toLocaleDateString()} - ${end.toLocaleDateString()}`;
            } else if (period === 'month') {
                return now.toLocaleDateString('default', { month: 'long', year: 'numeric' });
            } else if (period === 'year') {
                return now.toLocaleDateString('default', { year: 'numeric' });
            }
            return 'All Time';
        },
        toggleDropdown() {
            this.dropdownVisible = !this.dropdownVisible;
        },
        handleLogout() {
            localStorage.removeItem('authToken');
            this.router.push('/login');
            this.closeHamburgerDropdown();
            this.dropdownVisible = false;
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

.wrapper {
    background-color: #F4F4F4;
    /* Removed min-height: 93vh; */
}

.card {
    margin-left: 25px;
    margin-right: 12px;
}

.table-responsive {
    overflow-x: auto;
}

.table {
    width: 100%;
}

.navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background-color: rgb(255, 255, 255);
    color: #fff;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 50px;
    padding: 0 20px;
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

.navbar-right {
    display: flex;
    align-items: center;
    gap: 10px;
    position: relative;
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

.dropdown-menu {
    min-width: 0;
    max-width: none;
    width: 100px !important;
    background-color: #ffffff;
    border-radius: 5px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    position: absolute;
    top: 100%;
    right: 0;
    overflow: hidden;
    display: block;
}

.dropdown-item {
    text-align: left;
    color: #7d7d7d;
    cursor: pointer;
    width: 100%;
    font-size: 12px;
    padding: 8px 10px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    background-color: transparent;
    border: none;
}

.dropdown-item:hover {
    background-color: #ffffff;
}

.dropdown-menu ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar {
    width: 300px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 50px;
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

.sidebar nav ul li:not(.router-link-active):hover {
    transform: scale(1.05);
    transition: transform 0.2s ease-in-out;
}

.sidebar nav ul li a {
    color: white;
    text-decoration: none;
    font-size: 18px;
    font-weight: 200;
}

.content {
    margin-left: 300px;
    padding: 20px;
    padding-top: 70px;
}

.report-section {
    margin-top: 20px;
    padding: 15px;
    border: 1px solid #eee;
    border-radius: 5px;
    background-color: #f9f9f9;
}

.summary {
    font-size: 1.2em;
    margin-top: 10px;
}

.table-responsive {
    overflow-x: auto;
    margin-top: 10px;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 8px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}

.table th {
    background-color: #f2f2f2;
    font-weight: bold;
}

.table tbody tr:hover {
    background-color: #f5f5f5;
}

.numeric {
    text-align: right;
}

div>button {
    padding: 10px 18px;
    margin-right: 10px;
    border: 1px solid #0086E7;
    border-radius: 5px;
    cursor: pointer;
    background-color: white;
    color: #0086E7;
    font-size: 14px;
    transition: background-color 0.3s, color 0.3s;
}

div>button.active {
    background-color: #0086E7;
    color: white;
}

div>button:hover:not(.active) {
    background-color: #e0f7fa;
}

h1 {
    font-family: 'LibreCaslonDisplay-Regular', serif;
    color: #333;
    margin-bottom: 20px;
}

h2 {
    color: #004B81;
    margin-top: 15px;
    margin-bottom: 10px;
    font-size: 1.5em;
}

p {
    color: #555;
    font-size: 14px;
}

.summary {
    font-size: 1.3em;
    color: #0086E7;
    font-weight: bold;
}
</style>