<template>
  <div class="container">
    <div class="card mb-5">
      <div class="card-header">
        <h4>
          Suppliers
          <RouterLink to="/suppliers/create" class="btn btn-primary float-end">Add</RouterLink>
        </h4>
      </div>
      <div class="card-body">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Contact</th>
              <th>E-mail</th>
              <th>Phone</th>
              <th>Address</th>
            </tr>
          </thead>
          <tbody v-if="suppliers.length > 0">
            <tr v-for="(supplier, index) in suppliers" :key="index">
              <td>{{ supplier.id }}</td>
              <td>{{ supplier.supplier_name }}</td>
              <td>{{ supplier.contact_name }}</td>
              <td>{{ supplier.contact_email }}</td>
              <td>{{ supplier.contact_phone }}</td>
              <td>{{ supplier.address }}</td>
              <td>
                <RouterLink :to="{ path: '/suppliers/' + supplier.id + '/edit' }" class="btn btn-success">Edit</RouterLink>
                <button type="button" @click="deleteSupplier(supplier.id)" class="btn btn-danger">Delete</button>
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
  name: 'Suppliers',
  data() {
    return {
      suppliers: [],
    };
  },
  mounted() {
    this.getSuppliers();
  },
  methods: {
    getSuppliers() {
      axios.get('http://localhost:8001/api/suppliers')
        .then(res => {
          this.suppliers = res.data.suppliers;
        })
        .catch(error => {
          console.error('Error fetching supplier:', error);
        });
    },
    deleteSupplier(id) {
      if (confirm('Confirm action: DELETE')) {
        axios.delete(`http://localhost:8001/api/suppliers/${id}`)
          .then(res => {
            alert(res.data.message);
            this.getCategories();
          })
          .catch(error => {
            console.error('Error deleting supplier:', error);
          });
      }
    },
  },
};
</script>