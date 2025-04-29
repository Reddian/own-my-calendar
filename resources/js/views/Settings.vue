<template>
  <div class="settings-container">
    <h1 class="text-2xl font-bold mb-6">Settings</h1>
    
    <!-- Account Settings -->
    <div class="settings-section">
      <h2 class="text-xl font-semibold mb-4">Account Settings</h2>
      <Form @submit="handleUpdateProfile" v-slot="{ errors }" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Name</label>
          <Field 
            v-model="profileForm.name" 
            name="name"
            type="text" 
            rules="required|min:2|max:50"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            :class="{ 'border-red-500': errors.name }"
          />
          <ErrorMessage name="name" class="text-red-500 text-sm mt-1" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Email</label>
          <Field 
            v-model="profileForm.email" 
            name="email"
            type="email" 
            rules="required|email"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            :class="{ 'border-red-500': errors.email }"
          />
          <ErrorMessage name="email" class="text-red-500 text-sm mt-1" />
        </div>
        <button 
          type="submit" 
          class="btn-primary"
          :disabled="isLoading"
        >
          {{ isLoading ? 'Saving...' : 'Save Changes' }}
        </button>
      </Form>
    </div>

    <!-- Calendar Settings -->
    <div class="settings-section">
      <h2 class="text-xl font-semibold mb-4">Calendar Settings</h2>
      <Form @submit="handleUpdateCalendarSettings" v-slot="{ errors }" class="space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <label class="block text-sm font-medium text-gray-700">Auto-sync with Google Calendar</label>
            <p class="text-sm text-gray-500">Automatically sync your calendar events</p>
          </div>
          <Field 
            v-model="calendarForm.autoSync" 
            name="autoSync"
            type="checkbox"
            as="ToggleSwitch"
            :disabled="isLoading"
          />
        </div>
        <div class="flex items-center justify-between">
          <div>
            <label class="block text-sm font-medium text-gray-700">Show Weekends</label>
            <p class="text-sm text-gray-500">Display weekend days in calendar view</p>
          </div>
          <Field 
            v-model="calendarForm.showWeekends" 
            name="showWeekends"
            type="checkbox"
            as="ToggleSwitch"
            :disabled="isLoading"
          />
        </div>
        <button 
          type="submit" 
          class="btn-primary"
          :disabled="isLoading"
        >
          {{ isLoading ? 'Saving...' : 'Save Changes' }}
        </button>
      </Form>
    </div>

    <!-- Notification Settings -->
    <div class="settings-section">
      <h2 class="text-xl font-semibold mb-4">Notification Settings</h2>
      <Form @submit="handleUpdateNotificationSettings" v-slot="{ errors }" class="space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <label class="block text-sm font-medium text-gray-700">Email Notifications</label>
            <p class="text-sm text-gray-500">Receive notifications via email</p>
          </div>
          <Field 
            v-model="notificationForm.email" 
            name="emailNotifications"
            type="checkbox"
            as="ToggleSwitch"
            :disabled="isLoading"
          />
        </div>
        <div class="flex items-center justify-between">
          <div>
            <label class="block text-sm font-medium text-gray-700">Weekly Summary</label>
            <p class="text-sm text-gray-500">Get a weekly summary of your calendar</p>
          </div>
          <Field 
            v-model="notificationForm.weeklySummary" 
            name="weeklySummary"
            type="checkbox"
            as="ToggleSwitch"
            :disabled="isLoading"
          />
        </div>
        <button 
          type="submit" 
          class="btn-primary"
          :disabled="isLoading"
        >
          {{ isLoading ? 'Saving...' : 'Save Changes' }}
        </button>
      </Form>
    </div>

    <!-- Notification Component -->
    <Notification 
      v-if="notification.message" 
      :message="notification.message"
      :type="notification.type"
      :duration="notification.duration"
    />
  </div>
</template>

<script>
import { mapState, mapGetters, mapActions } from 'vuex';
import { Form, Field, ErrorMessage, configure } from 'vee-validate';
import * as rules from '@vee-validate/rules';
import ToggleSwitch from '@/components/ToggleSwitch.vue';
import Notification from '@/components/Notification.vue';

// Configure VeeValidate
Object.keys(rules).forEach(rule => {
  configure({
    validateOnBlur: true,
    validateOnChange: true,
    validateOnInput: false,
    validateOnModelUpdate: true,
  });
});

export default {
  name: 'Settings',
  components: {
    Form,
    Field,
    ErrorMessage,
    ToggleSwitch,
    Notification
  },
  data() {
    return {
      profileForm: {
        name: '',
        email: ''
      },
      calendarForm: {
        autoSync: false,
        showWeekends: true
      },
      notificationForm: {
        email: true,
        weeklySummary: true
      }
    };
  },
  computed: {
    ...mapState('settings', ['profile', 'calendar', 'notifications', 'isLoading', 'error']),
    ...mapState('notifications', ['notifications']),
    notification() {
      return this.notifications[0] || { message: '', type: '', duration: 3000 };
    }
  },
  created() {
    this.fetchProfile();
    this.fetchSettings();
  },
  methods: {
    ...mapActions('settings', [
      'fetchProfile',
      'updateProfile',
      'fetchSettings',
      'updateCalendarSettings',
      'updateNotificationSettings'
    ]),
    ...mapActions('notifications', ['addNotification']),
    async handleUpdateProfile(values, { resetForm }) {
      try {
        await this.updateProfile(this.profileForm);
        this.addNotification({
          message: 'Profile updated successfully',
          type: 'success'
        });
        resetForm();
      } catch (error) {
        this.addNotification({
          message: error.message || 'Failed to update profile',
          type: 'error'
        });
      }
    },
    async handleUpdateCalendarSettings(values, { resetForm }) {
      try {
        await this.updateCalendarSettings(this.calendarForm);
        this.addNotification({
          message: 'Calendar settings updated successfully',
          type: 'success'
        });
        resetForm();
      } catch (error) {
        this.addNotification({
          message: error.message || 'Failed to update calendar settings',
          type: 'error'
        });
      }
    },
    async handleUpdateNotificationSettings(values, { resetForm }) {
      try {
        await this.updateNotificationSettings(this.notificationForm);
        this.addNotification({
          message: 'Notification settings updated successfully',
          type: 'success'
        });
        resetForm();
      } catch (error) {
        this.addNotification({
          message: error.message || 'Failed to update notification settings',
          type: 'error'
        });
      }
    }
  },
  watch: {
    profile: {
      immediate: true,
      handler(newProfile) {
        if (newProfile) {
          this.profileForm = { ...newProfile };
        }
      }
    },
    calendar: {
      immediate: true,
      handler(newCalendar) {
        if (newCalendar) {
          this.calendarForm = { ...newCalendar };
        }
      }
    },
    notifications: {
      immediate: true,
      handler(newNotifications) {
        if (newNotifications) {
          this.notificationForm = { ...newNotifications };
        }
      }
    }
  }
};
</script>

<style scoped>
.settings-container {
  @apply max-w-4xl mx-auto p-6;
}

.settings-section {
  @apply bg-white rounded-lg shadow p-6 mb-6;
}

.btn-primary {
  @apply inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed;
}
</style> 