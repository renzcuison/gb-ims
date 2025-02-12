<template>
  <div class="container mt-4">
    <div class="card mb-5">
      <div class="card-header">
        <h4>Stock In</h4>
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
                <div class="d-flex align-items-center">
                  <button type="button" class="btn btn-danger ms-3" @click="removeSelectedItem(index)">Remove</button>
                </div>
              </div>

              <div class="mt-1">
                <small><strong>Supplier:</strong> {{ stock.supplier_name || 'Unknown' }}</small><br />
                <small><strong>Contact:</strong> {{ stock.contact_name || 'Unknown' }}</small><br />
                <small><strong>SKUs: </strong>
                  <span v-for="(sku, skuIndex) in stock.skus" :key="skuIndex">{{ sku }}{{ skuIndex < stock.skus.length -
                    1 ? ', ' : '' }}</span>
                </small>
              </div>

              <div class="mb-3 mt-3">
                <label for="quantity">Quantity</label>
                <input v-model.number="stock.quantity" @input="updateSkusForQuantity(index)" type="number"
                  class="form-control" id="quantity" min="1" placeholder="Enter quantity" />
              </div>
            </div>
          </div>
        </div>

        <div class="mb-3 mt-3">
          <label for="reason">Reason</label>
          <select v-model="selectedReason" class="form-control">
            <option value="stock-in">Add</option>
            <option value="return">Return</option>
            <option value="other">Other</option>
          </select>
        </div>

        <div class="mb-3">
          <label for="description">Description</label>
          <textarea v-model="descriptionText" class="form-control" id="description" rows="3"></textarea>
        </div>

        <RouterLink to="/stocks" class="btn btn-primary">Back</RouterLink>
        <button type="button" @click="saveStock" class="btn btn-primary float-end">Add</button>
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
      stockSearchQuery: '',
      filteredItems: [],
      selectedItems: [],
      descriptionText: '',
      selectedReason: 'stock-in',
      items: [],
      suppliers: [],
      units: ['Pc', 'Box', 'Kg', 'G', 'Liter', 'Ml', 'Meter', 'Cm', 'Bundle'],
    };
  },

  created() {
    this.fetchItems();
    this.fetchSuppliers();
  },

  methods: {
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
      axios
        .get('http://localhost:8001/api/suppliers')
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
          skus: [],
          category_id: stock.category_id || null,
          physical_count: stock.physical_count || 0,
          on_hand: stock.on_hand || 0,
          sold: stock.sold || 0,
          price_per_unit: stock.price_per_unit || 0,
          description: stock.description || '',
        };

        this.selectedItems.push(newItem);

        console.log("Added selected stock item:", newItem);

        this.addSku(this.selectedItems.length - 1);
      }
    },

    removeSelectedItem(index) {
      this.selectedItems.splice(index, 1);
    },

    clearAllSelectedItems() {
      this.selectedItems = [];
    },

    updateSkusForQuantity(index) {
      const stock = this.selectedItems[index];
      const newQuantity = stock.quantity;

      if (newQuantity <= 0) {
        stock.quantity = 1;
        return;
      }

      const currentSkuCount = stock.skus.length;

      if (currentSkuCount < newQuantity) {
        const additionalSkus = newQuantity - currentSkuCount;
        this.generateAdditionalSkus(index, additionalSkus);
      }

      if (currentSkuCount > newQuantity) {
        stock.skus.splice(newQuantity);
      }

      stock.sku = stock.skus.join(', ');
    },

    generateAdditionalSkus(index, additionalCount) {
      const stock = this.selectedItems[index];

      for (let i = 0; i < additionalCount; i++) {
        const supplier = (stock.supplier_name || 'Unknown').substring(0, 3).toUpperCase();
        const itemName = (stock.item_name || 'Unknown').substring(0, 3).toUpperCase();
        const randomNumber = Math.floor(100 + Math.random() * 900);
        const sku = `${supplier}-${itemName}-${randomNumber}-${stock.unit_of_measure.toUpperCase()}`;

        stock.skus.push(sku);
      }
    },

    addSku(index) {
      const stock = this.selectedItems[index];
      const quantity = stock.quantity;

      if (quantity <= 0) return;

      this.generateAdditionalSkus(index, quantity);
    },

    saveStock() {
      this.selectedItems.forEach((stock) => {
        stock.physical_count = stock.physical_count || 0;
        stock.on_hand = stock.on_hand || 0;
        stock.physical_count += stock.quantity;
        stock.on_hand += stock.quantity;

        stock.skus = stock.skus.flatMap((sku) => {
          if (sku.includes(',')) {
            return sku.split(',').map((s) => s.trim());
          }
          return [sku];
        });

        stock.selectedSkus = stock.selectedSkus || [];

        const details = stock.selectedSkus.map(sku => ({
          stock_id: stock.stock_id,
          sku: sku,
          quantity: stock.quantity,
        }));

        console.log('selectedSkus before details:', stock.selectedSkus);
        console.log('Details array:', details);

        if (details.length === 0) {
          console.error('Error: Details array is empty.');
          return;
        }

        const payload = {
          ...stock,
          skus: stock.skus || [],
          physical_count: stock.physical_count,
          on_hand: stock.on_hand,
          action: "stock-in",
          reason: this.selectedReason,
          quantity: stock.selectedSkus.length,
          description: this.descriptionText,
          transaction_type: "in",
          details: details,
        };

        delete payload.sku;
        console.log(`Payload for stock ID ${stock.stock_id}:`, payload);

        axios.post(`http://localhost:8001/api/transactions`, payload)
          .then((response) => {
            console.log(`Stock-in processed for stock ID ${stock.stock_id}`, response.data);
            return axios.put(`http://localhost:8001/api/stocks/${stock.stock_id}`, payload);
          })
          .then((response) => {
            console.log(`Stock updated successfully for stock ID ${stock.stock_id}`, response.data);
            this.$router.push('/stocks');
          })
          .catch((error) => {
            console.error("Error processing stock-in:", error.response ? error.response.data : error.message);
          });
      });
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

  },
};
</script>

<style scoped>
.table {
  margin-top: 1rem;
}
</style>
