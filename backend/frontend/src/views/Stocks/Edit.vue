<template>
  <div class="container mt-4">
    <div class="card mb-5">
      <div class="card-header">
        <h4>Edit Stock</h4>
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
        <button type="button" @click="updateStock" class="btn btn-primary">Save</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'StocksEdit',
  data() {
    return {
      stockId: '',
      errorList: '',
      model: {
        item_name: '',
        quantity: ''
      }
    }
  },
  mounted() {
    this.stockId = this.$route.params.id
    this.getStockData(this.stockId)
  },
  methods: {
    getStockData(stockId) {
      axios.get(`http://localhost:8001/api/stocks/${stockId}`)
        .then(res => {
          this.model = res.data.stock
        })
        .catch(error => {
          if (error.response && error.response.status === 404) {
            alert(error.response.data.message)
          }
        })
    },
    updateStock() {
      axios.put(`http://localhost:8001/api/stocks/${this.stockId}`, this.model)
        .then(res => {
          alert(res.data.message)
          this.errorList = ''
          this.$router.push('/stocks')
        })
        .catch(error => {
          if (error.response) {
            if (error.response.status === 422) {
              this.errorList = error.response.data.errors
            } else if (error.response.status === 404) {
              alert(error.response.data.message)
            }
          }
        })
    }
  }
}
</script>
