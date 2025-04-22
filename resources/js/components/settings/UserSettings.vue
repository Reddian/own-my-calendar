&lt;template&gt;
  &lt;div class="user-settings"&gt;
    &lt;div class="settings-header"&gt;
      &lt;h2&gt;User Settings&lt;/h2&gt;
    &lt;/div&gt;
    
    &lt;div class="settings-content"&gt;
      &lt;div class="settings-nav"&gt;
        &lt;div 
          v-for="section in sections" 
          :key="section.id" 
          class="nav-item"
          :class="{ active: activeSection === section.id }"
          @click="setActiveSection(section.id)"
        &gt;
          &lt;i :class="section.icon"&gt;&lt;/i&gt;
          {{ section.name }}
        &lt;/div&gt;
      &lt;/div&gt;
      
      &lt;div class="settings-panel"&gt;
        &lt;!-- Profile Settings --&gt;
        &lt;div v-if="activeSection === 'profile'" class="settings-section"&gt;
          &lt;h3&gt;Profile Settings&lt;/h3&gt;
          
          &lt;form @submit.prevent="updateProfile" class="settings-form"&gt;
            &lt;div class="form-group"&gt;
              &lt;label for="name"&gt;Name&lt;/label&gt;
              &lt;input 
                type="text" 
                id="name" 
                v-model="profile.name" 
                class="form-control"
                required
              &gt;
            &lt;/div&gt;
            
            &lt;div class="form-group"&gt;
              &lt;label for="email"&gt;Email Address&lt;/label&gt;
              &lt;input 
                type="email" 
                id="email" 
                v-model="profile.email" 
                class="form-control"
                required
              &gt;
            &lt;/div&gt;
            
            &lt;div class="form-actions"&gt;
              &lt;button 
                type="submit" 
                class="btn-save"
                :disabled="isUpdating"
              &gt;
                {{ isUpdating ? 'Saving...' : 'Save Changes' }}
              &lt;/button&gt;
            &lt;/div&gt;
          &lt;/form&gt;
        &lt;/div&gt;
        
        &lt;!-- Password Settings --&gt;
        &lt;div v-if="activeSection === 'password'" class="settings-section"&gt;
          &lt;h3&gt;Change Password&lt;/h3&gt;
          
          &lt;form @submit.prevent="updatePassword" class="settings-form"&gt;
            &lt;div class="form-group"&gt;
              &lt;label for="current_password"&gt;Current Password&lt;/label&gt;
              &lt;input 
                type="password" 
                id="current_password" 
                v-model="passwordForm.current_password" 
                class="form-control"
                required
              &gt;
            &lt;/div&gt;
            
            &lt;div class="form-group"&gt;
              &lt;label for="new_password"&gt;New Password&lt;/label&gt;
              &lt;input 
                type="password" 
                id="new_password" 
                v-model="passwordForm.new_password" 
                class="form-control"
                required
                minlength="8"
              &gt;
              &lt;div class="form-hint"&gt;Password must be at least 8 characters long&lt;/div&gt;
            &lt;/div&gt;
            
            &lt;div class="form-group"&gt;
              &lt;label for="new_password_confirmation"&gt;Confirm New Password&lt;/label&gt;
              &lt;input 
                type="password" 
                id="new_password_confirmation" 
                v-model="passwordForm.new_password_confirmation" 
                class="form-control"
                required
              &gt;
            &lt;/div&gt;
            
            &lt;div class="form-actions"&gt;
              &lt;button 
                type="submit" 
                class="btn-save"
                :disabled="isUpdating || !passwordsMatch"
              &gt;
                {{ isUpdating ? 'Updating...' : 'Update Password' }}
              &lt;/button&gt;
            &lt;/div&gt;
            
            &lt;div v-if="!passwordsMatch" class="form-error"&gt;
              Passwords do not match
            &lt;/div&gt;
          &lt;/form&gt;
        &lt;/div&gt;
        
        &lt;!-- Calendar Connections --&gt;
        &lt;div v-if="activeSection === 'calendars'" class="settings-section"&gt;
          &lt;h3&gt;Google Calendar Connections&lt;/h3&gt;
          
          &lt;div v-if="isLoading" class="loading-indicator"&gt;
            &lt;p&gt;Loading your calendar connections...&lt;/p&gt;
          &lt;/div&gt;
          
          &lt;div v-else-if="!hasCalendars" class="no-calendars"&gt;
            &lt;div class="empty-state"&gt;
              &lt;i class="fas fa-calendar-alt empty-icon"&gt;&lt;/i&gt;
              &lt;h4&gt;No Calendars Connected&lt;/h4&gt;
              &lt;p&gt;Connect your Google Calendars to start grading your schedule.&lt;/p&gt;
              &lt;button @click="connectNewCalendar" class="btn-connect"&gt;
                Connect Google Calendar
              &lt;/button&gt;
            &lt;/div&gt;
          &lt;/div&gt;
          
          &lt;div v-else class="calendars-list"&gt;
            &lt;div class="list-header"&gt;
              &lt;div class="list-actions"&gt;
                &lt;button @click="connectNewCalendar" class="btn-connect-small"&gt;
                  &lt;i class="fas fa-plus-circle"&gt;&lt;/i&gt; Add Calendar
                &lt;/button&gt;
              &lt;/div&gt;
            &lt;/div&gt;
            
            &lt;div 
              v-for="calendar in calendars" 
              :key="calendar.id" 
              class="calendar-item"
              :class="{ 'primary-calendar': calendar.is_primary }"
            &gt;
              &lt;div class="calendar-color" :style="{ backgroundColor: calendar.color || '#4285F4' }"&gt;&lt;/div&gt;
              &lt;div class="calendar-info"&gt;
                &lt;h4&gt;{{ calendar.name }}&lt;/h4&gt;
                &lt;p v-if="calendar.description" class="calendar-description"&gt;{{ calendar.description }}&lt;/p&gt;
                &lt;span v-if="calendar.is_primary" class="primary-badge"&gt;Primary&lt;/span&gt;
              &lt;/div&gt;
              &lt;div class="calendar-actions"&gt;
                &lt;button 
                  @click="disconnectCalendar(calendar.id)" 
                  class="btn-disconnect"
                  :disabled="isProcessing"
                &gt;
                  Disconnect
                &lt;/button&gt;
              &lt;/div&gt;
            &lt;/div&gt;
            
            &lt;div class="disconnect-all"&gt;
              &lt;button 
                @click="disconnectAllCalendars" 
                class="btn-disconnect-all"
                :disabled="isProcessing"
              &gt;
                Disconnect All Calendars
              &lt;/button&gt;
            &lt;/div&gt;
          &lt;/div&gt;
        &lt;/div&gt;
        
        &lt;!-- Notification Settings --&gt;
        &lt;div v-if="activeSection === 'notifications'" class="settings-section"&gt;
          &lt;h3&gt;Email Notification Settings&lt;/h3&gt;
          
          &lt;form @submit.prevent="updateNotifications" class="settings-form"&gt;
            &lt;div class="form-group checkbox-group"&gt;
              &lt;label class="checkbox-label"&gt;
                &lt;input 
                  type="checkbox" 
                  v-model="notifications.weekly_grade" 
                &gt;
                &lt;span class="checkbox-text"&gt;
                  &lt;strong&gt;Weekly Grade Updates&lt;/strong&gt;
                  &lt;span class="checkbox-description"&gt;Receive your calendar grade and recommendations every week&lt;/span&gt;
                &lt;/span&gt;
              &lt;/label&gt;
            &lt;/div&gt;
            
            &lt;div v-if="notifications.weekly_grade" class="form-group time-select"&gt;
              &lt;label for="weekly_grade_day"&gt;Day of Week&lt;/label&gt;
              &lt;select 
                id="weekly_grade_day" 
                v-model="notifications.weekly_grade_day" 
                class="form-control"
              &gt;
                &lt;option value="1"&gt;Monday&lt;/option&gt;
                &lt;option value="2"&gt;Tuesday&lt;/option&gt;
                &lt;option value="3"&gt;Wednesday&lt;/option&gt;
                &lt;option value="4"&gt;Thursday&lt;/option&gt;
                &lt;option value="5"&gt;Friday&lt;/option&gt;
                &lt;option value="6"&gt;Saturday&lt;/option&gt;
                &lt;option value="0"&gt;Sunday&lt;/option&gt;
              &lt;/select&gt;
              
              &lt;label for="weekly_grade_time"&gt;Time&lt;/label&gt;
              &lt;select 
                id="weekly_grade_time" 
                v-model="notifications.weekly_grade_time" 
                class="form-control"
              &gt;
                &lt;option v-for="time in timeOptions" :key="time.value" :value="time.value"&gt;
                  {{ time.label }}
                &lt;/option&gt;
              &lt;/select&gt;
            &lt;/div&gt;
            
            &lt;div class="form-group checkbox-group"&gt;
              &lt;label class="checkbox-label"&gt;
                &lt;input 
                  type="checkbox" 
                  v-model="notifications.weekly_reminder" 
                &gt;
                &lt;span class="checkbox-text"&gt;
                  &lt;strong&gt;Weekly Planning Reminder&lt;/strong&gt;
                  &lt;span class="checkbox-description"&gt;Receive a reminder to plan your week&lt;/span&gt;
                &lt;/span&gt;
              &lt;/label&gt;
            &lt;/div&gt;
            
            &lt;div v-if="notifications.weekly_reminder" class="form-group time-select"&gt;
              &lt;label for="weekly_reminder_day"&gt;Day of Week&lt;/label&gt;
              &lt;select 
                id="weekly_reminder_day" 
                v-model="notifications.weekly_reminder_day" 
                class="form-control"
              &gt;
                &lt;option value="1"&gt;Monday&lt;/option&gt;
                &lt;option value="2"&gt;Tuesday&lt;/option&gt;
                &lt;option value="3"&gt;Wednesday&lt;/option&gt;
                &lt;option value="4"&gt;Thursday&lt;/option&gt;
                &lt;option value="5"&gt;Friday&lt;/option&gt;
                &lt;option value="6"&gt;Saturday&lt;/option&gt;
                &lt;option value="0"&gt;Sunday&lt;/option&gt;
              &lt;/select&gt;
              
              &lt;label for="weekly_reminder_time"&gt;Time&lt;/label&gt;
              &lt;select 
                id="weekly_reminder_time" 
                v-model="notifications.weekly_reminder_time" 
                class="form-control"
              &gt;
                &lt;option v-for="time in timeOptions" :key="time.value" :value="time.value"&gt;
                  {{ time.label }}
                &lt;/option&gt;
              &lt;/select&gt;
            &lt;/div&gt;
            
            &lt;div class="form-group"&gt;
              &lt;label for="timezone"&gt;Your Timezone&lt;/label&gt;
              &lt;select 
                id="timezone" 
                v-model="notifications.timezone" 
                class="form-control"
              &gt;
                &lt;option v-for="tz in timezones" :key="tz.value" :value="tz.value"&gt;
                  {{ tz.label }}
                &lt;/option&gt;
              &lt;/select&gt;
            &lt;/div&gt;
            
            &lt;div class="form-actions"&gt;
              &lt;button 
                type="submit" 
                class="btn-save"
                :disabled="isUpdating"
              &gt;
                {{ isUpdating ? 'Saving...' : 'Save Notification Settings' }}
              &lt;/button&gt;
            &lt;/div&gt;
          &lt;/form&gt;
        &lt;/div&gt;
        
        &lt;!-- Subscription Settings --&gt;
        &lt;div v-if="activeSection === 'subscription'" class="settings-section"&gt;
          &lt;h3&gt;Subscription Settings&lt;/h3&gt;
          
          &lt;div v-if="isLoading" class="loading-indicator"&gt;
            &lt;p&gt;Loading your subscription details...&lt;/p&gt;
          &lt;/div&gt;
          
          &lt;div v-else class="subscription-details"&gt;
            &lt;div class="current-plan" :class="{ 'premium-plan': isPremium, 'free-plan': !isPremium }"&gt;
              &lt;div class="plan-badge"&gt;{{ isPremium ? 'Premium' : 'Free' }}&lt;/div&gt;
              &lt;h4&gt;{{ isPremium ? 'Premium Plan' : 'Free Plan' }}&lt;/h4&gt;
              &lt;p v-if="isPremium"&gt;You have unlimited calendar grades with your premium subscription.&lt;/p&gt;
              &lt;p v-else&gt;You have used {{ subscription.grades_used }} of {{ subscription.grades_limit }} free calendar grades.&lt;/p&gt;
              
              &lt;div v-if="subscription && subscription.trial" class="trial-notice"&gt;
                &lt;p&gt;Your trial ends on {{ formatDate(subscription.trial_ends_at) }}&lt;/p&gt;
              &lt;/div&gt;
              
              &lt;div v-if="subscription && subscription.canceled" class="cancellation-notice"&gt;
                &lt;p&gt;Your subscription will end on {{ formatDate(subscription.ends_at) }}&lt;/p&gt;
              &lt;/div&gt;
            &lt;/div&gt;
            
            &lt;div class="subscription-actions"&gt;
              &lt;button 
                v-if="!isPremium" 
                @click="upgradeSubscription" 
                class="btn-upgrade"
                :disabled="isProcessing"
              &gt;
                {{ isProcessing ? 'Processing...' : 'Upgrade to Premium - $9/month' }}
              &lt;/button&gt;
              
              &lt;button 
                v-if="isPremium && !subscription.canceled" 
                @click="cancelSubscription" 
                class="btn-cancel"
                :disabled="isProcessing"
              &gt;
                {{ isProcessing ? 'Processing...' : 'Cancel Subscription' }}
              &lt;/button&gt;
            &lt;/div&gt;
          &lt;/div&gt;
        &lt;/div&gt;
      &lt;/div&gt;
    &lt;/div&gt;
  &lt;/div&gt;
&lt;/template&gt;

&lt;script&gt;
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

export default {
  name: 'UserSettings',
  
  setup() {
    // Navigation
    const sections = [
      { id: 'profile', name: 'Profile', icon: 'fas fa-user' },
      { id: 'password', name: 'Password', icon: 'fas fa-lock' },
      { id: 'calendars', name: 'Calendars', icon: 'fas fa-calendar-alt' },
      { id: 'notifications', name: 'Notifications', icon: 'fas fa-bell' },
      { id: 'subscription', name: 'Subscription', icon: 'fas fa-credit-card' }
    ];
    const activeSection = ref('profile');
    
    // State
    const isLoading = ref(true);
    const isUpdating = ref(false);
    const isProcessing = ref(false);
    const error = ref(null);
    
    // Profile
    const profile = ref({
      name: '',
      email: ''
    });
    
    // Password
    const passwordForm = ref({
      current_password: '',
      new_password: '',
      new_password_confirmation: ''
    });
    
    // Calendars
    const calendars = ref([]);
    const hasCalendars = computed(() => calendars.value.length > 0);
    
    // Notifications
    const notifications = ref({
      weekly_grade: false,
      weekly_grade_day: '1', // Monday
      weekly_grade_time: '09:00',
      weekly_reminder: false,
      weekly_reminder_day: '0', // Sunday
      weekly_reminder_time: '18:00',
      timezone: 'America/New_York'
    });
    
    // Time options for dropdowns
    const timeOptions = Array.from({ length: 24 }, (_, hour) => {
      return [
        { value: `${hour.toString().padStart(2, '0')}:00`, label: `${hour % 12 || 12}:00 ${hour < 12 ? 'AM' : 'PM'}` },
        { value: `${hour.toString().padStart(2, '0')}:30`, label: `${hour % 12 || 12}:30 ${hour < 12 ? 'AM' : 'PM'}` }
      ];
    }).flat();
    
    // Timezone options
    const timezones = [
      { value: 'America/New_York', label: 'Eastern Time (ET)' },
      { value: 'America/Chicago', label: 'Central Time (CT)' },
      { value: 'America/Denver', label: 'Mountain Time (MT)' },
      { value: 'America/Los_Angeles', label: 'Pacific Time (PT)' },
      { value: 'America/Anchorage', label: 'Alaska Time (AKT)' },
      { value: 'Pacific/Honolulu', label: 'Hawaii Time (HT)' },
      { value: 'Europe/London', label: 'Greenwich Mean Time (GMT)' },
      { value: 'Europe/Paris', label: 'Central European Time (CET)' },
      { value: 'Asia/Tokyo', label: 'Japan Standard Time (JST)' },
      { value: 'Australia/Sydney', label: 'Australian Eastern Time (AET)' }
    ];
    
    // Subscription
    const subscription = ref(null);
    const isPremium = computed(() => {
      if (!subscription.value) return false;
      return subscription.value.plan === 'premium';
    });
    
    // Computed
    const passwordsMatch = computed(() => {
      return passwordForm.value.new_password === passwordForm.value.new_password_confirmation;
    });
    
    // Methods
    const setActiveSection = (section) => {
      activeSection.value = section;
      
      // Load section-specific data
      if (section === 'profile') {
        fetchProfile();
      } else if (section === 'calendars') {
        fetchCalendars();
      } else if (section === 'notifications') {
        fetchNotifications();
      } else if (section === 'subscription') {
        fetchSubscription();
      }
    };
    
    // Profile methods
    const fetchProfile = async () => {
      isLoading.value = true;
      try {
        const response = await axios.get('/api/profile/me');
        profile.value = response.data;
      } catch (err) {
        console.error('Error fetching profile:', err);
        error.value = 'Failed to load profile';
      } finally {
        isLoading.value = false;
      }
    };
    
    const updateProfile = async () => {
      isUpdating.value = true;
      try {
        const response = await axios.put('/api/profile/me', profile.value);
        if (response.data.success) {
          // Show success message
          alert('Profile updated successfully');
        } else {
          error.value = response.data.error || 'Failed to update profile';
        }
      } catch (err) {
        console.error('Error updating profile:', err);
        error.value = 'Failed to update profile';
      } finally {
        isUpdating.value = false;
      }
    };
    
    // Password methods
    const updatePassword = async () => {
      if (!passwordsMatch.value) return;
      
      isUpdating.value = true;
      try {
        const response = await axios.put('/api/profile/password', passwordForm.value);
        if (response.data.success) {
          // Show success message and clear form
          alert('Password updated successfully');
          passwordForm.value = {
            current_password: '',
            new_password: '',
            new_password_confirmation: ''
          };
        } else {
          error.value = response.data.error || 'Failed to update password';
        }
      } catch (err) {
        console.error('Error updating password:', err);
        error.value = 'Failed to update password';
      } finally {
        isUpdating.value = false;
      }
    };
    
    // Calendar methods
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
    
    const disconnectAllCalendars = async () => {
      if (!confirm('Are you sure you want to disconnect all calendars? This action cannot be undone.')) {
        return;
      }
      
      isProcessing.value = true;
      try {
        const response = await axios.post('/api/calendars/disconnect-all');
        
        if (response.data.success) {
          // Clear local state
          calendars.value = [];
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
    
    // Notification methods
    const fetchNotifications = async () => {
      isLoading.value = true;
      try {
        const response = await axios.get('/api/notifications/settings');
        if (response.data.success) {
          notifications.value = {
            ...notifications.value,
            ...response.data.settings
          };
        } else {
          error.value = response.data.error || 'Failed to load notification settings';
        }
      } catch (err) {
        console.error('Error fetching notification settings:', err);
        error.value = 'Failed to load notification settings';
      } finally {
        isLoading.value = false;
      }
    };
    
    const updateNotifications = async () => {
      isUpdating.value = true;
      try {
        const response = await axios.put('/api/notifications/settings', notifications.value);
        if (response.data.success) {
          // Show success message
          alert('Notification settings updated successfully');
        } else {
          error.value = response.data.error || 'Failed to update notification settings';
        }
      } catch (err) {
        console.error('Error updating notification settings:', err);
        error.value = 'Failed to update notification settings';
      } finally {
        isUpdating.value = false;
      }
    };
    
    // Subscription methods
    const fetchSubscription = async () => {
      isLoading.value = true;
      try {
        const response = await axios.get('/api/subscription');
        if (response.data.success) {
          subscription.value = response.data;
        } else {
          error.value = response.data.error || 'Failed to load subscription details';
        }
      } catch (err) {
        console.error('Error fetching subscription:', err);
        error.value = 'Failed to load subscription details';
      } finally {
        isLoading.value = false;
      }
    };
    
    const upgradeSubscription = async () => {
      isProcessing.value = true;
      try {
        const response = await axios.post('/api/subscription/checkout');
        if (response.data.success) {
          // Redirect to Stripe checkout
          window.location.href = response.data.checkout_url;
        } else {
          error.value = response.data.error || 'Failed to create checkout session';
          isProcessing.value = false;
        }
      } catch (err) {
        console.error('Error creating checkout session:', err);
        error.value = 'Failed to create checkout session';
        isProcessing.value = false;
      }
    };
    
    const cancelSubscription = async () => {
      if (!confirm('Are you sure you want to cancel your subscription? You will lose access to premium features at the end of your billing period.')) {
        return;
      }
      
      isProcessing.value = true;
      try {
        const response = await axios.post('/api/subscription/cancel');
        if (response.data.success) {
          await fetchSubscription(); // Refresh subscription data
        } else {
          error.value = response.data.error || 'Failed to cancel subscription';
        }
      } catch (err) {
        console.error('Error cancelling subscription:', err);
        error.value = 'Failed to cancel subscription';
      } finally {
        isProcessing.value = false;
      }
    };
    
    // Utility methods
    const formatDate = (dateString) => {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
      });
    };
    
    onMounted(() => {
      fetchProfile();
    });
    
    return {
      // Navigation
      sections,
      activeSection,
      setActiveSection,
      
      // State
      isLoading,
      isUpdating,
      isProcessing,
      error,
      
      // Profile
      profile,
      fetchProfile,
      updateProfile,
      
      // Password
      passwordForm,
      passwordsMatch,
      updatePassword,
      
      // Calendars
      calendars,
      hasCalendars,
      fetchCalendars,
      connectNewCalendar,
      disconnectCalendar,
      disconnectAllCalendars,
      
      // Notifications
      notifications,
      timeOptions,
      timezones,
      fetchNotifications,
      updateNotifications,
      
      // Subscription
      subscription,
      isPremium,
      fetchSubscription,
      upgradeSubscription,
      cancelSubscription,
      formatDate
    };
  }
};
&lt;/script&gt;

&lt;style lang="scss" scoped&gt;
.user-settings {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  margin-bottom: 2rem;
  
  .settings-header {
    padding: 1.5rem;
    border-bottom: 1px solid #eee;
    
    h2 {
      margin: 0;
      font-size: 1.5rem;
      color: #333;
    }
  }
  
  .settings-content {
    display: flex;
    min-height: 500px;
    
    .settings-nav {
      width: 220px;
      border-right: 1px solid #eee;
      padding: 1.5rem 0;
      
      .nav-item {
        padding: 0.75rem 1.5rem;
        cursor: pointer;
        transition: background-color 0.2s;
        
        &:hover {
          background-color: #f5f7fa;
        }
        
        &.active {
          background-color: #e8f0fe;
          color: #4a90e2;
          font-weight: 500;
          border-right: 3px solid #4a90e2;
        }
        
        i {
          margin-right: 0.75rem;
          width: 16px;
          text-align: center;
        }
      }
    }
    
    .settings-panel {
      flex: 1;
      padding: 1.5rem;
      
      .settings-section {
        h3 {
          margin-top: 0;
          margin-bottom: 1.5rem;
          font-size: 1.2rem;
          color: #333;
        }
      }
      
      .loading-indicator {
        padding: 2rem;
        text-align: center;
        color: #666;
      }
      
      .settings-form {
        max-width: 500px;
        
        .form-group {
          margin-bottom: 1.5rem;
          
          label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #555;
          }
          
          .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            
            &:focus {
              outline: none;
              border-color: #4a90e2;
              box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
            }
          }
          
          .form-hint {
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: #666;
          }
          
          &.checkbox-group {
            .checkbox-label {
              display: flex;
              align-items: flex-start;
              cursor: pointer;
              
              input[type="checkbox"] {
                margin-top: 0.25rem;
                margin-right: 0.75rem;
              }
              
              .checkbox-text {
                display: flex;
                flex-direction: column;
                
                .checkbox-description {
                  font-size: 0.85rem;
                  color: #666;
                  font-weight: normal;
                  margin-top: 0.25rem;
                }
              }
            }
          }
          
          &.time-select {
            display: flex;
            gap: 1rem;
            
            label {
              margin-bottom: 0.5rem;
            }
            
            .form-control {
              flex: 1;
            }
          }
        }
        
        .form-actions {
          margin-top: 2rem;
          
          .btn-save {
            padding: 0.75rem 1.5rem;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            
            &:hover:not(:disabled) {
              background-color: #3a80d2;
            }
            
            &:disabled {
              opacity: 0.7;
              cursor: not-allowed;
            }
          }
        }
        
        .form-error {
          margin-top: 1rem;
          color: #d32f2f;
          font-size: 0.9rem;
        }
      }
      
      .no-calendars {
        .empty-state {
          text-align: center;
          max-width: 400px;
          margin: 2rem auto;
          
          .empty-icon {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 1rem;
          }
          
          h4 {
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
      
      .calendars-list {
        .list-header {
          margin-bottom: 1rem;
          display: flex;
          justify-content: flex-end;
          
          .btn-connect-small {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            background-color: #4a90e2;
            color: white;
            
            &:hover {
              background-color: #3a80d2;
            }
            
            i {
              margin-right: 0.25rem;
            }
          }
        }
        
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
            
            h4 {
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
          
          .calendar-actions {
            .btn-disconnect {
              padding: 0.5rem 1rem;
              border-radius: 4px;
              font-size: 0.9rem;
              background-color: transparent;
              color: #d32f2f;
              border: none;
              cursor: pointer;
              
              &:hover:not(:disabled) {
                background-color: #ffebee;
              }
              
              &:disabled {
                opacity: 0.7;
                cursor: not-allowed;
              }
            }
          }
        }
        
        .disconnect-all {
          margin-top: 2rem;
          text-align: center;
          
          .btn-disconnect-all {
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            font-size: 0.9rem;
            background-color: transparent;
            color: #d32f2f;
            border: 1px solid #ffcdd2;
            cursor: pointer;
            
            &:hover:not(:disabled) {
              background-color: #ffebee;
            }
            
            &:disabled {
              opacity: 0.7;
              cursor: not-allowed;
            }
          }
        }
      }
      
      .subscription-details {
        .current-plan {
          position: relative;
          padding: 1.5rem;
          border-radius: 8px;
          margin-bottom: 1.5rem;
          
          &.premium-plan {
            background-color: #f0f7ff;
            border: 1px solid #cce5ff;
          }
          
          &.free-plan {
            background-color: #f5f5f5;
            border: 1px solid #e0e0e0;
          }
          
          .plan-badge {
            position: absolute;
            top: -10px;
            right: 20px;
            background-color: #4a90e2;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            
            .free-plan & {
              background-color: #777;
            }
          }
          
          h4 {
            margin-top: 0;
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
          }
          
          p {
            margin-bottom: 0;
            color: #555;
          }
          
          .trial-notice, .cancellation-notice {
            margin-top: 1rem;
            padding: 0.75rem;
            border-radius: 4px;
            font-size: 0.9rem;
          }
          
          .trial-notice {
            background-color: #fff8e1;
            border: 1px solid #ffe082;
          }
          
          .cancellation-notice {
            background-color: #ffebee;
            border: 1px solid #ffcdd2;
          }
        }
        
        .subscription-actions {
          display: flex;
          flex-direction: column;
          gap: 0.75rem;
          max-width: 300px;
          
          button {
            padding: 0.75rem 1rem;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: background-color 0.2s;
            
            &:disabled {
              opacity: 0.7;
              cursor: not-allowed;
            }
          }
          
          .btn-upgrade {
            background-color: #4a90e2;
            color: white;
            
            &:hover:not(:disabled) {
              background-color: #3a80d2;
            }
          }
          
          .btn-cancel {
            background-color: #f5f5f5;
            color: #d32f2f;
            border: 1px solid #e0e0e0;
            
            &:hover:not(:disabled) {
              background-color: #ffebee;
            }
          }
        }
      }
    }
  }
}
&lt;/style&gt;
