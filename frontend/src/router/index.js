import { createRouter, createWebHistory } from 'vue-router'

import LoginView from '../views/Login/View.vue'
import RegisterView from '../views/Login/Create.vue'

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

import OrderView from '../views/Shops/View.vue'
import OrderCreate from '../views/Order/Create.vue'
import OrderEdit from '../views/Order/Edit.vue'

import ShopView from '../views/Shops/ShopView.vue'
import ItemDetails from '../views/Shops/ItemDetails.vue'
import Checkout from '../views/Shops/Checkout.vue'
import OrderPage from '../views/Order/OrderPage.vue'

import EmailVerification from '../views/Verify/EmailVerification.vue'
import EmailWaiting from '../views/Verify/EmailWaiting.vue'

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
      path: '/register',
      name: 'create-login',
      component: RegisterView,
    },
    {
      path: '/shop',
      name: 'Shop',
      component: ShopView,
    },
    {
      path: '/checkout',
      name: 'Checkout',
      component: Checkout,
    },
    {
      path: '/order',
      name: 'OrderPage',
      component: OrderPage,
    },
    {
      path: '/item/:id',
      name: 'ItemDetails',
      component: ItemDetails,
      props: true,
      meta: { requiresAdmin: true }
    },
    {
      path: '/categories',
      name: 'categories',
      component: CategoriesView,
      meta: { requiresAdmin: true }
    },
    {
      path: '/categories/create',
      name: 'categoriesCreate',
      component: CategoriesCreate,
      meta: { requiresAdmin: true }
    },
    {
      path: '/categories/:id/edit',
      name: 'categoriesEdit',
      component: CategoriesEdit,
      meta: { requiresAdmin: true }
    },

    {
      path: '/suppliers',
      name: 'suppliers',
      component: SuppliersView,
      meta: { requiresAdmin: true }
    },
    {
      path: '/suppliers/create',
      name: 'suppliersCreate',
      component: SuppliersCreate,
      meta: { requiresAdmin: true }
    },
    {
      path: '/suppliers/:id/edit',
      name: 'suppliersEdit',
      component: SuppliersEdit,
      meta: { requiresAdmin: true }
    },

    {
      path: '/transactions',
      name: 'transactions',
      component: TransactionsView,
      meta: { requiresAdmin: true }
    },
    {
      path: '/transactions/create',
      name: 'transactionsCreate',
      component: TransactionsCreate,
      meta: { requiresAdmin: true }
    },
    {
      path: '/transactions/:id/edit',
      name: 'transactionsEdit',
      component: TransactionsEdit,
      meta: { requiresAdmin: true }
    },
    {
      path: '/employees',
      name: 'employees',
      component: EmployeesView,
      meta: { requiresAdmin: true }
    },
    {
      path: '/employees/create',
      name: 'employeesCreate',
      component: EmployeesCreate,
      meta: { requiresAdmin: true }
    },
    {
      path: '/employees/:id/edit',
      name: 'employeesEdit',
      component: EmployeesEdit,
      meta: { requiresAdmin: true }
    },

    {
      path: '/customers',
      name: 'customers',
      component: CustomersView,
      meta: { requiresAdmin: true }
    },
    {
      path: '/customers/create',
      name: 'customersCreate',
      component: CustomersCreate,
      meta: { requiresAdmin: true }
    },
    {
      path: '/customers/:id/edit',
      name: 'customersEdit',
      component: CustomersEdit,
      meta: { requiresAdmin: true }
    },

    {
      path: '/stocks',
      name: 'stocks',
      component: StocksView,
      meta: { requiresAdmin: true }
    },
    {
      path: '/stocks/lowstock/:stockId?',
      name: 'stocksLowStock',
      component: StocksLowStock,
      meta: { requiresAdmin: true }
    },
    {
      path: '/stocks/uncreate/:stockId?',
      name: 'stocksUncreate',
      component: StocksUncreate,
      meta: { requiresAdmin: true }
    },
    {
      path: '/stocks/in',
      name: 'stocksIn',
      component: StocksIn,
      meta: { requiresAdmin: true }
    },
    {
      path: '/stocks/create',
      name: 'stocksCreate',
      component: StocksCreate,
      meta: { requiresAdmin: true }
    },
    {
      path: '/stocks/:id/edit',
      name: 'stocksEdit',
      component: StocksEdit,
      meta: { requiresAdmin: true }
    },
    {
      path: '/orders',
      name: 'orders',
      component: OrderView,
      meta: { requiresAdmin: true }
    },
    {
      path: '/orders/create',
      name: 'orderCreate',
      component: OrderCreate,
      meta: { requiresAdmin: true }
    },
    {
      path: '/orders/:id/edit',
      name: 'orderEdit',
      component: OrderEdit,
      meta: { requiresAdmin: true }
    },

    {
      path: '/verify-email',
      name: 'EmailVerification',
      component: EmailVerification,
    },
    {
      path: '/email-waiting',
      name: 'EmailWaiting',
      component: EmailWaiting,
      
    },
    
  ],
})

router.beforeEach(async (to, from, next) => {
  const authToken = localStorage.getItem('authToken');

  if (to.path === '/login' && authToken) {
    try {
      const res = await fetch('http://localhost:8001/api/user', {
        method: 'GET',
        headers: {
          Authorization: `Bearer ${authToken}`,
        },
      });

      const user = await res.json();

      if (user.email_verified_at) {
        return next('/shop');
      } else {
        return next('/email-waiting');
      }
    } catch (err) {
      localStorage.removeItem('authToken');
      return next();
    }
  }

  if (to.matched.some((record) => record.meta.requiresAuth || record.meta.requiresAdmin)) {
    if (!authToken) {
      return next('/login');
    }

    try {
      const response = await fetch('http://localhost:8001/api/user', {
        method: 'GET',
        headers: {
          Authorization: `Bearer ${authToken}`,
        },
      });

      if (response.status === 401) {
        localStorage.removeItem('authToken');
        return next('/login');
      }

      const user = await response.json();

      if (!user.email_verified_at && to.path !== '/email-waiting') {
        return next('/email-waiting');
      }

      
      if (to.matched.some((record) => record.meta.requiresAdmin) && user.role !== 'admin') {
        return next('/shop');
      }

      return next();
    } catch (err) {
      console.error('Route guard error:', err);
      localStorage.removeItem('authToken');
      return next('/login');
    }
  }

  return next();
});



export default router
