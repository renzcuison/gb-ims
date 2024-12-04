<template>
  <div class="container mt-4">  
    <div class="card mb-5">
      <div class="card-header">
        <h4>Edit Customer</h4>
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
        <button type="button" @click="updateCustomer" class="btn btn-primary">Save</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'CustomerEdit',
  data() {
    return {
      customerId: '',
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
  mounted() {
    this.customerId = this.$route.params.id;
    this.getCustomerData(this.customerId);
  },
  methods: {
    getCustomerData(customerId) {
      axios.get(`http://localhost:8001/api/customers/${customerId}`)
        .then(res => {
          this.model.customer = res.data.customer;
        })
        .catch(error => {
          if (error.response && error.response.status === 404) {
            alert(error.response.data.message);
          }
        });
    },
    updateCustomer() {
      axios.put(`http://localhost:8001/api/customers/${this.customerId}`, this.model.customer)
        .then(res => {
          alert(res.data.message);
          this.errorList = '';
          this.$router.push('/customers');
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
