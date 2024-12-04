<template>
  <div class="container mt-4">  
    <div class="card mb-5">
      <div class="card-header">
        <h4>New Customer</h4>
      </div>
      <div class="card-body">
        <ul class="alert alert-warning" v-if="Object.keys(errorList).length > 0">
          <li class="mb-0 ms-3" v-for="(error, index) in errorList" :key="index">
            {{ error[0] }}  
          </li>
        </ul>
        <div class="mb-3">
          <label for="">Last Name</label>
          <input type="text" v-model="model.customer.last_name" class="form-control">
        </div>
        <div class="mb-3">
          <label for="">First Name</label>
          <input type="text" v-model="model.customer.first_name" class="form-control">
        </div>
        <div class="mb-3">
          <label for="">Phone</label>
          <input type="text" v-model="model.customer.phone" class="form-control">
        </div>
        <div class="mb-3">
          <label for="">Email</label>
          <input type="email" v-model="model.customer.email" class="form-control">
        </div>
        <RouterLink to="/customers" class="btn btn-primary float-end">Back</RouterLink>
        <button type="button" @click="saveCustomer" class="btn btn-primary">Add</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'CustomerCreate',
  data() {
    return {
      errorList: '',
      model: {
        customer: {
          last_name: '',
          first_name: '',
          phone: '',
          email: '',
        }
      }
    }
  },
  methods: {
    saveCustomer() {
      axios.post('http://localhost:8001/api/customers', this.model.customer)
        .then(res => {
          alert(res.data.message);
          this.model.customer = {
            last_name: '',
            first_name: '',
            phone: '',
            email: '',
          }
          this.errorList = '';
          this.$router.push('/customers');
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
