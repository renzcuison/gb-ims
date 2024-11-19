<template>
  <div class="container">
    <div class="card mb-5">
      <div class="card-header">
        <h4>
          Items
          <RouterLink to="/items/create" class="btn btn-primary float-end">Add</RouterLink>
        </h4>
      </div>
      <div class="card-body">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Description</th>
              <th>Category</th>
              <th>Supplier</th>
              <th>Price /unit</th>
            </tr>
          </thead>
          <tbody v-if="items.length > 0">
            <tr v-for="item in items" :key="item.id">
              <td>{{ item.id }}</td>
              <td>{{ item.name }}</td>
              <td>{{ item.description }}</td>
              <td>{{ item.category ? item.category.category_name : 'N/A' }}</td>
              <td>{{ item.supplier ? item.supplier.supplier_name : 'N/A' }}</td>
              <td>{{ item.price_per_unit }}</td>
              <td>
                <RouterLink :to="{ path: '/items/' + item.id + '/edit' }" class="btn btn-success">Edit</RouterLink>
                <button type="button" @click="deleteItem(item.id)" class="btn btn-danger">Delete</button>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="8">↺</td>
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
  name: 'Items',
  data() {
    return {
      items: [],
    };
  },
  mounted() {
    this.getItems();
  },
  methods: {
    getItems() {
      const apiUrl = 'http://localhost:8001/api/items';
      axios.get(apiUrl)
        .then(res => {
          console.log('API Response:', res.data); 
          this.items = res.data.items;
        })
        .catch(error => {
          console.error('Error fetching items:', error); 
        });
    },
    deleteItem(id) {
      if (confirm('Confirm action: DELETE')) {
        axios.delete(`http://localhost:8001/api/items/${id}`)
          .then(res => {
            alert(res.data.message);
            this.getItems();
          })
          .catch(error => {
            console.error('Error deleting item:', error); 
          });
      }
    },
  },
};
</script>