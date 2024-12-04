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
          <label for="item_name">Item Name</label>
          <input type="text" id="item_name" v-model="model.item.item_name" class="form-control">
        </div>

        <div class="mb-3">
          <label for="description">Description</label>
          <input type="text" id="description" v-model="model.item.description" class="form-control">
        </div>

        <div class="mb-3">
          <label for="category_id">Category</label>
          <select id="category_id" v-model="model.item.category_id" class="form-select">
            <option value="" disabled>Select Category</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.category_name }}
            </option>
          </select>
        </div>

        <div class="mb-3">
          <label for="supplier_id">Supplier</label>
          <select id="supplier_id" v-model="model.item.supplier_id" class="form-select">
            <option value="" disabled>Select Supplier</option>
            <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
              {{ supplier.supplier_name }}
            </option>
          </select>
        </div>

        <div class="mb-3">
          <label for="price_per_unit">Price</label>
          <input type="text" id="price_per_unit" v-model="model.item.price_per_unit" class="form-control">
        </div>

        <!-- Unit of Measure Dropdown -->
        <div class="mb-3">
          <label for="unit_of_measure">Unit of Measure</label>
          <select v-model="model.item.unit_of_measure" class="form-select">
            <option value="" disabled>Select Unit</option>
            <option v-for="unit in units" :key="unit" :value="unit">{{ unit }}</option>
          </select>
        </div>

        <!-- Removed Quantity Fields (physical_count, on_hand, sold) -->

        <RouterLink to="/stocks" class="btn btn-primary float-end">Back</RouterLink>
        <button type="button" @click="updateStock" class="btn btn-primary">Save</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'StockEdit',
  data() {
    return {
      model: {
        item: {
          id: '',
          item_name: '',
          description: '',
          category_id: '',
          supplier_id: '',
          price_per_unit: '',
          unit_of_measure: '', // Added unit_of_measure field
        }
      },
      categories: [],
      suppliers: [],
      units: ['Pc', 'Box', 'Kg', 'G', 'Liter', 'Ml', 'Meter', 'Cm', 'Bundle'], // Unit options for dropdown
      errorList: {}
    };
  },
  mounted() {
    this.fetchCategories();
    this.fetchSuppliers();
    this.getItem();
  },
  methods: {
    getItem() {
      const id = this.$route.params.id;
      axios.get(`http://localhost:8001/api/stocks/${id}`)
        .then(res => {
          this.model.item = res.data.stock;
        })
        .catch(error => {
          console.error('Error fetching stock:', error);
        });
    },
    updateStock() {
      const id = this.$route.params.id;
      axios.put(`http://localhost:8001/api/stocks/${id}`, this.model.item)
        .then(res => {
          alert(res.data.message);
          this.$router.push('/stocks');
        })
        .catch(error => {
          if (error.response) {
            if (error.response.status === 422) {
              this.errorList = error.response.data.errors;
            } else {
              console.error('Error:', error.response.data.message);
            }
          } else {
            console.error('Error:', error.message);
          }
        });
    },
    fetchCategories() {
      axios.get('http://localhost:8001/api/categories')
        .then(response => {
          this.categories = response.data.categories;
        })
        .catch(error => {
          console.error('Error fetching categories:', error);
        });
    },
    fetchSuppliers() {
      axios.get('http://localhost:8001/api/suppliers')
        .then(response => {
          this.suppliers = response.data.suppliers;
        })
        .catch(error => {
          console.error('Error fetching suppliers:', error);
        });
    }
  }
};
</script>
