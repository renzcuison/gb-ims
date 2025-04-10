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
    },
    {
      path: '/categories',
      name: 'categories',
      component: CategoriesView,
    },
    {
      path: '/categories/create',
      name: 'categoriesCreate',
      component: CategoriesCreate,
    },
    {
      path: '/categories/:id/edit',
      name: 'categoriesEdit',
      component: CategoriesEdit,
    },

    {
      path: '/suppliers',
      name: 'suppliers',
      component: SuppliersView,
    },
    {
      path: '/suppliers/create',
      name: 'suppliersCreate',
      component: SuppliersCreate,
    },
    {
      path: '/suppliers/:id/edit',
      name: 'suppliersEdit',
      component: SuppliersEdit,
    },

    {
      path: '/transactions',
      name: 'transactions',
      component: TransactionsView,
    },
    {
      path: '/transactions/create',
      name: 'transactionsCreate',
      component: TransactionsCreate,
    },
    {
      path: '/transactions/:id/edit',
      name: 'transactionsEdit',
      component: TransactionsEdit,
    },
    {
      path: '/employees',
      name: 'employees',
      component: EmployeesView,
    },
    {
      path: '/employees/create',
      name: 'employeesCreate',
      component: EmployeesCreate,
    },
    {
      path: '/employees/:id/edit',
      name: 'employeesEdit',
      component: EmployeesEdit,
    },

    {
      path: '/customers',
      name: 'customers',
      component: CustomersView,
    },
    {
      path: '/customers/create',
      name: 'customersCreate',
      component: CustomersCreate,
    },
    {
      path: '/customers/:id/edit',
      name: 'customersEdit',
      component: CustomersEdit,
    },

    {
      path: '/stocks',
      name: 'stocks',
      component: StocksView,
    },
    {
      path: '/stocks/lowstock/:stockId?',
      name: 'stocksLowStock',
      component: StocksLowStock,
    },
    {
      path: '/stocks/uncreate/:stockId?',
      name: 'stocksUncreate',
      component: StocksUncreate,
    },
    {
      path: '/stocks/in',
      name: 'stocksIn',
      component: StocksIn,
    },
    {
      path: '/stocks/create',
      name: 'stocksCreate',
      component: StocksCreate,
    },
    {
      path: '/stocks/:id/edit',
      name: 'stocksEdit',
      component: StocksEdit,
    },
    {
      path: '/orders',
      name: 'orders',
      component: OrderView,
    },
    {
      path: '/orders/create',
      name: 'orderCreate',
      component: OrderCreate,
    },
    {
      path: '/orders/:id/edit',
      name: 'orderEdit',
      component: OrderEdit,
    },

    {
      path: '/verify-email',
      name: 'EmailVerification',
      component: EmailVerification,
    },
  ],
})

router.beforeEach(async (to, from, next) => {
  const authToken = localStorage.getItem('authToken')
  const userRole = localStorage.getItem('role') 

  if (to.matched.some((record) => record.meta.requiresAuth)) {
    if (!authToken) {
      return next('/login') 
    }

    try {
      const response = await fetch('http://localhost:8001/api/user', {
        method: 'GET',
        headers: {
          Authorization: `Bearer ${authToken}`,
        },
      })

      if (response.status === 401) {
        localStorage.removeItem('authToken')
        return next('/login')
      }

      const user = await response.json()

      if (!user.email_verified_at && to.path !== '/verify-email') {
        return next('/verify-email')
      }

      if (
        to.matched.some((record) => record.meta.requiresAdmin) &&
        user.role !== 'admin'
      ) {
        return next('/forbidden')
      }
    } catch (error) {
      console.error('Error in route guard:', error)
      localStorage.removeItem('authToken')
      return next('/login')
    }
  }

  if (to.path === '/login' && authToken) {
    return next('/shop')
  }

  next()
})

export default router
