<template>
	<div class="container mt-4">	
		<div class ="card mb-5">
			<div class ="card-header">
				<h4>New Supplier</h4>
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
				<button type="button" @click="saveSupplier" class="btn btn-primary">Add</button>
			</div>
		</div>
	</div>

</template>

<script>
	import axios from 'axios'
	export default{
		name: 'categoryCreate',
		data(){
			return{
				errorList: '',
				model: {
					supplier: {
						supplier_name: '',
						description: '',
					}
				}
			}
		},
		methods: {
			saveSupplier(){

				var mythis = this;
				axios.post('http://localhost:8001/api/suppliers', this.model.supplier)
				.then(res => {
					console.log(res.data)
					alert(res.data.message);

					this.model.supplier = {
						supplier_name: '',
						description: '',
					}
					this.errorList = '';

					this.$router.push('/suppliers');
				})
				.catch(function (error) {

					if(error.response){

						if (error.response.status == 422){

							mythis.errorList = error.response.data.errors;

						} else if (error.request) {
							console.log(error.request);
						} else {
							console.log('Error', error.message);
						}
					}

				});
			}
		},
	}
</script>