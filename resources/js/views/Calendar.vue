<template>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-md-3">
        <div class="card sidebar-card">
          <div class="card-header">
            <h5>Calendar Settings</h5>
          </div>
          <div class="card-body">
            <!-- Calendar Selection -->
            <div class="mb-4">
              <h6>Connected Calendars</h6>
              <div class="calendar-list">
                <div v-for="calendar in calendars" :key="calendar.id" class="calendar-item">
                  <div class="form-check">
                    <input 
                      class="form-check-input" 
                      type="checkbox" 
                      :id="'calendar-' + calendar.id"
                      v-model="calendar.selected"
                      @change="updateCalendarSelection(calendar)"
                    >
                    <label class="form-check-label" :for="'calendar-' + calendar.id">
                      {{ calendar.name }}
                    </label>
                  </div>
                  <div class="calendar-actions">
                    <button 
                      class="btn btn-sm btn-outline-primary"
                      @click="toggleCalendarVisibility(calendar)"
                    >
                      <i :class="['fas', calendar.visible ? 'fa-eye' : 'fa-eye-slash']"></i>
                    </button>
                    <button 
                      class="btn btn-sm btn-outline-danger"
                      @click="disconnectCalendar(calendar)"
                    >
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                </div>
              </div>
              <button 
                class="btn btn-primary btn-sm mt-3"
                @click="connectNewCalendar"
              >
                <i class="fas fa-plus me-2"></i>Connect Calendar
              </button>
            </div>

            <!-- Calendar Filters -->
            <div class="mb-4">
              <h6>Filters</h6>
              <div class="filter-options">
                <div class="form-check mb-2">
                  <input 
                    class="form-check-input" 
                    type="checkbox" 
                    id="filter-meetings"
                    v-model="filters.meetings"
                  >
                  <label class="form-check-label" for="filter-meetings">
                    Meetings
                  </label>
                </div>
                <div class="form-check mb-2">
                  <input 
                    class="form-check-input" 
                    type="checkbox" 
                    id="filter-tasks"
                    v-model="filters.tasks"
                  >
                  <label class="form-check-label" for="filter-tasks">
                    Tasks
                  </label>
                </div>
                <div class="form-check mb-2">
                  <input 
                    class="form-check-input" 
                    type="checkbox" 
                    id="filter-personal"
                    v-model="filters.personal"
                  >
                  <label class="form-check-label" for="filter-personal">
                    Personal Events
                  </label>
                </div>
              </div>
            </div>

            <!-- Calendar Stats -->
            <div class="calendar-stats">
              <h6>Today's Stats</h6>
              <div class="stat-item">
                <span class="stat-label">Total Events:</span>
                <span class="stat-value">{{ stats.totalEvents }}</span>
              </div>
              <div class="stat-item">
                <span class="stat-label">Busy Time:</span>
                <span class="stat-value">{{ stats.busyTime }}</span>
              </div>
              <div class="stat-item">
                <span class="stat-label">Free Time:</span>
                <span class="stat-value">{{ stats.freeTime }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Calendar -->
      <div class="col-md-9">
        <div class="card calendar-card">
          <div class="card-header">
            <div class="calendar-header">
              <div class="calendar-nav">
                <button class="btn btn-outline-primary" @click="previousMonth">
                  <i class="fas fa-chevron-left"></i>
                </button>
                <h4 class="calendar-title">{{ currentMonth }}</h4>
                <button class="btn btn-outline-primary" @click="nextMonth">
                  <i class="fas fa-chevron-right"></i>
                </button>
              </div>
              <div class="calendar-views">
                <button 
                  class="btn btn-outline-primary"
                  :class="{ active: view === 'month' }"
                  @click="setView('month')"
                >
                  Month
                </button>
                <button 
                  class="btn btn-outline-primary"
                  :class="{ active: view === 'week' }"
                  @click="setView('week')"
                >
                  Week
                </button>
                <button 
                  class="btn btn-outline-primary"
                  :class="{ active: view === 'day' }"
                  @click="setView('day')"
                >
                  Day
                </button>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="calendar-container">
              <!-- Calendar grid will be rendered here -->
              <div class="calendar-grid">
                <div class="calendar-weekdays">
                  <div v-for="day in weekdays" :key="day" class="weekday">
                    {{ day }}
                  </div>
                </div>
                <div class="calendar-days">
                  <div 
                    v-for="day in calendarDays" 
                    :key="day.date"
                    class="calendar-day"
                    :class="{
                      'today': day.isToday,
                      'other-month': !day.isCurrentMonth,
                      'has-events': day.hasEvents
                    }"
                  >
                    <div class="day-number">{{ day.day }}</div>
                    <div class="day-events">
                      <div 
                        v-for="event in day.events" 
                        :key="event.id"
                        class="event-item"
                        :class="event.type"
                        @click="openEventDetails(event)"
                      >
                        {{ event.title }}
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

    <!-- Event Details Modal -->
    <div v-if="selectedEvent" class="modal fade show" style="display: block;" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ selectedEvent.title }}</h5>
            <button type="button" class="btn-close" @click="selectedEvent = null"></button>
          </div>
          <div class="modal-body">
            <div class="event-details">
              <div class="detail-item">
                <i class="fas fa-clock"></i>
                <span>{{ selectedEvent.time }}</span>
              </div>
              <div class="detail-item">
                <i class="fas fa-calendar"></i>
                <span>{{ selectedEvent.calendar }}</span>
              </div>
              <div class="detail-item" v-if="selectedEvent.location">
                <i class="fas fa-map-marker-alt"></i>
                <span>{{ selectedEvent.location }}</span>
              </div>
              <div class="detail-item" v-if="selectedEvent.description">
                <i class="fas fa-info-circle"></i>
                <span>{{ selectedEvent.description }}</span>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="selectedEvent = null">Close</button>
            <button type="button" class="btn btn-primary" @click="editEvent(selectedEvent)">Edit</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useStore } from 'vuex';

export default {
  name: 'Calendar',
  setup() {
    const store = useStore();
    const calendars = ref([]);
    const filters = ref({
      meetings: true,
      tasks: true,
      personal: true
    });
    const stats = ref({
      totalEvents: 0,
      busyTime: '0h 0m',
      freeTime: '0h 0m'
    });
    const currentDate = ref(new Date());
    const view = ref('month');
    const selectedEvent = ref(null);
    const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    const currentMonth = computed(() => {
      return currentDate.value.toLocaleString('default', { month: 'long', year: 'numeric' });
    });

    const calendarDays = computed(() => {
      // Implementation of calendar days calculation
      // This is a placeholder - you'll need to implement the actual calendar logic
      return [];
    });

    const fetchCalendars = async () => {
      try {
        const response = await axios.get('/api/calendars');
        calendars.value = response.data;
      } catch (error) {
        console.error('Failed to fetch calendars:', error);
      }
    };

    const updateCalendarSelection = async (calendar) => {
      try {
        await axios.post('/api/calendars/update-selection', {
          calendar_id: calendar.id,
          selected: calendar.selected
        });
      } catch (error) {
        console.error('Failed to update calendar selection:', error);
      }
    };

    const toggleCalendarVisibility = async (calendar) => {
      try {
        await axios.post('/api/calendars/update-visibility', {
          calendar_id: calendar.id,
          visible: !calendar.visible
        });
        calendar.visible = !calendar.visible;
      } catch (error) {
        console.error('Failed to toggle calendar visibility:', error);
      }
    };

    const disconnectCalendar = async (calendar) => {
      try {
        await axios.post('/api/calendars/disconnect', {
          calendar_id: calendar.id
        });
        calendars.value = calendars.value.filter(c => c.id !== calendar.id);
      } catch (error) {
        console.error('Failed to disconnect calendar:', error);
      }
    };

    const connectNewCalendar = () => {
      // Implementation for connecting a new calendar
      // This will likely involve OAuth flow
    };

    const previousMonth = () => {
      currentDate.value = new Date(
        currentDate.value.getFullYear(),
        currentDate.value.getMonth() - 1,
        1
      );
    };

    const nextMonth = () => {
      currentDate.value = new Date(
        currentDate.value.getFullYear(),
        currentDate.value.getMonth() + 1,
        1
      );
    };

    const setView = (newView) => {
      view.value = newView;
    };

    const openEventDetails = (event) => {
      selectedEvent.value = event;
    };

    const editEvent = (event) => {
      // Implementation for editing an event
    };

    onMounted(() => {
      fetchCalendars();
    });

    return {
      calendars,
      filters,
      stats,
      currentMonth,
      calendarDays,
      weekdays,
      view,
      selectedEvent,
      updateCalendarSelection,
      toggleCalendarVisibility,
      disconnectCalendar,
      connectNewCalendar,
      previousMonth,
      nextMonth,
      setView,
      openEventDetails,
      editEvent
    };
  }
};
</script>

<style scoped>
.sidebar-card {
  height: 100%;
  border-radius: 10px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.calendar-card {
  border-radius: 10px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.calendar-nav {
  display: flex;
  align-items: center;
  gap: 10px;
}

.calendar-title {
  margin: 0;
  min-width: 200px;
  text-align: center;
}

.calendar-views {
  display: flex;
  gap: 5px;
}

.calendar-views .btn.active {
  background-color: var(--primary-purple);
  color: white;
}

.calendar-grid {
  display: grid;
  grid-template-rows: auto 1fr;
  height: 100%;
}

.calendar-weekdays {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  text-align: center;
  padding: 10px 0;
  border-bottom: 1px solid #eee;
}

.calendar-days {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  grid-template-rows: repeat(6, 1fr);
  gap: 1px;
  background-color: #eee;
  flex: 1;
}

.calendar-day {
  background-color: white;
  padding: 5px;
  min-height: 100px;
  position: relative;
}

.calendar-day.today {
  background-color: #e6f7ff;
}

.calendar-day.other-month {
  color: #999;
}

.calendar-day.has-events::after {
  content: '';
  position: absolute;
  bottom: 5px;
  left: 50%;
  transform: translateX(-50%);
  width: 5px;
  height: 5px;
  background-color: var(--primary-purple);
  border-radius: 50%;
}

.day-number {
  font-weight: bold;
  margin-bottom: 5px;
}

.day-events {
  max-height: calc(100% - 25px);
  overflow-y: auto;
}

.event-item {
  font-size: 0.8rem;
  padding: 2px 5px;
  margin-bottom: 2px;
  border-radius: 3px;
  cursor: pointer;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.event-item.meeting {
  background-color: #e3f2fd;
  color: #1976d2;
}

.event-item.task {
  background-color: #f3e5f5;
  color: #7b1fa2;
}

.event-item.personal {
  background-color: #e8f5e9;
  color: #2e7d32;
}

.calendar-list {
  max-height: 200px;
  overflow-y: auto;
  margin-bottom: 15px;
}

.calendar-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #eee;
}

.calendar-actions {
  display: flex;
  gap: 5px;
}

.stat-item {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid #eee;
}

.stat-label {
  color: #666;
}

.stat-value {
  font-weight: bold;
  color: var(--primary-purple);
}

.event-details .detail-item {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}

.event-details .detail-item i {
  width: 20px;
  color: var(--primary-purple);
  margin-right: 10px;
}
</style> 