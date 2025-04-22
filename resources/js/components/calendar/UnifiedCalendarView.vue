&lt;template&gt;
  &lt;div class="unified-calendar-view"&gt;
    &lt;div class="calendar-container"&gt;
      &lt;div class="calendar-header"&gt;
        &lt;div class="date-navigation"&gt;
          &lt;button @click="previousWeek" class="nav-button"&gt;
            &lt;i class="fas fa-chevron-left"&gt;&lt;/i&gt;
          &lt;/button&gt;
          &lt;h2&gt;{{ formattedDateRange }}&lt;/h2&gt;
          &lt;button @click="nextWeek" class="nav-button"&gt;
            &lt;i class="fas fa-chevron-right"&gt;&lt;/i&gt;
          &lt;/button&gt;
        &lt;/div&gt;
        &lt;div class="view-controls"&gt;
          &lt;button @click="setView('week')" :class="{ active: currentView === 'week' }" class="view-button"&gt;Week&lt;/button&gt;
          &lt;button @click="setView('month')" :class="{ active: currentView === 'month' }" class="view-button"&gt;Month&lt;/button&gt;
        &lt;/div&gt;
      &lt;/div&gt;
      
      &lt;div v-if="isLoading" class="loading-indicator"&gt;
        &lt;p&gt;Loading your calendar data...&lt;/p&gt;
      &lt;/div&gt;
      
      &lt;div v-else-if="!hasCalendars" class="no-calendars"&gt;
        &lt;div class="empty-state"&gt;
          &lt;i class="fas fa-calendar-alt empty-icon"&gt;&lt;/i&gt;
          &lt;h3&gt;No Calendars Connected&lt;/h3&gt;
          &lt;p&gt;Connect your Google Calendars to view and grade your schedule.&lt;/p&gt;
          &lt;button @click="navigateToCalendarManager" class="btn-connect"&gt;
            Manage Calendars
          &lt;/button&gt;
        &lt;/div&gt;
      &lt;/div&gt;
      
      &lt;div v-else class="calendar-grid"&gt;
        &lt;!-- Week View --&gt;
        &lt;div v-if="currentView === 'week'" class="week-view"&gt;
          &lt;div class="time-column"&gt;
            &lt;div class="day-header"&gt;&lt;/div&gt;
            &lt;div v-for="hour in hours" :key="hour" class="time-slot"&gt;
              {{ formatHour(hour) }}
            &lt;/div&gt;
          &lt;/div&gt;
          
          &lt;div v-for="day in weekDays" :key="day.date" class="day-column"&gt;
            &lt;div class="day-header" :class="{ 'today': isToday(day.date) }"&gt;
              &lt;div class="day-name"&gt;{{ day.dayName }}&lt;/div&gt;
              &lt;div class="day-number"&gt;{{ day.dayNumber }}&lt;/div&gt;
            &lt;/div&gt;
            
            &lt;div class="day-events"&gt;
              &lt;div v-for="hour in hours" :key="hour" class="time-slot"&gt;
                &lt;!-- Events will be positioned absolutely within these slots --&gt;
              &lt;/div&gt;
              
              &lt;div 
                v-for="event in getEventsForDay(day.date)" 
                :key="event.id" 
                class="calendar-event"
                :style="getEventStyle(event, day.date)"
                :class="{ 'all-day-event': event.all_day }"
              &gt;
                &lt;div class="event-content" :style="{ backgroundColor: event.calendar_color || '#4285F4' }"&gt;
                  &lt;div class="event-time" v-if="!event.all_day"&gt;
                    {{ formatEventTime(event) }}
                  &lt;/div&gt;
                  &lt;div class="event-title"&gt;{{ event.title }}&lt;/div&gt;
                  &lt;div class="event-calendar"&gt;{{ event.calendar_name }}&lt;/div&gt;
                &lt;/div&gt;
              &lt;/div&gt;
            &lt;/div&gt;
          &lt;/div&gt;
        &lt;/div&gt;
        
        &lt;!-- Month View --&gt;
        &lt;div v-else-if="currentView === 'month'" class="month-view"&gt;
          &lt;div v-for="dayName in dayNames" :key="dayName" class="day-header"&gt;
            {{ dayName }}
          &lt;/div&gt;
          
          &lt;div 
            v-for="day in monthDays" 
            :key="day.date" 
            class="month-day"
            :class="{ 
              'other-month': !day.isCurrentMonth, 
              'today': isToday(day.date),
              'has-events': getEventsForDay(day.date).length > 0
            }"
          &gt;
            &lt;div class="day-number"&gt;{{ day.dayNumber }}&lt;/div&gt;
            
            &lt;div class="day-events-mini"&gt;
              &lt;div 
                v-for="(event, index) in getEventsForDay(day.date).slice(0, 3)" 
                :key="event.id" 
                class="month-event"
                :style="{ backgroundColor: event.calendar_color || '#4285F4' }"
              &gt;
                {{ event.title }}
              &lt;/div&gt;
              
              &lt;div v-if="getEventsForDay(day.date).length > 3" class="more-events"&gt;
                +{{ getEventsForDay(day.date).length - 3 }} more
              &lt;/div&gt;
            &lt;/div&gt;
          &lt;/div&gt;
        &lt;/div&gt;
      &lt;/div&gt;
    &lt;/div&gt;
    
    &lt;div class="grades-sidebar"&gt;
      &lt;div class="sidebar-header"&gt;
        &lt;h3&gt;Calendar Grade&lt;/h3&gt;
        &lt;button @click="gradeCalendar" class="btn-grade" :disabled="isGrading || !canGrade"&gt;
          {{ isGrading ? 'Grading...' : 'Grade Now' }}
        &lt;/button&gt;
      &lt;/div&gt;
      
      &lt;div v-if="isGrading" class="grading-progress"&gt;
        &lt;p&gt;Analyzing your calendar...&lt;/p&gt;
        &lt;div class="progress-bar"&gt;
          &lt;div class="progress-fill"&gt;&lt;/div&gt;
        &lt;/div&gt;
      &lt;/div&gt;
      
      &lt;div v-else-if="!currentGrade" class="no-grade"&gt;
        &lt;p&gt;No grade available for this week.&lt;/p&gt;
        &lt;p v-if="!canGrade" class="upgrade-notice"&gt;
          You've used all your free grades. 
          &lt;router-link to="/subscription" class="upgrade-link"&gt;Upgrade to Premium&lt;/router-link&gt; 
          for unlimited grades.
        &lt;/p&gt;
      &lt;/div&gt;
      
      &lt;div v-else class="grade-results"&gt;
        &lt;div class="overall-grade"&gt;
          &lt;div class="grade-circle" :class="gradeClass"&gt;
            {{ currentGrade.overall_grade }}
          &lt;/div&gt;
          &lt;div class="grade-label"&gt;Overall Grade&lt;/div&gt;
        &lt;/div&gt;
        
        &lt;div class="grade-details"&gt;
          &lt;h4&gt;Strengths&lt;/h4&gt;
          &lt;ul class="strengths-list"&gt;
            &lt;li v-for="(strength, index) in currentGrade.strengths" :key="'strength-'+index"&gt;
              {{ strength }}
            &lt;/li&gt;
          &lt;/ul&gt;
          
          &lt;h4&gt;Areas for Improvement&lt;/h4&gt;
          &lt;ul class="improvements-list"&gt;
            &lt;li v-for="(improvement, index) in currentGrade.improvements" :key="'improvement-'+index"&gt;
              {{ improvement }}
            &lt;/li&gt;
          &lt;/ul&gt;
          
          &lt;h4&gt;Recommendations&lt;/h4&gt;
          &lt;div class="recommendations"&gt;
            &lt;p v-for="(recommendation, index) in currentGrade.recommendations" :key="'rec-'+index"&gt;
              {{ recommendation }}
            &lt;/p&gt;
          &lt;/div&gt;
        &lt;/div&gt;
        
        &lt;div class="rule-grades"&gt;
          &lt;h4&gt;Calendar Rules Grades&lt;/h4&gt;
          &lt;div v-for="(ruleGrade, rule) in currentGrade.rule_grades" :key="rule" class="rule-grade-item"&gt;
            &lt;div class="rule-letter"&gt;{{ rule }}&lt;/div&gt;
            &lt;div class="rule-grade" :class="getRuleGradeClass(ruleGrade)"&gt;{{ ruleGrade }}&lt;/div&gt;
          &lt;/div&gt;
        &lt;/div&gt;
      &lt;/div&gt;
    &lt;/div&gt;
  &lt;/div&gt;
&lt;/template&gt;

&lt;script&gt;
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import dayjs from 'dayjs';

export default {
  name: 'UnifiedCalendarView',
  
  setup() {
    const router = useRouter();
    const events = ref([]);
    const calendars = ref([]);
    const currentGrade = ref(null);
    const isLoading = ref(true);
    const isGrading = ref(false);
    const canGrade = ref(true);
    const error = ref(null);
    
    // Calendar view state
    const currentView = ref('week');
    const currentDate = ref(dayjs());
    const hours = ref(Array.from({ length: 24 }, (_, i) => i));
    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    
    // Computed properties
    const hasCalendars = computed(() => calendars.value.length > 0);
    
    const weekDays = computed(() => {
      const startOfWeek = currentDate.value.startOf('week');
      return Array.from({ length: 7 }, (_, i) => {
        const date = startOfWeek.add(i, 'day');
        return {
          date: date.format('YYYY-MM-DD'),
          dayName: date.format('ddd'),
          dayNumber: date.format('D')
        };
      });
    });
    
    const monthDays = computed(() => {
      const startOfMonth = currentDate.value.startOf('month');
      const startOfGrid = startOfMonth.startOf('week');
      const endOfMonth = currentDate.value.endOf('month');
      const endOfGrid = endOfMonth.endOf('week');
      const totalDays = endOfGrid.diff(startOfGrid, 'day') + 1;
      
      return Array.from({ length: totalDays }, (_, i) => {
        const date = startOfGrid.add(i, 'day');
        return {
          date: date.format('YYYY-MM-DD'),
          dayNumber: date.format('D'),
          isCurrentMonth: date.month() === currentDate.value.month()
        };
      });
    });
    
    const formattedDateRange = computed(() => {
      if (currentView.value === 'week') {
        const startOfWeek = currentDate.value.startOf('week');
        const endOfWeek = currentDate.value.endOf('week');
        return `${startOfWeek.format('MMM D')} - ${endOfWeek.format('MMM D, YYYY')}`;
      } else {
        return currentDate.value.format('MMMM YYYY');
      }
    });
    
    const gradeClass = computed(() => {
      if (!currentGrade.value) return '';
      
      const grade = currentGrade.value.overall_grade;
      if (grade === 'A' || grade === 'A-' || grade === 'A+') return 'grade-a';
      if (grade === 'B' || grade === 'B-' || grade === 'B+') return 'grade-b';
      if (grade === 'C' || grade === 'C-' || grade === 'C+') return 'grade-c';
      if (grade === 'D' || grade === 'D-' || grade === 'D+') return 'grade-d';
      return 'grade-f';
    });
    
    // Methods
    const fetchCalendars = async () => {
      try {
        const response = await axios.get('/api/calendars');
        if (response.data.success) {
          calendars.value = response.data.calendars;
        } else {
          error.value = response.data.error || 'Failed to load calendars';
        }
      } catch (err) {
        console.error('Error fetching calendars:', err);
        error.value = 'Failed to load calendars';
      }
    };
    
    const fetchEvents = async () => {
      isLoading.value = true;
      try {
        let startDate, endDate;
        
        if (currentView.value === 'week') {
          startDate = currentDate.value.startOf('week').format('YYYY-MM-DD');
          endDate = currentDate.value.endOf('week').format('YYYY-MM-DD');
        } else {
          startDate = currentDate.value.startOf('month').startOf('week').format('YYYY-MM-DD');
          endDate = currentDate.value.endOf('month').endOf('week').format('YYYY-MM-DD');
        }
        
        const response = await axios.post('/api/calendars/events', {
          start_date: startDate,
          end_date: endDate
        });
        
        if (response.data.success) {
          events.value = response.data.events;
        } else {
          error.value = response.data.error || 'Failed to load events';
        }
      } catch (err) {
        console.error('Error fetching events:', err);
        error.value = 'Failed to load events';
      } finally {
        isLoading.value = false;
      }
    };
    
    const fetchCurrentGrade = async () => {
      try {
        const startDate = currentDate.value.startOf('week').format('YYYY-MM-DD');
        const endDate = currentDate.value.endOf('week').format('YYYY-MM-DD');
        
        const response = await axios.post('/api/grades/date-range', {
          start_date: startDate,
          end_date: endDate
        });
        
        if (response.data.success && response.data.grades.length > 0) {
          currentGrade.value = response.data.grades[0];
        } else {
          currentGrade.value = null;
        }
      } catch (err) {
        console.error('Error fetching grade:', err);
        currentGrade.value = null;
      }
    };
    
    const checkGradeEligibility = async () => {
      try {
        const response = await axios.get('/api/subscription/can-grade');
        canGrade.value = response.data.can_grade;
      } catch (err) {
        console.error('Error checking grade eligibility:', err);
        canGrade.value = false;
      }
    };
    
    const gradeCalendar = async () => {
      if (!canGrade.value || isGrading.value) return;
      
      isGrading.value = true;
      try {
        const startDate = currentDate.value.startOf('week').format('YYYY-MM-DD');
        const endDate = currentDate.value.endOf('week').format('YYYY-MM-DD');
        
        // First increment the grades used counter
        await axios.post('/api/subscription/increment-grades');
        
        // Then request the AI grading
        const response = await axios.post('/api/ai/grade-calendar', {
          start_date: startDate,
          end_date: endDate
        });
        
        if (response.data.success) {
          currentGrade.value = response.data.grade;
          await checkGradeEligibility(); // Update eligibility after grading
        } else {
          error.value = response.data.error || 'Failed to grade calendar';
        }
      } catch (err) {
        console.error('Error grading calendar:', err);
        error.value = 'Failed to grade calendar';
      } finally {
        isGrading.value = false;
      }
    };
    
    const previousWeek = () => {
      if (currentView.value === 'week') {
        currentDate.value = currentDate.value.subtract(1, 'week');
      } else {
        currentDate.value = currentDate.value.subtract(1, 'month');
      }
      fetchEvents();
      fetchCurrentGrade();
    };
    
    const nextWeek = () => {
      if (currentView.value === 'week') {
        currentDate.value = currentDate.value.add(1, 'week');
      } else {
        currentDate.value = currentDate.value.add(1, 'month');
      }
      fetchEvents();
      fetchCurrentGrade();
    };
    
    const setView = (view) => {
      currentView.value = view;
      fetchEvents();
    };
    
    const getEventsForDay = (date) => {
      return events.value.filter(event => {
        const eventDate = dayjs(event.start).format('YYYY-MM-DD');
        return eventDate === date;
      });
    };
    
    const formatHour = (hour) => {
      return dayjs().hour(hour).minute(0).format('h A');
    };
    
    const formatEventTime = (event) => {
      return `${dayjs(event.start).format('h:mm A')} - ${dayjs(event.end).format('h:mm A')}`;
    };
    
    const getEventStyle = (event, date) => {
      if (event.all_day) {
        return {
          gridRow: '1',
          gridColumn: '1 / -1'
        };
      }
      
      const eventStart = dayjs(event.start);
      const eventEnd = dayjs(event.end);
      const startHour = eventStart.hour() + (eventStart.minute() / 60);
      const endHour = eventEnd.hour() + (eventEnd.minute() / 60);
      const duration = endHour - startHour;
      
      return {
        gridRowStart: Math.floor(startHour) + 1,
        gridRowEnd: 'span ' + Math.ceil(duration),
        zIndex: 10
      };
    };
    
    const isToday = (date) => {
      return date === dayjs().format('YYYY-MM-DD');
    };
    
    const getRuleGradeClass = (grade) => {
      if (grade === 'A' || grade === 'A-' || grade === 'A+') return 'grade-a';
      if (grade === 'B' || grade === 'B-' || grade === 'B+') return 'grade-b';
      if (grade === 'C' || grade === 'C-' || grade === 'C+') return 'grade-c';
      if (grade === 'D' || grade === 'D-' || grade === 'D+') return 'grade-d';
      return 'grade-f';
    };
    
    const navigateToCalendarManager = () => {
      router.push('/calendars');
    };
    
    onMounted(() => {
      fetchCalendars();
      fetchEvents();
      fetchCurrentGrade();
      checkGradeEligibility();
    });
    
    return {
      events,
      calendars,
      currentGrade,
      isLoading,
      isGrading,
      canGrade,
      error,
      currentView,
      currentDate,
      hours,
      dayNames,
      hasCalendars,
      weekDays,
      monthDays,
      formattedDateRange,
      gradeClass,
      previousWeek,
      nextWeek,
      setView,
      getEventsForDay,
      formatHour,
      formatEventTime,
      getEventStyle,
      isToday,
      gradeCalendar,
      getRuleGradeClass,
      navigateToCalendarManager
    };
  }
};
&lt;/script&gt;

&lt;style lang="scss" scoped&gt;
.unified-calendar-view {
  display: flex;
  gap: 1.5rem;
  height: calc(100vh - 180px);
  min-height: 600px;
  
  .calendar-container {
    flex: 1;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    
    .calendar-header {
      padding: 1rem;
      border-bottom: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center;
      
      .date-navigation {
        display: flex;
        align-items: center;
        gap: 1rem;
        
        h2 {
          margin: 0;
          font-size: 1.2rem;
          font-weight: 500;
        }
        
        .nav-button {
          background: none;
          border: 1px solid #ddd;
          border-radius: 50%;
          width: 32px;
          height: 32px;
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
          
          &:hover {
            background-color: #f5f5f5;
          }
        }
      }
      
      .view-controls {
        display: flex;
        gap: 0.5rem;
        
        .view-button {
          padding: 0.5rem 1rem;
          border: 1px solid #ddd;
          border-radius: 4px;
          background: none;
          cursor: pointer;
          
          &:hover {
            background-color: #f5f5f5;
          }
          
          &.active {
            background-color: #4a90e2;
            color: white;
            border-color: #4a90e2;
          }
        }
      }
    }
    
    .loading-indicator, .no-calendars {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      
      .empty-state {
        text-align: center;
        max-width: 400px;
        
        .empty-icon {
          font-size: 3rem;
          color: #ccc;
          margin-bottom: 1rem;
        }
        
        h3 {
          margin-top: 0;
          margin-bottom: 0.5rem;
          font-size: 1.2rem;
        }
        
        p {
          margin-bottom: 1.5rem;
          color: #666;
        }
        
        .btn-connect {
          padding: 0.75rem 1.5rem;
          border-radius: 4px;
          font-size: 1rem;
          font-weight: 500;
          cursor: pointer;
          border: none;
          background-color: #4a90e2;
          color: white;
          
          &:hover {
            background-color: #3a80d2;
          }
        }
      }
    }
    
    .calendar-grid {
      flex: 1;
      overflow: auto;
      
      .week-view {
        display: flex;
        min-height: 100%;
        
        .time-column {
          width: 60px;
          border-right: 1px solid #eee;
          
          .day-header {
            height: 60px;
            border-bottom: 1px solid #eee;
          }
          
          .time-slot {
            height: 60px;
            padding: 0.25rem;
            text-align: right;
            color: #666;
            font-size: 0.8rem;
            border-bottom: 1px solid #f5f5f5;
          }
        }
        
        .day-column {
          flex: 1;
          min-width: 120px;
          border-right: 1px solid #eee;
          position: relative;
          
          &:last-child {
            border-right: none;
          }
          
          .day-header {
            height: 60px;
            padding: 0.5rem;
            text-align: center;
            border-bottom: 1px solid #eee;
            
            &.today {
              background-color: #e8f0fe;
              
              .day-number {
                background-color: #4a90e2;
                color: white;
              }
            }
            
            .day-name {
              font-size: 0.9rem;
              font-weight: 500;
            }
            
            .day-number {
              display: inline-block;
              width: 24px;
              height: 24px;
              line-height: 24px;
              border-radius: 50%;
              margin-top: 0.25rem;
            }
          }
          
          .day-events {
            position: relative;
            
            .time-slot {
              height: 60px;
              border-bottom: 1px solid #f5f5f5;
            }
            
            .calendar-event {
              position: absolute;
              left: 0;
              right: 0;
              padding: 0 0.25rem;
              z-index: 5;
              
              &.all-day-event {
                top: 0;
                height: 24px;
              }
              
              .event-content {
                height: 100%;
                padding: 0.25rem 0.5rem;
                border-radius: 4px;
                color: white;
                font-size: 0.8rem;
                overflow: hidden;
                
                .event-time {
                  font-weight: 500;
                  margin-bottom: 0.25rem;
                }
                
                .event-title {
                  font-weight: 500;
                  white-space: nowrap;
                  overflow: hidden;
                  text-overflow: ellipsis;
                }
                
                .event-calendar {
                  font-size: 0.7rem;
                  opacity: 0.8;
                }
              }
            }
          }
        }
      }
      
      .month-view {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        
        .day-header {
          padding: 0.75rem;
          text-align: center;
          font-weight: 500;
          border-bottom: 1px solid #eee;
        }
        
        .month-day {
          min-height: 100px;
          border-right: 1px solid #eee;
          border-bottom: 1px solid #eee;
          padding: 0.5rem;
          
          &:nth-child(7n) {
            border-right: none;
          }
          
          &.other-month {
            background-color: #f9f9f9;
            color: #aaa;
          }
          
          &.today {
            background-color: #e8f0fe;
            
            .day-number {
              background-color: #4a90e2;
              color: white;
            }
          }
          
          &.has-events {
            .day-number {
              font-weight: 500;
            }
          }
          
          .day-number {
            display: inline-block;
            width: 24px;
            height: 24px;
            line-height: 24px;
            text-align: center;
            border-radius: 50%;
            margin-bottom: 0.5rem;
          }
          
          .day-events-mini {
            .month-event {
              margin-bottom: 0.25rem;
              padding: 0.15rem 0.35rem;
              border-radius: 2px;
              color: white;
              font-size: 0.7rem;
              white-space: nowrap;
              overflow: hidden;
              text-overflow: ellipsis;
            }
            
            .more-events {
              font-size: 0.7rem;
              color: #666;
              text-align: center;
            }
          }
        }
      }
    }
  }
  
  .grades-sidebar {
    width: 320px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    
    .sidebar-header {
      padding: 1rem;
      border-bottom: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center;
      
      h3 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 500;
      }
      
      .btn-grade {
        padding: 0.5rem 1rem;
        border-radius: 4px;
        background-color: #4a90e2;
        color: white;
        border: none;
        font-weight: 500;
        cursor: pointer;
        
        &:hover:not(:disabled) {
          background-color: #3a80d2;
        }
        
        &:disabled {
          background-color: #ccc;
          cursor: not-allowed;
        }
      }
    }
    
    .grading-progress, .no-grade {
      padding: 2rem 1rem;
      text-align: center;
      
      .progress-bar {
        height: 8px;
        background-color: #eee;
        border-radius: 4px;
        margin-top: 1rem;
        overflow: hidden;
        
        .progress-fill {
          height: 100%;
          width: 50%;
          background-color: #4a90e2;
          animation: progress 2s infinite;
        }
      }
      
      .upgrade-notice {
        margin-top: 1rem;
        padding: 1rem;
        background-color: #f5f7fa;
        border-radius: 4px;
        font-size: 0.9rem;
        
        .upgrade-link {
          color: #4a90e2;
          font-weight: 500;
          text-decoration: none;
          
          &:hover {
            text-decoration: underline;
          }
        }
      }
    }
    
    .grade-results {
      flex: 1;
      padding: 1rem;
      overflow-y: auto;
      
      .overall-grade {
        text-align: center;
        margin-bottom: 1.5rem;
        
        .grade-circle {
          display: inline-flex;
          align-items: center;
          justify-content: center;
          width: 80px;
          height: 80px;
          border-radius: 50%;
          font-size: 2rem;
          font-weight: 700;
          color: white;
          margin-bottom: 0.5rem;
          
          &.grade-a {
            background-color: #4caf50;
          }
          
          &.grade-b {
            background-color: #8bc34a;
          }
          
          &.grade-c {
            background-color: #ffc107;
          }
          
          &.grade-d {
            background-color: #ff9800;
          }
          
          &.grade-f {
            background-color: #f44336;
          }
        }
        
        .grade-label {
          font-size: 0.9rem;
          color: #666;
        }
      }
      
      .grade-details {
        margin-bottom: 1.5rem;
        
        h4 {
          margin-top: 1.5rem;
          margin-bottom: 0.75rem;
          font-size: 1rem;
          color: #333;
        }
        
        ul {
          margin: 0;
          padding-left: 1.5rem;
          
          li {
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
          }
        }
        
        .recommendations {
          p {
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
            line-height: 1.5;
          }
        }
      }
      
      .rule-grades {
        h4 {
          margin-top: 1.5rem;
          margin-bottom: 0.75rem;
          font-size: 1rem;
          color: #333;
        }
        
        .rule-grade-item {
          display: flex;
          align-items: center;
          margin-bottom: 0.5rem;
          
          .rule-letter {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f5f5f5;
            border-radius: 4px;
            margin-right: 0.75rem;
            font-weight: 500;
          }
          
          .rule-grade {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            color: white;
            font-weight: 500;
            
            &.grade-a {
              background-color: #4caf50;
            }
            
            &.grade-b {
              background-color: #8bc34a;
            }
            
            &.grade-c {
              background-color: #ffc107;
            }
            
            &.grade-d {
              background-color: #ff9800;
            }
            
            &.grade-f {
              background-color: #f44336;
            }
          }
        }
      }
    }
  }
}

@keyframes progress {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(100%);
  }
}
&lt;/style&gt;
