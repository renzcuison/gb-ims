<template>
	<div class="container mt-4">	
		<div class="card mb-5">
			<div class="card-header">
				<h4>Edit Category</h4>
			</div>
			<div class="card-body">
				<ul class="alert alert-warning" v-if="Object.keys(errorList).length > 0">
					<li class="mb-0 ms-3" v-for="(error, index) in errorList" :key="index">
						{{ error[0] }}	
					</li>
				</ul>

				<div class="mb-3">
					<label for="">Category Name</label>
					<input type="text" v-model="model.category.category_name" class="form-control">
				</div>

				<div class="mb-3">
					<label for="">Description</label>
					<input type="text" v-model="model.category.description" class="form-control">
				</div>

				<RouterLink to="/categories" class="btn btn-primary float-end">Back</RouterLink>
				<button type="button" @click="updateCategory" class="btn btn-primary">Save</button>
			</div>
		</div>
	</div>
</template>

<script>
import axios from 'axios'

export default {
	name: 'categoryEdit',
	data() {
		return {
			categoryId: '',  
			errorList: '',   
			model: {         
				category: {
					category_name: '',
					description: '',
				}
			}
		}
	},
	mounted() {
		this.categoryId = this.$route.params.id;
		this.getCategoryData(this.categoryId);  
	},
	methods: {
		getCategoryData(categoryId) {
			axios.get(`http://localhost:8001/api/categories/${categoryId}`)
				.then(res => {
					this.model.category = res.data.category;  
				})
				.catch(error => {
					if (error.response && error.response.status === 404) {
						alert(error.response.data.message);
					}
				});
		},

		updateCategory() {
			axios.put(`http://localhost:8001/api/categories/${this.categoryId}`, this.model.category)
				.then(res => {
					alert(res.data.message);
					this.errorList = '';  
					this.$router.push('/categories');  
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