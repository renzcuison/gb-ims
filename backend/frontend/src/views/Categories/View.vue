<template>
  <div class="container">
    <div class="card mb-5">
      <div class="card-header">
        <h4>
          Categories
          <RouterLink to="/categories/create" class="btn btn-primary float-end">Add</RouterLink>
        </h4>
      </div>
      <div class="card-body">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Description</th>
            </tr>
          </thead>
          <tbody v-if="categories.length > 0">
            <tr v-for="(category, index) in categories" :key="index">
              <td>{{ category.id }}</td>
              <td>{{ category.category_name }}</td>
              <td>{{ category.description }}</td>
              <td>
                <RouterLink :to="{ path: '/categories/' + category.id + '/edit' }" class="btn btn-success">Edit</RouterLink>
                <button type="button" @click="deleteCategory(category.id)" class="btn btn-danger">Delete</button>
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
  name: 'Categories',
  data() {
    return {
      categories: [],
    };
  },
  mounted() {
    this.getCategories();
  },
  methods: {
    getCategories() {
      axios.get('http://localhost:8001/api/categories')
        .then(res => {
          this.categories = res.data.categories;
        })
        .catch(error => {
          console.error('Error fetching category:', error);
        });
    },
    deleteCategory(id) {
      if (confirm('Confirm action: DELETE')) {
        axios.delete(`http://localhost:8001/api/categories/${id}`)
          .then(res => {
            alert(res.data.message);
            this.getCategories();
          })
          .catch(error => {
            console.error('Error deleting category:', error);
          });
      }
    },
  },
};
</script>