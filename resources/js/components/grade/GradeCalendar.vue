<template>
  <div class="grade-calendar-container">
    <div class="grade-header">
      <h1>Grade Your Calendar</h1>
      <p>Analyze how well your calendar aligns with your goals and priorities</p>
    </div>

    <div v-if="loading" class="loading-indicator">
      <p>Loading your calendar data...</p>
    </div>

    <div v-else-if="!calendarConnected" class="connect-calendar">
      <h2>Connect Your Google Calendar</h2>
      <p>To grade your calendar, we need to connect to your Google Calendar account.</p>
      <button @click="connectGoogleCalendar" class="btn-primary">Connect Google Calendar</button>
    </div>

    <div v-else class="grading-interface">
      <div class="date-selector">
        <h3>Select Week to Grade</h3>
        <div class="date-inputs">
          <div class="date-field">
            <label for="week-start">Week Start</label>
            <input 
              type="date" 
              id="week-start" 
              v-model="selectedWeekStart" 
              @change="loadCalendarData"
            >
          </div>
          <div class="date-field">
            <label for="week-end">Week End</label>
            <input 
              type="date" 
              id="week-end" 
              v-model="selectedWeekEnd" 
              @change="loadCalendarData"
            >
          </div>
        </div>
        <button @click="useCurrentWeek" class="btn-secondary">Use Current Week</button>
      </div>

      <div v-if="calendarData" class="calendar-preview">
        <h3>Calendar Preview</h3>
        <div class="calendar-grid">
          <!-- Simplified calendar view for prototype -->
          <div v-for="(day, index) in calendarDays" :key="index" class="calendar-day">
            <div class="day-header">{{ day.name }}</div>
            <div class="day-events">
              <div v-for="(event, eventIndex) in day.events" :key="eventIndex" class="event-item">
                <div class="event-time">{{ formatEventTime(event) }}</div>
                <div class="event-title">{{ event.title }}</div>
              </div>
              <div v-if="day.events.length === 0" class="no-events">No events</div>
            </div>
          </div>
        </div>
      </div>

      <div class="grading-actions">
        <button @click="gradeCalendar" class="btn-primary" :disabled="grading">
          {{ grading ? 'Grading...' : 'Grade My Calendar' }}
        </button>
      </div>

      <div v-if="gradeResult" class="grade-results">
        <h3>Your Calendar Grade</h3>
        <div class="grade-summary">
          <div class="grade-circle" :class="gradeClass(gradeResult.overall_grade)">
            {{ Math.round(gradeResult.overall_grade) }}
          </div>
          <div class="grade-details">
            <h4>Overall Assessment</h4>
            <p>{{ gradeResult.week_start_date }} to {{ gradeResult.week_end_date }}</p>
          </div>
        </div>

        <div class="rule-grades">
          <h4>Calendar Rules Assessment</h4>
          <div class="rules-grid">
            <div v-for="(grade, rule) in gradeResult.rule_grades" :key="rule" class="rule-item">
              <div class="rule-letter">{{ rule }}</div>
              <div class="rule-score" :class="gradeClass(grade)">{{ Math.round(grade) }}</div>
              <div class="rule-name">{{ getRuleName(rule) }}</div>
            </div>
          </div>
        </div>

        <div class="grade-feedback">
          <div class="feedback-section">
            <h4>Strengths</h4>
            <p>{{ gradeResult.strengths }}</p>
          </div>
          <div class="feedback-section">
            <h4>Areas for Improvement</h4>
            <p>{{ gradeResult.improvement_areas }}</p>
          </div>
          <div class="feedback-section">
            <h4>Recommendations</h4>
            <p>{{ gradeResult.recommendations }}</p>
          </div>
        </div>

        <div class="grade-actions">
          <router-link :to="{ name: 'dashboard' }" class="btn-secondary">Back to Dashboard</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import { useRouter, useRoute } from 'vue-router';

export default {
  name: 'GradeCalendar',
  setup() {
    const store = useStore();
    const router = useRouter();
    const route = useRoute();
    
    const loading = ref(false);
    const grading = ref(false);
    const calendarConnected = ref(false); // This would be determined by Google Calendar integration
    const calendarData = ref(null);
    const gradeResult = ref(null);
    
    // Date selection
    const today = new Date();
    const startOfWeek = new Date(today);
    startOfWeek.setDate(today.getDate() - today.getDay()); // Sunday
    const endOfWeek = new Date(today);
    endOfWeek.setDate(startOfWeek.getDate() + 6); // Saturday
    
    const selectedWeekStart = ref(formatDateForInput(route.params.weekStart ? new Date(route.params.weekStart) : startOfWeek));
    const selectedWeekEnd = ref(formatDateForInput(route.params.weekEnd ? new Date(route.params.weekEnd) : endOfWeek));
    
    // Calendar days for display
    const calendarDays = computed(() => {
      if (!calendarData.value || !calendarData.value.events) return [];
      
      const days = [];
      const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
      
      // Create array for each day of the week
      for (let i = 0; i < 7; i++) {
        const date = new Date(selectedWeekStart.value);
        date.setDate(date.getDate() + i);
        const dateStr = formatDateForInput(date);
        
        days.push({
          date: date,
          name: dayNames[i],
          events: getEventsForDay(dateStr)
        });
      }
      
      return days;
    });
    
    // Get events for a specific day from the calendar data
    function getEventsForDay(dateStr) {
      if (!calendarData.value || !calendarData.value.events) return [];
      
      return calendarData.value.events.filter(event => {
        // Handle all-day events
        if (event.all_day) {
          return event.start.includes(dateStr);
        }
        
        // Handle regular events
        const eventDate = event.start.split('T')[0];
        return eventDate === dateStr;
      }).map(event => {
        // Format the event for display
        let start = event.all_day ? 'All day' : formatTimeFromDateTime(event.start);
        let end = event.all_day ? '' : formatTimeFromDateTime(event.end);
        
        return {
          id: event.id,
          title: event.title,
          description: event.description,
          start: start,
          end: end,
          all_day: event.all_day,
          location: event.location,
          attendees: event.attendees
        };
      });
    }
    
    // Helper function to format time from datetime string
    function formatTimeFromDateTime(dateTimeStr) {
      if (!dateTimeStr) return '';
      
      const date = new Date(dateTimeStr);
      return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    
    function formatEventTime(event) {
      return `${event.start} - ${event.end}`;
    }
    
    function formatDateForInput(date) {
      return date.toISOString().split('T')[0];
    }
    
    function useCurrentWeek() {
      selectedWeekStart.value = formatDateForInput(startOfWeek);
      selectedWeekEnd.value = formatDateForInput(endOfWeek);
      loadCalendarData();
    }
    
    async function loadCalendarData() {
      loading.value = true;
      
      try {
        const response = await axios.post('/api/google/events', {
          start_date: selectedWeekStart.value,
          end_date: selectedWeekEnd.value
        });
        
        calendarData.value = {
          weekStart: selectedWeekStart.value,
          weekEnd: selectedWeekEnd.value,
          events: response.data.events
        };
        
        calendarConnected.value = true;
      } catch (error) {
        console.error('Failed to load calendar data:', error);
        if (error.response && error.response.status === 401) {
          calendarConnected.value = false;
        }
      } finally {
        loading.value = false;
      }
    }
    
    async function connectGoogleCalendar() {
      loading.value = true;
      
      try {
        const response = await axios.get('/api/google/redirect');
        window.open(response.data.auth_url, '_blank');
        
        // Poll for connection status changes
        const pollInterval = setInterval(async () => {
          try {
            const checkResponse = await axios.get('/api/google/check-connection');
            if (checkResponse.data.connected) {
              clearInterval(pollInterval);
              calendarConnected.value = true;
              loadCalendarData();
            }
          } catch (error) {
            console.error('Failed to check connection status:', error);
          }
        }, 5000); // Check every 5 seconds
        
        // Stop polling after 5 minutes (300 seconds)
        setTimeout(() => {
          clearInterval(pollInterval);
          loading.value = false;
        }, 300000);
        
      } catch (error) {
        console.error('Failed to connect Google Calendar:', error);
        loading.value = false;
      }
    }
    
    async function gradeCalendar() {
      grading.value = true;
      
      try {
        // Call the AI grading API endpoint
        const response = await axios.post('/api/ai/grade-calendar', {
          start_date: selectedWeekStart.value,
          end_date: selectedWeekEnd.value
        });
        
        // Set the grade result from the API response
        gradeResult.value = response.data.grade;
        
        // Save the grade to the store
        store.dispatch('saveGrade', gradeResult.value);
        
      } catch (error) {
        console.error('Failed to grade calendar:', error);
        alert('Failed to grade your calendar. Please try again later.');
      } finally {
        grading.value = false;
      }
    }
    
    function gradeClass(grade) {
      if (grade >= 90) return 'grade-a';
      if (grade >= 80) return 'grade-b';
      if (grade >= 70) return 'grade-c';
      if (grade >= 60) return 'grade-d';
      return 'grade-f';
    }
    
    function getRuleName(rule) {
      const ruleNames = {
        'A': 'Non-Negotiables',
        'B': 'Determine Where You Will Sacrifice',
        'C': 'Money-Making Activities',
        'D': 'Reflection Time',
        'E': 'Learning Time',
        'F': 'Planning / Me Time',
        'G': 'Remove One Activity',
        'H': 'Self-Assessment',
        'I': 'Protect Your Time',
        'J': 'Honest Evaluation',
        'K': 'Journal Your Progress',
        'L': 'Live and Die by Your Calendar',
        'M': 'Manage Your Life',
        'N': 'Never Deviate',
        'O': 'Organize Daily',
        'P': 'Purpose of This Meeting',
        'Q': 'Question Your Own Beliefs',
        'R': 'Reject All Requests',
        'S': 'Slowly Build a Week',
        'T': 'Train Yourself to Think',
        'U': 'Understand Importance',
        'V': 'Evaluate Yourself',
        'W': 'Without Your Calendar',
        'X': 'X-Factor',
        'Y': 'Yearn for Less',
        'Z': 'Zenith (Your Mount Everest)'
      };
      
      return ruleNames[rule] || rule;
    }
    
    onMounted(() => {
      loadCalendarData();
    });
    
    return {
      loading,
      grading,
      calendarConnected,
      calendarData,
      gradeResult,
      selectedWeekStart,
      selectedWeekEnd,
      calendarDays,
      formatEventTime,
      useCurrentWeek,
      loadCalendarData,
      connectGoogleCalendar,
      gradeCalendar,
      gradeClass,
      getRuleName
    };
  }
};
</script>

<style lang="scss" scoped>
.grade-calendar-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
  
  .grade-header {
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
  
  .loading-indicator, .connect-calendar {
    text-align: center;
    padding: 3rem;
    background-color: #f9f9f9;
    border-radius: 8px;
    margin-bottom: 2rem;
    
    h2 {
      margin-bottom: 1rem;
    }
    
    p {
      margin-bottom: 1.5rem;
    }
  }
  
  .btn-primary {
    background-color: #4a90e2;
    color: white;
    border: none;
    padding: 0.75rem 2rem;
    font-size: 1rem;
    border-radius: 4px;
    cursor: pointer;
    
    &:hover {
      background-color: #3a80d2;
    }
    
    &:disabled {
      background-color: #a0c0e8;
      cursor: not-allowed;
    }
  }
  
  .btn-secondary {
    background-color: #f5f5f5;
    color: #333;
    border: none;
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    
    &:hover {
      background-color: #e0e0e0;
    }
  }
  
  .grading-interface {
    .date-selector {
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      padding: 1.5rem;
      margin-bottom: 2rem;
      
      h3 {
        margin-bottom: 1rem;
      }
      
      .date-inputs {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
        
        @media (max-width: 576px) {
          flex-direction: column;
        }
        
        .date-field {
          flex: 1;
          
          label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            color: #555;
          }
          
          input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
            font-size: 1rem;
          }
        }
      }
    }
    
    .calendar-preview {
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      padding: 1.5rem;
      margin-bottom: 2rem;
      
      h3 {
        margin-bottom: 1rem;
      }
      
      .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 0.5rem;
        
        @media (max-width: 992px) {
          grid-template-columns: repeat(3, 1fr);
        }
        
        @media (max-width: 576px) {
          grid-template-columns: 1fr;
        }
        
        .calendar-day {
          border: 1px solid #eee;
          border-radius: 4px;
          overflow: hidden;
          
          .day-header {
            background-color: #f5f5f5;
            padding: 0.5rem;
            text-align: center;
            font-weight: 500;
            border-bottom: 1px solid #eee;
          }
          
          .day-events {
            padding: 0.5rem;
            max-height: 300px;
            overflow-y: auto;
            
            .event-item {
              padding: 0.5rem;
              border-bottom: 1px solid #f5f5f5;
              
              &:last-child {
                border-bottom: none;
              }
              
              .event-time {
                font-size: 0.8rem;
                color: #666;
                margin-bottom: 0.25rem;
              }
              
              .event-title {
                font-size: 0.9rem;
              }
            }
            
            .no-events {
              padding: 1rem;
              text-align: center;
              color: #999;
              font-style: italic;
            }
          }
        }
      }
    }
    
    .grading-actions {
      text-align: center;
      margin-bottom: 2rem;
    }
    
    .grade-results {
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      padding: 1.5rem;
      
      h3 {
        margin-bottom: 1.5rem;
        text-align: center;
      }
      
      .grade-summary {
        display: flex;
        align-items: center;
        margin-bottom: 2rem;
        
        .grade-circle {
          width: 100px;
          height: 100px;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 2.5rem;
          font-weight: bold;
          color: white;
          margin-right: 2rem;
          
          &.grade-a { background-color: #4caf50; }
          &.grade-b { background-color: #8bc34a; }
          &.grade-c { background-color: #ffc107; }
          &.grade-d { background-color: #ff9800; }
          &.grade-f { background-color: #f44336; }
        }
        
        .grade-details {
          h4 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
          }
          
          p {
            color: #666;
          }
        }
      }
      
      .rule-grades {
        margin-bottom: 2rem;
        
        h4 {
          margin-bottom: 1rem;
        }
        
        .rules-grid {
          display: grid;
          grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
          gap: 1rem;
          
          .rule-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            border: 1px solid #eee;
            border-radius: 4px;
            
            .rule-letter {
              font-weight: bold;
              margin-right: 0.5rem;
              width: 20px;
            }
            
            .rule-score {
              width: 30px;
              height: 30px;
              border-radius: 50%;
              display: flex;
              align-items: center;
              justify-content: center;
              font-size: 0.8rem;
              font-weight: bold;
              color: white;
              margin-right: 0.75rem;
              
              &.grade-a { background-color: #4caf50; }
              &.grade-b { background-color: #8bc34a; }
              &.grade-c { background-color: #ffc107; }
              &.grade-d { background-color: #ff9800; }
              &.grade-f { background-color: #f44336; }
            }
            
            .rule-name {
              font-size: 0.9rem;
              flex-grow: 1;
            }
          }
        }
      }
      
      .grade-feedback {
        margin-bottom: 2rem;
        
        .feedback-section {
          margin-bottom: 1.5rem;
          
          h4 {
            margin-bottom: 0.5rem;
            padding-bottom: 0.25rem;
            border-bottom: 1px solid #eee;
          }
          
          p {
            line-height: 1.5;
          }
        }
      }
      
      .grade-actions {
        text-align: center;
      }
    }
  }
}
</style>
