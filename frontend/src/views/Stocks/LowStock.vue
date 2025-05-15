<template>
  <div class="container mt-4">
    <div class="card mb-5">
      <div class="card-header">
        <h4>Restock</h4>
      </div>
      <div class="card-body">
        <ul class="alert alert-warning" v-if="Object.keys(errorList).length > 0">
          <li class="mb-0 ms-3" v-for="(error, index) in errorList" :key="index">
            {{ error[0] }}
          </li>
        </ul>

        <div class="mb-3">
          <label for="item_id">Item Name</label>
          <select v-model="model.stock_id" @change="updateItemDetails" class="form-control" disabled>
            <option v-for="stock in stocks" :key="stock.id" :value="stock.id">{{ stock.name }}</option>
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
          <select v-model="model.unit_of_measure" class="form-control" disabled>
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
          <input type="text" v-model="supplierName" class="form-control" disabled />
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
  name: 'StocksLowStock',
  data() {
    return {
      errorList: '',
      model: {
        stock_id: '',
        quantity: 1,
        unit_of_measure: '',
        supplier_id: '',
        description: ''
      },
      itemName: '',
      supplierName: '',
      stocks: [],
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
      if (!this.model.description) {
        this.model.description = "";
      }

      this.timestamp = new Date().toLocaleString();

      const newRemark = `IN ${this.timestamp} - RESUPPLY +${this.model.quantity} `;

      const selectedItem = this.items.find(item => item.id === this.model.item_id);
      if (!selectedItem) {
        this.errorList = { item: ["Item not found"] };
        return;
      }

      axios.get('http://localhost:8001/api/stocks')
        .then((res) => {
          const existingStock = res.data.stocks.find(stock => stock.item_id === this.model.item_id);

          if (existingStock) {
            const updatedPayload = {
              item_id: this.model.item_id,
              item_name: selectedItem.name,
              quantity: existingStock.quantity + parseInt(this.model.quantity),
              unit_of_measure: this.model.unit_of_measure,
              supplier_id: this.model.supplier_id,
              description: existingStock.description ? `${existingStock.description}\n${newRemark}` : newRemark,
            };

            axios.put(`http://localhost:8001/api/stocks/${existingStock.id}`, updatedPayload)
              .then((updateRes) => {
                alert(updateRes.data.message);
                this.resetForm();
                this.$router.push('/stocks');
              })
              .catch((error) => {
                console.error('Error updating stock:', error);
              });
          } else {
            const payload = {
              item_id: this.model.item_id,
              item_name: selectedItem.name,
              quantity: parseInt(this.model.quantity),
              unit_of_measure: this.model.unit_of_measure,
              supplier_id: this.model.supplier_id,
              description: newRemark,
            };

            axios.post('http://localhost:8001/api/stocks', payload)
              .then((res) => {
                alert(res.data.message);
                this.resetForm();
                this.$router.push('/stocks');
              })
              .catch((error) => {
                if (error.response) {
                  if (error.response.status === 422) {
                    this.errorList = error.response.data.errors;
                  } else {
                    console.error('Error saving stock:', error.message);
                  }
                }
              });
          }
        })
        .catch((error) => {
          console.error('Error fetching stocks for validation:', error);
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
