<template>
  <div class="container mt-4">
    <div class="card mb-5">
      <div class="card-header">
        <h4>Stock In</h4>
      </div>
      <div class="card-body">
        <ul class="alert alert-warning" v-if="Object.keys(errorList).length > 0">
          <li class="mb-0 ms-3" v-for="(error, index) in errorList" :key="index">
            {{ error[0] }}
          </li>
        </ul>

        <div class="mb-3">
          <label for="item_id">Item Name</label>
          <select v-model="model.item_id" @change="updateItemDetails" class="form-control">
            <option v-for="item in items" :key="item.id" :value="item.id">{{ item.name }}</option>
          </select>
        </div>

        <div class="mb-3">
          <label for="quantity">Quantity</label>
          <div class="input-group">
            <button type="button" class="btn btn-outline-secondary" @click="adjustQuantity(-1)"
              :disabled="model.quantity <= 1">-</button>
            <input type="number" v-model.number="model.quantity" class="form-control" min="1"
              @input="validateQuantity" />
            <button type="button" class="btn btn-outline-secondary" @click="adjustQuantity(1)">+</button>
          </div>
        </div>

        <div class="mb-3">
          <label for="unit_of_measure">Unit of Measure</label>
          <select v-model="model.unit_of_measure" class="form-control">
            <option v-for="unit in units" :key="unit" :value="unit">{{ unit }}</option>
          </select>
        </div>

        <div class="mb-3">
          <label for="supplier_id">Supplier</label>
          <select v-model="model.supplier_id" class="form-control" disabled>
            <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.supplier_name }}
            </option>
          </select>
        </div>

        <div class="mb-3">
          <label for="supplier_name">Contact Person</label>
          <input type="text" v-model="supplierName" class="form-control" disabled>
        </div>

        <div class="mb-3">
          <label for="description">Description</label>
          <textarea v-model="model.description" class="form-control"></textarea>
        </div>

        <RouterLink to="/stocks" class="btn btn-primary float-end">Back</RouterLink>
        <button type="button" @click="saveStock" class="btn btn-primary">Add</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'StocksIn',
  data() {
    return {
      errorList: '',
      model: {
        item_id: '',
        quantity: 1,
        unit_of_measure: '',
        supplier_id: '',
        description: ''
      },
      itemName: '',
      supplierName: '',
      items: [],
      suppliers: [],
      units: ['Pc', 'Box', 'Kg', 'G', 'Liter', 'Ml', 'Meter', 'Cm', 'Bundle'],
      existingStocks: [],
    };
  },
  created() {
    this.fetchSuppliers()
      .then(() => {
        const stockId = this.$route.params.stockId;
        if (stockId) {
          this.fetchStock(stockId);
        }
        this.fetchItems();
        this.fetchExistingStocks();
      })
      .catch(error => {
        console.error('Error fetching suppliers:', error);
      });
  },
  methods: {
    fetchStock(stockId) {
      axios.get(`http://localhost:8001/api/stocks/${stockId}`)
        .then((res) => {
          const stock = res.data.stock;
          console.log('Fetched Stock:', stock);

          this.model.item_id = stock.item_id;
          this.model.unit_of_measure = stock.unit_of_measure;
          this.model.supplier_id = stock.supplier_id;
          this.model.description = stock.description;

          const selectedItem = this.items.find(item => item.id === this.model.item_id);
          this.itemName = selectedItem ? selectedItem.name : '';

          const supplier = this.suppliers.find(supplier => supplier.id === this.model.supplier_id);
          this.supplierName = supplier ? supplier.contact_name : '';
        })
        .catch((error) => {
          console.error('Error fetching stock:', error);
        });
    },

    fetchItems() {
      axios.get('http://localhost:8001/api/items')
        .then((res) => {
          this.items = res.data.items;
        })
        .catch((error) => {
          console.error('Error fetching items:', error);
        });
    },

    fetchSuppliers() {
      return new Promise((resolve, reject) => {
        axios.get('http://localhost:8001/api/suppliers')
          .then((res) => {
            this.suppliers = res.data.suppliers;
            resolve();
          })
          .catch((error) => {
            console.error('Error fetching suppliers:', error);
            reject(error);
          });
      });
    },

    fetchExistingStocks() {
      axios.get('http://localhost:8001/api/stocks')
        .then((res) => {
          this.existingStocks = res.data.stocks;
        })
        .catch((error) => {
          console.error('Error fetching existing stocks:', error);
        });
    },

    updateItemDetails() {
      const selectedItem = this.items.find(item => item.id === this.model.item_id);
      if (selectedItem) {
        this.model.unit_of_measure = selectedItem.unit_of_measure || 'Pc';
        this.model.supplier_id = selectedItem.supplier_id;

        const supplier = this.suppliers.find(supplier => supplier.id === selectedItem.supplier_id);
        this.supplierName = supplier ? supplier.contact_name : '';
      }
    },

    adjustQuantity(amount) {
      const newQuantity = this.model.quantity + amount;
      if (newQuantity > 0) {
        this.model.quantity = newQuantity;
      }
    },

    validateQuantity() {
      if (this.model.quantity <= 0) {
        this.model.quantity = 1;
      }
    },

    saveStock() {
      const duplicateItem = this.existingStocks.find(
        stock => stock.item_id === this.model.item_id
      );

      if (duplicateItem) {
        alert(`The item "${duplicateItem.item_name}" already exists with the unit of measure "${duplicateItem.unit_of_measure}".`);
        return;
      }

      const selectedItem = this.items.find(item => item.id === this.model.item_id);
      const payload = {
        item_id: this.model.item_id,
        item_name: selectedItem ? selectedItem.name : '',
        quantity: parseInt(this.model.quantity),
        unit_of_measure: this.model.unit_of_measure,
        supplier_id: this.model.supplier_id,
        description: this.model.description,
      };

      axios.post('http://localhost:8001/api/stocks', payload)
        .then(res => {
          alert(res.data.message);
          this.resetForm();
          this.$router.push('/stocks');
        })
        .catch(error => {
          if (error.response) {
            if (error.response.status === 422) {
              this.errorList = error.response.data.errors;
            } else if (error.response.status === 409) {
              alert(error.response.data.message);
            } else {
              console.error('Error:', error.response.data);
            }
          } else {
            console.error('Error:', error.message);
          }
        });
    },

    resetForm() {
      this.model = {
        item_id: '',
        quantity: 1,
        unit_of_measure: '',
        supplier_id: '',
        description: '',
      };
      this.supplierName = '';
      this.errorList = '';
    }
  }
};
</script>
