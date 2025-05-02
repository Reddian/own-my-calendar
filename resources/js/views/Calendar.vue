<template>
  <div>
    <h1 class="page-title">Calendar</h1>

    <!-- Premium CTA -->
    <div v-if="!isPremium" class="alert alert-info premium-cta">
      <i class="fas fa-star"></i>
      <strong>Upgrade to Premium!</strong> Unlock unlimited calendar grading, extended history, and more features.
      <router-link to="/settings#subscription" class="btn btn-sm btn-primary ms-3">Upgrade Now</router-link>
    </div>

    <div v-if="isLoading" class="loading-indicator">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading events...</span>
      </div>
      <p>Loading calendar events...</p>
    </div>
    <div v-else-if="fetchError" class="alert alert-danger">
      {{ fetchError }}
    </div>

    <div v-else class="calendar-container">
      <div class="calendar-header">
        <div class="calendar-navigation">
          <button 
            class="btn btn-outline-primary prev-week-btn" 
            @click="prevWeek" 
            :disabled="!canNavigateBackward"
            title="Previous Week">
            <i class="fas fa-chevron-left"></i>
          </button>
          <h2 class="week-title">{{ currentWeekRange }}</h2>
          <button 
            class="btn btn-outline-primary next-week-btn" 
            @click="nextWeek" 
            :disabled="!canNavigateForward"
            title="Next Week">
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>

      <!-- Week View -->
      <div class="calendar-view week-view">
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
            <!-- Debug: Log day being processed -->
            <!-- {{ console.log('Processing day:', day.dateStr) }} -->
            <div v-for="event in getEventsForDay(day.dateStr)" :key="event.id" 
                 class="week-event" 
                 :style="getEventStyle(event)">
              <!-- Debug: Log event being rendered -->
              <!-- {{ console.log('Rendering event:', event.title, event.id) }} -->
              {{ event.title }}<br>{{ event.timeRange }}
            </div>
          </div>
        </div>
      </div>

      <!-- Calendar Grading Button -->
      <div class="calendar-grading-action">
        <button 
          id="grade-calendar-btn" 
          class="btn btn-primary btn-lg" 
          @click="gradeCurrentWeek" 
          :disabled="isGrading || !canGradeWeek">
          <span v-if="isGrading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
          <i v-else class="fas fa-check-circle"></i> 
          {{ isGrading ? 'Grading...' : 'Grade This Week' }}
        </button>
        <div v-if="gradeError" class="alert alert-danger mt-3">{{ gradeError }}</div>
      </div>

      <!-- Calendar Grade Result Modal -->
      <div class="modal fade" id="gradeResultModal" tabindex="-1" aria-labelledby="gradeResultModalLabel" aria-hidden="true" ref="gradeModal">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="gradeResultModalLabel">Your Calendar Grade for {{ currentWeekRange }}</h5>
              <button type="button" class="btn-close" @click="closeGradeModal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div v-if="gradeResult" class="grade-result">
                <div class="grade-header">
                  <div class="grade-letter">{{ gradeResult.overall_grade || 'N/A' }}</div>
                  <!-- Assuming score is part of the result, otherwise remove -->
                  <!-- <div class="grade-score">{{ gradeResult.score || '--' }}<span>/100</span></div> -->
                </div>
                <div class="grade-summary">
                  <p>{{ gradeResult.summary || 'Grading complete.' }}</p> 
                </div>
                <div v-if="gradeResult.strengths && gradeResult.strengths.length > 0" class="grade-section">
                  <h4>Strengths</h4>
                  <ul class="recommendations-list">
                    <li v-for="(strength, index) in gradeResult.strengths" :key="`strength-${index}`">
                      {{ strength }}
                    </li>
                  </ul>
                </div>
                <div v-if="gradeResult.improvement_areas && gradeResult.improvement_areas.length > 0" class="grade-section">
                  <h4>Areas for Improvement</h4>
                  <ul class="recommendations-list">
                    <li v-for="(area, index) in gradeResult.improvement_areas" :key="`improve-${index}`">
                       {{ area }}
                    </li>
                  </ul>
                </div>
                <div v-if="gradeResult.recommendations && gradeResult.recommendations.length > 0" class="grade-section">
                  <h4>Recommendations</h4>
                  <ul class="recommendations-list">
                    <li v-for="(rec, index) in gradeResult.recommendations" :key="`rec-${index}`">
                      <div class="recommendation-title">{{ rec.title || 'Recommendation' }}</div>
                      <div class="recommendation-description">{{ rec.description }}</div>
                    </li>
                  </ul>
                </div>
              </div>
              <div v-else>
                <p>Loading grade results...</p>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeGradeModal">Close</button>
              <!-- <button type="button" class="btn btn-primary">Save Recommendations</button> -->
            </div>
          </div>
        </div>
      </div>

      <div class="calendar-legend">
        <!-- Legend items -->
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
import { ref, computed, onMounted, watch } from 'vue';
import { useStore } from 'vuex';
import axios from 'axios';
import { Modal } from 'bootstrap';

// Store
const store = useStore();

// State
const today = new Date();
today.setHours(0, 0, 0, 0);
const currentDate = ref(new Date(today));
const events = ref([]);
const isLoading = ref(true);
const fetchError = ref(null);
const gradeModal = ref(null);
let bsModal = null;
const isGrading = ref(false);
const gradeError = ref(null);
const gradeResult = ref(null);
const canGradeWeek = ref(true); // Will be checked on mount

// --- Subscription Status ---
const isPremium = computed(() => store.getters['user/isSubscribed']);

// --- Date Formatting and Calculation Helpers ---
function getStartOfWeek(date) {
  const d = new Date(date);
  d.setHours(0, 0, 0, 0);
  const day = d.getDay();
  const diff = d.getDate() - day;
  return new Date(d.setDate(diff));
}

function getEndOfWeek(date) {
  const startOfWeek = getStartOfWeek(date);
  const endOfWeek = new Date(startOfWeek);
  endOfWeek.setDate(startOfWeek.getDate() + 6);
  return endOfWeek;
}

function formatDate(date) {
  return date.toISOString().split('T')[0];
}

function formatTime(dateTimeString) {
    if (!dateTimeString) return '';
    const date = new Date(dateTimeString);
    return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
}

// --- Computed Properties ---
const currentWeekStart = computed(() => getStartOfWeek(currentDate.value));
const currentWeekEnd = computed(() => getEndOfWeek(currentDate.value));

const currentWeekRange = computed(() => {
  const start = currentWeekStart.value.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
  const end = currentWeekEnd.value.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  return `${start} - ${end}`;
});

const weekDays = computed(() => {
  const start = currentWeekStart.value;
  const days = [];
  for (let i = 0; i < 7; i++) {
    const day = new Date(start);
    day.setDate(day.getDate() + i);
    days.push({
      dateStr: formatDate(day),
      shortDayName: day.toLocaleDateString('en-US', { weekday: 'short' }),
      dayOfMonth: day.getDate(),
      isToday: day.toDateString() === today.toDateString()
    });
  }
  return days;
});

const timeSlots = computed(() => {
  const slots = [];
  for (let i = 8; i <= 17; i++) {
    const hour = i % 12 === 0 ? 12 : i % 12;
    const ampm = i < 12 ? 'AM' : 'PM';
    slots.push(`${hour} ${ampm}`);
  }
  return slots;
});

// --- Navigation Limits ---
const todayWeekStart = computed(() => getStartOfWeek(today));

const minAllowedWeekStart = computed(() => {
    const date = new Date(todayWeekStart.value);
    if (isPremium.value) {
        date.setDate(date.getDate() - 7 * 2);
    } // Free: min is current week (todayWeekStart)
    return date;
});

const maxAllowedWeekStart = computed(() => {
    const date = new Date(todayWeekStart.value);
    if (isPremium.value) {
        date.setDate(date.getDate() + 7 * 8);
    } else {
        date.setDate(date.getDate() + 7 * 2);
    }
    return date;
});

const canNavigateBackward = computed(() => {
    const prevWeekStartDate = new Date(currentWeekStart.value);
    prevWeekStartDate.setDate(prevWeekStartDate.getDate() - 7);
    return prevWeekStartDate >= minAllowedWeekStart.value;
});

const canNavigateForward = computed(() => {
    const nextWeekStartDate = new Date(currentWeekStart.value);
    nextWeekStartDate.setDate(nextWeekStartDate.getDate() + 7);
    return nextWeekStartDate <= maxAllowedWeekStart.value;
});

// --- Event Fetching ---
async function fetchEventsForWeek(startDate, endDate) {
  isLoading.value = true;
  fetchError.value = null;
  console.log(`[Calendar] Fetching events from ${formatDate(startDate)} to ${formatDate(endDate)}`);
  try {
    const response = await axios.post('/api/calendars/events', {
      start_date: formatDate(startDate),
      end_date: formatDate(endDate),
    });
    // Debug: Log raw response
    console.log('[Calendar] Raw events received:', response.data.events);

    events.value = response.data.events.map(event => {
        const start = event.start ? new Date(event.start) : null;
        const end = event.end ? new Date(event.end) : null;
        let timeRange = 'All Day';
        let startHour = 0;
        let durationMinutes = 24 * 60;
        let dateStr = null;

        if (start) {
            dateStr = start.toISOString().split('T')[0]; // Use ISO string for reliable date part
        }

        if (start && end && !event.allDay) {
            timeRange = `${formatTime(event.start)} - ${formatTime(event.end)}`;
            startHour = start.getHours();
            durationMinutes = (end.getTime() - start.getTime()) / (1000 * 60);
        } else if (start && !event.allDay) {
            timeRange = formatTime(event.start);
            startHour = start.getHours();
            durationMinutes = 60; // Default duration if end is missing?
        }

        const processedEvent = {
            ...event,
            dateStr: dateStr,
            timeRange,
            startHour,
            durationMinutes,
            color: 'var(--primary-purple)' // Default color
        };
        // Debug: Log processed event
        // console.log('[Calendar] Processed event:', processedEvent);
        return processedEvent;
    });
    console.log("[Calendar] Processed events array:", events.value);
  } catch (error) {
    console.error('[Calendar] Error fetching calendar events:', error);
    fetchError.value = 'Failed to load calendar events. Please ensure your Google Calendar is connected and try again.';
    events.value = [];
  } finally {
    isLoading.value = false;
  }
}

// --- Event Display Logic ---
function getEventsForDay(dateStr) {
  // Debug: Log filtering process
  console.log(`[Calendar] Filtering events for day: ${dateStr}`);
  const dayEvents = events.value.filter(event => event.dateStr === dateStr);
  console.log(`[Calendar] Found ${dayEvents.length} events for ${dateStr}:`, dayEvents);
  return dayEvents;
}

function getEventStyle(event) {
  // Debug: Log style calculation
  console.log(`[Calendar] Calculating style for event: ${event.title} (ID: ${event.id})`);
  if (event.allDay) {
      const style = {
          top: '0px',
          height: '20px',
          backgroundColor: event.color || 'var(--secondary-color)',
          opacity: 0.8,
          fontSize: '10px',
          lineHeight: '20px',
          zIndex: 0
      };
      console.log('[Calendar] All-day event style:', style);
      return style;
  }
  const hourHeight = 60;
  const startOffsetHours = event.startHour - 8; // Assuming calendar starts at 8 AM
  const top = Math.max(0, startOffsetHours * hourHeight + (new Date(event.start).getMinutes() / 60) * hourHeight);
  const height = Math.max(15, (event.durationMinutes / 60) * hourHeight);

  const style = {
    top: `${top}px`,
    height: `${height}px`,
    backgroundColor: event.color || 'var(--primary-purple)'
  };
  console.log('[Calendar] Timed event style:', style);
  return style;
}

// --- Navigation ---
function prevWeek() {
  if (!canNavigateBackward.value) return;
  const newDate = new Date(currentDate.value);
  newDate.setDate(newDate.getDate() - 7);
  currentDate.value = newDate;
}

function nextWeek() {
  if (!canNavigateForward.value) return;
  const newDate = new Date(currentDate.value);
  newDate.setDate(newDate.getDate() + 7);
  currentDate.value = newDate;
}

// --- Grading Logic ---
async function checkGradingAbility() {
    try {
        const response = await axios.get('/api/subscription/can-grade');
        canGradeWeek.value = response.data.can_grade;
        if (!canGradeWeek.value && !isPremium.value) {
            gradeError.value = 'You have reached your free grading limit. Upgrade to Premium for unlimited grading.';
        }
    } catch (error) {
        console.error('[Calendar] Error checking grading ability:', error);
        canGradeWeek.value = false; // Assume cannot grade if check fails
        gradeError.value = 'Could not verify grading ability.';
    }
}

async function gradeCurrentWeek() {
  if (!canGradeWeek.value) {
      gradeError.value = isPremium.value ? 'An error occurred checking grade status.' : 'You have reached your free grading limit. Upgrade to Premium for unlimited grading.';
      return;
  }

  isGrading.value = true;
  gradeError.value = null;
  gradeResult.value = null; // Clear previous results

  try {
    console.log(`[Calendar] Grading week: ${formatDate(currentWeekStart.value)} to ${formatDate(currentWeekEnd.value)}`);
    const response = await axios.post('/api/ai/grade-calendar', {
      start_date: formatDate(currentWeekStart.value),
      end_date: formatDate(currentWeekEnd.value),
    });

    gradeResult.value = response.data.grade; // Assuming the grade object is nested under 'grade'
    console.log("[Calendar] Grading successful:", gradeResult.value);
    openGradeModal();

    // Increment grade count for non-premium users after successful grading
    if (!isPremium.value) {
        try {
            await axios.post('/api/subscription/increment-grades');
            checkGradingAbility(); // Re-check ability after incrementing
        } catch (incError) {
            console.error('[Calendar] Error incrementing grade count:', incError);
            // Handle this? Maybe just log it.
        }
    }

  } catch (error) {
    console.error('[Calendar] Error grading calendar:', error);
    gradeError.value = error.response?.data?.error || 'An unexpected error occurred while grading the calendar.';
  } finally {
    isGrading.value = false;
  }
}

// --- Modal Logic ---
function openGradeModal() {
  if (bsModal) {
    bsModal.show();
  }
}

function closeGradeModal() {
  if (bsModal) {
    bsModal.hide();
  }
}

// --- Watchers ---
watch(currentWeekStart, (newStart, oldStart) => {
  if (newStart && (!oldStart || newStart.getTime() !== oldStart.getTime())) {
    fetchEventsForWeek(newStart, currentWeekEnd.value);
    // Reset grade error when navigating
    gradeError.value = null;
    // Re-check grading ability for the new week (optional, depends on logic)
    // checkGradingAbility(); 
  }
});

// --- Lifecycle Hooks ---
onMounted(async () => {
  if (gradeModal.value) {
    bsModal = new Modal(gradeModal.value);
  }
  
  // Ensure user data is loaded before initial fetch and checks
  if (!store.state.user.user) {
      console.log("[Calendar] Waiting for user data...");
      // Wait for fetchUser to complete if it hasn't already
      // This assumes fetchUser is dispatched reliably in App.vue or similar entry point
      await store.dispatch('user/fetchUser'); 
  }

  if (store.state.user.user) {
      console.log("[Calendar] User data loaded. Fetching initial data...");
      await checkGradingAbility(); // Check if user can grade initially
      await fetchEventsForWeek(currentWeekStart.value, currentWeekEnd.value);
  } else {
      console.error("[Calendar] User data not loaded after waiting. Cannot fetch events or check grading status.");
      isLoading.value = false;
      fetchError.value = "User data not loaded. Cannot fetch events or check grading status.";
      canGradeWeek.value = false;
  }
});

</script>

<style scoped>
/* Styles remain largely the same */
.loading-indicator {
    text-align: center;
    padding: 50px;
    color: var(--text-color-secondary);
}

.loading-indicator .spinner-border {
    width: 3rem;
    height: 3rem;
    margin-bottom: 1rem;
}

.prev-week-btn[disabled],
.next-week-btn[disabled] {
    opacity: 0.5;
    cursor: not-allowed;
    background-color: transparent;
    color: var(--primary-purple);
}

.week-title {
    margin: 0 15px;
    font-style: italic;
    min-width: 200px;
    text-align: center;
}

.calendar-navigation {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20px;
}

.prev-week-btn, .next-week-btn {
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

.prev-week-btn:not([disabled]):hover,
.next-week-btn:not([disabled]):hover {
  background-color: var(--primary-purple);
  color: white;
}

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
  min-height: 600px;
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
.time-slot:last-child {
    border-bottom: none;
}

.day-column {
  position: relative;
  border-right: 1px solid rgba(255, 255, 255, 0.1);
  min-height: 600px;
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
  cursor: pointer;
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

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

#grade-calendar-btn:hover:not([disabled]) {
  transform: translateY(-2px);
  box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
}

#grade-calendar-btn[disabled] {
    opacity: 0.6;
    cursor: not-allowed;
}

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

.grade-section {
    margin-bottom: 20px;
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

.modal {
    color: #333;
}

.premium-cta {
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

</style>

