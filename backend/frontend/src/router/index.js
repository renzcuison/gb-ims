import { createRouter, createWebHistory } from 'vue-router'

import ItemsView from '../views/Items/View.vue'
import ItemsCreate from '../views/Items/Create.vue'
import ItemsEdit from '../views/Items/Edit.vue'

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
import StocksCreate from '../views/Stocks/Create.vue'
import StocksOut from '../views/Stocks/Out.vue'
import StocksEdit from '../views/Stocks/Edit.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [

    {
      path: '/items',
      name: 'items',
      component: ItemsView
    },
    {
      path: '/items/create',
      name: 'itemsCreate',
      component: ItemsCreate
    },
    {
      path: '/items/:id/edit',
      name: 'itemsEdit',
      component: ItemsEdit
    },

    {
      path: '/categories',
      name: 'categories',
      component: CategoriesView
    },
    {
      path: '/categories/create',
      name: 'categoriesCreate',
      component: CategoriesCreate
    },
    {
      path: '/categories/:id/edit',
      name: 'categoriesEdit',
      component: CategoriesEdit
    },

    {
      path: '/suppliers',
      name: 'suppliers',
      component: SuppliersView
    },
    {
      path: '/suppliers/create',
      name: 'suppliersCreate',
      component: SuppliersCreate
    },
    {
      path: '/suppliers/:id/edit',
      name: 'suppliersEdit',
      component: SuppliersEdit
    },

    {
      path: '/transactions',
      name: 'transactions',
      component: TransactionsView
    },
    {
      path: '/transactions/create',
      name: 'transactionsCreate',
      component: TransactionsCreate
    },
    {
      path: '/transactions/:id/edit',
      name: 'transactionsEdit',
      component: TransactionsEdit
    },


    {
      path: '/employees',
      name: 'employees',
      component: EmployeesView
    },
    {
      path: '/employees/create',
      name: 'employeesCreate',
      component: EmployeesCreate
    },
    {
      path: '/employees/:id/edit',
      name: 'employeesEdit',
      component: EmployeesEdit
    },

    {
      path: '/customers',
      name: 'customers',
      component: CustomersView
    },
    {
      path: '/customers/create',
      name: 'customersCreate',
      component: CustomersCreate
    },
    {
      path: '/customers/:id/edit',
      name: 'customersEdit',
      component: CustomersEdit
    },

    {
      path: '/stocks',
      name: 'stocks',
      component: StocksView
    },
    {
      path: '/stocks/create',
      name: 'stocksCreate',
      component: StocksCreate
    },
    {
      path: '/stocks/out',
      name: 'stocksOut',
      component: StocksOut
    },
    {
      path: '/stocks/:id/edit',
      name: 'stocksEdit',
      component: StocksEdit
    }

  ]
})

export default router
