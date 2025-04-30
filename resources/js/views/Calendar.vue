<template>
  <div>
    <h1 class="page-title">Calendar</h1>

    <div class="calendar-container">
      <div class="calendar-header">
        <div class="calendar-navigation">
          <button class="btn btn-outline-primary prev-month-btn" @click="prevMonth"><i class="fas fa-chevron-left"></i></button>
          <h2 class="month-title">{{ currentMonthYear }}</h2>
          <button class="btn btn-outline-primary next-month-btn" @click="nextMonth"><i class="fas fa-chevron-right"></i></button>
        </div>
        <div class="calendar-view-options">
          <button class="btn btn-outline-primary" :class="{ active: currentView === 'month' }" @click="setView('month')">Month</button>
          <button class="btn btn-outline-primary" :class="{ active: currentView === 'week' }" @click="setView('week')">Week</button>
          <button class="btn btn-outline-primary" :class="{ active: currentView === 'day' }" @click="setView('day')">Day</button>
        </div>
      </div>
      
      <!-- Month View -->
      <div class="calendar-view month-view" v-if="currentView === 'month'">
        <div class="calendar-grid">
          <div class="calendar-day-header">Sun</div>
          <div class="calendar-day-header">Mon</div>
          <div class="calendar-day-header">Tue</div>
          <div class="calendar-day-header">Wed</div>
          <div class="calendar-day-header">Thu</div>
          <div class="calendar-day-header">Fri</div>
          <div class="calendar-day-header">Sat</div>
          <!-- Calendar days will be dynamically generated -->
          <div v-for="day in monthDays" :key="day.dateStr" 
               :class="[
                 'calendar-day', 
                 { 'prev-month': day.isPrevMonth, 'next-month': day.isNextMonth, 'today': day.isToday, 'has-events': day.hasEvents }
               ]">
            {{ day.dayOfMonth }}
            <!-- Event indicators can be added here -->
          </div>
        </div>
      </div>
      
      <!-- Week View -->
      <div class="calendar-view week-view" v-if="currentView === 'week'">
        <div class="week-header">
          <div class="time-column"></div>
          <div v-for="day in weekDays" :key="day.dateStr" class="week-day" :class="{ today: day.isToday }">
            {{ day.shortDayName }}<br>{{ day.dayOfMonth }}
          </div>
        </div>
        <div class="week-grid">
          <div class="time-slots">
            <div v-for="hour in timeSlots" :key="hour" class="time-slot">{{ hour }}</div>
          </div>
          <div v-for="day in weekDays" :key="day.dateStr" class="day-column">
            <!-- Events will be dynamically placed here -->
            <div v-for="event in getEventsForDay(day.dateStr)" :key="event.id" 
                 class="week-event" 
                 :style="getEventStyle(event)">
              {{ event.title }}<br>{{ event.timeRange }}
            </div>
          </div>
        </div>
      </div>
      
      <!-- Day View -->
      <div class="calendar-view day-view" v-if="currentView === 'day'">
        <div class="day-header">
          <h3>{{ currentDayFormatted }}</h3>
        </div>
        <div class="day-schedule">
          <div class="time-slots">
             <div v-for="hour in timeSlots" :key="hour" class="time-slot">{{ hour }}</div>
          </div>
          <div class="day-events">
             <!-- Events will be dynamically placed here -->
            <div v-for="event in getEventsForDay(currentDay.dateStr)" :key="event.id" 
                 class="day-event" 
                 :style="getEventStyle(event)">
              <div class="event-time">{{ event.timeRange }}</div>
              <div class="event-title">{{ event.title }}</div>
              <div class="event-location">{{ event.location }}</div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Calendar Grading Button (Week View Only) -->
      <div class="calendar-grading-action" v-if="currentView === 'week'">
        <button id="grade-calendar-btn" class="btn btn-primary btn-lg" @click="openGradeModal">
          <i class="fas fa-check-circle"></i> Grade This Week
        </button>
      </div>
      
      <!-- Calendar Grade Result Modal -->
      <div class="modal fade" id="gradeResultModal" tabindex="-1" aria-labelledby="gradeResultModalLabel" aria-hidden="true" ref="gradeModal">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="gradeResultModalLabel">Your Calendar Grade</h5>
              <button type="button" class="btn-close" @click="closeGradeModal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <!-- Grade result content - needs dynamic data -->
              <div class="grade-result">
                <div class="grade-header">
                  <div class="grade-letter">B+</div>
                  <div class="grade-score">87<span>/100</span></div>
                </div>
                <div class="grade-summary">
                  <p>Your calendar is well-organized but has some room for improvement.</p>
                </div>
                <div class="grade-recommendations">
                  <h4>Recommendations</h4>
                  <ul class="recommendations-list">
                    <li>
                      <div class="recommendation-title">Add buffer time between meetings</div>
                      <div class="recommendation-description">Your back-to-back meetings on Tuesday could lead to burnout. Try adding 15-minute buffers.</div>
                    </li>
                    <!-- Add other recommendations -->
                  </ul>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeGradeModal">Close</button>
              <button type="button" class="btn btn-primary">Save Recommendations</button>
            </div>
          </div>
        </div>
      </div>
      
      <div class="calendar-legend">
        <!-- Legend items - can be made dynamic -->
        <div class="legend-item">
          <div class="legend-color" style="background-color: var(--primary-purple);"></div>
          <span>Meetings</span>
        </div>
        <div class="legend-item">
          <div class="legend-color" style="background-color: var(--primary-teal);"></div>
          <span>Deadlines</span>
        </div>
        <div class="legend-item">
          <div class="legend-color" style="background-color: var(--accent-yellow);"></div>
          <span>Presentations</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
// Assuming Bootstrap's JS is loaded globally or imported
// import { Modal } from 'bootstrap'; // If using Bootstrap's JS module

const currentView = ref('week'); // 'month', 'week', 'day'
const currentDate = ref(new Date());
const gradeModal = ref(null);
let bsModal = null;

// --- Computed Properties for Display ---
const currentMonthYear = computed(() => {
  return currentDate.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
});

const currentDayFormatted = computed(() => {
  return currentDate.value.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
});

// --- Placeholder Data & Logic (Needs actual implementation) ---
const monthDays = computed(() => { 
  // Logic to generate days for the month view based on currentDate
  // Mark today, prev/next month days, days with events
  console.log("Generating month days for", currentDate.value);
  return []; // Placeholder
}); 

const weekDays = computed(() => { 
  // Logic to generate days for the week view based on currentDate
  // Mark today
  console.log("Generating week days for", currentDate.value);
  const startOfWeek = new Date(currentDate.value);
  startOfWeek.setDate(startOfWeek.getDate() - startOfWeek.getDay()); // Assuming Sunday start
  const days = [];
  for (let i = 0; i < 7; i++) {
    const day = new Date(startOfWeek);
    day.setDate(day.getDate() + i);
    days.push({
        dateStr: day.toISOString().split('T')[0],
        shortDayName: day.toLocaleDateString('en-US', { weekday: 'short' }),
        dayOfMonth: day.getDate(),
        isToday: day.toDateString() === new Date().toDateString()
    });
  }
  return days;
});

const timeSlots = computed(() => {
  // Generate time slots (e.g., 8 AM to 5 PM)
  const slots = [];
  for (let i = 8; i <= 17; i++) {
      const hour = i % 12 === 0 ? 12 : i % 12;
      const ampm = i < 12 ? 'AM' : 'PM';
      slots.push(`${hour} ${ampm}`);
  }
  return slots;
});

const events = ref([ // Placeholder event data
  { id: 1, dateStr: '2025-04-30', title: 'Team Meeting', timeRange: '10:00 - 11:00 AM', location: 'Conference Room A', startHour: 10, durationMinutes: 60, color: 'var(--primary-purple)' },
  { id: 2, dateStr: '2025-05-01', title: 'Client Presentation', timeRange: '2:00 - 3:30 PM', location: 'Online', startHour: 14, durationMinutes: 90, color: 'var(--accent-yellow)' },
]);

function getEventsForDay(dateStr) {
  return events.value.filter(event => event.dateStr === dateStr);
}

function getEventStyle(event) {
  // Calculate top and height based on startHour and durationMinutes
  // Assuming 60px per hour slot starting at 8 AM
  const topOffset = (event.startHour - 8) * 60; 
  const height = (event.durationMinutes / 60) * 60;
  return {
    top: `${topOffset}px`,
    height: `${height}px`,
    backgroundColor: event.color
  };
}

// --- Methods ---
function setView(view) {
  currentView.value = view;
}

function prevMonth() {
  currentDate.value = new Date(currentDate.value.setMonth(currentDate.value.getMonth() - 1));
}

function nextMonth() {
  currentDate.value = new Date(currentDate.value.setMonth(currentDate.value.getMonth() + 1));
}

function openGradeModal() {
  // Logic to fetch grade data might go here
  if (bsModal) {
    bsModal.show();
  }
}

function closeGradeModal() {
  if (bsModal) {
    bsModal.hide();
  }
}

// --- Lifecycle Hooks ---
onMounted(() => {
  // Initialize Bootstrap modal if loaded globally
  if (window.bootstrap && gradeModal.value) {
      bsModal = new window.bootstrap.Modal(gradeModal.value);
  }
  // Fetch initial calendar data if needed
});

</script>

<style scoped>
/* Import styles from calendar.blade.php @section('styles') */
/* Calendar Navigation Styling */
.calendar-navigation {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20px;
}

.month-title {
  margin: 0 15px;
  font-style: italic;
}

.prev-month-btn, .next-month-btn {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-color: var(--primary-purple);
  color: var(--primary-purple);
  cursor: pointer;
  transition: all 0.2s ease;
}

.prev-month-btn:hover, .next-month-btn:hover {
  background-color: var(--primary-purple);
  color: white;
}

/* Calendar View Options */
.calendar-view-options {
  display: flex;
  gap: 10px;
  justify-content: center;
  margin-bottom: 20px;
}

/* Add active class styling if not covered by Bootstrap */
.calendar-view-options .btn.active {
    background-color: var(--primary-purple);
    color: white;
    border-color: var(--primary-purple);
}

/* Month View Styling */
.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 10px;
}

.calendar-day-header {
  text-align: center;
  font-weight: bold;
  padding: 10px;
}

.calendar-day {
  min-height: 100px; /* Use min-height */
  border-radius: 10px;
  padding: 10px;
  background-color: rgba(255, 255, 255, 0.1);
  position: relative;
}

.calendar-day.prev-month,
.calendar-day.next-month {
  opacity: 0.5;
}

.calendar-day.today {
  background-color: rgba(126, 87, 255, 0.2);
  border: 2px solid var(--primary-purple);
}

.calendar-day.has-events {
  background-color: rgba(255, 255, 255, 0.2);
}

.event-indicator {
  font-size: 12px;
  padding: 3px 6px;
  border-radius: 4px;
  margin-top: 5px;
  color: white;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Week View Styling */
.week-header {
  display: grid;
  grid-template-columns: 60px repeat(7, 1fr);
  gap: 5px;
  margin-bottom: 10px;
}

.week-day {
  text-align: center;
  padding: 10px;
  background-color: rgba(255, 255, 255, 0.1);
  border-radius: 5px;
  font-weight: bold;
}

.week-day.today {
  background-color: rgba(126, 87, 255, 0.2);
  border: 2px solid var(--primary-purple);
}

.week-grid {
  display: grid;
  grid-template-columns: 60px repeat(7, 1fr);
  height: 600px;
  position: relative;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  overflow: hidden;
}

.time-slots {
  display: flex;
  flex-direction: column;
  border-right: 1px solid rgba(255, 255, 255, 0.1);
}

.time-slot {
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  color: rgba(255, 255, 255, 0.7);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.day-column {
  position: relative;
  border-right: 1px solid rgba(255, 255, 255, 0.1);
  height: 600px;
}

.day-column:last-child {
  border-right: none;
}

.week-event {
  position: absolute;
  left: 5px;
  right: 5px;
  padding: 5px;
  border-radius: 5px;
  font-size: 12px;
  color: white;
  overflow: hidden;
  z-index: 1;
  cursor: pointer; /* Add cursor for potential interaction */
}

/* Day View Styling */
.day-header {
  text-align: center;
  margin-bottom: 20px;
}

.day-schedule {
  display: grid;
  grid-template-columns: 60px 1fr;
  height: 600px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  overflow: hidden;
}

.day-events {
  position: relative;
  height: 600px;
}

.day-event {
  position: absolute;
  left: 10px;
  right: 10px;
  padding: 10px;
  border-radius: 5px;
  color: white;
  z-index: 1;
  cursor: pointer; /* Add cursor for potential interaction */
}

.event-time {
  font-weight: bold;
  margin-bottom: 5px;
}

.event-location {
  font-size: 12px;
  opacity: 0.8;
}

/* Calendar Grading Action */
.calendar-grading-action {
  margin-top: 30px;
  text-align: center;
}

#grade-calendar-btn {
  padding: 12px 30px;
  font-size: 18px;
  background: linear-gradient(to right, var(--primary-purple), var(--primary-teal));
  border: none;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
}

#grade-calendar-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
}

/* Grade Result Modal Styling */
.grade-header {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 40px;
  margin-bottom: 20px;
}

.grade-letter {
  font-size: 72px;
  font-weight: bold;
  color: var(--primary-purple);
  background: linear-gradient(to right, var(--primary-purple), var(--primary-teal));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.grade-score {
  font-size: 48px;
  font-weight: bold;
}

.grade-score span {
  font-size: 24px;
  opacity: 0.7;
}

.grade-summary {
  text-align: center;
  font-size: 18px;
  margin-bottom: 30px;
}

.recommendations-list {
  list-style: none;
  padding: 0;
}

.recommendations-list li {
  background-color: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
  padding: 15px;
  margin-bottom: 15px;
}

.recommendation-title {
  font-weight: bold;
  font-size: 18px;
  margin-bottom: 5px;
  color: var(--primary-teal);
}

/* Calendar Legend */
.calendar-legend {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-top: 20px;
}

.legend-item {
  display: flex;
  align-items: center;
}

.legend-color {
  width: 15px;
  height: 15px;
  border-radius: 3px;
  margin-right: 5px;
}

/* Ensure modal styles work correctly */
.modal {
    color: #333; /* Set default text color for modal content */
}

.modal-content {
    background-color: #fff; /* Or your desired modal background */
}

.modal-header,
.modal-body,
.modal-footer {
    color: inherit;
}

</style>
