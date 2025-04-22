&lt;template&gt;
  &lt;div class="calendar-manager"&gt;
    &lt;div class="section-header"&gt;
      &lt;h2&gt;Manage Your Calendars&lt;/h2&gt;
      &lt;div class="actions"&gt;
        &lt;button 
          @click="connectNewCalendar" 
          class="btn-connect"
          :disabled="isLoading"
        &gt;
          &lt;i class="fas fa-plus-circle"&gt;&lt;/i&gt; Connect New Calendar
        &lt;/button&gt;
      &lt;/div&gt;
    &lt;/div&gt;
    
    &lt;div v-if="isLoading" class="loading-indicator"&gt;
      &lt;p&gt;Loading your calendars...&lt;/p&gt;
    &lt;/div&gt;
    
    &lt;div v-else-if="!hasCalendars" class="no-calendars"&gt;
      &lt;div class="empty-state"&gt;
        &lt;i class="fas fa-calendar-alt empty-icon"&gt;&lt;/i&gt;
        &lt;h3&gt;No Calendars Connected&lt;/h3&gt;
        &lt;p&gt;Connect your Google Calendars to start grading your schedule.&lt;/p&gt;
        &lt;button @click="connectNewCalendar" class="btn-connect-large"&gt;
          Connect Google Calendar
        &lt;/button&gt;
      &lt;/div&gt;
    &lt;/div&gt;
    
    &lt;div v-else class="calendars-list"&gt;
      &lt;div 
        v-for="calendar in calendars" 
        :key="calendar.id" 
        class="calendar-item"
        :class="{ 'primary-calendar': calendar.is_primary }"
      &gt;
        &lt;div class="calendar-color" :style="{ backgroundColor: calendar.color || '#4285F4' }"&gt;&lt;/div&gt;
        &lt;div class="calendar-info"&gt;
          &lt;h3&gt;{{ calendar.name }}&lt;/h3&gt;
          &lt;p v-if="calendar.description" class="calendar-description"&gt;{{ calendar.description }}&lt;/p&gt;
          &lt;span v-if="calendar.is_primary" class="primary-badge"&gt;Primary&lt;/span&gt;
        &lt;/div&gt;
        &lt;div class="calendar-controls"&gt;
          &lt;div class="control-group"&gt;
            &lt;label class="control-label"&gt;Include in Grading:&lt;/label&gt;
            &lt;label class="toggle-switch"&gt;
              &lt;input 
                type="checkbox" 
                :checked="calendar.is_selected" 
                @change="updateSelection(calendar.id, $event.target.checked)"
              &gt;
              &lt;span class="toggle-slider"&gt;&lt;/span&gt;
            &lt;/label&gt;
          &lt;/div&gt;
          &lt;div class="control-group"&gt;
            &lt;label class="control-label"&gt;Visible in Calendar:&lt;/label&gt;
            &lt;label class="toggle-switch"&gt;
              &lt;input 
                type="checkbox" 
                :checked="calendar.is_visible" 
                @change="updateVisibility(calendar.id, $event.target.checked)"
              &gt;
              &lt;span class="toggle-slider"&gt;&lt;/span&gt;
            &lt;/label&gt;
          &lt;/div&gt;
          &lt;button 
            @click="disconnectCalendar(calendar.id)" 
            class="btn-disconnect"
            :disabled="isProcessing"
          &gt;
            Disconnect
          &lt;/button&gt;
        &lt;/div&gt;
      &lt;/div&gt;
    &lt;/div&gt;
  &lt;/div&gt;
&lt;/template&gt;

&lt;script&gt;
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
          // Redirect to Google OAuth
          window.location.href = response.data.auth_url;
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
    
    // Update calendar selection (include in grading)
    const updateSelection = async (calendarId, isSelected) => {
      isProcessing.value = true;
      try {
        const response = await axios.post('/api/calendars/selection', {
          calendar_id: calendarId,
          is_selected: isSelected
        });
        
        if (response.data.success) {
          // Update local state
          const index = calendars.value.findIndex(cal => cal.id === calendarId);
          if (index !== -1) {
            calendars.value[index].is_selected = isSelected;
          }
        } else {
          error.value = response.data.error || 'Failed to update selection';
        }
      } catch (err) {
        console.error('Error updating selection:', err);
        error.value = 'Failed to update selection';
      } finally {
        isProcessing.value = false;
      }
    };
    
    // Update calendar visibility
    const updateVisibility = async (calendarId, isVisible) => {
      isProcessing.value = true;
      try {
        const response = await axios.post('/api/calendars/visibility', {
          calendar_id: calendarId,
          is_visible: isVisible
        });
        
        if (response.data.success) {
          // Update local state
          const index = calendars.value.findIndex(cal => cal.id === calendarId);
          if (index !== -1) {
            calendars.value[index].is_visible = isVisible;
          }
        } else {
          error.value = response.data.error || 'Failed to update visibility';
        }
      } catch (err) {
        console.error('Error updating visibility:', err);
        error.value = 'Failed to update visibility';
      } finally {
        isProcessing.value = false;
      }
    };
    
    // Disconnect a calendar
    const disconnectCalendar = async (calendarId) => {
      if (!confirm('Are you sure you want to disconnect this calendar? This action cannot be undone.')) {
        return;
      }
      
      isProcessing.value = true;
      try {
        const response = await axios.post('/api/calendars/disconnect', {
          calendar_id: calendarId
        });
        
        if (response.data.success) {
          // Remove from local state
          calendars.value = calendars.value.filter(cal => cal.id !== calendarId);
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
    
    onMounted(() => {
      fetchCalendars();
    });
    
    return {
      calendars,
      isLoading,
      isProcessing,
      error,
      hasCalendars,
      connectNewCalendar,
      updateSelection,
      updateVisibility,
      disconnectCalendar
    };
  }
};
&lt;/script&gt;

&lt;style lang="scss" scoped&gt;
.calendar-manager {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  margin-bottom: 2rem;
  
  .section-header {
    padding: 1.5rem;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
    
    h2 {
      margin: 0;
      font-size: 1.5rem;
      color: #333;
    }
    
    .actions {
      display: flex;
      gap: 0.75rem;
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
    }
  }
  
  .calendars-list {
    padding: 1rem;
    
    .calendar-item {
      display: flex;
      align-items: center;
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1rem;
      border: 1px solid #eee;
      transition: box-shadow 0.2s;
      
      &:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      }
      
      &.primary-calendar {
        background-color: #f8f9fa;
      }
      
      .calendar-color {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        margin-right: 1rem;
      }
      
      .calendar-info {
        flex: 1;
        
        h3 {
          margin: 0;
          font-size: 1rem;
          font-weight: 500;
          color: #333;
        }
        
        .calendar-description {
          margin: 0.25rem 0 0 0;
          font-size: 0.85rem;
          color: #666;
        }
        
        .primary-badge {
          display: inline-block;
          background-color: #e8f0fe;
          color: #1a73e8;
          font-size: 0.7rem;
          padding: 0.15rem 0.5rem;
          border-radius: 12px;
          margin-top: 0.25rem;
        }
      }
      
      .calendar-controls {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        
        .control-group {
          display: flex;
          align-items: center;
          gap: 0.5rem;
          
          .control-label {
            font-size: 0.85rem;
            color: #555;
          }
        }
      }
    }
  }
  
  .btn-connect, .btn-disconnect {
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: background-color 0.2s;
    
    &:disabled {
      opacity: 0.7;
      cursor: not-allowed;
    }
  }
  
  .btn-connect {
    background-color: #4a90e2;
    color: white;
    
    &:hover:not(:disabled) {
      background-color: #3a80d2;
    }
    
    i {
      margin-right: 0.25rem;
    }
  }
  
  .btn-connect-large {
    padding: 0.75rem 1.5rem;
    border-radius: 4px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    border: none;
    background-color: #4a90e2;
    color: white;
    transition: background-color 0.2s;
    
    &:hover {
      background-color: #3a80d2;
    }
  }
  
  .btn-disconnect {
    background-color: transparent;
    color: #d32f2f;
    
    &:hover:not(:disabled) {
      background-color: #ffebee;
    }
  }
  
  .toggle-switch {
    position: relative;
    display: inline-block;
    width: 36px;
    height: 20px;
    
    input {
      opacity: 0;
      width: 0;
      height: 0;
      
      &:checked + .toggle-slider {
        background-color: #4a90e2;
      }
      
      &:checked + .toggle-slider:before {
        transform: translateX(16px);
      }
      
      &:disabled + .toggle-slider {
        opacity: 0.5;
        cursor: not-allowed;
      }
    }
    
    .toggle-slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #ccc;
      transition: .4s;
      border-radius: 20px;
      
      &:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 2px;
        bottom: 2px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
      }
    }
  }
}
&lt;/style&gt;
