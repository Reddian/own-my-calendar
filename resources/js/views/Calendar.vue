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
              <div v-if="parsedGradeResult" class="grade-result">
                <div class="grade-header">
                  <!-- Apply dynamic color class and show letter grade -->
                  <div class="grade-number" :class="gradeColorClass">{{ parsedGradeResult.overall_grade || "N/A" }}</div>
                  <div class="grade-letter">{{ letterGrade }}</div>
                </div>
                <div class="grade-summary">
                  <p>{{ parsedGradeResult.summary || "Grading complete." }}</p> 
                </div>
                <div v-if="parsedGradeResult.strengths && parsedGradeResult.strengths.length > 0" class="grade-section">
                  <h4>Strengths</h4>
                  <ul class="recommendations-list">
                    <li v-for="(strength, index) in parsedGradeResult.strengths" :key="`strength-${index}`">
                      {{ strength }}
                    </li>
                  </ul>
                </div>
                <div v-if="parsedGradeResult.improvement_areas && parsedGradeResult.improvement_areas.length > 0" class="grade-section">
                  <h4>Areas for Improvement</h4>
                  <ul class="recommendations-list">
                    <li v-for="(area, index) in parsedGradeResult.improvement_areas" :key="`improve-${index}`">
                       {{ area }}
                    </li>
                  </ul>
                </div>
                <div v-if="parsedGradeResult.recommendations && parsedGradeResult.recommendations.length > 0" class="grade-section">
                  <h4>Recommendations</h4>
                  <ul class="recommendations-list">
                    <li v-for="(rec, index) in parsedGradeResult.recommendations" :key="`rec-${index}`">
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
        <h4>Event Categories</h4>
        <div class="legend-items">
          <div class="legend-item" @click="selectCategory('non-negotiable')" :class="{ 'selected': selectedCategory === 'non-negotiable' }">
            <div class="legend-color" style="background-color: #8A2BE2;"></div>
            <span>Non-Negotiable Events</span>
          </div>
          <div class="legend-item" @click="selectCategory('money-making')" :class="{ 'selected': selectedCategory === 'money-making' }">
            <div class="legend-color" style="background-color: #00A86B;"></div>
            <span>Money-Making Activities</span>
          </div>
          <div class="legend-item" @click="selectCategory('growth-learning')" :class="{ 'selected': selectedCategory === 'growth-learning' }">
            <div class="legend-color" style="background-color: #4169E1;"></div>
            <span>Growth & Learning Events</span>
          </div>
          <div class="legend-item" @click="selectCategory('energy-renewal')" :class="{ 'selected': selectedCategory === 'energy-renewal' }">
            <div class="legend-color" style="background-color: #FF7F50;"></div>
            <span>Energy Renewal Activities</span>
          </div>
          <div class="legend-item" @click="selectCategory('low-value')" :class="{ 'selected': selectedCategory === 'low-value' }">
            <div class="legend-color" style="background-color: #808080;"></div>
            <span>Low-Value Activities</span>
          </div>
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
const gradeResult = ref(null); // Raw result from API
const canGradeWeek = ref(true); // Will be checked on mount
const gridScrollContainer = ref(null); // Ref for the scrollable container
const userTimezone = computed(() => store.state.user.user?.timezone || Intl.DateTimeFormat().resolvedOptions().timeZone);
const selectedCategory = ref(null); // Track selected category

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
    // Always try to parse, replacing space with T if needed
    const parsableDateTimeString = dateTimeString.includes("T") ? dateTimeString : dateTimeString.replace(" ", "T");
    const date = new Date(parsableDateTimeString);
    if (isNaN(date.getTime())) {
      throw new Error("Invalid Date object");
    }
    // Display time in the user"s selected timezone
    return date.toLocaleTimeString("en-US", {
      hour: "numeric",
      minute: "2-digit",
      hour12: true,
      timeZone: userTimezone.value, // Use user"s timezone
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

// --- Category Selection ---
function selectCategory(category) {
  // Toggle selection - if already selected, deselect it
  selectedCategory.value = selectedCategory.value === category ? null : category;
  console.log(`[Calendar] Category selected: ${selectedCategory.value}`);
}

// --- Event Fetching ---
async function fetchEventsForWeek(startDate, endDate) {
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
    });
    console.log("[Calendar] Raw events received:", response.data.events);

    events.value = response.data.events
      .map((event) => {
        let start = null;
        let end = null;
        let dateStr = null;
        let timeRange = "All Day";
        let startHour = 0;
        let startMinute = 0;
        let durationMinutes = 24 * 60;
        // Default isAllDay based on backend flag, fallback to true if flag missing/null
        let isAllDayEvent = event.allDay !== false;

        try {
          if (event.start) {
            // Always try to parse the start string into a Date object
            // Replace space with "T" for better compatibility if "T" is missing
            const parsableStartString = event.start.includes("T")
              ? event.start
              : event.start.replace(" ", "T");
            start = new Date(parsableStartString);

            // Check for invalid date
            if (isNaN(start.getTime())) {
              throw new Error(
                `Invalid Date object created from event.start: ${event.start}`
              );
            }

            // Extract date string (use UTC date parts to avoid timezone shifts affecting the date itself)
            dateStr = start.toISOString().split("T")[0];

            // Determine if it"s effectively all-day
            // Condition: Backend flag is true OR (flag is not explicitly false AND time is midnight)
            if (
              event.allDay === true ||
              (event.allDay !== false &&
                start.getHours() === 0 &&
                start.getMinutes() === 0 &&
                start.getSeconds() === 0)
            ) {
              isAllDayEvent = true;
              startHour = 0;
              startMinute = 0;
              timeRange = "All Day";
              durationMinutes = 24 * 60; // Default all-day duration
            } else {
              // It"s a timed event
              isAllDayEvent = false;
              startHour = start.getHours();
              startMinute = start.getMinutes();
              // Calculate duration and timeRange later
            }
          } else {
            // No start time provided, treat as error or skip?
            console.error("[Calendar] Event missing start time:", event);
            return null; // Skip this event if it has no start time
          }

          // Process end time only if it"s a timed event
          if (!isAllDayEvent && event.end) {
            const parsableEndString = event.end.includes("T")
              ? event.end
              : event.end.replace(" ", "T");
            end = new Date(parsableEndString);
            if (isNaN(end.getTime())) {
              console.warn("[Calendar] Invalid end date object:", event.end);
              end = null; // Ignore invalid end date
            }
          }

          // Calculate duration and timeRange for timed events
          if (!isAllDayEvent && start) {
            // Check start is valid
            if (end && end > start) {
              // Check end is valid and after start
              timeRange = `${formatTime(event.start)} - ${formatTime(event.end)}`;
              durationMinutes = (end.getTime() - start.getTime()) / (1000 * 60);
              // Handle potential cross-day events or zero duration
              if (durationMinutes <= 0) {
                durationMinutes = 30; // Minimum duration 30 mins
              }
            } else {
              // Timed event without a valid end time or end time is before start
              timeRange = formatTime(event.start);
              durationMinutes = 60; // Default duration 60 mins
            }
          }
          // For all-day events, timeRange is already set to "All Day"
        } catch (e) {
          console.error("[Calendar] Error processing event:", event, e);
          return null; // Skip this event if processing fails
        }

        // Return the processed event with all required fields
        return {
          id: event.id || `event-${Math.random().toString(36).substr(2, 9)}`, // Generate ID if missing
          title: event.title || "Untitled Event",
          start: start,
          end: end,
          dateStr: dateStr,
          isAllDay: isAllDayEvent,
          startHour: startHour,
          startMinute: startMinute,
          durationMinutes: durationMinutes,
          timeRange: timeRange,
          calendarName: event.calendar_name || "",
          color: event.color || "#4285F4", // Default blue color
        };
      })
      .filter((event) => event !== null); // Remove any events that failed processing

    console.log("[Calendar] Processed events:", events.value);
    isLoading.value = false;
  } catch (error) {
    console.error("[Calendar] Error fetching events:", error);
    fetchError.value = "Failed to load calendar events. Please try again later.";
    isLoading.value = false;
  } finally {
    isRefreshing.value = false;
  }
}

// --- Event Display Helpers ---
function getEventsForDay(dateStr) {
  return events.value.filter((event) => event.dateStr === dateStr);
}

function getEventStyle(event) {
  if (event.isAllDay) {
    return {
      backgroundColor: event.color,
      top: "0px",
      height: "30px",
      width: "100%",
      zIndex: 10,
    };
  } else {
    const top = event.startHour * HOUR_HEIGHT_PX + (event.startMinute / 60) * HOUR_HEIGHT_PX;
    const height = (event.durationMinutes / 60) * HOUR_HEIGHT_PX;
    return {
      backgroundColor: event.color,
      top: `${top}px`,
      height: `${height}px`,
      width: "calc(100% - 4px)",
      zIndex: 5,
    };
  }
}

// --- Navigation ---
function prevWeek() {
  const newDate = new Date(currentDate.value);
  newDate.setDate(newDate.getDate() - 7);
  currentDate.value = newDate;
}

function nextWeek() {
  const newDate = new Date(currentDate.value);
  newDate.setDate(newDate.getDate() + 7);
  currentDate.value = newDate;
}

// --- Calendar Grading ---
async function gradeCurrentWeek() {
  if (isGrading.value || !canGradeWeek.value) return;
  
  isGrading.value = true;
  gradeError.value = null;
  
  try {
    // First check if user can grade (subscription status)
    const canGradeResponse = await axios.get("/api/subscription/can-grade");
    if (!canGradeResponse.data.can_grade) {
      throw new Error(canGradeResponse.data.message || "You've reached your grading limit. Please upgrade to premium.");
    }
    
    // Proceed with grading
    const response = await axios.post("/api/ai/grade-calendar", {
      start_date: formatDate(currentWeekStart.value),
      end_date: formatDate(currentWeekEnd.value),
    });
    
    gradeResult.value = response.data;
    console.log("[Calendar] Grade result:", gradeResult.value);
    
    // Increment grades used counter
    await axios.post("/api/subscription/increment-grades");
    
    // Show the modal with results
    showGradeModal();
    
  } catch (error) {
    console.error("[Calendar] Grading error:", error);
    gradeError.value = error.response?.data?.message || error.message || "Failed to grade calendar. Please try again later.";
  } finally {
    isGrading.value = false;
  }
}

// --- Modal Handling ---
function showGradeModal() {
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

// --- Grade Result Parsing ---
const parsedGradeResult = computed(() => {
  if (!gradeResult.value) return null;
  return gradeResult.value;
});

// --- Letter Grade Calculation ---
const letterGrade = computed(() => {
  if (!parsedGradeResult.value || !parsedGradeResult.value.overall_grade) return "N/A";
  
  const score = parsedGradeResult.value.overall_grade;
  if (score >= 97) return "A+";
  if (score >= 93) return "A";
  if (score >= 90) return "A-";
  if (score >= 87) return "B+";
  if (score >= 83) return "B";
  if (score >= 80) return "B-";
  if (score >= 77) return "C+";
  if (score >= 73) return "C";
  if (score >= 70) return "C-";
  if (score >= 67) return "D+";
  if (score >= 63) return "D";
  if (score >= 60) return "D-";
  return "F";
});

// --- Grade Color Class ---
const gradeColorClass = computed(() => {
  if (!parsedGradeResult.value || !parsedGradeResult.value.overall_grade) return "";
  
  const score = parsedGradeResult.value.overall_grade;
  if (score >= 90) return "grade-a";
  if (score >= 80) return "grade-b";
  if (score >= 70) return "grade-c";
  if (score >= 60) return "grade-d";
  return "grade-f";
});

// --- Lifecycle Hooks ---
watch(currentDate, async () => {
  await fetchEventsForWeek(currentWeekStart.value, currentWeekEnd.value);
});

onMounted(async () => {
  // Check if user can grade calendar
  try {
    const response = await axios.get("/api/subscription/can-grade");
    canGradeWeek.value = response.data.can_grade;
  } catch (error) {
    console.error("[Calendar] Error checking grade permission:", error);
    canGradeWeek.value = false;
  }
  
  // Fetch events for current week
  await fetchEventsForWeek(currentWeekStart.value, currentWeekEnd.value);
  
  // Scroll to 8 AM (or configured hour)
  nextTick(() => {
    if (gridScrollContainer.value) {
      gridScrollContainer.value.scrollTop = SCROLL_TO_HOUR * HOUR_HEIGHT_PX;
    }
  });
});
</script>

<style>
/* Global styles like fonts.css and dashboard.css are loaded via spa.blade.php */

/* Add any component-specific styles here */
.calendar-legend {
  margin-top: 2rem;
  padding: 1rem;
  border-radius: 8px;
  background-color: #f8f9fa;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.calendar-legend h4 {
  margin-bottom: 1rem;
  font-size: 1.1rem;
  color: #333;
}

.legend-items {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
}

.legend-item {
  display: flex;
  align-items: center;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  background-color: white;
  border: 1px solid #e9ecef;
}

.legend-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.legend-item.selected {
  background-color: #f0f0f0;
  border-color: #ccc;
  box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
}

.legend-color {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  margin-right: 8px;
}

.nav-item a {
    color: inherit; /* Ensure router-links inherit text color */
    text-decoration: none;
    display: flex; /* Ensure icon and text are aligned */
    align-items: center;
    gap: 10px; /* Space between icon and text */
    padding: 10px 15px; /* Add padding to the link itself */
}

.nav-item.active span,
.nav-item:hover span {
    color: var(--primary-purple); /* Or your active/hover color */
}

.nav-item.active i,
.nav-item:hover i {
    color: var(--primary-purple); /* Apply color to icon too */
}

/* Ensure router-link clicks work */
.nav-item span {
    cursor: pointer;
}

/* Styles for the Subscription CTA */
.subscription-cta {
    background-color: #e9ecef; /* Light grey background */
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem; /* Space below the CTA */
    border-radius: 0.375rem; /* Rounded corners */
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap; /* Allow wrapping on smaller screens */
}

.cta-content {
    flex-grow: 1;
    margin-right: 1rem; /* Space between text and button */
}

.subscription-cta h4 {
    margin-bottom: 0.25rem;
    font-size: 1.1rem;
    font-weight: 600;
}

.subscription-cta p {
    margin-bottom: 0;
    font-size: 0.9rem;
    color: #495057; /* Darker grey text */
}

.subscription-cta .btn-primary {
    white-space: nowrap; /* Prevent button text wrapping */
}

@media (max-width: 768px) {
    .subscription-cta {
        flex-direction: column;
        align-items: flex-start;
    }
    .cta-content {
        margin-right: 0;
        margin-bottom: 0.75rem; /* Space between text and button on mobile */
    }
}
</style>
