import { createRouter, createWebHistory } from 'vue-router'

import LoginView from '../views/Login/View.vue'; 
import RegisterView from '../views/Login/Create.vue'; 


import CategoriesView from '../views/Categories/View.vue'
import CategoriesCreate from '../views/Categories/Create.vue'
import CategoriesEdit from '../views/Categories/Edit.vue'

import SuppliersView from '../views/Suppliers/View.vue'
import SuppliersCreate from '../views/Suppliers/Create.vue'
import SuppliersEdit from '../views/Suppliers/Edit.vue'

import TransactionsView from '../views/Transactions/View.vue'
import TransactionsCreate from '../views/Transactions/Create.vue'
import TransactionsEdit from '../views/Transactions/Edit.vue'

import EmployeesView from '../views/Employees/View.vue'
import EmployeesCreate from '../views/Employees/Create.vue'
import EmployeesEdit from '../views/Employees/Edit.vue'

import CustomersView from '../views/Customers/View.vue'
import CustomersCreate from '../views/Customers/Create.vue'
import CustomersEdit from '../views/Customers/Edit.vue'

import StocksView from '../views/Stocks/View.vue'
import StocksIn from '../views/Stocks/In.vue'
import StocksUncreate from '../views/Stocks/Uncreate.vue'
import StocksLowStock from '../views/Stocks/LowStock.vue'
import StocksCreate from '../views/Stocks/Create.vue'
import StocksEdit from '../views/Stocks/Edit.vue'

import OrderView from '../views/Shops/View.vue';
import OrderCreate from '../views/Order/Create.vue';
import OrderEdit from '../views/Order/Edit.vue';

import ShopView from '../views/Shops/ShopView.vue';
import ItemDetails from '../views/Shops/ItemDetails.vue';
import Checkout from '../views/Shops/Checkout.vue';
import OrderPage from '../views/Order/OrderPage.vue';

import EmailVerification from '../views/Verify/EmailVerification.vue';


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/login',
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
    },
    {
      path: '/login/create',
      name: 'create-login',
      component: RegisterView,
    },
    {
      path: '/shop', 
      name: 'Shop',
      component: ShopView,
      meta: { requiresAuth: true },
    },
    {
      path: '/checkout',
      name: 'Checkout',
      component: Checkout, 
      meta: { requiresAuth: true },
    },
    {
      path: '/order',
      name: 'OrderPage',
      component: OrderPage,
      meta: { requiresAuth: true },
    },
    {
      path: '/item/:id',
      name: 'ItemDetails',
      component: ItemDetails,
      props: true,
      meta: { requiresAuth: true },
    },
    {
      path: '/categories',
      name: 'categories',
      component: CategoriesView,
      meta: { requiresAuth: true },
    },
    {
      path: '/categories/create',
      name: 'categoriesCreate',
      component: CategoriesCreate,
      meta: { requiresAuth: true }

    },
    {
      path: '/categories/:id/edit',
      name: 'categoriesEdit',
      component: CategoriesEdit,
      meta: { requiresAuth: true }
    },

    {
      path: '/suppliers',
      name: 'suppliers',
      component: SuppliersView,
      meta: { requiresAuth: true },
    },
    {
      path: '/suppliers/create',
      name: 'suppliersCreate',
      component: SuppliersCreate,
      meta: { requiresAuth: true },
    },
    {
      path: '/suppliers/:id/edit',
      name: 'suppliersEdit',
      component: SuppliersEdit,
      meta: { requiresAuth: true },
    },

    {
      path: '/transactions',
      name: 'transactions',
      component: TransactionsView,
      meta: { requiresAuth: true }
    },
    {
      path: '/transactions/create',
      name: 'transactionsCreate',
      component: TransactionsCreate,
      meta: { requiresAuth: true }
    },
    {
      path: '/transactions/:id/edit',
      name: 'transactionsEdit',
      component: TransactionsEdit,
      meta: { requiresAuth: true }
    },
    {
      path: '/employees',
      name: 'employees',
      component: EmployeesView,
      meta: { requiresAuth: true }
    },
    {
      path: '/employees/create',
      name: 'employeesCreate',
      component: EmployeesCreate,
      meta: { requiresAuth: true }
    },
    {
      path: '/employees/:id/edit',
      name: 'employeesEdit',
      component: EmployeesEdit,
      meta: { requiresAuth: true }
    },

    {
      path: '/customers',
      name: 'customers',
      component: CustomersView,
      meta: { requiresAuth: true }
    },
    {
      path: '/customers/create',
      name: 'customersCreate',
      component: CustomersCreate,
      meta: { requiresAuth: true }
    },
    {
      path: '/customers/:id/edit',
      name: 'customersEdit',
      component: CustomersEdit,
      meta: { requiresAuth: true }
    },

    {
      path: '/stocks',
      name: 'stocks',
      component: StocksView,
      meta: { requiresAuth: true }
    },
    {
      path: '/stocks/lowstock/:stockId?',
      name: 'stocksLowStock',
      component: StocksLowStock,
      meta: { requiresAuth: true }
    },    
    {
      path: '/stocks/uncreate/:stockId?',
      name: 'stocksUncreate',
      component: StocksUncreate,
      meta: { requiresAuth: true }
    },
    {
      path: '/stocks/in',
      name: 'stocksIn',
      component: StocksIn,
      meta: { requiresAuth: true }
    },
    {
      path: '/stocks/create',
      name: 'stocksCreate',
      component: StocksCreate,
      meta: { requiresAuth: true }
    },
    {
      path: '/stocks/:id/edit',
      name: 'stocksEdit',
      component: StocksEdit,
      meta: { requiresAuth: true }
    },
    {
      path: '/orders',
      name: 'orders',
      component: OrderView, 
      meta: { requiresAuth: true }
    },
    {
      path: '/orders/create',
      name: 'orderCreate',
      component: OrderCreate,
      meta: { requiresAuth: true }
    },
    {
      path: '/orders/:id/edit',
      name: 'orderEdit',
      component: OrderEdit, 
      meta: { requiresAuth: true }
    },
    
    {
      path: '/verify-email',
      name: 'EmailVerification',
      component: EmailVerification,
      meta: { requiresAuth: true }
    },
  ]
});

router.beforeEach(async (to, from, next) => {
  const authToken = localStorage.getItem('authToken');

  if (to.matched.some((record) => record.meta.requiresAuth)) {
    if (!authToken) return next('/login');

    try {
      const response = await fetch('http://localhost:8001/api/user', {
        method: 'GET',
        headers: { Authorization: `Bearer ${authToken}` },
      });

      if (!response.ok) {
        localStorage.removeItem('authToken');
        return next('/login');
      }

      const user = await response.json();
      console.log('User data:', user); // Debugging

      const isVerified = user.email_verified_at !== null;

      if (!isVerified && to.path !== '/verify-email') {
        return next('/verify-email');
      }

      if (isVerified && to.path === '/verify-email') {
        return next('/shop');
      }

      return next();
    } catch (error) {
      console.error('Error verifying authentication:', error);
      localStorage.removeItem('authToken');
      return next('/login');
    }
  }

  if (to.path === '/login' && authToken) {
    return next('/shop');
  }

  next();
});

export default router
