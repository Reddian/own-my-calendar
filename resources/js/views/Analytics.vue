<template>
  <div class="container">
    <div class="row">
      <div class="col-12">
        <div class="card analytics-card">
          <div class="card-header">
            <h1 class="text-center">Calendar Analytics</h1>
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

            <!-- Summary Cards -->
            <div class="row mb-4">
              <div class="col-md-3">
                <div class="summary-card">
                  <div class="summary-icon">
                    <i class="fas fa-clock"></i>
                  </div>
                  <div class="summary-content">
                    <h3>{{ summary.totalHours }}h</h3>
                    <p>Total Time</p>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="summary-card">
                  <div class="summary-icon">
                    <i class="fas fa-calendar-check"></i>
                  </div>
                  <div class="summary-content">
                    <h3>{{ summary.totalEvents }}</h3>
                    <p>Total Events</p>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="summary-card">
                  <div class="summary-icon">
                    <i class="fas fa-chart-line"></i>
                  </div>
                  <div class="summary-content">
                    <h3>{{ summary.productivityScore }}%</h3>
                    <p>Productivity Score</p>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="summary-card">
                  <div class="summary-icon">
                    <i class="fas fa-balance-scale"></i>
                  </div>
                  <div class="summary-content">
                    <h3>{{ summary.workLifeBalance }}%</h3>
                    <p>Work-Life Balance</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Charts -->
            <div class="row mb-4">
              <div class="col-md-6">
                <div class="chart-card">
                  <h3>Time Distribution</h3>
                  <div class="chart-container">
                    <canvas ref="timeDistributionChart"></canvas>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="chart-card">
                  <h3>Event Types</h3>
                  <div class="chart-container">
                    <canvas ref="eventTypesChart"></canvas>
                  </div>
                </div>
              </div>
            </div>

            <!-- Detailed Analytics -->
            <div class="detailed-analytics">
              <h3>Detailed Analytics</h3>
              <div class="table-responsive">
                <table class="table">
                  <thead>
                    <tr>
                      <th>Category</th>
                      <th>Time Spent</th>
                      <th>% of Total</th>
                      <th>Events</th>
                      <th>Trend</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="category in detailedAnalytics" :key="category.id">
                      <td>
                        <i :class="['fas', category.icon]"></i>
                        {{ category.name }}
                      </td>
                      <td>{{ category.timeSpent }}</td>
                      <td>{{ category.percentage }}%</td>
                      <td>{{ category.events }}</td>
                      <td>
                        <span :class="['trend', category.trend > 0 ? 'up' : 'down']">
                          <i :class="['fas', category.trend > 0 ? 'fa-arrow-up' : 'fa-arrow-down']"></i>
                          {{ Math.abs(category.trend) }}%
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Recommendations -->
            <div class="recommendations mt-4">
              <h3>Recommendations</h3>
              <div class="row">
                <div 
                  v-for="recommendation in recommendations" 
                  :key="recommendation.id"
                  class="col-md-4 mb-3"
                >
                  <div class="recommendation-card">
                    <div class="recommendation-icon">
                      <i :class="['fas', recommendation.icon]"></i>
                    </div>
                    <div class="recommendation-content">
                      <h4>{{ recommendation.title }}</h4>
                      <p>{{ recommendation.description }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue';
import Chart from 'chart.js/auto';

export default {
  name: 'Analytics',
  setup() {
    const dateRange = ref('30');
    const startDate = ref('');
    const endDate = ref('');
    const summary = ref({
      totalHours: 0,
      totalEvents: 0,
      productivityScore: 0,
      workLifeBalance: 0
    });
    const detailedAnalytics = ref([]);
    const recommendations = ref([]);
    const timeDistributionChart = ref(null);
    const eventTypesChart = ref(null);

    const fetchAnalytics = async () => {
      try {
        const params = {
          range: dateRange.value,
          start_date: startDate.value,
          end_date: endDate.value
        };

        const response = await axios.get('/api/analytics', { params });
        const data = response.data;

        summary.value = data.summary;
        detailedAnalytics.value = data.detailed;
        recommendations.value = data.recommendations;

        updateCharts(data.charts);
      } catch (error) {
        console.error('Failed to fetch analytics:', error);
      }
    };

    const updateDateRange = () => {
      if (dateRange.value !== 'custom') {
        fetchAnalytics();
      }
    };

    const updateCustomRange = () => {
      if (dateRange.value === 'custom' && startDate.value && endDate.value) {
        fetchAnalytics();
      }
    };

    const updateCharts = (chartData) => {
      // Time Distribution Chart
      if (timeDistributionChart.value) {
        new Chart(timeDistributionChart.value, {
          type: 'pie',
          data: {
            labels: chartData.timeDistribution.labels,
            datasets: [{
              data: chartData.timeDistribution.data,
              backgroundColor: [
                '#4CAF50',
                '#2196F3',
                '#FFC107',
                '#9C27B0',
                '#F44336'
              ]
            }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: {
                position: 'right'
              }
            }
          }
        });
      }

      // Event Types Chart
      if (eventTypesChart.value) {
        new Chart(eventTypesChart.value, {
          type: 'bar',
          data: {
            labels: chartData.eventTypes.labels,
            datasets: [{
              label: 'Events',
              data: chartData.eventTypes.data,
              backgroundColor: '#2196F3'
            }]
          },
          options: {
            responsive: true,
            scales: {
              y: {
                beginAtZero: true
              }
            }
          }
        });
      }
    };

    onMounted(() => {
      fetchAnalytics();
    });

    watch([dateRange, startDate, endDate], () => {
      fetchAnalytics();
    });

    return {
      dateRange,
      startDate,
      endDate,
      summary,
      detailedAnalytics,
      recommendations,
      timeDistributionChart,
      eventTypesChart,
      updateDateRange,
      updateCustomRange
    };
  }
};
</script>

<style scoped>
.analytics-card {
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

.summary-card {
  background: #f8f9fa;
  border-radius: 10px;
  padding: 20px;
  display: flex;
  align-items: center;
  height: 100%;
}

.summary-icon {
  width: 50px;
  height: 50px;
  background: var(--primary-purple);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
}

.summary-icon i {
  font-size: 1.5rem;
}

.summary-content h3 {
  margin: 0;
  font-size: 1.8rem;
  color: var(--primary-purple);
}

.summary-content p {
  margin: 0;
  color: #666;
}

.chart-card {
  background: white;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  height: 100%;
}

.chart-container {
  position: relative;
  height: 300px;
}

.detailed-analytics {
  background: white;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  margin-top: 20px;
}

.table th {
  background: #f8f9fa;
  border-bottom: 2px solid #dee2e6;
}

.table td {
  vertical-align: middle;
}

.trend {
  display: inline-flex;
  align-items: center;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 0.8rem;
}

.trend.up {
  background: #e8f5e9;
  color: #2e7d32;
}

.trend.down {
  background: #ffebee;
  color: #c62828;
}

.recommendation-card {
  background: white;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  height: 100%;
  display: flex;
  align-items: flex-start;
}

.recommendation-icon {
  width: 40px;
  height: 40px;
  background: var(--primary-purple);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
  flex-shrink: 0;
}

.recommendation-content h4 {
  margin: 0 0 10px 0;
  color: var(--primary-purple);
}

.recommendation-content p {
  margin: 0;
  color: #666;
  font-size: 0.9rem;
}
</style> 