<template>
  <div class="container mt-4">
    <div class="card mb-5">
      <div class="card-header">
        <h4>Stock Out</h4>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <input v-model="stockSearchQuery" @input="filterItemList" class="form-control" type="text" id="stock-search"
            placeholder="Search by Item Name or Stock ID" />
        </div>

        <div class="d-flex flex-wrap">
          <div v-for="(item, index) in filteredItems" :key="item.id" class="card p-2 m-2"
            style="width: 18rem; cursor: pointer" @click="selectItem(item)">
            <img class="card-img-top" :src="item.image_url || ''" alt="Item image" />
            <div class="card-body">
              <h5 class="card-title">{{ item.item_name }}</h5>
              <p class="card-text">Item ID: {{ item.id }}</p>
              <button class="btn btn-primary">Select</button>
            </div>
          </div>
        </div>

        <div v-if="selectedItems.length > 0">
          <div class="border p-3 rounded mb-3">
            <div class="d-flex justify-content-between align-items-center">
              <h5>Stock/s Selected</h5>
              <button class="btn btn-danger mb-3" @click="clearAllSelectedItems">Clear All</button>
            </div>
            <div v-for="(stock, index) in selectedItems" :key="stock.stock_id" class="mb-2 p-3 border rounded">
              <div class="d-flex justify-content-between align-items-center">
                <span>{{ stock.item_name }}</span>
                <button type="button" class="btn btn-danger ms-3" @click="removeSelectedItem(index)">Remove</button>
              </div>

              <div class="mt-1">
                <small><strong>Supplier:</strong> {{ stock.supplier_name || 'Unknown' }}</small><br />
                <small><strong>Contact:</strong> {{ stock.contact_name || 'Unknown' }}</small><br />
              </div>

              <div class="mb-3 mt-3">
                <small><strong>Available Items: </strong></small>
                <div class="sku-container">
                  <div v-for="(sku, skuIndex) in stock.skus" :key="skuIndex" class="form-check mt-2">
                    <div class="d-flex justify-content-between">
                      <div>
                        <input class="form-check-input" type="checkbox" :id="'sku-' + stock.stock_id + '-' + skuIndex"
                          :checked="stock.selectedSkus.includes(sku.sku)" @change="toggleSkuSelection(stock, sku)" />
                        <label class="form-check-label" :for="'sku-' + stock.stock_id + '-' + skuIndex">
                          {{ sku.sku }}
                        </label>
                      </div>
                      <div class="text-end">
                        <small>Stock ID: {{ sku.stock_id }}</small><br />
                        <small>Added At: {{ formatDate(sku.created_at) }}</small><br />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="mb-3 mt-3">
          <label for="reason">Reason for Stock Out</label>
          <select v-model="selectedReason" class="form-control">
            <option value="sold">Sold</option>
            <option value="expired">Expired</option>
            <option value="damaged">Damaged</option>
            <option value="other">Other</option>
          </select>
        </div>

        <div class="mb-3">
          <label for="description">Description</label>
          <textarea v-model="descriptionText" class="form-control" id="description" rows="3"></textarea>
        </div>

        <RouterLink to="/stocks" class="btn btn-primary">Back</RouterLink>
        <button type="button" @click="saveStock" class="btn btn-primary float-end">Process Stock Out</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'StocksOut',
  data() {
    return {
      stockSearchQuery: '',
      filteredItems: [],
      selectedItems: [],
      descriptionText: '',
      selectedReason: 'sold',
      items: [],
      suppliers: [],
    };
  },

  created() {
    this.fetchItems();
    this.fetchSuppliers();
  },

  methods: {
    toggleSkuSelection(stock, sku) {
      const skuIndex = stock.selectedSkus.indexOf(sku.sku);

      if (skuIndex !== -1) {
        stock.selectedSkus.splice(skuIndex, 1);
        console.log("Payload for DELETE SKU:", { stockId: stock.stock_id, skuId: sku.sku });
        this.removeSkuFromDatabase(stock.stock_id, sku.sku);
      } else {
        stock.selectedSkus.push(sku.sku);
        console.log("Payload for ADD SKU:", { stockId: stock.stock_id, skuId: sku.sku });
      }
    },

    removeSkuFromDatabase(stockId, skuId) {
      if (!skuId || !stockId) {
        console.log("Missing SKU ID or Stock ID:", { skuId, stockId });
        return;
      }

      console.log(`Removing SKU ${skuId} from stock ID ${stockId}`);

      axios.delete(`http://localhost:8001/api/stocks/${stockId}/skus/${skuId}`)
        .then(response => {
          console.log(`Successfully removed SKU ${skuId} from stock ID ${stockId}`, response);

          const stock = this.selectedItems.find(item => item.stock_id === stockId);
          if (stock) {
            stock.skus = stock.skus.filter(sku => sku.sku !== skuId);
          }
        })
        .catch(error => {
          console.error(`Error removing SKU ${skuId} for stock ID ${stockId}:`, error);
        });
    },

    fetchItems() {
      axios
        .get('http://localhost:8001/api/stocks')
        .then((res) => {
          this.items = res.data.stocks.map(stock => ({
            ...stock,
            showTransactions: false,
            transactions: []
          }));
          this.filteredItems = [...this.items];
        })
        .catch((error) => console.error('Error fetching stocks:', error));
    },

    fetchSuppliers() {
      axios.get('http://localhost:8001/api/suppliers')
        .then((res) => {
          this.suppliers = res.data.suppliers;
        })
        .catch((error) => console.error('Error fetching suppliers:', error));
    },

    filterItemList() {
      const query = this.stockSearchQuery.toLowerCase().trim();
      this.filteredItems = query
        ? this.items.filter(
          (stock) =>
            stock.item_name.toLowerCase().includes(query) ||
            stock.id.toString().includes(query)
        )
        : [...this.items];
    },

    selectItem(stock) {
      if (!this.selectedItems.some((selected) => selected.stock_id === stock.id)) {
        const selectedStock = this.items.find((s) => s.id === stock.id);
        const supplier = selectedStock.supplier;

        const newItem = {
          stock_id: stock.id,
          item_name: stock.item_name,
          unit_of_measure: 'Pc',
          supplier_id: supplier?.id || null,
          supplier_name: supplier?.supplier_name || 'Unknown',
          contact_name: supplier?.contact_name || 'Unknown',
          quantity: 1,
          skus: selectedStock.skus || [],
          selectedSkus: [],
          category_id: stock.category_id || null,
          physical_count: stock.physical_count || 0,
          on_hand: stock.on_hand || 0,
          sold: stock.sold || 0,
          price_per_unit: stock.price_per_unit || 0,
          description: stock.description || '',
        };

        this.selectedItems.push(newItem);
      }
    },

    removeSelectedItem(index) {
      this.selectedItems.splice(index, 1);
    },

    clearAllSelectedItems() {
      this.selectedItems = [];
    },

    saveStock() {
      this.selectedItems.forEach((stock) => {
        const skusToRemove = stock.selectedSkus;
        const quantityToRemove = skusToRemove.length;

        skusToRemove.forEach((sku) => {
          this.removeSkuFromDatabase(stock.stock_id, sku);
        });

        const payload = {
          stock_id: stock.stock_id,
          item_name: stock.item_name,
          category_id: stock.category_id,
          supplier_id: stock.supplier_id,
          unit_of_measure: stock.unit_of_measure,
          physical_count: stock.physical_count,
          on_hand: Math.max(0, (stock.on_hand || 0) - quantityToRemove),
          sold: this.selectedReason === 'sold' ? (stock.sold || 0) + quantityToRemove : stock.sold,
          price_per_unit: stock.price_per_unit,
          description: stock.description || 'No description',
        };

        console.log(`Payload for stock ID ${stock.stock_id}:`, payload);

        axios
          .put(`http://localhost:8001/api/stocks/${stock.stock_id}`, payload)
          .then((response) => {
            console.log(`Stock updated successfully for stock ID ${stock.stock_id}`, response.data);
            this.$router.push('/stocks');
          })
          .catch((error) => {
            console.error(`Error processing stock out for stock ID ${stock.stock_id}:`, error);
          });
      });
    },

    formatDate(date) {
      const options = { year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric', second: 'numeric' };
      return new Date(date).toLocaleDateString(undefined, options);
    }
  },

  toggleTransactionDetails(stock) {
    if (!stock.showTransactions) {
      this.fetchTransactions(stock.id);
    }
    stock.showTransactions = !stock.showTransactions;
  },

  fetchTransactions(stockId) {
    const stock = this.items.find(s => s.id === stockId);
    if (stock && !stock.transactions.length) {
      axios
        .get(`/api/transactions?stock_id=${stockId}`)
        .then((response) => {
          stock.transactions = response.data.transactions || [];
        })
        .catch((error) => {
          console.error(`Failed to fetch transactions for stock ID ${stockId}:`, error);
        });
    }
  }
};
</script>

<style scoped>
.table {
  margin-top: 1rem;
}

.card-img-top {
  height: 200px;
  object-fit: cover;
}

.card-body {
  padding: 0.5rem;
}

.card-title {
  font-size: 1.2rem;
}

.card-text {
  font-size: 1rem;
}

.form-check {
  margin-bottom: 0.5rem;
}

.text-end {
  text-align: right;
}

.sku-container {
  max-height: 200px;
  overflow-y: auto;
  font-size: 0.9rem;
}

.sku-container .form-check {
  font-size: 0.8rem;
}

.alert {
  padding: 1rem;
  margin-top: 1rem;
  text-align: center;
  font-weight: bold;
}

.alert-success {
  background-color: #d4edda;
  color: #155724;
}

.alert-error {
  background-color: #f8d7da;
  color: #721c24;
}
</style>
