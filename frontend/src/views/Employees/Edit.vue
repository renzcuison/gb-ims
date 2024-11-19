<template>
  <div class="container mt-4">  
    <div class="card mb-5">
      <div class="card-header">
        <h4>Edit Employee</h4>
      </div>
      <div class="card-body">
        <ul class="alert alert-warning" v-if="Object.keys(errorList).length > 0">
          <li class="mb-0 ms-3" v-for="(error, index) in errorList" :key="index">
            {{ error[0] }}  
          </li>
        </ul>
        <div class="mb-3">
          <label for="">Last Name</label>
          <input type="text" v-model="model.employee.last_name" class="form-control">
        </div>
        <div class="mb-3">
          <label for="">First Name</label>
          <input type="text" v-model="model.employee.first_name" class="form-control">
        </div>
        <div class="mb-3">
          <label for="">Role</label>
          <input type="text" v-model="model.employee.role" class="form-control">
        </div>
        <RouterLink to="/employees" class="btn btn-primary float-end">Back</RouterLink>
        <button type="button" @click="updateEmployee" class="btn btn-primary">Save</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'EmployeeEdit',
  data() {
    return {
      employeeId: '',
      errorList: '',
      model: {
        employee: {
          last_name: '',
          first_name: '',
          role: '',
        }
      }
    }
  },
  mounted() {
    this.employeeId = this.$route.params.id;
    this.getEmployeeData(this.employeeId);
  },
  methods: {
    getEmployeeData(employeeId) {
      axios.get(`http://localhost:8001/api/employees/${employeeId}`)
        .then(res => {
          this.model.employee = res.data.employee;
        })
        .catch(error => {
          if (error.response && error.response.status === 404) {
            alert(error.response.data.message);
          }
        });
    },
    updateEmployee() {
      axios.put(`http://localhost:8001/api/employees/${this.employeeId}`, this.model.employee)
        .then(res => {
          alert(res.data.message);
          this.errorList = '';
          this.$router.push('/employees');
        })
        .catch(error => {
          if (error.response) {
            if (error.response.status === 422) {
              this.errorList = error.response.data.errors;
            } else if (error.response.status === 404) {
              alert(error.response.data.message);
            }
          }
        });
    }
  }
}
</script>