<template>
  <div class="calendar-manager">
    <div class="calendar-header">
      <h2>Connected Calendars</h2>
      <button @click="connectNewCalendar" class="btn-primary" :disabled="isProcessing">
        {{ isProcessing ? 'Connecting...' : 'Connect New Calendar' }}
      </button>
    </div>

    <div v-if="isLoading" class="loading-indicator">
      <p>Loading calendars...</p>
    </div>

    <div v-else-if="error" class="error-message">
      <p>{{ error }}</p>
      <button @click="fetchCalendars" class="btn-secondary">Retry</button>
    </div>

    <div v-else-if="!hasCalendars" class="no-calendars">
      <p>No calendars connected. Connect your Google Calendar to get started.</p>
    </div>

    <div v-else class="calendar-list">
      <div v-for="calendar in calendars" :key="calendar.id" class="calendar-item">
        <div class="calendar-info">
          <div class="calendar-color" :style="{ backgroundColor: calendar.color || '#666' }"></div>
          <div class="calendar-details">
            <h3>{{ calendar.name }}</h3>
            <p v-if="calendar.description">{{ calendar.description }}</p>
            <p class="calendar-id">ID: {{ calendar.calendar_id }}</p>
          </div>
        </div>
        
        <div class="calendar-actions">
          <div class="calendar-toggle">
            <label class="toggle-label">
              <input 
                type="checkbox" 
                v-model="calendar.is_selected"
                @change="updateCalendarSelection(calendar.id, calendar.is_selected)"
              >
              <span class="toggle-text">Include in Grading</span>
            </label>
          </div>
          
          <div class="calendar-toggle">
            <label class="toggle-label">
              <input 
                type="checkbox" 
                v-model="calendar.is_visible"
                @change="updateCalendarVisibility(calendar.id, calendar.is_visible)"
              >
              <span class="toggle-text">Show in Calendar</span>
            </label>
          </div>
          
          <button 
            @click="disconnectCalendar(calendar.id)" 
            class="btn-danger"
            :disabled="isProcessing"
          >
            Disconnect
          </button>
        </div>
      </div>
    </div>

    <div v-if="hasCalendars" class="calendar-actions-footer">
      <button 
        @click="disconnectAllCalendars" 
        class="btn-danger"
        :disabled="isProcessing"
      >
        Disconnect All Calendars
      </button>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

export default {
  name: 'CalendarManager',
  
  setup() {
    const calendars = ref([]);
    const isLoading = ref(true);
    const isProcessing = ref(false);
    const error = ref(null);
    
    const hasCalendars = computed(() => calendars.value.length > 0);
    
    // Fetch user's calendars
    const fetchCalendars = async () => {
      isLoading.value = true;
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
      } finally {
        isLoading.value = false;
      }
    };
    
    // Connect a new calendar
    const connectNewCalendar = async () => {
      isProcessing.value = true;
      try {
        const response = await axios.get('/api/calendars/auth');
        if (response.data.success) {
          // Open Google OAuth in a new window
          window.open(response.data.auth_url, '_blank');
          
          // Poll for connection status changes
          const pollInterval = setInterval(async () => {
            try {
              const checkResponse = await axios.get('/api/calendars/check-connection');
              if (checkResponse.data.connected) {
                clearInterval(pollInterval);
                await fetchCalendars();
                isProcessing.value = false;
              }
            } catch (err) {
              console.error('Error checking connection:', err);
            }
          }, 5000); // Check every 5 seconds
          
          // Stop polling after 5 minutes
          setTimeout(() => {
            clearInterval(pollInterval);
            isProcessing.value = false;
          }, 300000);
        } else {
          error.value = response.data.error || 'Failed to get authorization URL';
          isProcessing.value = false;
        }
      } catch (err) {
        console.error('Error connecting calendar:', err);
        error.value = 'Failed to connect calendar';
        isProcessing.value = false;
      }
    };
    
    // Update calendar selection
    const updateCalendarSelection = async (calendarId, isSelected) => {
      isProcessing.value = true;
      try {
        const response = await axios.post('/api/calendars/selection', {
          calendar_id: calendarId,
          is_selected: isSelected
        });
        
        if (!response.data.success) {
          error.value = response.data.error || 'Failed to update calendar selection';
          // Revert the change
          const calendar = calendars.value.find(c => c.id === calendarId);
          if (calendar) {
            calendar.is_selected = !isSelected;
          }
        }
      } catch (err) {
        console.error('Error updating calendar selection:', err);
        error.value = 'Failed to update calendar selection';
        // Revert the change
        const calendar = calendars.value.find(c => c.id === calendarId);
        if (calendar) {
          calendar.is_selected = !isSelected;
        }
      } finally {
        isProcessing.value = false;
      }
    };
    
    // Update calendar visibility
    const updateCalendarVisibility = async (calendarId, isVisible) => {
      isProcessing.value = true;
      try {
        const response = await axios.post('/api/calendars/visibility', {
          calendar_id: calendarId,
          is_visible: isVisible
        });
        
        if (!response.data.success) {
          error.value = response.data.error || 'Failed to update calendar visibility';
          // Revert the change
          const calendar = calendars.value.find(c => c.id === calendarId);
          if (calendar) {
            calendar.is_visible = !isVisible;
          }
        }
      } catch (err) {
        console.error('Error updating calendar visibility:', err);
        error.value = 'Failed to update calendar visibility';
        // Revert the change
        const calendar = calendars.value.find(c => c.id === calendarId);
        if (calendar) {
          calendar.is_visible = !isVisible;
        }
      } finally {
        isProcessing.value = false;
      }
    };
    
    // Disconnect a single calendar
    const disconnectCalendar = async (calendarId) => {
      if (!confirm('Are you sure you want to disconnect this calendar?')) {
        return;
      }
      
      isProcessing.value = true;
      try {
        const response = await axios.post('/api/calendars/disconnect', {
          calendar_id: calendarId
        });
        
        if (response.data.success) {
          await fetchCalendars();
        } else {
          error.value = response.data.error || 'Failed to disconnect calendar';
        }
      } catch (err) {
        console.error('Error disconnecting calendar:', err);
        error.value = 'Failed to disconnect calendar';
      } finally {
        isProcessing.value = false;
      }
    };
    
    // Disconnect all calendars
    const disconnectAllCalendars = async () => {
      if (!confirm('Are you sure you want to disconnect all calendars?')) {
        return;
      }
      
      isProcessing.value = true;
      try {
        const response = await axios.post('/api/calendars/disconnect-all');
        
        if (response.data.success) {
          await fetchCalendars();
        } else {
          error.value = response.data.error || 'Failed to disconnect all calendars';
        }
      } catch (err) {
        console.error('Error disconnecting all calendars:', err);
        error.value = 'Failed to disconnect all calendars';
      } finally {
        isProcessing.value = false;
      }
    };
    
    onMounted(() => {
      fetchCalendars();
    });
    
    return {
      calendars,
      isLoading,
      isProcessing,
      error,
      hasCalendars,
      fetchCalendars,
      connectNewCalendar,
      updateCalendarSelection,
      updateCalendarVisibility,
      disconnectCalendar,
      disconnectAllCalendars
    };
  }
};
</script>

<style lang="scss" scoped>
.calendar-manager {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem;
  
  .calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    
    h2 {
      margin: 0;
    }
  }
  
  .loading-indicator,
  .error-message,
  .no-calendars {
    text-align: center;
    padding: 2rem;
    background-color: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 2rem;
  }
  
  .calendar-list {
    .calendar-item {
      background-color: #fff;
      border: 1px solid #e9ecef;
      border-radius: 8px;
      padding: 1.5rem;
      margin-bottom: 1rem;
      
      .calendar-info {
        display: flex;
        align-items: flex-start;
        margin-bottom: 1rem;
        
        .calendar-color {
          width: 16px;
          height: 16px;
          border-radius: 4px;
          margin-right: 1rem;
          margin-top: 4px;
        }
        
        .calendar-details {
          flex: 1;
          
          h3 {
            margin: 0 0 0.5rem;
          }
          
          p {
            margin: 0;
            color: #6c757d;
            
            &.calendar-id {
              font-size: 0.875rem;
              margin-top: 0.5rem;
            }
          }
        }
      }
      
      .calendar-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
        
        .calendar-toggle {
          .toggle-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            
            .toggle-text {
              font-size: 0.875rem;
              color: #6c757d;
            }
          }
        }
      }
    }
  }
  
  .calendar-actions-footer {
    margin-top: 2rem;
    text-align: center;
  }
  
  .btn-primary,
  .btn-secondary,
  .btn-danger {
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    
    &:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
  }
  
  .btn-primary {
    background-color: #007bff;
    color: #fff;
    border: none;
    
    &:hover:not(:disabled) {
      background-color: #0056b3;
    }
  }
  
  .btn-secondary {
    background-color: #6c757d;
    color: #fff;
    border: none;
    
    &:hover:not(:disabled) {
      background-color: #5a6268;
    }
  }
  
  .btn-danger {
    background-color: #dc3545;
    color: #fff;
    border: none;
    
    &:hover:not(:disabled) {
      background-color: #c82333;
    }
  }
}
</style>
