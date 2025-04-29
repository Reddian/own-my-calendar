<template>
  <div class="container">
    <div class="row">
      <div class="col-12">
        <div class="card history-card">
          <div class="card-header">
            <h1 class="text-center">History</h1>
          </div>
          <div class="card-body">
            <!-- Date Range Selector -->
            <div class="date-range-selector mb-4">
              <div class="row align-items-center">
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="dateRange">Date Range</label>
                    <select 
                      id="dateRange" 
                      class="form-select" 
                      v-model="dateRange"
                      @change="updateDateRange"
                    >
                      <option value="7">Last 7 Days</option>
                      <option value="30">Last 30 Days</option>
                      <option value="90">Last 90 Days</option>
                      <option value="custom">Custom Range</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-4" v-if="dateRange === 'custom'">
                  <div class="form-group">
                    <label for="startDate">Start Date</label>
                    <input 
                      type="date" 
                      id="startDate" 
                      class="form-control" 
                      v-model="startDate"
                      @change="updateCustomRange"
                    >
                  </div>
                </div>
                <div class="col-md-4" v-if="dateRange === 'custom'">
                  <div class="form-group">
                    <label for="endDate">End Date</label>
                    <input 
                      type="date" 
                      id="endDate" 
                      class="form-control" 
                      v-model="endDate"
                      @change="updateCustomRange"
                    >
                  </div>
                </div>
              </div>
            </div>

            <!-- Activity Type Filter -->
            <div class="activity-type-filter mb-4">
              <div class="row">
                <div class="col-md-3">
                  <div class="form-check">
                    <input 
                      class="form-check-input" 
                      type="checkbox" 
                      id="filterEvents"
                      v-model="filters.events"
                      @change="fetchHistory"
                    >
                    <label class="form-check-label" for="filterEvents">
                      Events
                    </label>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-check">
                    <input 
                      class="form-check-input" 
                      type="checkbox" 
                      id="filterTasks"
                      v-model="filters.tasks"
                      @change="fetchHistory"
                    >
                    <label class="form-check-label" for="filterTasks">
                      Tasks
                    </label>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-check">
                    <input 
                      class="form-check-input" 
                      type="checkbox" 
                      id="filterCalendar"
                      v-model="filters.calendar"
                      @change="fetchHistory"
                    >
                    <label class="form-check-label" for="filterCalendar">
                      Calendar Changes
                    </label>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-check">
                    <input 
                      class="form-check-input" 
                      type="checkbox" 
                      id="filterSettings"
                      v-model="filters.settings"
                      @change="fetchHistory"
                    >
                    <label class="form-check-label" for="filterSettings">
                      Settings Changes
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <!-- History Timeline -->
            <div class="history-timeline">
              <div v-if="loading" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
              </div>
              <div v-else-if="historyItems.length === 0" class="text-center py-4">
                <p class="text-muted">No history items found</p>
              </div>
              <div v-else>
                <div 
                  v-for="item in historyItems" 
                  :key="item.id"
                  class="history-item"
                  :class="item.type"
                >
                  <div class="history-item-header">
                    <div class="history-item-icon">
                      <i :class="getIconClass(item.type)"></i>
                    </div>
                    <div class="history-item-title">
                      <h4>{{ item.title }}</h4>
                      <small class="text-muted">{{ formatDate(item.created_at) }}</small>
                    </div>
                  </div>
                  <div class="history-item-content">
                    <p>{{ item.description }}</p>
                    <div v-if="item.details" class="history-item-details">
                      <div 
                        v-for="(value, key) in item.details" 
                        :key="key"
                        class="detail-item"
                      >
                        <span class="detail-label">{{ formatDetailLabel(key) }}:</span>
                        <span class="detail-value">{{ value }}</span>
                      </div>
                    </div>
                  </div>
                  <div class="history-item-actions">
                    <button 
                      v-if="item.can_undo"
                      class="btn btn-sm btn-outline-primary"
                      @click="undoAction(item)"
                    >
                      <i class="fas fa-undo me-1"></i>Undo
                    </button>
                    <button 
                      v-if="item.can_view"
                      class="btn btn-sm btn-outline-secondary"
                      @click="viewDetails(item)"
                    >
                      <i class="fas fa-eye me-1"></i>View Details
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="totalPages > 1" class="pagination-container mt-4">
              <nav aria-label="History pagination">
                <ul class="pagination justify-content-center">
                  <li class="page-item" :class="{ disabled: currentPage === 1 }">
                    <a 
                      class="page-link" 
                      href="#" 
                      @click.prevent="changePage(currentPage - 1)"
                    >
                      Previous
                    </a>
                  </li>
                  <li 
                    v-for="page in totalPages" 
                    :key="page"
                    class="page-item"
                    :class="{ active: currentPage === page }"
                  >
                    <a 
                      class="page-link" 
                      href="#" 
                      @click.prevent="changePage(page)"
                    >
                      {{ page }}
                    </a>
                  </li>
                  <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                    <a 
                      class="page-link" 
                      href="#" 
                      @click.prevent="changePage(currentPage + 1)"
                    >
                      Next
                    </a>
                  </li>
                </ul>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { format } from 'date-fns';

export default {
  name: 'History',
  setup() {
    const historyItems = ref([]);
    const loading = ref(false);
    const dateRange = ref('30');
    const startDate = ref('');
    const endDate = ref('');
    const currentPage = ref(1);
    const totalPages = ref(1);
    const filters = ref({
      events: true,
      tasks: true,
      calendar: true,
      settings: true
    });

    const fetchHistory = async () => {
      loading.value = true;
      try {
        const params = {
          range: dateRange.value,
          start_date: startDate.value,
          end_date: endDate.value,
          page: currentPage.value,
          filters: filters.value
        };

        const response = await axios.get('/api/history', { params });
        historyItems.value = response.data.items;
        totalPages.value = response.data.total_pages;
      } catch (error) {
        console.error('Failed to fetch history:', error);
      } finally {
        loading.value = false;
      }
    };

    const updateDateRange = () => {
      if (dateRange.value !== 'custom') {
        currentPage.value = 1;
        fetchHistory();
      }
    };

    const updateCustomRange = () => {
      if (dateRange.value === 'custom' && startDate.value && endDate.value) {
        currentPage.value = 1;
        fetchHistory();
      }
    };

    const changePage = (page) => {
      if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
        fetchHistory();
      }
    };

    const undoAction = async (item) => {
      try {
        await axios.post(`/api/history/${item.id}/undo`);
        fetchHistory();
      } catch (error) {
        console.error('Failed to undo action:', error);
      }
    };

    const viewDetails = (item) => {
      // Implementation for viewing details
      // This could open a modal or navigate to a specific view
    };

    const getIconClass = (type) => {
      const icons = {
        event: 'fas fa-calendar-check',
        task: 'fas fa-tasks',
        calendar: 'fas fa-calendar-alt',
        settings: 'fas fa-cog'
      };
      return icons[type] || 'fas fa-history';
    };

    const formatDate = (date) => {
      return format(new Date(date), 'MMM d, yyyy h:mm a');
    };

    const formatDetailLabel = (key) => {
      return key.split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
    };

    onMounted(() => {
      fetchHistory();
    });

    return {
      historyItems,
      loading,
      dateRange,
      startDate,
      endDate,
      currentPage,
      totalPages,
      filters,
      fetchHistory,
      updateDateRange,
      updateCustomRange,
      changePage,
      undoAction,
      viewDetails,
      getIconClass,
      formatDate,
      formatDetailLabel
    };
  }
};
</script>

<style scoped>
.history-card {
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.card-header {
  background: linear-gradient(to right, var(--primary-purple), var(--primary-teal));
  color: white;
  border-top-left-radius: 10px;
  border-top-right-radius: 10px;
  padding: 20px;
}

.history-timeline {
  position: relative;
  padding: 20px 0;
}

.history-timeline::before {
  content: '';
  position: absolute;
  top: 0;
  left: 20px;
  height: 100%;
  width: 2px;
  background: #eee;
}

.history-item {
  position: relative;
  padding: 15px 20px 15px 50px;
  margin-bottom: 20px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.history-item::before {
  content: '';
  position: absolute;
  top: 20px;
  left: 15px;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: var(--primary-purple);
}

.history-item.event::before {
  background: #4CAF50;
}

.history-item.task::before {
  background: #2196F3;
}

.history-item.calendar::before {
  background: #9C27B0;
}

.history-item.settings::before {
  background: #FFC107;
}

.history-item-header {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}

.history-item-icon {
  width: 40px;
  height: 40px;
  background: #f8f9fa;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
}

.history-item-icon i {
  color: var(--primary-purple);
}

.history-item-title h4 {
  margin: 0;
  font-size: 1.1rem;
}

.history-item-content {
  margin-bottom: 15px;
}

.history-item-content p {
  margin: 0 0 10px 0;
  color: #666;
}

.history-item-details {
  background: #f8f9fa;
  padding: 10px;
  border-radius: 4px;
}

.detail-item {
  display: flex;
  margin-bottom: 5px;
}

.detail-label {
  font-weight: bold;
  margin-right: 10px;
  color: #666;
}

.detail-value {
  color: #333;
}

.history-item-actions {
  display: flex;
  gap: 10px;
}

.pagination-container {
  margin-top: 20px;
}

.page-link {
  color: var(--primary-purple);
}

.page-item.active .page-link {
  background-color: var(--primary-purple);
  border-color: var(--primary-purple);
}
</style> 