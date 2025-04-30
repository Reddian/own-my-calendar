<template>
  <div>
    <h1 class="page-title">Grade History</h1>

    <!-- Filters -->
    <div class="history-filters mb-4">
      <div class="row g-3 align-items-center">
        <div class="col-auto">
          <label for="dateRangeFilter" class="col-form-label">Date Range:</label>
        </div>
        <div class="col-auto">
          <select class="form-select form-select-sm" id="dateRangeFilter" v-model="selectedRange">
            <option value="last_7_days">Last 7 Days</option>
            <option value="last_30_days">Last 30 Days</option>
            <option value="last_90_days">Last 90 Days</option>
            <option value="all_time">All Time</option>
            <option value="custom">Custom Range</option>
          </select>
        </div>
        <!-- Add custom date range inputs if needed -->
        <div class="col-auto">
          <button class="btn btn-sm btn-primary" @click="applyFilters">Apply</button>
        </div>
      </div>
    </div>

    <!-- Grade History Table -->
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>
        <div v-else-if="gradeHistory.length === 0" class="alert alert-info">No grade history found for the selected period.</div>
        <div v-else class="table-responsive">
          <table class="table table-hover history-table">
            <thead>
              <tr>
                <th>Week Of</th>
                <th>Grade</th>
                <th>Score</th>
                <th>Change</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="grade in gradeHistory" :key="grade.id">
                <td>{{ formatDate(grade.week_start_date) }}</td>
                <td>
                  <span :class="getGradeClass(grade.letter_grade)">{{ grade.letter_grade }}</span>
                </td>
                <td>{{ grade.score }}/100</td>
                <td>
                  <span :class="getChangeClass(grade.change_from_previous)">
                    <i v-if="grade.change_from_previous > 0" class="fas fa-arrow-up"></i>
                    <i v-else-if="grade.change_from_previous < 0" class="fas fa-arrow-down"></i>
                    <i v-else class="fas fa-minus"></i>
                    {{ Math.abs(grade.change_from_previous) }}%
                  </span>
                </td>
                <td>
                  <button class="btn btn-sm btn-outline-primary" @click="viewGradeDetails(grade.id)">View Details</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Details Modal (Placeholder) -->
    <!-- You would typically create a separate modal component -->
    <div class="modal fade" id="gradeDetailsModal" tabindex="-1" ref="detailsModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Grade Details - {{ selectedGrade?.week_start_date }}</h5>
            <button type="button" class="btn-close" @click="closeDetailsModal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Details for the selected grade would go here.</p>
            <p>Score: {{ selectedGrade?.score }}</p>
            <p>Recommendations: {{ selectedGrade?.recommendations }}</p>
            <!-- Add more detailed breakdown -->
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeDetailsModal">Close</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
// Assuming Bootstrap's JS is loaded globally or imported
// import { Modal } from 'bootstrap';

const selectedRange = ref('last_30_days');
const gradeHistory = ref([]);
const loading = ref(false);
const error = ref(null);
const detailsModal = ref(null);
const selectedGrade = ref(null);
let bsDetailsModal = null;

// --- Methods ---
async function fetchGradeHistory() {
  loading.value = true;
  error.value = null;
  console.log(`Fetching history for range: ${selectedRange.value}`);
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000));
    // Replace with actual API call: e.g., const response = await axios.get('/api/grades/history', { params: { range: selectedRange.value } });
    // gradeHistory.value = response.data;
    
    // Placeholder Data
    gradeHistory.value = [
      { id: 1, week_start_date: '2025-04-21', letter_grade: 'B+', score: 87, change_from_previous: 2, recommendations: 'Add buffer time...' },
      { id: 2, week_start_date: '2025-04-14', letter_grade: 'B', score: 85, change_from_previous: -3, recommendations: 'Protect planning time...' },
      { id: 3, week_start_date: '2025-04-07', letter_grade: 'A-', score: 88, change_from_previous: 6, recommendations: 'Good job...' },
      { id: 4, week_start_date: '2025-03-31', letter_grade: 'C+', score: 82, change_from_previous: 4, recommendations: 'Schedule reflection...' },
    ].filter(g => { // Basic filtering simulation
        const date = new Date(g.week_start_date);
        const now = new Date();
        if (selectedRange.value === 'last_7_days') return now.getTime() - date.getTime() < 7 * 24 * 60 * 60 * 1000;
        if (selectedRange.value === 'last_30_days') return now.getTime() - date.getTime() < 30 * 24 * 60 * 60 * 1000;
        // Add other ranges
        return true; // Default to all for simulation
    });

  } catch (err) {
    console.error("Error fetching grade history:", err);
    error.value = 'Failed to load grade history. Please try again.';
  } finally {
    loading.value = false;
  }
}

function applyFilters() {
  fetchGradeHistory();
}

function formatDate(dateString) {
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('en-US', options);
}

function getGradeClass(letterGrade) {
  // Add classes based on grade for styling (e.g., text-success, text-warning, text-danger)
  if (['A+', 'A', 'A-'].includes(letterGrade)) return 'text-success fw-bold';
  if (['B+', 'B', 'B-'].includes(letterGrade)) return 'text-primary fw-bold';
  if (['C+', 'C', 'C-'].includes(letterGrade)) return 'text-warning fw-bold';
  return 'text-danger fw-bold';
}

function getChangeClass(change) {
  if (change > 0) return 'text-success';
  if (change < 0) return 'text-danger';
  return 'text-muted';
}

function viewGradeDetails(gradeId) {
  selectedGrade.value = gradeHistory.value.find(g => g.id === gradeId);
  if (bsDetailsModal && selectedGrade.value) {
    bsDetailsModal.show();
  }
}

function closeDetailsModal() {
  if (bsDetailsModal) {
    bsDetailsModal.hide();
  }
}

// --- Lifecycle Hooks ---
onMounted(() => {
  fetchGradeHistory();
  // Initialize Bootstrap modal
  if (window.bootstrap && detailsModal.value) {
    bsDetailsModal = new window.bootstrap.Modal(detailsModal.value);
  }
});

</script>

<style scoped>
.page-title {
  /* Style as needed */
  margin-bottom: 1.5rem;
}

.history-filters {
  /* Style as needed */
}

.history-table th {
  font-weight: 600;
}

.history-table .btn-outline-primary {
    border-color: var(--primary-purple);
    color: var(--primary-purple);
}

.history-table .btn-outline-primary:hover {
    background-color: var(--primary-purple);
    color: white;
}

/* Style grade letters */
.table span.fw-bold {
    display: inline-block;
    min-width: 25px; /* Ensure alignment */
    text-align: center;
}

/* Modal styling */
.modal {
    color: #333;
}

.modal-content {
    background-color: #fff;
}

.modal-header,
.modal-body,
.modal-footer {
    color: inherit;
}
</style>
