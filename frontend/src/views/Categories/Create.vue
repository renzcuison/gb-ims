<template>
	<div class="container mt-4">	
		<div class ="card mb-5">
			<div class ="card-header">
				<h4>New Category</h4>
			</div>
			<div class = "card-body">
				<ul class="alert alert-warning" v-if="Object.keys(this.errorList).length > 0">
					<li class ="mb-0 ms-3" v-for="(error, index) in this.errorList" :key="index">
						{{ error[0] }}	
					</li>
				</ul>
				<div class ="mb-3">
					<label for="">Category Name</label>
					<input type="text" v-model="model.category.category_name" class="form-control">
				</div>
				<div class ="mb-3">
					<label for="">Description</label>
					<input type="text" v-model="model.category.description" class="form-control">
				</div>
				<RouterLink to="/categories" class="btn btn-primary float-end">Back</RouterLink>
				<button type="button" @click="saveCategory" class="btn btn-primary">Add</button>
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
					category: {
						category_name: '',
						description: '',
					}
				}
			}
		},
		methods: {
			saveCategory(){

				var mythis = this;
				axios.post('http://localhost:8001/api/categories', this.model.category)
				.then(res => {
					console.log(res.data)
					alert(res.data.message);

					this.model.category = {
						category_name: '',
						description: '',
					}
					this.errorList = '';

					this.$router.push('/categories');
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