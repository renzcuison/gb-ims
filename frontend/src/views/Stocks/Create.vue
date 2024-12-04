<template>
  <div class="container mt-4">
    <div class="card mb-5">
      <div class="card-header">
        <h4>Add Stock/s</h4>
      </div>
      <div class="card-body">
        <ul class="alert alert-warning" v-if="Object.keys(errorList).length > 0">
          <li class="mb-0 ms-3" v-for="(error, index) in errorList" :key="index">
            {{ error[0] }}
          </li>
        </ul>

        <div v-for="(stock, index) in model.stocks" :key="index" class="mb-4">
          <div class="mb-3">
            <label for="item_name">
              <strong>STOCK #{{ index + 1 }}</strong>
            </label>
            <input
              type="text"
              v-model="stock.item_name"
              class="form-control"
              :class="{ 'is-invalid': isDuplicate(stock.item_name, index) }"
              placeholder="Item Name"
            />
            <div v-if="isDuplicate(stock.item_name, index)" class="text-danger">
              Duplicate item name.
            </div>
          </div>

          <div class="mb-3">
            <label for="description">Description</label>
            <input type="text" v-model="stock.description" class="form-control" placeholder="Description" />
          </div>

          <div class="mb-3">
            <label for="category_id">Category</label>
            <select v-model="stock.category_id" class="form-select">
              <option value="" disabled selected></option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.category_name }}
              </option>
            </select>
          </div>

          <div class="mb-3">
            <label for="supplier_id">Supplier</label>
            <select v-model="stock.supplier_id" class="form-select">
              <option value="" disabled selected></option>
              <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                {{ supplier.supplier_name }}
              </option>
            </select>
          </div>

          <div class="mb-3">
            <label for="unit_of_measure">Unit of Measure</label>
            <select
              v-model="stock.unit_of_measure"
              @blur="checkDuplicate(stock.item_name, stock.unit_of_measure, index)"
              class="form-select"
            >
              <option value="" disabled selected></option>
              <option v-for="unit in units" :key="unit" :value="unit">{{ unit }}</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="price_per_unit">Price per Unit (₱)</label>
            <input
              type="text"
              v-model="stock.price_per_unit"
              @input="handlePriceInput($event, index)"
              @blur="formatPrice(stock, index)"
              class="form-control"
              placeholder="Enter Price"
            />
          </div>

          <button
            v-if="index === model.stocks.length - 1"
            type="button"
            @click="addStock"
            class="btn btn-secondary me-3"
          >
            Add Another Stock
          </button>

          <button
            v-if="index > 0"
            type="button"
            @click="removeStock(index)"
            class="btn btn-danger"
          >
            Remove Stock
          </button>
        </div>

        <RouterLink to="/stocks" class="btn btn-primary float">Back</RouterLink>
        <button type="button" @click="saveStocks" class="btn btn-primary float-end">
          Confirm
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "StockCreate",
  data() {
    return {
      errorList: {},
      model: {
        stocks: [
          {
            item_name: "",
            description: "",
            category_id: "",
            supplier_id: "",
            unit_of_measure: "",
            price_per_unit: "",
            physical_count: 0,
            on_hand: 0,
            sold: 0,
          },
        ],
      },
      categories: [],
      suppliers: [],
      units: ['Pc', 'Box', 'Kg', 'G', 'Liter', 'Ml', 'Meter', 'Cm', 'Bundle'],
      checkTimeout: null,
      duplicateItems: [],
    };
  },
  mounted() {
    this.fetchCategories();
    this.fetchSuppliers();
  },
  methods: {
    isDuplicate(name, currentIndex) {
      const normalizedName = name.trim().toLowerCase();
      if (!normalizedName) return false;
      return this.model.stocks.some(
        (stock, index) =>
          index !== currentIndex &&
          stock.item_name.trim().toLowerCase() === normalizedName
      );
    },

    hasDuplicate() {
      return this.model.stocks.some((stock, index) => this.isDuplicate(stock.item_name, stock.unit_of_measure, index));
    },

    checkDuplicate(itemName, unitOfMeasure, index) {
      if (!itemName.trim() || !unitOfMeasure.trim()) {
        delete this.errorList[`${itemName}-${unitOfMeasure}`];
        return false;
      }

      clearTimeout(this.checkTimeout);
      this.checkTimeout = setTimeout(() => {
        axios
          .get(`http://localhost:8001/api/stocks/check-duplicate`, {
            params: { item_name: itemName, unit_of_measure: unitOfMeasure },
          })
          .then((response) => {
            if (response.data.exists) {
              this.errorList[`${itemName}-${unitOfMeasure}`] = "This stock already exists.";
            } else {
              delete this.errorList[`${itemName}-${unitOfMeasure}`];
            }
          })
          .catch((error) => {
            console.error("Error checking duplicate:", error);
          });
      }, 300);
      return false;
    },

    addStock() {
      this.model.stocks.push({
        item_name: "",
        description: "",
        category_id: "",
        supplier_id: "",
        unit_of_measure: "",
        price_per_unit: "",
        physical_count: 0,
        on_hand: 0,
        sold: 0,
      });
    },

    removeStock(index) {
      this.model.stocks.splice(index, 1);
    },

    saveStocks() {
      const hasDuplicate = this.model.stocks.some((stock, index) =>
        this.isDuplicate(stock.item_name, index)
      );

      if (hasDuplicate) {
        alert("There are duplicate item names in your stocks. Please resolve them before saving.");
        return;
      }

      const savePromises = this.model.stocks.map((stock) => {
        const payload = {
          ...stock,
          price_per_unit: parseFloat(stock.price_per_unit.replace(/[^0-9.]/g, "")),
        };

        return axios.post("http://localhost:8001/api/stocks", payload)
          .then((response) => {
            console.log("Stock saved successfully:", response.data);
          })
          .catch((error) => {
            if (error.response && error.response.status === 409) {
              alert(error.response.data.message);
            } else {
              console.error("Error saving stock:", error);
            }
            throw error;
          });
      });

      Promise.allSettled(savePromises)
        .then((results) => {
          const failed = results.filter((result) => result.status === "rejected");

          if (failed.length > 0) {
            alert("Some stocks could not be saved due to errors. Check the console for details.");
          } else {
            alert("All stocks saved successfully.");
            this.$router.push("/stocks");
          }
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

    handlePriceInput(event, index) {
      let input = event.target.value;
      input = String(input).replace(/[^0-9.]/g, "");

      if ((input.match(/\./g) || []).length > 1) {
        input = input.slice(0, input.lastIndexOf('.'));
      }

      this.model.stocks[index].price_per_unit = input;
    },

    formatPrice(stock, index) {
      let value = String(stock.price_per_unit);
      value = value.replace(/[^0-9.]/g, '');

      let formattedValue = parseFloat(value);
      if (isNaN(formattedValue)) {
        formattedValue = 0;
      }

      stock.price_per_unit = '₱ ' + formattedValue.toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
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
