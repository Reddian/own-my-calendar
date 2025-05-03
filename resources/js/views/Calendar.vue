<template>
  <div>
    <h1 class="page-title">Calendar</h1>

    <!-- Premium CTA -->
    <div v-if="!isPremium" class="alert alert-info premium-cta">
      <i class="fas fa-star"></i>
      <strong>Upgrade to Premium!</strong> Unlock unlimited calendar grading, extended history, and more features.
      <router-link to="/settings#subscription" class="btn btn-sm btn-primary ms-3">Upgrade Now</router-link>
    </div>

    <div v-if="isLoading && !isRefreshing" class="loading-indicator">
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
            :disabled="!canNavigateBackward || isRefreshing"
            title="Previous Week">
            <i class="fas fa-chevron-left"></i>
          </button>
          <h2 class="week-title">{{ currentWeekRange }}</h2>
          <button 
            class="btn btn-outline-primary next-week-btn" 
            @click="nextWeek" 
            :disabled="!canNavigateForward || isRefreshing"
            title="Next Week">
            <i class="fas fa-chevron-right"></i>
          </button>
          <!-- Removed Refresh Button as per user request to remove caching -->
        </div>
      </div>

      <!-- Week View -->
      <div class="calendar-view week-view">
        <div class="week-header">
          <div class="time-column"></div> <!-- Placeholder for time slots column header -->
          <div v-for="day in weekDays" :key="day.dateStr" class="week-day" :class="{ today: day.isToday }">
            {{ day.shortDayName }}<br>{{ day.dayOfMonth }}
          </div>
        </div>
        <div class="week-grid-scroll-container" ref="gridScrollContainer"> <!-- Added scroll container -->
          <div class="week-grid">
            <div class="time-slots">
              <div v-for="hour in timeSlots" :key="hour" class="time-slot">{{ hour }}</div>
            </div>
            <div v-for="day in weekDays" :key="day.dateStr" class="day-column">
              <!-- Add horizontal lines for hours -->
              <div v-for="hour in 24" :key="`line-${day.dateStr}-${hour}`" class="hour-line" :style="{ top: `${(hour - 1) * HOUR_HEIGHT_PX}px` }"></div>
              <!-- Events -->
              <div v-for="event in getEventsForDay(day.dateStr)" :key="event.id" 
                   class="week-event" 
                   :style="getEventStyle(event)">
                {{ event.title }}<br>{{ event.timeRange }}
              </div>
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
          :disabled="isGrading || !canGradeWeek || isRefreshing">
          <span v-if="isGrading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
          <i v-else class="fas fa-check-circle"></i> 
          {{ isGrading ? "Grading..." : "Grade This Week" }}
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
                  <div class="grade-letter">{{ gradeResult.overall_grade || "N/A" }}</div>
                </div>
                <div class="grade-summary">
                  <p>{{ gradeResult.summary || "Grading complete." }}</p> 
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
                      <div class="recommendation-title">{{ rec.title || "Recommendation" }}</div>
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
            </div>
          </div>
        </div>
      </div>

      <div class="calendar-legend">
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
import { ref, computed, onMounted, watch, nextTick } from "vue"; // Added nextTick
import { useStore } from "vuex";
import axios from "axios";
import { Modal } from "bootstrap";

// Store
const store = useStore();

// State
const today = new Date();
today.setHours(0, 0, 0, 0);
const currentDate = ref(new Date(today));
const events = ref([]);
const isLoading = ref(true);
const isRefreshing = ref(false); // Kept for potential future use, but button removed
const fetchError = ref(null);
const gradeModal = ref(null);
let bsModal = null;
const isGrading = ref(false);
const gradeError = ref(null);
const gradeResult = ref(null);
const canGradeWeek = ref(true); // Will be checked on mount
const gridScrollContainer = ref(null); // Ref for the scrollable container
const userTimezone = computed(() => store.state.user.user?.timezone || Intl.DateTimeFormat().resolvedOptions().timeZone);

// Constants for grid
const GRID_START_HOUR = 0; // Changed to 0 for 12 AM
const GRID_END_HOUR = 24; // Changed to 24 for up to 11:59 PM
const HOUR_HEIGHT_PX = 60; // Height of one hour slot in pixels
const SCROLL_TO_HOUR = 8; // Hour to scroll to initially (8 AM)

// --- Subscription Status ---
const isPremium = computed(() => store.getters["user/isSubscribed"]);

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
  return date.toISOString().split("T")[0];
}

function formatTime(dateTimeString) {
  if (!dateTimeString) return "";
  try {
    const date = new Date(dateTimeString);
    // Display time in the user's selected timezone
    return date.toLocaleTimeString("en-US", {
      hour: "numeric",
      minute: "2-digit",
      hour12: true,
      timeZone: userTimezone.value, // Use user's timezone
    });
  } catch (e) {
    console.error("[Calendar] Error formatting time:", dateTimeString, e);
    return "Invalid Time";
  }
}

// --- Computed Properties ---
const currentWeekStart = computed(() => getStartOfWeek(currentDate.value));
const currentWeekEnd = computed(() => getEndOfWeek(currentDate.value));

const currentWeekRange = computed(() => {
  const start = currentWeekStart.value.toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
  });
  const end = currentWeekEnd.value.toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
  });
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
      shortDayName: day.toLocaleDateString("en-US", { weekday: "short" }),
      dayOfMonth: day.getDate(),
      isToday: day.toDateString() === today.toDateString(),
    });
  }
  return days;
});

const timeSlots = computed(() => {
  const slots = [];
  for (let i = GRID_START_HOUR; i < GRID_END_HOUR; i++) {
    const hour = i % 12 === 0 ? 12 : i % 12;
    const ampm = i < 12 || i === 24 ? "AM" : "PM"; // Handle 12 AM and 12 PM correctly
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
  }
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
  // Removed forceRefresh parameter
  isLoading.value = true;
  fetchError.value = null;
  console.log(
    `[Calendar] Fetching events from ${formatDate(startDate)} to ${formatDate(
      endDate
    )} directly from API.`
  );
  try {
    const response = await axios.post("/api/calendars/events", {
      start_date: formatDate(startDate),
      end_date: formatDate(endDate),
      // No need to pass timezone, backend uses authenticated user's timezone
    });
    console.log("[Calendar] Raw events received:", response.data.events);

    events.value = response.data.events.map((event) => {
      let start = null;
      let end = null;
      let dateStr = null;
      let timeRange = "All Day";
      let startHour = 0;
      let startMinute = 0;
      let endHour = 24;
      let endMinute = 0;
      let durationMinutes = 24 * 60;

      try {
        // Dates from backend are already in user's timezone (RFC3339 with offset)
        if (event.start) {
          start = new Date(event.start);
          // Use the date part from the ISO string directly for day assignment
          dateStr = event.start.split("T")[0]; 
          // Get hours/minutes based on local interpretation of the ISO string
          startHour = start.getHours(); 
          startMinute = start.getMinutes();
        }
        if (event.end) {
          end = new Date(event.end);
          endHour = end.getHours();
          endMinute = end.getMinutes();
        }

        if (start && end && !event.allDay) {
          timeRange = `${formatTime(event.start)} - ${formatTime(event.end)}`;
          durationMinutes = (end.getTime() - start.getTime()) / (1000 * 60);
          // Handle potential day crossing or DST issues (simple duration)
          if (durationMinutes < 0) durationMinutes += 24 * 60;
        } else if (start && !event.allDay) {
          timeRange = formatTime(event.start);
          durationMinutes = 60; // Default duration if end is missing
          endHour = startHour + 1;
          endMinute = startMinute; // Align end minute with start minute
        }
      } catch (e) {
        console.error("[Calendar] Error processing event dates:", event, e);
      }

      return {
        ...event,
        dateStr: dateStr,
        timeRange,
        startHour,
        startMinute,
        endHour,
        endMinute,
        durationMinutes,
        color: "var(--primary-purple)", // Placeholder color
      };
    });
    console.log("[Calendar] Processed events array:", events.value);
    // Scroll to 8 AM after events are loaded
    await nextTick(); // Wait for DOM update
    scrollToTime();

  } catch (error) {
    console.error("[Calendar] Error fetching calendar events:", error);
    fetchError.value =
      error.response?.data?.error ||
      "Failed to load calendar events. Please ensure your Google Calendar is connected and try again.";
    events.value = [];
  } finally {
    isLoading.value = false;
    isRefreshing.value = false; // Still set this although button is removed
  }
}

// Removed manualRefresh function

// --- Event Display Logic ---
function getEventsForDay(dateStr) {
  // console.log(`[Calendar] Filtering events for day: ${dateStr}`);
  const dayEvents = events.value.filter((event) => event.dateStr === dateStr);
  // console.log(`[Calendar] Found ${dayEvents.length} events for ${dateStr}`);
  return dayEvents;
}

function getEventStyle(event) {
  if (event.allDay || !event.start) {
    // Render all-day events at the top
    return {
      position: "relative", // Changed from absolute for stacking
      top: "0px",
      height: "20px",
      backgroundColor: event.color || "var(--secondary-color)",
      opacity: 0.8,
      fontSize: "10px",
      lineHeight: "20px",
      zIndex: 0,
      marginBottom: "2px",
      overflow: "hidden",
      whiteSpace: "nowrap",
      textOverflow: "ellipsis",
      padding: "0 4px",
      borderRadius: "3px",
      border: "1px solid rgba(0, 0, 0, 0.1)",
      cursor: "pointer",
    };
  }

  // Calculate position for timed events within the 0-24 hour grid
  const startTotalMinutes = event.startHour * 60 + event.startMinute;
  // const endTotalMinutes = event.endHour * 60 + event.endMinute;

  // Calculate top position based on minutes from 12 AM (GRID_START_HOUR = 0)
  const topPosition = (startTotalMinutes / 60) * HOUR_HEIGHT_PX;

  // Calculate height based on duration
  let duration = event.durationMinutes;
  if (duration <= 0) duration = 30; // Min height for 0 duration events
  // Ensure minimum height of ~15 mins for visibility
  const minHeight = (15 / 60) * HOUR_HEIGHT_PX;
  let height = (duration / 60) * HOUR_HEIGHT_PX;
  if (height < minHeight) height = minHeight;

  const style = {
    position: "absolute",
    top: `${topPosition}px`,
    height: `${height - 2}px`, // Subtract a bit for visual spacing
    left: "2px",
    right: "2px",
    backgroundColor: event.color || "var(--primary-purple)",
    borderRadius: "3px",
    padding: "2px 4px",
    fontSize: "12px",
    color: "white",
    overflow: "hidden",
    zIndex: 1,
    border: "1px solid rgba(0, 0, 0, 0.2)",
    cursor: "pointer",
  };
  // console.log(`[Calendar] Style for ${event.title} (${event.timeRange}):`, style);
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

// --- Grading ---
async function checkGradingAbility() {
  try {
    const response = await axios.get("/api/subscription/can-grade");
    canGradeWeek.value = response.data.can_grade;
    if (!canGradeWeek.value) {
      gradeError.value = response.data.reason || "Grading limit reached for this period.";
    } else {
      gradeError.value = null; // Clear previous error if now able
    }
  } catch (error) {
    console.error("[Calendar] Error checking grading ability:", error);
    // Keep button enabled but show error on click if check fails
    canGradeWeek.value = true; // Assume can grade, let backend handle error on attempt
    gradeError.value = "Could not verify grading ability. Please try again."; // Set the error message
  }
}

async function gradeCurrentWeek() {
  isGrading.value = true;
  gradeError.value = null;
  gradeResult.value = null;

  // Re-check ability right before grading
  await checkGradingAbility();
  if (!canGradeWeek.value) {
      isGrading.value = false;
      // gradeError is already set by checkGradingAbility
      return; 
  }

  try {
    const response = await axios.post("/api/calendar/grade", {
      start_date: formatDate(currentWeekStart.value),
      end_date: formatDate(currentWeekEnd.value),
    });
    gradeResult.value = response.data.grade;
    openGradeModal();
  } catch (error) {
    console.error("[Calendar] Error grading calendar:", error);
    gradeError.value =
      error.response?.data?.error || "An error occurred while grading the calendar.";
  } finally {
    isGrading.value = false;
  }
}

function openGradeModal() {
  if (!bsModal) {
    bsModal = new Modal(gradeModal.value);
  }
  bsModal.show();
}

function closeGradeModal() {
  if (bsModal) {
    bsModal.hide();
  }
}

// --- Auto Scroll --- 
function scrollToTime(hour = SCROLL_TO_HOUR) {
  if (gridScrollContainer.value) {
    const scrollTop = hour * HOUR_HEIGHT_PX; // Calculate scroll position for the target hour
    gridScrollContainer.value.scrollTop = scrollTop;
    console.log(`[Calendar] Scrolled to hour ${hour} (scrollTop: ${scrollTop}px)`);
  }
}

// --- Lifecycle Hooks ---
onMounted(async () => {
  // Ensure user data (including timezone) is loaded before fetching events
  if (!store.state.user.user) {
    await store.dispatch("user/fetchUser");
  }
  fetchEventsForWeek(currentWeekStart.value, currentWeekEnd.value);
  checkGradingAbility();
  // Initialize modal instance
  if (gradeModal.value) {
    bsModal = new Modal(gradeModal.value);
  }
});

watch(currentDate, (newDate) => {
  const newWeekStart = getStartOfWeek(newDate);
  const newWeekEnd = getEndOfWeek(newDate);
  fetchEventsForWeek(newWeekStart, newWeekEnd);
});

// Watch for timezone changes in the user profile
watch(() => store.state.user.user?.timezone, (newTimezone, oldTimezone) => {
  if (newTimezone && newTimezone !== oldTimezone) {
    console.log(`[Calendar] Timezone changed to ${newTimezone}. Refetching events.`);
    fetchEventsForWeek(currentWeekStart.value, currentWeekEnd.value);
  }
});

</script>

<style scoped>
.page-title {
  margin-bottom: 1.5rem;
}

.premium-cta {
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
}

.premium-cta i {
  margin-right: 0.5rem;
  color: var(--primary-purple);
}

.loading-indicator {
  text-align: center;
  padding: 4rem 0;
}

.calendar-container {
  /* Add styles for the overall container if needed */
}

.calendar-header {
  display: flex;
  justify-content: center; /* Center navigation */
  align-items: center;
  margin-bottom: 1rem;
}

.calendar-navigation {
  display: flex;
  align-items: center;
}

.week-title {
  margin: 0 1rem;
  min-width: 200px; /* Ensure space for date range */
  text-align: center;
  font-size: 1.5rem;
}

.prev-week-btn,
.next-week-btn {
  /* Styles for navigation buttons */
}

.refresh-btn i.fa-spin {
  animation: fa-spin 2s infinite linear;
}

.calendar-view {
  border: 1px solid #dee2e6;
  border-radius: 0.25rem;
  background-color: #fff;
}

.week-header {
  display: flex;
  border-bottom: 1px solid #dee2e6;
  background-color: #f8f9fa;
}

.time-column {
  width: 60px; /* Width for time labels */
  flex-shrink: 0;
  border-right: 1px solid #dee2e6;
}

.week-day {
  flex: 1;
  text-align: center;
  padding: 0.5rem 0;
  font-weight: bold;
  border-left: 1px solid #dee2e6; /* Add border between days */
}

.week-day:first-of-type {
  border-left: none; /* Remove border for the first day */
}

.week-day.today {
  background-color: #e9f5ff;
  color: var(--primary-blue);
}

/* Added scroll container */
.week-grid-scroll-container {
  max-height: 70vh; /* Adjust max height as needed */
  overflow-y: auto;
  position: relative; /* Needed for absolute positioning of events */
}

.week-grid {
  display: flex;
  position: relative; /* Needed for absolute positioning of events */
}

.time-slots {
  width: 60px;
  flex-shrink: 0;
  border-right: 1px solid #dee2e6;
}

.time-slot {
  height: 60px; /* Corresponds to HOUR_HEIGHT_PX */
  border-bottom: 1px dotted #e0e0e0;
  text-align: right;
  padding-right: 5px;
  font-size: 12px;
  color: #6c757d;
  position: relative;
}

.time-slot:last-child {
  border-bottom: none;
}

.day-column {
  flex: 1;
  position: relative; /* Crucial for absolute positioning of events */
  min-height: calc(24 * 60px); /* Ensure column has full height for 24 hours */
  border-left: 1px solid #dee2e6; /* Add border between days */
}

.day-column:first-of-type {
  border-left: none; /* Remove border for the first day */
}

/* Add horizontal lines for hours */
.hour-line {
  position: absolute;
  left: 0;
  right: 0;
  height: 1px;
  background-color: #e0e0e0; /* Light gray line */
  z-index: 0; /* Behind events */
}

.week-event {
  /* Style is now primarily set dynamically by getEventStyle */
  transition: background-color 0.2s ease;
}

.week-event:hover {
  opacity: 0.8;
}

.calendar-grading-action {
  text-align: center;
  margin-top: 2rem;
}

.grade-result {
  /* Styles for the grade result display */
}

.grade-header {
  text-align: center;
  margin-bottom: 1.5rem;
}

.grade-letter {
  font-size: 4rem;
  font-weight: bold;
  color: var(--primary-purple);
  line-height: 1;
}

.grade-summary {
  text-align: center;
  font-size: 1.1rem;
  margin-bottom: 1.5rem;
}

.grade-section {
  margin-bottom: 1.5rem;
}

.grade-section h4 {
  color: var(--primary-blue);
  margin-bottom: 0.75rem;
}

.recommendations-list {
  list-style: none;
  padding-left: 0;
}

.recommendations-list li {
  background-color: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 0.25rem;
  padding: 0.75rem 1rem;
  margin-bottom: 0.5rem;
}

.recommendation-title {
  font-weight: bold;
  margin-bottom: 0.25rem;
}

.recommendation-description {
  font-size: 0.95rem;
}

.calendar-legend {
  margin-top: 2rem;
  display: flex;
  justify-content: center;
  gap: 1.5rem;
}

.legend-item {
  display: flex;
  align-items: center;
}

.legend-color {
  width: 15px;
  height: 15px;
  border-radius: 3px;
  margin-right: 0.5rem;
}

</style>

