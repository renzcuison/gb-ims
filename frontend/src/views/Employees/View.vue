<template>
  <div class="container">
    <div class="card mb-5">
      <div class="card-header">
        <h4>
          Employees
          <RouterLink to="/employees/create" class="btn btn-primary float-end">Add</RouterLink>
        </h4>
      </div>
      <div class="card-body">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>ID</th>
              <th>Last Name</th>
              <th>First Name</th>
              <th>Role</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody v-if="employees.length > 0">
            <tr v-for="(employee, index) in employees" :key="index">
              <td>{{ employee.id }}</td>
              <td>{{ employee.last_name }}</td>
              <td>{{ employee.first_name }}</td>
              <td>{{ employee.role }}</td>
              <td>
                <RouterLink :to="{ path: '/employees/' + employee.id + '/edit' }" class="btn btn-success">Edit</RouterLink>
                <button type="button" @click="deleteEmployee(employee.id)" class="btn btn-danger">Delete</button>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="5">↺</td>
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
  name: 'EmployeesList',
  data() {
    return {
      employees: [],
    };
  },
  mounted() {
    this.getEmployees();
  },
  methods: {
    getEmployees() {
      axios.get('http://localhost:8001/api/employees')
        .then(res => {
          this.employees = res.data.employees;
        })
        .catch(error => {
          console.error('Error fetching employees:', error);
        });
    },
    deleteEmployee(id) {
      if (confirm('Confirm action: DELETE')) {
        axios.delete(`http://localhost:8001/api/employees/${id}`)
          .then(res => {
            alert(res.data.message);
            this.getEmployees();
          })
          .catch(error => {
            console.error('Error deleting employee:', error);
          });
      }
    },
  },
};
</script>