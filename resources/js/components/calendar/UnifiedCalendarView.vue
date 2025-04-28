&lt;template&gt;
  &lt;div class="unified-calendar-view"&gt;
    &lt;div class="calendar-header"&gt;
      &lt;div class="date-navigation"&gt;
        &lt;button @click="previousWeek" class="btn-nav"&gt;
          &lt;i class="fas fa-chevron-left"&gt;&lt;/i&gt;
        &lt;/button&gt;
        &lt;h2&gt;{{ formattedDateRange }}&lt;/h2&gt;
        &lt;button @click="nextWeek" class="btn-nav"&gt;
          &lt;i class="fas fa-chevron-right"&gt;&lt;/i&gt;
        &lt;/button&gt;
      &lt;/div&gt;
      &lt;div class="view-controls"&gt;
        &lt;button 
          @click="setView('week')" 
          class="btn-view"
          :class="{ active: currentView === 'week' }"
        &gt;
          Week
        &lt;/button&gt;
        &lt;button 
          @click="setView('month')" 
          class="btn-view"
          :class="{ active: currentView === 'month' }"
        &gt;
          Month
        &lt;/button&gt;
      &lt;/div&gt;
    &lt;/div&gt;
    
    &lt;div v-if="isLoading" class="loading-indicator"&gt;
      &lt;p&gt;Loading your calendar...&lt;/p&gt;
    &lt;/div&gt;
    
    &lt;div v-else-if="!hasCalendars" class="no-calendars"&gt;
      &lt;div class="empty-state"&gt;
        &lt;i class="fas fa-calendar-alt empty-icon"&gt;&lt;/i&gt;
        &lt;h3&gt;No Calendars Connected&lt;/h3&gt;
        &lt;p&gt;Connect your Google Calendars to view and grade your schedule.&lt;/p&gt;
        &lt;router-link to="/settings" class="btn-connect"&gt;
          Connect Calendar
        &lt;/router-link&gt;
      &lt;/div&gt;
    &lt;/div&gt;
    
    &lt;div v-else class="calendar-container"&gt;
      &lt;div class="calendar-content"&gt;
        &lt;div v-if="currentView === 'week'" class="week-view"&gt;
          &lt;div class="day-headers"&gt;
            &lt;div class="time-column-header"&gt;&lt;/div&gt;
            &lt;div 
              v-for="day in weekDays" 
              :key="day.date" 
              class="day-header"
              :class="{ 'today': isToday(day.date) }"
            &gt;
              &lt;div class="day-name"&gt;{{ day.name }}&lt;/div&gt;
              &lt;div class="day-number"&gt;{{ day.number }}&lt;/div&gt;
            &lt;/div&gt;
          &lt;/div&gt;
          
          &lt;div class="week-grid"&gt;
            &lt;div class="time-column"&gt;
              &lt;div 
                v-for="hour in hours" 
                :key="hour" 
                class="hour-marker"
              &gt;
                {{ formatHour(hour) }}
              &lt;/div&gt;
            &lt;/div&gt;
            
            &lt;div 
              v-for="day in weekDays" 
              :key="day.date" 
              class="day-column"
              :class="{ 'today': isToday(day.date) }"
            &gt;
              &lt;div 
                v-for="event in getEventsForDay(day.date)" 
                :key="`${day.date}-${event.id}`"
                class="event-item"
                :style="[
                  getEventStyle(event, day.date),
                  { backgroundColor: event.calendar_color || '#4285F4' }
                ]"
              &gt;
                &lt;div class="event-time" v-if="!event.all_day"&gt;
                  {{ formatEventTime(event) }}
                &lt;/div&gt;
                &lt;div class="event-title"&gt;{{ event.title }}&lt;/div&gt;
                &lt;div class="event-calendar"&gt;{{ event.calendar_name }}&lt;/div&gt;
              &lt;/div&gt;
            &lt;/div&gt;
          &lt;/div&gt;
        &lt;/div&gt;
        
        &lt;div v-else class="month-view"&gt;
          &lt;div class="month-header"&gt;
            &lt;div 
              v-for="name in dayNames" 
              :key="name" 
              class="day-name"
            &gt;
              {{ name }}
            &lt;/div&gt;
          &lt;/div&gt;
          
          &lt;div class="month-grid"&gt;
            &lt;div 
              v-for="day in monthDays" 
              :key="day.date" 
              class="month-day"
              :class="{ 
                'other-month': !day.currentMonth,
                'today': isToday(day.date)
              }"
            &gt;
              &lt;div class="day-number"&gt;{{ day.number }}&lt;/div&gt;
              
              &lt;div class="day-events"&gt;
                &lt;div 
                  v-for="event in getEventsForDay(day.date)" 
                  :key="`${day.date}-${event.id}`"
                  class="month-event"
                  :style="{ backgroundColor: event.calendar_color || '#4285F4' }"
                &gt;
                  &lt;div class="event-title"&gt;{{ event.title }}&lt;/div&gt;
                &lt;/div&gt;
              &lt;/div&gt;
            &lt;/div&gt;
          &lt;/div&gt;
        &lt;/div&gt;
      &lt;/div&gt;
      
      &lt;div class="grades-sidebar"&gt;
        &lt;div class="sidebar-header"&gt;
          &lt;h3&gt;Calendar Grade&lt;/h3&gt;
          &lt;button 
            @click="gradeCalendar" 
            class="btn-grade"
            :disabled="isGrading || !canGrade"
          &gt;
            Grade My Calendar
          &lt;/button&gt;
        &lt;/div&gt;
        
        &lt;div v-if="isGrading" class="grading-progress"&gt;
          &lt;p&gt;Analyzing your calendar...&lt;/p&gt;
          &lt;div class="progress-bar"&gt;
            &lt;div class="progress-fill"&gt;&lt;/div&gt;
          &lt;/div&gt;
        &lt;/div&gt;
        
        &lt;div v-else-if="!currentGrade" class="no-grade"&gt;
          &lt;p&gt;Grade your calendar to see how well your schedule aligns with your goals.&lt;/p&gt;
          
          &lt;div v-if="!canGrade" class="upgrade-notice"&gt;
            &lt;p&gt;You've reached your free grading limit. &lt;router-link to="/subscription" class="upgrade-link"&gt;Upgrade&lt;/router-link&gt; to grade more frequently.&lt;/p&gt;
          &lt;/div&gt;
        &lt;/div&gt;
        
        &lt;div v-else class="grade-results"&gt;
          &lt;div class="overall-grade"&gt;
            &lt;div 
              class="grade-circle"
              :class="`grade-${currentGrade.letter.toLowerCase()}`"
            &gt;
              {{ currentGrade.letter }}
            &lt;/div&gt;
            &lt;div class="grade-label"&gt;{{ currentGrade.score }}/100&lt;/div&gt;
          &lt;/div&gt;
          
          &lt;div class="grade-details"&gt;
            &lt;h4&gt;Summary&lt;/h4&gt;
            &lt;p&gt;{{ currentGrade.summary }}&lt;/p&gt;
            
            &lt;h4&gt;Strengths&lt;/h4&gt;
            &lt;ul&gt;
              &lt;li v-for="(strength, index) in currentGrade.strengths" :key="`strength-${index}`"&gt;
                {{ strength }}
              &lt;/li&gt;
            &lt;/ul&gt;
            
            &lt;h4&gt;Areas for Improvement&lt;/h4&gt;
            &lt;ul&gt;
              &lt;li v-for="(improvement, index) in currentGrade.improvements" :key="`improvement-${index}`"&gt;
                {{ improvement }}
              &lt;/li&gt;
            &lt;/ul&gt;
            
            &lt;div class="recommendations"&gt;
              &lt;h4&gt;Recommendations&lt;/h4&gt;
              &lt;p v-for="(recommendation, index) in currentGrade.recommendations" :key="`recommendation-${index}`"&gt;
                {{ recommendation }}
              &lt;/p&gt;
            &lt;/div&gt;
          &lt;/div&gt;
          
          &lt;div class="rule-grades"&gt;
            &lt;h4&gt;Grading Criteria&lt;/h4&gt;
            &lt;div 
              v-for="(rule, index) in currentGrade.rules" 
              :key="`rule-${index}`"
              class="rule-grade-item"
            &gt;
              &lt;div class="rule-letter"&gt;{{ String.fromCharCode(65 + index) }}&lt;/div&gt;
              &lt;div class="rule-name"&gt;{{ rule.name }}&lt;/div&gt;
              &lt;div 
                class="rule-grade"
                :class="`grade-${rule.grade.toLowerCase()}`"
              &gt;
                {{ rule.grade }}
              &lt;/div&gt;
            &lt;/div&gt;
          &lt;/div&gt;
        &lt;/div&gt;
      &lt;/div&gt;
    &lt;/div&gt;
  &lt;/div&gt;
&lt;/template&gt;

&lt;script&gt;
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import dayjs from 'dayjs';

export default {
  name: 'UnifiedCalendarView',
  
  setup() {
    const events = ref([]);
    const calendars = ref([]);
    const currentGrade = ref(null);
    const isLoading = ref(true);
    const isGrading = ref(false);
    const canGrade = ref(true);
    const error = ref(null);
    
    const currentView = ref('week');
    const currentDate = ref(dayjs());
    
    const hours = Array.from({ length: 24 }, (_, i) => i);
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    
    const hasCalendars = computed(() => calendars.value.length > 0);
    
    const weekDays = computed(() => {
      const startOfWeek = currentDate.value.startOf('week');
      return Array.from({ length: 7 }, (_, i) => {
        const date = startOfWeek.add(i, 'day');
        return {
          date: date.format('YYYY-MM-DD'),
          name: dayNames[i],
          number: date.date()
        };
      });
    });
    
    const monthDays = computed(() => {
      const startOfMonth = currentDate.value.startOf('month');
      const startOfGrid = startOfMonth.startOf('week');
      const endOfMonth = currentDate.value.endOf('month');
      const endOfGrid = endOfMonth.endOf('week');
      
      const days = [];
      let day = startOfGrid;
      
      while (day.isBefore(endOfGrid) || day.isSame(endOfGrid, 'day')) {
        days.push({
          date: day.format('YYYY-MM-DD'),
          number: day.date(),
          currentMonth: day.month() === currentDate.value.month()
        });
        day = day.add(1, 'day');
      }
      
      return days;
    });
    
    const formattedDateRange = computed(() => {
      if (currentView.value === 'week') {
        const startOfWeek = currentDate.value.startOf('week');
        const endOfWeek = currentDate.value.endOf('week');
        
        if (startOfWeek.month() === endOfWeek.month()) {
          return `${startOfWeek.format('MMM D')} - ${endOfWeek.format('D, YYYY')}`;
        } else if (startOfWeek.year() === endOfWeek.year()) {
          return `${startOfWeek.format('MMM D')} - ${endOfWeek.format('MMM D, YYYY')}`;
        } else {
          return `${startOfWeek.format('MMM D, YYYY')} - ${endOfWeek.format('MMM D, YYYY')}`;
        }
      } else {
        return currentDate.value.format('MMMM YYYY');
      }
    });
    
    const gradeClass = computed(() => {
      if (!currentGrade.value) return '';
      return `grade-${currentGrade.value.letter.toLowerCase()}`;
    });
    
    // Fetch user's calendars
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
    
    // Fetch events for the current view
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
    
    // Fetch current grade
    const fetchCurrentGrade = async () => {
      try {
        const response = await axios.get('/api/grades/current-week');
        if (response.data.success) {
          currentGrade.value = response.data.grade;
        }
      } catch (err) {
        console.error('Error fetching current grade:', err);
      }
    };
    
    // Check if user can grade calendar
    const checkGradeEligibility = async () => {
      try {
        const response = await axios.get('/api/subscription/can-grade');
        canGrade.value = response.data.can_grade;
      } catch (err) {
        console.error('Error checking grade eligibility:', err);
        canGrade.value = false;
      }
    };
    
    // Grade calendar
    const gradeCalendar = async () => {
      if (isGrading.value || !canGrade.value) return;
      
      isGrading.value = true;
      try {
        const response = await axios.post('/api/ai/grade-calendar');
        
        if (response.data.success) {
          currentGrade.value = response.data.grade;
          
          // Increment grades used
          await axios.post('/api/subscription/increment-grades');
          
          // Check if user can still grade
          await checkGradeEligibility();
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
        gridRow: `${Math.floor(startHour) + 1} / span ${Math.ceil(duration)}`,
        zIndex: Math.floor(startHour)
      };
    };
    
    const isToday = (dateStr) => {
      return dayjs().format('YYYY-MM-DD') === dateStr;
    };
    
    // Watch for changes in calendars to trigger event fetch
    watch(calendars, () => {
      if (calendars.value.length > 0) {
        fetchEvents();
      }
    });
    
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
    };
  }
};
&lt;/script&gt;

&lt;style lang="scss" scoped&gt;
.unified-calendar-view {
  display: flex;
  gap: 1.5rem;
  
  .calendar-content {
    flex: 1;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    display: flex;
    flex-direction: column;
  }
  
  .calendar-header {
    padding: 1.5rem;
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
        font-size: 1.5rem;
        color: #333;
      }
      
      .btn-nav {
        background-color: transparent;
        border: none;
        color: #555;
        font-size: 1rem;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 4px;
        
        &:hover {
          background-color: #f5f5f5;
        }
      }
    }
    
    .view-controls {
      display: flex;
      gap: 0.5rem;
      
      .btn-view {
        background-color: transparent;
        border: 1px solid #ddd;
        color: #555;
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
        border-radius: 4px;
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
  
  .loading-indicator {
    padding: 2rem;
    text-align: center;
    color: #666;
  }
  
  .no-calendars {
    padding: 3rem 1.5rem;
    
    .empty-state {
      text-align: center;
      max-width: 400px;
      margin: 0 auto;
      
      .empty-icon {
        font-size: 3rem;
        color: #ccc;
        margin-bottom: 1rem;
      }
      
      h3 {
        margin-top: 0;
        margin-bottom: 0.5rem;
        font-size: 1.2rem;
        color: #333;
      }
      
      p {
        margin-bottom: 1.5rem;
        color: #666;
      }
      
      .btn-connect {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        background-color: #4a90e2;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        font-weight: 500;
        
        &:hover {
          background-color: #3a80d2;
        }
      }
    }
  }
  
  .week-view {
    display: flex;
    flex-direction: column;
    height: 100%;
    
    .day-headers {
      display: grid;
      grid-template-columns: 60px repeat(7, 1fr);
      border-bottom: 1px solid #eee;
      
      .day-header {
        padding: 0.75rem;
        text-align: center;
        
        &.today {
          background-color: #e8f0fe;
          
          .day-number {
            background-color: #4a90e2;
            color: white;
          }
        }
        
        .day-name {
          font-size: 0.85rem;
          color: #666;
          margin-bottom: 0.25rem;
        }
        
        .day-number {
          display: inline-block;
          width: 28px;
          height: 28px;
          line-height: 28px;
          border-radius: 50%;
          font-weight: 500;
        }
      }
    }
    
    .week-grid {
      display: grid;
      grid-template-columns: 60px repeat(7, 1fr);
      height: 100%;
      overflow-y: auto;
      
      .time-column {
        border-right: 1px solid #eee;
        
        .hour-marker {
          height: 60px;
          padding: 0.25rem 0.5rem;
          text-align: right;
          font-size: 0.75rem;
          color: #666;
          position: relative;
          
          &::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 8px;
            height: 1px;
            background-color: #eee;
          }
        }
      }
      
      .day-column {
        position: relative;
        border-right: 1px solid #eee;
        display: grid;
        grid-template-rows: repeat(24, 60px);
        
        &:last-child {
          border-right: none;
        }
        
        &.today {
          background-color: #f8f9fa;
        }
        
        &::after {
          content: '';
          position: absolute;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background-image: linear-gradient(#eee 1px, transparent 1px);
          background-size: 100% 60px;
          pointer-events: none;
        }
        
        .event-item {
          position: relative;
          margin: 0 2px;
          padding: 0.5rem;
          border-radius: 4px;
          color: white;
          overflow: hidden;
          z-index: 1;
          
          .event-time {
            font-size: 0.7rem;
            margin-bottom: 0.25rem;
            opacity: 0.9;
          }
          
          .event-title {
            font-weight: 500;
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
          }
          
          .event-calendar {
            font-size: 0.7rem;
            opacity: 0.9;
          }
        }
      }
    }
  }
  
  .month-view {
    display: flex;
    flex-direction: column;
    height: 100%;
    
    .month-header {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      border-bottom: 1px solid #eee;
      
      .day-name {
        padding: 0.75rem;
        text-align: center;
        font-size: 0.85rem;
        color: #666;
      }
    }
    
    .month-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      grid-template-rows: repeat(6, 1fr);
      flex: 1;
      
      .month-day {
        border-right: 1px solid #eee;
        border-bottom: 1px solid #eee;
        padding: 0.5rem;
        min-height: 100px;
        
        &:nth-child(7n) {
          border-right: none;
        }
        
        &:nth-last-child(-n+7) {
          border-bottom: none;
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
        
        .day-number {
          display: inline-block;
          width: 24px;
          height: 24px;
          line-height: 24px;
          text-align: center;
          border-radius: 50%;
          margin-bottom: 0.5rem;
        }
        
        .day-events {
          display: flex;
          flex-direction: column;
          gap: 0.25rem;
          
          .month-event {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            color: white;
            font-size: 0.8rem;
            
            .event-title {
              white-space: nowrap;
              overflow: hidden;
              text-overflow: ellipsis;
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
