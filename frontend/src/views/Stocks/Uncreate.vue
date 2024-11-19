<template>
  <div class="container mt-4">
    <div class="card mb-5">
      <div class="card-header">
        <h4>Stock Out</h4>
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
          <input type="text" v-model="model.unit_of_measure" class="form-control" readonly>
        </div>

        <div class="mb-3">
          <label for="supplier_id">Supplier</label>
          <select v-model="model.supplier_id" class="form-control" disabled>
            <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
              {{ supplier.supplier_name }}
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
        <button type="button" @click="saveStock" class="btn btn-primary">Confirm</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'StocksCreate',
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
        this.fetchStocks();
      })
      .catch(error => {
        console.error('Error fetching suppliers:', error);
      });
  },
  methods: {
    fetchStocks() {
      axios.get('http://localhost:8001/api/stocks')
        .then((res) => {
          this.stocks = res.data.stocks;
        })
        .catch((error) => {
          console.error('Error fetching stocks:', error);
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

    updateItemDetails() {
      const selectedItem = this.items.find(item => item.id === this.model.item_id);

      if (selectedItem) {
        const stock = this.stocks.find(stock => stock.item_id === selectedItem.id);

        if (stock) {
          this.model.unit_of_measure = stock.unit_of_measure;
          this.model.supplier_id = stock.supplier_id;

          const supplier = this.suppliers.find(supplier => supplier.id === stock.supplier_id);
          this.supplierName = supplier ? supplier.contact_name : '';
        } else {
          this.model.unit_of_measure = '';
          this.model.supplier_id = '';
          this.supplierName = '';
        }
      } else {
        this.model.unit_of_measure = '';
        this.model.supplier_id = '';
        this.supplierName = '';
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
      const selectedItem = this.items.find(item => item.id === this.model.item_id);

      if (!selectedItem) {
        this.errorList = { "item": ["Item not found"] };
        return;
      }

      const selectedStock = this.stocks.find(stock => stock.item_id === selectedItem.id);

      if (!selectedStock) {
        this.errorList = { "stock": ["Stock not found for selected item"] };
        return;
      }

      if (this.model.quantity > selectedStock.quantity) {
        this.errorList = { "quantity": ["Insufficient stock available"] };
        return;
      }

      const updatedQuantity = selectedStock.quantity - this.model.quantity;

      const updatedStock = {
        ...selectedStock,
        quantity: updatedQuantity,
      };

      axios.put(`http://localhost:8001/api/stocks/${selectedStock.id}`, updatedStock)
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
