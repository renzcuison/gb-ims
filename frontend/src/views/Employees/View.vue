<template>
  <div class="container">
    <div class="card mb-5">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="m-0">Employees</h4>
        <RouterLink to="/employees/create" class="btn btn-primary">Add Employee</RouterLink>
      </div>
      <div class="card-body">
        <div v-if="employees.length > 0" class="employee-list">
          <div
            v-for="(employee, index) in employees"
            :key="index"
            class="employee-card"
          >
            <div class="employee-avatar">
              <img :src="getAvatarUrl(employee.id)" alt="Employee Avatar" />
            </div>
            <div class="employee-details">
              <h5>{{ employee.first_name }} {{ employee.last_name }}</h5>
              <p class="role">{{ employee.role }}</p>
            </div>
            <div class="employee-actions">
              <RouterLink
                :to="{ path: '/employees/' + employee.id + '/edit' }"
                class="btn btn-outline-success btn-sm"
              >
                Edit
              </RouterLink>
              <button
                type="button"
                @click="deleteEmployee(employee.id)"
                class="btn btn-outline-danger btn-sm"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
        <div v-else class="text-center">
          <p class="empty-message">No employees found. Add some employees to get started!</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'EmployeesList',
  data() {
    return {
      employees: [],
    };
  },
  mounted() {
    this.getEmployees();
  },
  methods: {
    getEmployees() {
      axios
        .get('http://localhost:8001/api/employees')
        .then((res) => {
          this.employees = res.data.employees;
        })
        .catch((error) => {
          console.error('Error fetching employees:', error);
        });
    },
    deleteEmployee(id) {
      if (confirm('Confirm action: DELETE')) {
        axios
          .delete(`http://localhost:8001/api/employees/${id}`)
          .then((res) => {
            alert(res.data.message);
            this.getEmployees();
          })
          .catch((error) => {
            console.error('Error deleting employee:', error);
          });
      }
    },
    getAvatarUrl(id) {
      return `https://i.pravatar.cc/150?img=${id}`;
    },
  },
};
</script>

<style scoped>
/* Container for all employee cards */
.employee-list {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

/* Individual employee card */
.employee-card {
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: transform 0.3s, box-shadow 0.3s;
}

.employee-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
}

/* Avatar styling */
.employee-avatar img {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #dee2e6;
}

/* Employee details */
.employee-details {
  flex: 1;
}

.employee-details h5 {
  margin: 0;
  font-size: 1.1rem;
  color: #343a40;
}

.employee-details .role {
  margin: 0.2rem 0 0;
  font-size: 0.9rem;
  color: #6c757d;
  font-weight: 500;
}

/* Action buttons */
.employee-actions {
  display: flex;
  gap: 0.5rem;
}

.employee-actions .btn {
  transition: all 0.3s ease;
}

.employee-actions .btn:hover {
  color: #fff;
}

/* Empty state */
.empty-message {
  color: #adb5bd;
  font-size: 1.2rem;
  font-style: italic;
  margin-top: 1rem;
}
</style>
