<template>
  <div class="shop-view">
    <header class="shop-header">
      <nav>
        <ul class="menu">
          <li><a href="/shop">SHOP</a></li>
          <li><a href="#">ORDERS</a></li>
          <li><a href="#">MY ACCOUNT</a></li>
          <li><a href="#">ABOUT US</a></li>
          <li>
            <RouterLink to="/orders" class="cart-icon">
              🛒 <span class="cart-count">{{ cartQuantity }}</span>
            </RouterLink>
          </li>
        </ul>
      </nav>
    </header>

    <section class="item-section">
      <nav class="breadcrumb">
        <a href="/shop">Home</a> / {{ item.name }}
      </nav>

      <div class="item-main">
        <div class="item-image"></div>
        <div class="item-info">
          <h1 class="item-title">{{ item.name }}</h1>
          <p class="item-price">₱{{ item.price }}</p>
          <div class="item-description">
            <p>{{ item.description }}</p>
          </div>
          <div class="options">
            <label for="sizes">Sizes</label>
            <select id="sizes">
              <option value="" disabled selected>Choose an option</option>
              <option v-for="size in sizeChart" :key="size.size">{{ size.size }}</option>
            </select>
          </div>
          <div class="actions">
            <div class="quantity-selector">
              <button class="quantity-btn" @click="decreaseQuantity">-</button>
              <span class="quantity-display">{{ quantity }}</span>
              <button class="quantity-btn" @click="increaseQuantity">+</button>
            </div>
            <button class="buy-now" @click="buyNow">Buy Now</button>
            <button class="add-to-cart" @click="createOrder">Add to Cart</button>
          </div>
        </div>
      </div>

      <div class="size-chart">
        <h3>Size Chart</h3>
        <table>
          <thead>
            <tr>
              <th>Size</th>
              <th>Width</th>
              <th>Length</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="size in sizeChart" :key="size.size">
              <td>{{ size.size }}</td>
              <td>{{ size.width }}</td>
              <td>{{ size.length }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</template>

<script>
export default {
  data() {
    return {
      item: {},
      sizeChart: [
        { size: "S", width: '20"', length: '27"' },
        { size: "M", width: '21"', length: '28"' },
        { size: "L", width: '22"', length: '29"' },
        { size: "XL", width: '23"', length: '30"' },
      ],
      quantity: 1,
      items: [],
      cart: [],
    };
  },
  computed: {
    cartQuantity() {
      return this.cart.reduce((total, item) => total + item.quantity, 0);
    },
  },
  created() {
    this.fetchItemDetails();
    this.fetchItems();
  },
  methods: {
    async fetchItemDetails() {
      const itemId = this.$route.params.id;

      // Validate that the itemId exists
      if (!itemId) {
        console.error("Item ID is missing");
        return;
      }

      try {
        // Fetch data from the API
        const response = await fetch(`http://localhost:8001/api/stocks/${itemId}`);
        
        // Parse the JSON response
        const data = await response.json();
        console.log("API Response:", data);  // Log the full response
        console.log("Stock Object:", data.stock);  // Log the stock object

        // Check if the response contains the stock item data
        if (!data || !data.stock) {
          console.error("Item not found in the response");
          return;
        }

        // Set the item data to the component's state
        this.item = {
          name: data.stock.item_name || 'No name available',
          price: data.stock.price_per_unit || 'Price not available',
          description: data.stock.description || 'No description available'
        };

      } catch (error) {
        console.error('Error fetching item details:', error);
      }
    },

    async fetchItems() {
      try {
        const response = await fetch('http://localhost:8001/api/stocks');
        if (!response.ok) {
          throw new Error('Failed to fetch items');
        }
        const data = await response.json();
        this.items = data.stocks;
      } catch (error) {
        console.error('Error fetching items:', error);
      }
    },
    goToItemDetails(itemId) {
      this.$router.push({ name: 'ItemDetails', params: { id: itemId } });
    },
    increaseQuantity() {
      this.quantity++;
    },
    decreaseQuantity() {
      if (this.quantity > 1) {
        this.quantity--;
      }
    },
    buyNow() {
      const orderData = [
        {
          item: {
            name: this.item.item_name,
            id: this.item.id,
          },
          quantity: this.quantity,
          item_price_per_unit: this.item.price_per_unit,
        },
      ];

      console.log('Order Data:', orderData)
      this.$router.push({
        path: '/checkout',
        query: {
          orders: JSON.stringify(orderData),
        },
      });
    },
    async createOrder() {
      try {
        const orderData = {
          item_id: this.item.id,
          quantity: this.quantity,
          item_price_per_unit: this.item.price_per_unit,
        };

        console.log('Order Data:', orderData);

        const response = await fetch('http://localhost:8001/api/orders', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(orderData),
        });

        if (!response.ok) {
          throw new Error('Failed to create order');
        }

        const result = await response.json();
        alert('Added to Cart!');

        const existingItem = this.cart.find(item => item.id === this.item.id);
        if (existingItem) {
          existingItem.quantity += this.quantity;
        } else {
          this.cart.push({ ...this.item, quantity: this.quantity });
        }

        this.$router.push('/orders');
      } catch (error) {
        console.error('Error creating order:', error);
        alert('An error occurred while creating the order.');
      }
    },
  },
};
</script>

<style scoped>
.shop-view {
  font-family: 'Roboto', sans-serif;
  color: #2d3436;
  text-align: center;
  max-width: 1200px;
  margin: 0 auto;
}

.shop-header {
  padding: 20px 0;
  background-color: #f7f7f7;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

.menu {
  list-style: none;
  display: flex;
  justify-content: center;
  gap: 30px;
  margin: 0;
  padding: 0;
}

.menu a {
  text-decoration: none;
  font-weight: bold;
  font-size: 16px;
  color: #34495e;
  transition: color 0.3s ease;
}

.menu a:hover {
  color: #2980b9;
}

.cart-icon {
  position: relative;
}

.cart-count {
  background: #e74c3c;
  color: #fff;
  font-size: 12px;
  border-radius: 50%;
  padding: 2px 6px;
  position: absolute;
  top: -10px;
  right: -10px;
}

.item-section {
  margin: 40px 0;
}

.breadcrumb {
  font-size: 14px;
  color: #95a5a6;
  margin-bottom: 20px;
}

.breadcrumb a {
  text-decoration: none;
  color: #2980b9;
}

.breadcrumb a:hover {
  text-decoration: underline;
}

.item-main {
  display: flex;
  gap: 40px;
  align-items: flex-start;
  margin-bottom: 40px;
}

.quantity-selector {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  margin: 20px 0;
}

.quantity-btn {
  width: 50px;
  height: 50px;
  background-color: #ecf0f1;
  color: #2c3e50;
  font-size: 20px;
  font-weight: bold;
  border: 2px solid #bdc3c7;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.quantity-btn:hover {
  background-color: #bdc3c7;
  color: #fff;
}

.quantity-btn:active {
  transform: scale(0.95);
}

.quantity-display {
  font-size: 20px;
  font-weight: bold;
  padding: 0 10px;
  min-width: 40px;
  text-align: center;
  color: #2c3e50;
}

.item-image {
  flex: 1;
  height: 400px;
  background: linear-gradient(135deg, #11095c, #0a3992);
  border-radius: 12px;
}

.item-info {
  flex: 1;
  text-align: left;
}

.item-title {
  font-size: 28px;
  font-weight: 600;
  color: #2c3e50;
}

.item-price {
  font-size: 24px;
  font-weight: bold;
  color: #e74c3c;
  margin: 10px 0 20px;
}

.options {
  margin: 20px 0;
}

.actions {
  display: flex;
  gap: 15px;
  margin-top: 20px;
}

.buy-now {
  background: linear-gradient(135deg, #27ae60, #2ecc71);
  padding: 12px 30px;
  font-size: 16px;
  border: none;
  border-radius: 8px;
  color: white;
  cursor: pointer;
  transition: box-shadow 0.3s ease;
}

.buy-now:hover {
  box-shadow: 0 4px 10px rgba(39, 174, 96, 0.5);
}

.add-to-cart {
  background: linear-gradient(135deg, #2980b9, #3498db);
  padding: 12px 30px;
  font-size: 16px;
  border: none;
  border-radius: 8px;
  color: white;
  cursor: pointer;
  transition: box-shadow 0.3s ease;
}

.add-to-cart:hover {
  box-shadow: 0 4px 10px rgba(41, 128, 185, 0.5);
}

.size-chart {
  background: #fff;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

.size-chart h3 {
  font-size: 20px;
  margin-bottom: 10px;
}

.size-chart table {
  width: 100%;
  border-collapse: collapse;
}

.size-chart th,
.size-chart td {
  padding: 10px;
  text-align: center;
  border: 1px solid #ddd;
}
</style>