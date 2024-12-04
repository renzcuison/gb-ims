<template>
  <div class="container">
    <div class="card mb-5 shadow-sm border-0">
      <div class="card-header text-white d-flex align-items-center justify-content-between">
        <h4 class="m-0">Categories</h4>
        <RouterLink to="/categories/create" class="btn btn-light btn-sm fw-bold">+ Add Category</RouterLink>
      </div>
      <div class="card-body">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Description</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody v-if="categories.length > 0">
            <tr v-for="(category, index) in categories" :key="index">
              <td>{{ category.id }}</td>
              <td>{{ category.category_name }}</td>
              <td>{{ category.description }}</td>
              <td class="text-center">
                <RouterLink
                  :to="{ path: '/categories/' + category.id + '/edit' }"
                  class="btn btn-outline-primary btn-sm me-2"
                >
                  ✎ Edit
                </RouterLink>
                <button
                  type="button"
                  @click="deleteCategory(category.id)"
                  class="btn btn-outline-danger btn-sm"
                >
                  🗑️ Delete
                </button>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="4" class="text-center text-muted py-4">
                <i class="bi bi-folder-x display-6"></i>
                <p class="m-0">No categories found.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<style scoped>
.card {
  border-radius: 12px;
  overflow: hidden;
}

.card-header {
  font-weight: bold;
  font-size: 1.25rem;
  background: #0a3992;
}

.table {
  border-collapse: separate;
  border-spacing: 0 10px;
}

.table tbody tr {
  background-color: #f9f9f9;
  transition: transform 0.2s, box-shadow 0.2s;
}

.table tbody tr:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.table-light th {
  font-size: 1rem;
  color: #555;
}

.btn {
  font-size: 0.875rem;
}

.bi {
  font-size: 2rem;
}
</style>

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