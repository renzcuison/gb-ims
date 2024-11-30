<template>
  <div class="container mt-4">
    <div class="card mb-5">
      <div class="card-header">
        <h4>Add New Items</h4>
      </div>
      <div class="card-body">
        <ul class="alert alert-warning" v-if="Object.keys(errorList).length > 0">
          <li class="mb-0 ms-3" v-for="(error, index) in errorList" :key="index">
            {{ error[0] }}
          </li>
        </ul>

        <div v-for="(item, index) in model.items" :key="index" class="mb-4">
          <div class="mb-3">
            <label for="item_name">
              <strong>ITEM #{{ index + 1 }}</strong>
            </label>
            <input type="text" v-model="item.name" @input="checkDuplicate(item.name, index)" class="form-control"
              :class="{ 'is-invalid': isDuplicate(item.name, index) }" placeholder="" />
            <div v-if="isDuplicate(item.name, index)" class="text-danger">
              Duplicate item name.
            </div>
          </div>
          <div class="mb-3">
            <label for="description">Description</label>
            <input type="text" v-model="item.description" class="form-control" placeholder="" />
          </div>
          <div class="mb-3">
            <label for="category_id">Category</label>
            <select v-model="item.category_id" class="form-select">
              <option value="" disabled selected></option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.category_name }}
              </option>
            </select>
          </div>
          <div class="mb-3">
            <label for="supplier_id">Supplier</label>
            <select v-model="item.supplier_id" class="form-select">
              <option value="" disabled selected></option>
              <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                {{ supplier.supplier_name }}
              </option>
            </select>
          </div>
          <div class="mb-3">
            <label for="price_per_unit">Price/unit</label>
            <input type="text" v-model="item.price_per_unit" class="form-control" placeholder="" />
          </div>

          <button v-if="index === model.items.length - 1" type="button" @click="addItem" class="btn btn-secondary me-3">
            Add Another Item
          </button>

          <button v-if="index > 0" type="button" @click="removeItem(index)" class="btn btn-danger">
            Remove Item
          </button>
        </div>

        <RouterLink to="/items" class="btn btn-primary float">Back</RouterLink>
        <button type="button" @click="saveItems" class="btn btn-primary float-end" :disabled="hasDuplicate()">
          Confirm
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "ItemCreate",
  data() {
    return {
      errorList: {},
      model: {
        items: [
          {
            name: "",
            description: "",
            category_id: "",
            supplier_id: "",
            price_per_unit: "",
          },
        ],
      },
      categories: [],
      suppliers: [],
      checkTimeout: null,
      duplicateItems: [],
    };
  },
  mounted() {
    this.fetchCategories();
    this.fetchSuppliers();
  },
  methods: {
    checkDuplicate(itemName, index) {
      if (!itemName.trim()) {
        delete this.errorList[this.model.items[index].name];
        return;
      }

      clearTimeout(this.checkTimeout);
      this.checkTimeout = setTimeout(() => {
        console.log('Checking duplicate for:', itemName);
        axios.get(`http://localhost:8001/api/items/check-duplicate?name=${itemName}`)
          .then((response) => {
            console.log('API Response:', response.data);
            if (response.data.exists) {
              this.errorList[itemName] = "This item already exists.";
            } else {
              delete this.errorList[itemName];
            }
          })
          .catch((error) => {
            console.error('Error checking duplicate:', error);
          });
      }, 300);
    },

    isDuplicate(name, currentIndex) {
      const normalizedName = name.trim().toLowerCase();

      return this.duplicateItems.includes(normalizedName) || this.model.items.some(
        (item, index) => index !== currentIndex && item.name.trim().toLowerCase() === normalizedName
      );
    },

    hasDuplicate() {
      return this.model.items.some((item, index) => this.isDuplicate(item.name, index));
    },

    addItem() {
      this.model.items.push({
        name: "",
        description: "",
        category_id: "",
        supplier_id: "",
        price_per_unit: "",
      });
    },

    removeItem(index) {
      this.model.items.splice(index, 1);
    },

    saveItems() {
      if (this.hasDuplicate()) {
        alert("Resolve duplicate item names before saving.");
        return;
      }

      const savePromises = this.model.items.map((item) =>
        axios.post("http://localhost:8001/api/items", item)
      );

      Promise.allSettled(savePromises)
        .then((results) => {
          const failedItems = results.filter(
            (result) => result.status === "rejected"
          );

          if (failedItems.length > 0) {
            alert("Some items could not be saved. Please check the errors.");
          } else {
            alert("All items have been saved successfully.");
            this.model.items = [
              {
                name: "",
                description: "",
                category_id: "",
                supplier_id: "",
                price_per_unit: "",
              },
            ];
            this.errorList = {};
            this.$router.push("/items");
          }
        })
        .catch((error) => {
          console.error("Error while saving items:", error);
          alert("An error occurred while saving items.");
        });
    },

    fetchCategories() {
      axios
        .get("http://localhost:8001/api/categories")
        .then((response) => {
          this.categories = response.data.categories;
        })
        .catch((error) => {
          console.error("Error fetching categories:", error);
        });
    },

    fetchSuppliers() {
      axios
        .get("http://localhost:8001/api/suppliers")
        .then((response) => {
          this.suppliers = response.data.suppliers;
        })
        .catch((error) => {
          console.error("Error fetching suppliers:", error);
        });
    },
  },
};
</script>

<style>
.is-invalid {
  border-color: red !important;
}
</style>
