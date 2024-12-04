<template>
	<div class="container mt-4">	
		<div class ="card mb-5">
			<div class ="card-header">
				<h4>Edit Supplier</h4>
			</div>
			<div class = "card-body">
				<ul class="alert alert-warning" v-if="Object.keys(this.errorList).length > 0">
					<li class ="mb-0 ms-3" v-for="(error, index) in this.errorList" :key="index">
						{{ error[0] }}	
					</li>
				</ul>
				<div class ="mb-3">
					<label for="">Supplier Name</label>
					<input type="text" v-model="model.supplier.supplier_name" class="form-control">
				</div>
				<div class ="mb-3">
					<label for="">Contact Name</label>
					<input type="text" v-model="model.supplier.contact_name" class="form-control">
				</div>
				<div class ="mb-3">
					<label for="">E-mail</label>
					<input type="text" v-model="model.supplier.contact_email" class="form-control">
				</div>
				<div class ="mb-3">
					<label for="">Phone Number</label>
					<input type="text" v-model="model.supplier.contact_phone" class="form-control">
				</div>
				<div class ="mb-3">
					<label for="">Address</label>
					<input type="text" v-model="model.supplier.address" class="form-control">
				</div>
				<RouterLink to="/suppliers" class="btn btn-primary float-end">Back</RouterLink>
				<button type="button" @click="updateSupplier" class="btn btn-primary">Save</button>
			</div>
		</div>
	</div>

</template>

<script>
import axios from 'axios'

export default {
	name: 'supplierEdit',
	data() {
		return {
			supplierId: '',  
			errorList: '',   
			model: {         
				supplier: {
					supplier_name: '',
					contact_name: '',
					contact_email: '',
					contact_phone: '',
					address: '',
				}
			}
		}
	},
	mounted() {
		this.supplierId = this.$route.params.id;
		this.getSupplierData(this.supplierId);  
	},
	methods: {
		getSupplierData(supplierId) {
			axios.get(`http://localhost:8001/api/suppliers/${supplierId}`)
				.then(res => {
					this.model.supplier = res.data.supplier;  
				})
				.catch(error => {
					if (error.response && error.response.status === 404) {
						alert(error.response.data.message);
					}
				});
		},

		updateSupplier() {
			axios.put(`http://localhost:8001/api/suppliers/${this.supplierId}`, this.model.supplier)
				.then(res => {
					alert(res.data.message);
					this.errorList = '';  
					this.$router.push('/suppliers');  
				})
				.catch(error => {
					if (error.response) {
						if (error.response.status === 422) {
							this.errorList = error.response.data.errors;  
						} else if (error.response.status === 404) {
							alert(error.response.data.message);
						}
					}
				});
		}
	},
}
</script>