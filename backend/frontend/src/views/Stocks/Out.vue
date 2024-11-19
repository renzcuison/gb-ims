<template>
  <div class="container mt-4">
    <div class="card mb-5">
      <div class="card-header">
        <h4>Stock Out</h4>
      </div>
      <div class="card-body">
        <ul class="alert alert-warning" v-if="Object.keys(errorList).length > 0">
          <li class="mb-0 ms-3" v-for="(error, index) in errorList" :key="index">
            {{ error[0] }}
          </li>
        </ul>
        <div class="mb-3">
          <label for="">Item Name</label>
          <input type="text" v-model="model.item_name" class="form-control">
        </div>
        <div class="mb-3">
          <label for="">Quantity</label>
          <input type="number" v-model="model.quantity" class="form-control">
        </div>
        <RouterLink to="/stocks" class="btn btn-primary float-end">Back</RouterLink>
        <button type="button" @click="saveStock" class="btn btn-primary">Confirm</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'StocksCreate',
  data() {
    return {
      errorList: '',
      model: {
        item_name: '',
        quantity: ''
      }
    }
  },
  methods: {
    saveStock() {
      axios.post('http://localhost:8001/api/stocks', this.model)
        .then(res => {
          alert(res.data.message)
          this.model = {
            item_name: '',
            quantity: ''
          }
          this.errorList = ''
          this.$router.push('/stocks')
        })
        .catch(error => {
          if (error.response) {
            if (error.response.status == 422) {
              this.errorList = error.response.data.errors
            } else {
              console.log('Error', error.message)
            }
          }
        })
    }
  }
}
</script>
