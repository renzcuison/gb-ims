<template>
  <div class="container">
    <div class="card mb-5">
      <div class="card-header">
        <h4>
          Customers
          <RouterLink to="/customers/create" class="btn btn-primary float-end">Add</RouterLink>
        </h4>
      </div>
      <div class="card-body">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>ID</th>
              <th>Last Name</th>
              <th>First Name</th>
              <th>Phone</th>
              <th>Email</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody v-if="customers.length > 0">
            <tr v-for="(customer, index) in customers" :key="index">
              <td>{{ customer.id }}</td>
              <td>{{ customer.last_name }}</td>
              <td>{{ customer.first_name }}</td>
              <td>{{ customer.phone }}</td>
              <td>{{ customer.email }}</td>
              <td>
                <RouterLink :to="{ path: '/customers/' + customer.id + '/edit' }" class="btn btn-success">Edit</RouterLink>
                <button type="button" @click="deleteCustomer(customer.id)" class="btn btn-danger">Delete</button>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="6">↺</td>
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
  name: 'CustomersList',
  data() {
    return {
      customers: [],
    };
  },
  mounted() {
    this.getCustomers();
  },
  methods: {
    getCustomers() {
      axios.get('http://localhost:8001/api/customers')
        .then(res => {
          this.customers = res.data.customers;
        })
        .catch(error => {
          console.error('Error fetching customers:', error);
        });
    },
    deleteCustomer(id) {
      if (confirm('Confirm action: DELETE')) {
        axios.delete(`http://localhost:8001/api/customers/${id}`)
          .then(res => {
            alert(res.data.message);
            this.getCustomers();
          })
          .catch(error => {
            console.error('Error deleting customer:', error);
          });
      }
    },
  },
};
</script>