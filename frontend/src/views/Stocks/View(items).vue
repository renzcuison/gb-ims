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
        <div class="d-flex">
          <div class="flex-grow-1 me-3">
            <input type="text" v-model="searchQuery" @input="filterItems" class="form-control"
              placeholder="Enter Search Query" />
          </div>
          <div class="d-flex align-items-center">
            <label class="form-check-label me-1 mb-0 d-inline">Sort</label>
            <label class="form-check-label me-2 mb-0 d-inline">by:</label>
            <select v-model="sortOption" class="form-select" @change="applySearchAndSort">
              <option value="alpha-asc">Alphabetical: A-Z</option>
              <option value="alpha-desc">Alphabetical: Z-A</option>
              <option value="price-asc">Price: Low to High</option>
              <option value="price-desc">Price: High to Low</option>
              <option value="all">View All</option>
            </select>
          </div>
        </div>

        <div class="mb-3">
          <div>
            <label class="form-check-label search-by-label">Search By:</label>
            <label class="form-check-label search-by-label">
              <input type="radio" v-model="searchBy" value="id" class="form-check-input" @change="filterItems" />
              ID
            </label>
            <label class="form-check-label search-by-label">
              <input type="radio" v-model="searchBy" value="name" class="form-check-input" @change="filterItems" />
              Item Name
            </label>
          </div>
        </div>

        <table class="table table-bordered">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Description</th>
              <th>Category</th>
              <th>Supplier</th>
              <th>Price /unit</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody v-if="filteredItems.length > 0">
            <tr v-for="item in filteredItems" :key="item.id">
              <td>{{ item.id }}</td>
              <td>{{ item.name }}</td>
              <td>{{ item.description }}</td>
              <td>{{ item.category ? item.category.category_name : "N/A" }}</td>
              <td>{{ item.supplier ? item.supplier.supplier_name : "N/A" }}</td>
              <td>{{ item.price_per_unit }}</td>
              <td>
                <RouterLink :to="{ path: '/items/' + item.id + '/edit' }" class="btn btn-success">Edit</RouterLink>
                <button type="button" @click="deleteItem(item.id)" class="btn btn-danger">Delete</button>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="7">↺</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "Items",
  data() {
    return {
      items: [],
      filteredItems: [],
      searchQuery: "",
      searchBy: "id",
      sortOption: "all",
    };
  },
  mounted() {
    this.getItems();
  },
  methods: {
    getItems() {
      const apiUrl = "http://localhost:8001/api/items";
      axios
        .get(apiUrl)
        .then((res) => {
          this.items = res.data.items;
          this.filteredItems = [...this.items];
          this.applySort();
        })
        .catch((error) => {
          console.error("Error fetching items:", error);
        });
    },
    deleteItem(id) {
      if (confirm("Confirm action: DELETE")) {
        axios
          .delete(`http://localhost:8001/api/items/${id}`)
          .then((res) => {
            alert(res.data.message);
            this.getItems();
          })
          .catch((error) => {
            console.error("Error deleting item:", error);
          });
      }
    },
    filterItems() {
      const query = this.searchQuery.toLowerCase().trim();

      if (!query) {
        this.filteredItems = [...this.items];
      } else {
        if (this.searchBy === "id") {
          this.filteredItems = this.items.filter((item) =>
            item.id.toString().includes(query)
          );
        } else if (this.searchBy === "name") {
          this.filteredItems = this.items.filter((item) =>
            item.name.toLowerCase().includes(query)
          );
        }
      }

      this.applySort();

    },

    applySort() {
      let sortedItems = [...this.filteredItems];

      if (this.sortOption === "alpha-asc") {
        sortedItems.sort((a, b) => a.name.localeCompare(b.name));
      } else if (this.sortOption === "alpha-desc") {
        sortedItems.sort((a, b) => b.name.localeCompare(a.name));
      } else if (this.sortOption === "price-asc") {
        sortedItems.sort((a, b) => a.price_per_unit - b.price_per_unit);
      } else if (this.sortOption === "price-desc") {
        sortedItems.sort((a, b) => b.price_per_unit - a.price_per_unit);
      } else if (this.sortOption === "all") {
        sortedItems = [...this.items];
      }

      if (this.searchQuery.trim()) {
        const query = this.searchQuery.toLowerCase();
        if (this.searchBy === "id") {
          sortedItems = sortedItems.filter(item =>
            item.id.toString().includes(query)
          );
        } else if (this.searchBy === "name") {
          sortedItems = sortedItems.filter(item =>
            item.name.toLowerCase().includes(query)
          );
        }
      }

      this.filteredItems = sortedItems;
    },

    applySearchAndSort() {
      this.filterItems();
    },

  },
};
</script>

<style scoped>
.search-by-label {
  margin-left: 14px;
  font-size: 12px;
}

.form-select {
  color: grey;
  font-size: 12px;
}

.form-check-label {
  font-size: 12px;
}

.form-control {
  font-size: 12px;
}
</style>
