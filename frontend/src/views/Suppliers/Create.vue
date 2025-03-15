<template>
	<div class="container mt-4">
		<div class="card mb-5">
			<div class="card-header">
				<h4>New Supplier</h4>
			</div>
			<div class="card-body">
				<!-- Error messages -->
				<ul class="alert alert-warning" v-if="Object.keys(this.errorList).length > 0">
					<li class="mb-0 ms-3" v-for="(error, index) in this.errorList" :key="index">
						{{ error[0] }}
					</li>
				</ul>

				<!-- Form Fields -->
				<div class="mb-3">
					<label for="">Supplier Name</label>
					<input type="text" v-model="model.supplier.supplier_name" class="form-control">
				</div>
				<div class="mb-3">
					<label for="">Contact Name</label>
					<input type="text" v-model="model.supplier.contact_name" class="form-control">
				</div>
				<div class="mb-3">
					<label for="">E-mail</label>
					<input type="text" v-model="model.supplier.contact_email" class="form-control">
				</div>
				<div class="mb-3">
					<label for="">Phone Number</label>
					<input type="text" v-model="model.supplier.contact_phone" class="form-control">
				</div>
				<div class="mb-3">
					<label for="">Address</label>
					<input type="text" v-model="model.supplier.address" class="form-control">
				</div>

				<!-- Buttons -->
				<RouterLink to="/suppliers" class="btn btn-primary float-end">Back</RouterLink>
				<button type="button" @click="saveSupplier" class="btn btn-primary">Add</button>
			</div>
		</div>
	</div>
</template>

<script>
import axios from 'axios';

export default {
	name: 'categoryCreate',
	data() {
		return {
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
		};
	},
	methods: {
		saveSupplier() {
			const authToken = localStorage.getItem('authToken'); // Retrieve token
			if (!authToken) {
				alert("You are not logged in. Please log in and try again.");
				this.$router.push('/login');
				return;
			}

			axios.post('http://localhost:8001/api/suppliers', this.model.supplier, {
				headers: {
					'Authorization': `Bearer ${authToken}`, // Add Authorization token
					'Content-Type': 'application/json'
				}
			})
				.then(res => {
					console.log(res.data);
					alert(res.data.message);

					// Reset form
					this.model.supplier = {
						supplier_name: '',
						contact_name: '',
						contact_email: '',
						contact_phone: '',
						address: '',
					};
					this.errorList = '';

					this.$router.push('/suppliers');
				})
				.catch(error => {
					if (error.response) {
						if (error.response.status === 422) {
							this.errorList = error.response.data.errors;
						} else if (error.response.status === 401) {
							alert("Unauthorized: Please log in again.");
							localStorage.removeItem('authToken'); // Clear invalid token
							this.$router.push('/login'); // Redirect to login page
						} else {
							console.log('Error:', error.message);
						}
					} else {
						console.log("Request failed:", error);
					}
				});
		}
	}
};
</script>
