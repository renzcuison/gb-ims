<template>
  <div class="container mt-4">  
    <div class="card mb-5">
      <div class="card-header">
        <h4>New Employee</h4>
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
        <button type="button" @click="saveEmployee" class="btn btn-primary">Add</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'EmployeeCreate',
  data() {
    return {
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
  methods: {
    saveEmployee() {
      axios.post('http://localhost:8001/api/employees', this.model.employee)
        .then(res => {
          alert(res.data.message);
          this.model.employee = {
            last_name: '',
            first_name: '',
            role: '',
          }
          this.errorList = '';
          this.$router.push('/employees');
        })
        .catch(error => {
          if (error.response) {
            if (error.response.status == 422) {
              this.errorList = error.response.data.errors;
            } else {
              console.log('Error', error.message);
            }
          }
        });
    }
  }
}
</script>
