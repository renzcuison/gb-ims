<template>
    <div class="modal-overlay">
      <div class="modal-content">
        <div class="modal-header">
          <h4>Select Items</h4>
        </div>
  
        <div class="modal-body">
          <input type="text" v-model="searchQuery" placeholder="Search" class="form-control mb-2" />
  
          <!-- Filter Row with Brands and Category Side by Side -->
          <div class="filter-row">
            <div class="brand-select">
              <span class="brand-label">Brands</span>
              <select v-model="selectedBrand" class="form-select">
                <option value="" disabled selected hidden>Select Brand</option>
                <option value="">...</option>
                <option value="Nike">Nike</option>
                <option value="Adidas">Adidas</option>
                <option value="Uniqlo">Uniqlo</option>
                <option value="Chanel">Chanel</option>
                <option value="Dior">Dior</option>
                <option value="Gucci">Gucci</option>
                <option value="Michael Kors">Michael Kors</option>
                <option value="L'Oreal">L'Oreal</option>
                <option value="Converse">Converse</option>
              </select>
            </div>
  
            <!-- Sort Button with Dropdown -->
            <div class="sort-dropdown">
              <button class="sort-button" @click="toggleSortDropdown">
                Sort <FontAwesomeIcon :icon="faSort" class="sort-icon" />
              </button>
              <div v-if="showSortDropdown" class="dropdown-menu">
                <button v-for="category in categories" 
                        :key="category.id" 
                        @click="sortByCategory(category.category_name)">
                  Sort by {{ category.category_name }}
                </button>
                <button @click="sortByCategory('')">Clear Sort</button>
              </div>
            </div>
          </div>
  
          <!-- Item Grid -->
          <div class="item-grid">
            <div
              v-for="item in filteredItems"
              :key="item.item_id"
              class="item-box"
              :class="{ 'selected': selectedItems.includes(item) }"
              @click="selectItem(item)"
            >
              <span>X</span> <!-- Placeholder for item image -->
              <p>{{ item.name }}</p>
            </div>
          </div>
        </div>
  
        <div class="modal-footer">
          <button @click="$emit('close')" class="btn btn-secondary">Close</button>
          <button @click="$emit('select', selectedItems)" class="btn btn-primary">Select</button>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import { faSort } from '@fortawesome/free-solid-svg-icons';
  
  export default {
    components: { FontAwesomeIcon },
    data() {
      return {
        faSort,
        searchQuery: '',
        selectedBrand: '',
        selectedCategory: '',
        items: [],
        categories: [],
        showSortDropdown: false,
        selectedSortCategory: '',
        selectedItems: []
      };
    },
    computed: {
      filteredItems() {
        return this.items.filter(item => {
          const matchesSearch = this.searchQuery
            ? item.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
              item.brand.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
              (item.category && typeof item.category === 'string' && item.category.toLowerCase().includes(this.searchQuery.toLowerCase())) ||
              (item.description && item.description.toLowerCase().includes(this.searchQuery.toLowerCase()))
            : true;
  
          const matchesBrand = this.selectedBrand ? item.brand === this.selectedBrand : true;
          const matchesCategory = this.selectedCategory ? (item.category && typeof item.category === 'string' && item.category === this.selectedCategory) : true;
          const matchesSortCategory = this.selectedSortCategory
            ? item.category === this.selectedSortCategory
            : true;
  
          return matchesSearch && matchesBrand && matchesCategory && matchesSortCategory;
        });
      }
    },
    methods: {
      toggleSortDropdown() {
        this.showSortDropdown = !this.showSortDropdown;
      },
      sortByCategory(category) {
        this.selectedSortCategory = category;
        this.selectedCategory = category; // Update the selected category as well
      },
      selectItem(item) {
        if (!this.selectedItems.includes(item)) {
          this.selectedItems.push(item);
        }
      },
      fetchItems() {
        axios.get('http://localhost:8001/api/items').then(response => {
          this.items = response.data.items;
        });
      },
      fetchCategories() {
        axios.get('http://localhost:8001/api/categories').then(response => {
          this.categories = response.data.categories;
        });
      }
    },
    mounted() {
      this.fetchItems();
      this.fetchCategories();
    }
  };
  </script>
  
  
  <style>
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .modal-content {
    background: white;
    padding: 20px;
    border-radius: 5px;
    width: 80%;
    max-width: 600px;
  }
  
  .modal-footer {
    display: flex;
    justify-content: space-between;
    padding: 10px;
    height: 50px;
  }
  
  .modal-footer .btn {
    padding: 10px 20px;
  }
  
  .item-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
  }
  
  .item-box {
    border: 1px solid #ccc;
    padding: 10px;
    text-align: center;
    cursor: pointer;
  }
  
  .filter-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
  }
  
  .brand-select {
    display: flex;
    align-items: center;
    margin-right: 45%;
    width: 200px;
  }
  
  .brand-label {
    font-weight: bold;
    margin-right: 5px;
  }
  
  .form-select {
    padding: 5px;
  }
  
  .sort-button {
    background: none;
    border: none;
    color: #000;
    font-weight: bold;
    cursor: pointer;
  }
  
  .sort-icon {
    margin-left: 5px;
  }
  
  .sort-dropdown {
    position: relative;
  }
  
  .dropdown-menu {
    position: absolute;
    background-color: white;
    border: 1px solid #ccc;
    top: 100%;
    left: 0;
    padding: 5px;
    width: 200px;
    display: block;
  }
  
  .dropdown-menu button {
    padding: 10px;
    border: none;
    width: 100%;
    text-align: left;
    background-color: white;
    cursor: pointer;
  }
  
  .dropdown-menu button:hover {
    background-color: #f1f1f1;
  }
  
  .selected {
    border: 2px solid blue;
  }
  </style>
  