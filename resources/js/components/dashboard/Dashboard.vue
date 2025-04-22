<template>
  <div class="dashboard-container">
    <div class="dashboard-header">
      <h1>Your Calendar Dashboard</h1>
      <p>Track your progress and see how well you're planning your time</p>
    </div>

    <div v-if="loading" class="loading-indicator">
      <p>Loading your data...</p>
    </div>

    <div v-else-if="!hasGrades" class="no-data">
      <h2>No grades yet</h2>
      <p>You haven't graded any of your calendar weeks yet. Start by grading your current week!</p>
      <router-link :to="{ name: 'grade' }" class="btn-primary">Grade This Week</router-link>
    </div>

    <div v-else class="dashboard-content">
      <div class="current-week-summary">
        <h2>Current Week</h2>
        <div v-if="currentWeekGrade" class="grade-card">
          <div class="grade-circle" :class="gradeClass(currentWeekGrade.overall_grade)">
            {{ Math.round(currentWeekGrade.overall_grade) }}
          </div>
          <div class="grade-details">
            <p class="date-range">{{ formatDateRange(currentWeekGrade.week_start_date, currentWeekGrade.week_end_date) }}</p>
            <div class="strengths-weaknesses">
              <div class="strengths">
                <h4>Strengths</h4>
                <p>{{ currentWeekGrade.strengths || 'No strengths recorded' }}</p>
              </div>
              <div class="improvements">
                <h4>Areas for Improvement</h4>
                <p>{{ currentWeekGrade.improvement_areas || 'No improvement areas recorded' }}</p>
              </div>
            </div>
            <router-link :to="{ name: 'grade' }" class="btn-secondary">Grade Again</router-link>
          </div>
        </div>
        <div v-else class="no-current-grade">
          <p>You haven't graded your calendar for the current week.</p>
          <router-link :to="{ name: 'grade' }" class="btn-primary">Grade This Week</router-link>
        </div>
      </div>

      <div class="historical-grades">
        <h2>Historical Grades</h2>
        <div class="grades-chart">
          <!-- Chart would go here - simplified for prototype -->
          <div class="chart-placeholder">
            <div v-for="(grade, index) in grades" :key="index" class="chart-bar">
              <div class="bar" :style="{ height: `${grade.overall_grade}%` }"></div>
              <span class="bar-label">{{ formatShortDate(grade.week_start_date) }}</span>
            </div>
          </div>
        </div>

        <div class="grades-list">
          <h3>Recent Grades</h3>
          <div v-for="(grade, index) in recentGrades" :key="index" class="grade-item">
            <div class="grade-item-score" :class="gradeClass(grade.overall_grade)">
              {{ Math.round(grade.overall_grade) }}
            </div>
            <div class="grade-item-details">
              <p class="date-range">{{ formatDateRange(grade.week_start_date, grade.week_end_date) }}</p>
              <router-link :to="{ name: 'grade', params: { weekStart: grade.week_start_date, weekEnd: grade.week_end_date } }" class="btn-text">View Details</router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { computed, onMounted } from 'vue';
import { useStore } from 'vuex';

export default {
  name: 'Dashboard',
  setup() {
    const store = useStore();
    
    const loading = computed(() => store.getters.loading);
    const grades = computed(() => store.getters.grades || []);
    const currentWeekGrade = computed(() => store.getters.currentWeekGrade);
    const hasGrades = computed(() => grades.value.length > 0);
    const recentGrades = computed(() => {
      return [...grades.value]
        .sort((a, b) => new Date(b.week_start_date) - new Date(a.week_start_date))
        .slice(0, 5);
    });

    onMounted(async () => {
      await store.dispatch('fetchGrades');
      await store.dispatch('fetchCurrentWeekGrade');
    });

    const formatDateRange = (start, end) => {
      const startDate = new Date(start);
      const endDate = new Date(end);
      return `${startDate.toLocaleDateString()} - ${endDate.toLocaleDateString()}`;
    };

    const formatShortDate = (date) => {
      const d = new Date(date);
      return `${d.getMonth() + 1}/${d.getDate()}`;
    };

    const gradeClass = (grade) => {
      if (grade >= 90) return 'grade-a';
      if (grade >= 80) return 'grade-b';
      if (grade >= 70) return 'grade-c';
      if (grade >= 60) return 'grade-d';
      return 'grade-f';
    };

    return {
      loading,
      grades,
      currentWeekGrade,
      hasGrades,
      recentGrades,
      formatDateRange,
      formatShortDate,
      gradeClass
    };
  }
};
</script>

<style lang="scss" scoped>
.dashboard-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
  
  .dashboard-header {
    text-align: center;
    margin-bottom: 2rem;
    
    h1 {
      font-size: 2rem;
      margin-bottom: 0.5rem;
    }
    
    p {
      font-size: 1.1rem;
      color: #666;
    }
  }
  
  .loading-indicator, .no-data {
    text-align: center;
    padding: 3rem;
    background-color: #f9f9f9;
    border-radius: 8px;
    
    h2 {
      margin-bottom: 1rem;
    }
    
    .btn-primary {
      display: inline-block;
      margin-top: 1rem;
      background-color: #4a90e2;
      color: white;
      padding: 0.75rem 2rem;
      border-radius: 4px;
      text-decoration: none;
      font-weight: 500;
      
      &:hover {
        background-color: #3a80d2;
      }
    }
  }
  
  .dashboard-content {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    
    @media (min-width: 768px) {
      grid-template-columns: 1fr 1fr;
    }
    
    .current-week-summary, .historical-grades {
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      padding: 1.5rem;
      
      h2 {
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #eee;
      }
    }
    
    .grade-card {
      display: flex;
      align-items: flex-start;
      
      .grade-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        font-weight: bold;
        color: white;
        margin-right: 1.5rem;
        flex-shrink: 0;
        
        &.grade-a { background-color: #4caf50; }
        &.grade-b { background-color: #8bc34a; }
        &.grade-c { background-color: #ffc107; }
        &.grade-d { background-color: #ff9800; }
        &.grade-f { background-color: #f44336; }
      }
      
      .grade-details {
        flex-grow: 1;
        
        .date-range {
          font-size: 0.9rem;
          color: #666;
          margin-bottom: 0.75rem;
        }
        
        .strengths-weaknesses {
          margin-bottom: 1rem;
          
          h4 {
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
            color: #555;
          }
          
          p {
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
          }
        }
        
        .btn-secondary {
          display: inline-block;
          background-color: #f5f5f5;
          color: #333;
          padding: 0.5rem 1rem;
          border-radius: 4px;
          text-decoration: none;
          font-size: 0.9rem;
          
          &:hover {
            background-color: #e0e0e0;
          }
        }
      }
    }
    
    .no-current-grade {
      text-align: center;
      padding: 2rem;
      background-color: #f9f9f9;
      border-radius: 4px;
      
      p {
        margin-bottom: 1rem;
      }
      
      .btn-primary {
        display: inline-block;
        background-color: #4a90e2;
        color: white;
        padding: 0.75rem 2rem;
        border-radius: 4px;
        text-decoration: none;
        font-weight: 500;
        
        &:hover {
          background-color: #3a80d2;
        }
      }
    }
    
    .historical-grades {
      .grades-chart {
        margin-bottom: 2rem;
        
        .chart-placeholder {
          height: 200px;
          display: flex;
          align-items: flex-end;
          justify-content: space-between;
          padding: 1rem 0;
          border-bottom: 1px solid #eee;
          
          .chart-bar {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            
            .bar {
              width: 20px;
              background-color: #4a90e2;
              border-radius: 2px 2px 0 0;
              margin-bottom: 0.5rem;
            }
            
            .bar-label {
              font-size: 0.8rem;
              color: #666;
            }
          }
        }
      }
      
      .grades-list {
        h3 {
          margin-bottom: 1rem;
          font-size: 1.2rem;
        }
        
        .grade-item {
          display: flex;
          align-items: center;
          padding: 0.75rem 0;
          border-bottom: 1px solid #eee;
          
          &:last-child {
            border-bottom: none;
          }
          
          .grade-item-score {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            margin-right: 1rem;
            
            &.grade-a { background-color: #4caf50; }
            &.grade-b { background-color: #8bc34a; }
            &.grade-c { background-color: #ffc107; }
            &.grade-d { background-color: #ff9800; }
            &.grade-f { background-color: #f44336; }
          }
          
          .grade-item-details {
            flex-grow: 1;
            
            .date-range {
              font-size: 0.9rem;
              margin-bottom: 0.25rem;
            }
            
            .btn-text {
              font-size: 0.8rem;
              color: #4a90e2;
              text-decoration: none;
              
              &:hover {
                text-decoration: underline;
              }
            }
          }
        }
      }
    }
  }
}
</style>
