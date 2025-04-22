<template>
  <div class="google-calendar-connect">
    <div v-if="loading" class="loading-indicator">
      <p>Checking Google Calendar connection...</p>
    </div>
    
    <div v-else-if="connected" class="connection-status connected">
      <div class="status-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
          <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
      </div>
      <div class="status-text">
        <h3>Google Calendar Connected</h3>
        <p>Your Google Calendar is successfully connected to Own My Calendar.</p>
      </div>
      <div class="connection-actions">
        <button @click="disconnectGoogleCalendar" class="btn-secondary">Disconnect</button>
      </div>
    </div>
    
    <div v-else class="connection-status not-connected">
      <div class="status-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
          <line x1="16" y1="2" x2="16" y2="6"></line>
          <line x1="8" y1="2" x2="8" y2="6"></line>
          <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
      </div>
      <div class="status-text">
        <h3>Connect Google Calendar</h3>
        <p>Connect your Google Calendar to enable calendar grading based on your schedule.</p>
      </div>
      <div class="connection-actions">
        <button @click="connectGoogleCalendar" class="btn-primary">Connect Google Calendar</button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';

export default {
  name: 'GoogleCalendarConnect',
  emits: ['connection-changed'],
  
  setup(props, { emit }) {
    const loading = ref(true);
    const connected = ref(false);
    
    const checkConnection = async () => {
      loading.value = true;
      try {
        const response = await axios.get('/api/google/check-connection');
        connected.value = response.data.connected;
        emit('connection-changed', connected.value);
      } catch (error) {
        console.error('Failed to check Google Calendar connection:', error);
        connected.value = false;
        emit('connection-changed', false);
      } finally {
        loading.value = false;
      }
    };
    
    const connectGoogleCalendar = async () => {
      try {
        const response = await axios.get('/api/google/redirect');
        // Open the Google OAuth consent screen in a new window
        window.open(response.data.auth_url, '_blank');
        
        // Poll for connection status changes
        const pollInterval = setInterval(async () => {
          const checkResponse = await axios.get('/api/google/check-connection');
          if (checkResponse.data.connected) {
            clearInterval(pollInterval);
            connected.value = true;
            emit('connection-changed', true);
          }
        }, 5000); // Check every 5 seconds
        
        // Stop polling after 5 minutes (300 seconds)
        setTimeout(() => {
          clearInterval(pollInterval);
        }, 300000);
        
      } catch (error) {
        console.error('Failed to connect Google Calendar:', error);
      }
    };
    
    const disconnectGoogleCalendar = async () => {
      try {
        await axios.post('/api/google/disconnect');
        connected.value = false;
        emit('connection-changed', false);
      } catch (error) {
        console.error('Failed to disconnect Google Calendar:', error);
      }
    };
    
    onMounted(() => {
      checkConnection();
    });
    
    return {
      loading,
      connected,
      connectGoogleCalendar,
      disconnectGoogleCalendar
    };
  }
};
</script>

<style lang="scss" scoped>
.google-calendar-connect {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  padding: 1.5rem;
  margin-bottom: 2rem;
  
  .loading-indicator {
    text-align: center;
    padding: 1rem;
    color: #666;
  }
  
  .connection-status {
    display: flex;
    align-items: center;
    
    .status-icon {
      margin-right: 1.5rem;
      
      svg {
        width: 40px;
        height: 40px;
      }
    }
    
    .status-text {
      flex-grow: 1;
      
      h3 {
        margin-bottom: 0.5rem;
      }
      
      p {
        color: #666;
        margin-bottom: 0;
      }
    }
    
    &.connected {
      .status-icon {
        color: #4caf50;
      }
    }
    
    &.not-connected {
      .status-icon {
        color: #4a90e2;
      }
    }
  }
  
  .connection-actions {
    .btn-primary {
      background-color: #4a90e2;
      color: white;
      border: none;
      padding: 0.75rem 1.5rem;
      font-size: 1rem;
      border-radius: 4px;
      cursor: pointer;
      
      &:hover {
        background-color: #3a80d2;
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
      
      &:hover {
        background-color: #e0e0e0;
      }
    }
  }
}
</style>
