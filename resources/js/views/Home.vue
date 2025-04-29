<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h1 class="text-center">Welcome to Own My Calendar</h1>
          </div>
          <div class="card-body">
            <div class="text-center mb-4">
              <p class="lead">Take control of your time and boost your productivity</p>
            </div>

            <!-- Quick Stats -->
            <div class="row mb-4">
              <div class="col-md-4">
                <div class="stat-card">
                  <i class="fas fa-calendar-check stat-icon"></i>
                  <h3>{{ stats.eventsToday }}</h3>
                  <p>Events Today</p>
                </div>
              </div>
              <div class="col-md-4">
                <div class="stat-card">
                  <i class="fas fa-chart-line stat-icon"></i>
                  <h3>{{ stats.productivityScore }}%</h3>
                  <p>Productivity Score</p>
                </div>
              </div>
              <div class="col-md-4">
                <div class="stat-card">
                  <i class="fas fa-clock stat-icon"></i>
                  <h3>{{ stats.freeTime }}</h3>
                  <p>Free Time Today</p>
                </div>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions mb-4">
              <h2 class="text-center mb-3">Quick Actions</h2>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <button class="btn btn-primary btn-lg w-100" @click="navigateToCalendar">
                    <i class="fas fa-calendar-alt me-2"></i>View Calendar
                  </button>
                </div>
                <div class="col-md-6 mb-3">
                  <button class="btn btn-success btn-lg w-100" @click="navigateToAnalytics">
                    <i class="fas fa-chart-bar me-2"></i>View Analytics
                  </button>
                </div>
              </div>
            </div>

            <!-- Recent Activity -->
            <div class="recent-activity">
              <h2 class="text-center mb-3">Recent Activity</h2>
              <div class="activity-list">
                <div v-for="activity in recentActivities" :key="activity.id" class="activity-item">
                  <div class="activity-icon">
                    <i :class="activity.icon"></i>
                  </div>
                  <div class="activity-content">
                    <p class="activity-text">{{ activity.text }}</p>
                    <small class="activity-time">{{ activity.time }}</small>
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
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

export default {
  name: 'Home',
  setup() {
    const router = useRouter();
    const stats = ref({
      eventsToday: 0,
      productivityScore: 0,
      freeTime: '0h 0m'
    });

    const recentActivities = ref([]);

    const fetchStats = async () => {
      try {
        const response = await axios.get('/api/home/stats');
        stats.value = response.data;
      } catch (error) {
        console.error('Failed to fetch stats:', error);
      }
    };

    const fetchRecentActivities = async () => {
      try {
        const response = await axios.get('/api/home/activities');
        recentActivities.value = response.data;
      } catch (error) {
        console.error('Failed to fetch activities:', error);
      }
    };

    const navigateToCalendar = () => {
      router.push('/calendar');
    };

    const navigateToAnalytics = () => {
      router.push('/analytics');
    };

    onMounted(() => {
      fetchStats();
      fetchRecentActivities();
    });

    return {
      stats,
      recentActivities,
      navigateToCalendar,
      navigateToAnalytics
    };
  }
};
</script>

<style scoped>
.card {
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

.stat-card {
  text-align: center;
  padding: 20px;
  background: #f8f9fa;
  border-radius: 10px;
  height: 100%;
}

.stat-icon {
  font-size: 2.5rem;
  color: var(--primary-purple);
  margin-bottom: 15px;
}

.quick-actions .btn {
  padding: 15px;
  font-size: 1.1rem;
}

.activity-list {
  max-height: 300px;
  overflow-y: auto;
}

.activity-item {
  display: flex;
  align-items: center;
  padding: 15px;
  border-bottom: 1px solid #eee;
}

.activity-icon {
  width: 40px;
  height: 40px;
  background: #f8f9fa;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
}

.activity-icon i {
  color: var(--primary-purple);
}

.activity-content {
  flex: 1;
}

.activity-text {
  margin: 0;
  font-size: 0.9rem;
}

.activity-time {
  color: #666;
  font-size: 0.8rem;
}
</style> 