<template>
  <div class="container">
    <div class="card mb-5 shadow-sm border-0">
      <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
        <h4 class="m-0">Customers</h4>
        <RouterLink to="/customers/create" class="btn btn-light btn-sm fw-bold">+ Add Customer</RouterLink>
      </div>
      <div class="card-body">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Last Name</th>
              <th>First Name</th>
              <th>Phone</th>
              <th>Email</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody v-if="customers.length > 0">
            <tr v-for="(customer, index) in customers" :key="index">
              <td>{{ customer.id }}</td>
              <td>{{ customer.last_name }}</td>
              <td>{{ customer.first_name }}</td>
              <td>{{ customer.phone }}</td>
              <td>{{ customer.email }}</td>
              <td class="text-center">
                <RouterLink
                  :to="{ path: '/customers/' + customer.id + '/edit' }"
                  class="btn btn-outline-success btn-sm me-2"
                >
                  ✎ Edit
                </RouterLink>
                <button
                  type="button"
                  @click="deleteCustomer(customer.id)"
                  class="btn btn-outline-danger btn-sm"
                >
                  🗑️ Delete
                </button>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="6" class="text-center text-muted py-4">
                <i class="bi bi-person-x display-6"></i>
                <p class="m-0">No customers found.</p>
              </td>
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

<style scoped>
.card {
  border-radius: 12px;
  overflow: hidden;
}

.card-header {
  font-weight: bold;
  font-size: 1.25rem;
  background: #0a3992;
}

.table {
  border-collapse: separate;
  border-spacing: 0 10px;
}

.table tbody tr {
  background-color: #f9f9f9;
  transition: transform 0.2s, box-shadow 0.2s;
}

.table tbody tr:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.table-light th {
  font-size: 1rem;
  color: #555;
}

.btn {
  font-size: 0.875rem;
}

.bi {
  font-size: 2rem;
}
</style>
