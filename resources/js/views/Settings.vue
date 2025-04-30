<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-10">
        <!-- Alert Messages for Google Calendar Callback -->
        <div v-if="googleSuccessMessage" class="alert alert-success alert-dismissible fade show" role="alert">
          {{ googleSuccessMessage }}
          <button type="button" class="btn-close" @click="clearGoogleMessages" aria-label="Close"></button>
        </div>
        <div v-if="googleErrorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
          {{ googleErrorMessage }}
          <button type="button" class="btn-close" @click="clearGoogleMessages" aria-label="Close"></button>
        </div>
        <!-- Placeholder for other general success/error messages -->
        <div v-if="generalErrorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
          {{ generalErrorMessage }}
          <button type="button" class="btn-close" @click="generalErrorMessage = ''" aria-label="Close"></button>
        </div>

        <div class="card">
          <div class="card-header">
            <h1>Settings</h1>
          </div>
          <div class="card-body">
            <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
              <!-- Tabs remain the same -->
              <li class="nav-item" role="presentation">
                <button class="nav-link" :class="{ active: activeTab === 'account' }" @click="setActiveTab('account')" type="button">Account</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" :class="{ active: activeTab === 'notifications' }" @click="setActiveTab('notifications')" type="button">Notifications</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" :class="{ active: activeTab === 'subscription' }" @click="setActiveTab('subscription')" type="button">Subscription</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" :class="{ active: activeTab === 'calendar' }" @click="setActiveTab('calendar')" type="button">Calendar Integration</button>
              </li>
            </ul>

            <div class="tab-content p-3" id="settingsTabsContent">
              <!-- Account Settings -->
              <div class="tab-pane fade" :class="{ 'show active': activeTab === 'account' }">
                <!-- Account form remains the same -->
                <h3>Account Information</h3>
                <form @submit.prevent="updateAccount">
                  <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" v-model="accountForm.name">
                  </div>
                  <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" v-model="accountForm.email">
                  </div>
                  <button type="submit" class="btn btn-primary">Update Account</button>
                </form>

                <hr class="my-4">

                <h3>Change Password</h3>
                <form @submit.prevent="updatePassword">
                  <div class="mb-3">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" class="form-control" id="current_password" v-model="passwordForm.current_password">
                  </div>
                  <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="password" v-model="passwordForm.password">
                  </div>
                  <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="password_confirmation" v-model="passwordForm.password_confirmation">
                  </div>
                  <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
              </div>

              <!-- Notification Settings -->
              <div class="tab-pane fade" :class="{ 'show active': activeTab === 'notifications' }">
                <!-- Notifications form remains the same -->
                <h3>Notification Preferences</h3>
                <form @submit.prevent="updateNotifications">
                  <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="weekly_grade_email" v-model="notificationForm.weekly_grade_email">
                    <label class="form-check-label" for="weekly_grade_email">Send me weekly grade emails</label>
                  </div>
                  <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="planning_reminder" v-model="notificationForm.planning_reminder">
                    <label class="form-check-label" for="planning_reminder">Send me weekly planning reminders</label>
                  </div>
                  <div class="mb-3">
                    <label for="reminder_day" class="form-label">Reminder Day</label>
                    <select class="form-select" id="reminder_day" v-model="notificationForm.reminder_day">
                      <option value="Sunday">Sunday</option>
                      <option value="Monday">Monday</option>
                      <option value="Tuesday">Tuesday</option>
                      <option value="Wednesday">Wednesday</option>
                      <option value="Thursday">Thursday</option>
                      <option value="Friday">Friday</option>
                      <option value="Saturday">Saturday</option>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label for="reminder_time" class="form-label">Reminder Time</label>
                    <input type="time" class="form-control" id="reminder_time" v-model="notificationForm.reminder_time">
                  </div>
                  <button type="submit" class="btn btn-primary">Save Notification Settings</button>
                </form>
              </div>

              <!-- Subscription Settings -->
              <div class="tab-pane fade" :class="{ 'show active': activeTab === 'subscription' }">
                <!-- Subscription content remains the same -->
                <h3>Subscription Status</h3>
                <div class="card mb-4">
                  <div class="card-body">
                    <div v-if="isSubscribed" class="subscription-status active">
                      <div class="status-icon"><i class="fas fa-check-circle"></i></div>
                      <div class="status-details">
                        <h4>Premium Plan</h4>
                        <p>Your subscription is active.</p>
                        <p class="text-muted">Next billing date: {{ nextBillingDate }}</p>
                      </div>
                    </div>
                    <div v-else class="subscription-status inactive">
                      <div class="status-icon"><i class="fas fa-info-circle"></i></div>
                      <div class="status-details">
                        <h4>Free Plan</h4>
                        <p>You are currently on the free plan.</p>
                        <p class="text-muted">{{ gradesRemaining }} grades remaining</p>
                      </div>
                    </div>

                    <div class="mt-4">
                      <button v-if="isSubscribed" type="button" class="btn btn-outline-danger" @click="openCancelModal">
                        Cancel Subscription
                      </button>
                      <router-link v-else to="/subscription" class="btn btn-primary">Upgrade to Premium</router-link>
                    </div>
                  </div>
                </div>

                <div v-if="isSubscribed" class="card">
                  <div class="card-header"><h5>Subscription Benefits</h5></div>
                  <div class="card-body">
                    <ul class="subscription-benefits">
                      <li><i class="fas fa-check text-success"></i> Connect unlimited calendars</li>
                      <li><i class="fas fa-check text-success"></i> Unlimited calendar grades</li>
                      <li><i class="fas fa-check text-success"></i> Advanced AI recommendations</li>
                      <li><i class="fas fa-check text-success"></i> Priority support</li>
                      <li><i class="fas fa-check text-success"></i> Detailed analytics</li>
                    </ul>
                  </div>
                </div>
              </div>

              <!-- Calendar Integration Settings -->
              <div class="tab-pane fade" :class="{ 'show active': activeTab === 'calendar' }" id="calendar-integration">
                <h3>Google Calendar Integration</h3>
                <div class="card mb-4">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <h5>Connect Your Calendar</h5>
                        <p class="text-muted">Connect your Google Calendar to start grading and improving your schedule.</p>
                      </div>
                      <button v-if="!isGoogleConnected" type="button" class="btn btn-primary" @click="connectGoogleCalendar">
                        Connect Calendar
                      </button>
                      <button v-else type="button" class="btn btn-outline-danger" @click="disconnectGoogleCalendar">
                        Disconnect Calendar
                      </button>
                    </div>
                  </div>
                </div>

                <div class="connected-calendars">
                  <h5>Connected Calendars</h5>
                  <p class="text-muted">Select the calendars you want to include in your analysis.</p>
                  <!-- Display loading state -->
                  <div v-if="isLoadingCalendars" class="text-center my-3">
                    <div class="spinner-border text-primary" role="status">
                      <span class="visually-hidden">Loading calendars...</span>
                    </div>
                  </div>
                  <!-- Display error state -->
                  <div v-else-if="calendarFetchError" class="alert alert-warning">
                    {{ calendarFetchError }}
                  </div>
                  <!-- Display no calendars connected -->
                  <div v-else-if="!isGoogleConnected || connectedCalendars.length === 0" class="alert alert-info">
                    No calendars connected yet. Click the "Connect Calendar" button to get started.
                  </div>
                  <!-- Display connected calendars list with checkboxes -->
                  <ul v-else class="list-group">
                    <li v-for="calendar in connectedCalendars" :key="calendar.id" class="list-group-item d-flex justify-content-between align-items-center">
                      <div>
                        <span :style="{ color: calendar.backgroundColor }">■</span> <!-- Simple color indicator -->
                        {{ calendar.summary }} 
                        <span v-if="calendar.primary" class="badge bg-secondary ms-2">Primary</span>
                      </div>
                      <div class="form-check form-switch">
                        <input 
                          class="form-check-input" 
                          type="checkbox" 
                          role="switch" 
                          :id="'calendar-select-' + calendar.id" 
                          :checked="calendar.is_selected" 
                          @change="toggleCalendarSelection(calendar)"
                        >
                        <label class="form-check-label" :for="'calendar-select-' + calendar.id">Include</label>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Cancel Subscription Modal -->
    <div class="modal fade" id="cancelSubscriptionModal" tabindex="-1" ref="cancelModal">
      <!-- Modal content remains the same -->
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Cancel Subscription</h5>
            <button type="button" class="btn-close" @click="closeCancelModal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to cancel your subscription?</p>
            <p>You will still have access to premium features until the end of your current billing period.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeCancelModal">Keep Subscription</button>
            <button type="button" class="btn btn-danger" @click="confirmCancelSubscription" :disabled="isCancelling">
              <span v-if="isCancelling" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              Yes, Cancel Subscription
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios'; // Import axios for API calls
import { Modal } from 'bootstrap'; // Import Modal for programmatic control

const route = useRoute();
const router = useRouter();

const activeTab = ref('account'); // Default tab

// --- Google Callback Messages ---
const googleSuccessMessage = ref('');
const googleErrorMessage = ref('');
const generalErrorMessage = ref(''); // For other errors like selection update

// --- Form Data ---
const accountForm = reactive({ name: '', email: '' });
const passwordForm = reactive({ current_password: '', password: '', password_confirmation: '' });
const notificationForm = reactive({
  weekly_grade_email: true,
  planning_reminder: true,
  reminder_day: 'Sunday',
  reminder_time: '18:00'
});

// --- Subscription Data ---
const isSubscribed = ref(true); // Placeholder
const nextBillingDate = ref('May 30, 2025'); // Placeholder
const gradesRemaining = ref(0); // Placeholder
const isCancelling = ref(false);

// --- Calendar Data ---
const isGoogleConnected = ref(false); 
const connectedCalendars = ref([]); 
const isLoadingCalendars = ref(false);
const calendarFetchError = ref('');

// --- Modals Refs ---
const cancelModal = ref(null);
let bsCancelModal = null;

// --- Methods ---
function setActiveTab(tabName) {
  activeTab.value = tabName;
  router.replace({ hash: `#${tabName}` });
}

function clearGoogleMessages() {
  googleSuccessMessage.value = '';
  googleErrorMessage.value = '';
  // Remove query params from URL without reloading
  router.replace({ query: {} }); 
}

async function updateAccount() { /* ... API call ... */ }
async function updatePassword() { /* ... API call ... */ }
async function updateNotifications() { /* ... API call ... */ }

function openCancelModal() { if (bsCancelModal) bsCancelModal.show(); }
function closeCancelModal() { if (bsCancelModal) bsCancelModal.hide(); }

async function confirmCancelSubscription() {
  isCancelling.value = true;
  try {
    // TODO: Call API to cancel subscription
    await new Promise(resolve => setTimeout(resolve, 1500));
    isSubscribed.value = false;
    closeCancelModal();
  } catch (error) {
    console.error('Failed to cancel subscription:', error);
  } finally {
    isCancelling.value = false;
  }
}

function connectGoogleCalendar() {
  window.location.href = '/google/redirect';
}

async function disconnectGoogleCalendar() {
  console.log('Disconnecting Google Calendar...');
  googleSuccessMessage.value = ''; // Clear previous messages
  googleErrorMessage.value = '';
  generalErrorMessage.value = '';
  try {
    // TODO: Call API to disconnect Google Calendar (e.g., axios.post('/api/google/disconnect'))
    await axios.post('/api/calendars/disconnect-all'); // Assuming an endpoint to disconnect all
    isGoogleConnected.value = false;
    connectedCalendars.value = []; // Clear calendar list
    googleSuccessMessage.value = 'Google Calendar disconnected successfully.';
  } catch (error) {
    console.error('Failed to disconnect Google Calendar:', error);
    googleErrorMessage.value = 'Failed to disconnect Google Calendar. Please try again.';
  }
}

// Fetch Google connection status and calendars
async function fetchGoogleData() {
  isLoadingCalendars.value = true;
  calendarFetchError.value = '';
  console.log("Starting fetchGoogleData..."); // DEBUG
  try {
    // Check connection status first
    console.log("Checking connection status at /api/calendars/check-connection..."); // DEBUG
    const statusResponse = await axios.get("/api/calendars/check-connection");
    console.log("Connection status response:", statusResponse); // DEBUG
    isGoogleConnected.value = statusResponse.data.connected;
    console.log("isGoogleConnected value before IF:", isGoogleConnected.value); // DEBUG

    if (isGoogleConnected.value) {
      console.log("Inside IF block (isGoogleConnected is true)"); // DEBUG
      // If connected, fetch the list of calendars
      try {
        console.log("Attempting to fetch calendar list from /api/calendars..."); // DEBUG
        const calendarsResponse = await axios.get("/api/calendars");
        console.log("Calendar list response:", calendarsResponse); // DEBUG
        
        // Check if the response structure is as expected
        if (calendarsResponse.data && Array.isArray(calendarsResponse.data.calendars)) {
          connectedCalendars.value = calendarsResponse.data.calendars;
          console.log("Connected calendars set:", connectedCalendars.value); // DEBUG
        } else {
          console.error("Unexpected response structure for calendar list:", calendarsResponse.data); // DEBUG
          calendarFetchError.value = "Could not parse calendar list from server.";
          connectedCalendars.value = []; // Clear list on error
        }
      } catch (listError) {
        console.error("Error fetching calendar list:", listError); // DEBUG
        calendarFetchError.value = "Could not load Google Calendar list. Please try again later.";
        connectedCalendars.value = []; // Clear list on error
      }
    } else {
      console.log("Outside IF block (isGoogleConnected is false), clearing calendar list."); // DEBUG
      connectedCalendars.value = []; // Clear list if not connected
    }
  } catch (error) {
    console.error("Error checking Google connection status:", error); // DEBUG
    calendarFetchError.value = "Could not load Google Calendar status. Please try again later.";
    isGoogleConnected.value = false;
    connectedCalendars.value = []; // Clear list on error
  } finally {
    isLoadingCalendars.value = false;
    console.log("Finished fetchGoogleData."); // DEBUG
  }
}

// Toggle calendar selection
async function toggleCalendarSelection(calendar) {
  const newSelectionState = !calendar.is_selected;
  const calendarId = calendar.id;
  console.log(`Toggling selection for calendar ${calendarId} to ${newSelectionState}`);
  generalErrorMessage.value = ''; // Clear previous errors

  // Optimistically update UI
  const calendarIndex = connectedCalendars.value.findIndex(c => c.id === calendarId);
  if (calendarIndex !== -1) {
    connectedCalendars.value[calendarIndex].is_selected = newSelectionState;
  }

  try {
    await axios.post('/api/calendars/update-selection', {
      calendar_id: calendarId,
      is_selected: newSelectionState
    });
    console.log(`Successfully updated selection for calendar ${calendarId}`);
    // Optionally show a success message
  } catch (error) {
    console.error(`Failed to update selection for calendar ${calendarId}:`, error);
    generalErrorMessage.value = `Failed to update selection for ${calendar.summary}. Please try again.`;
    // Revert UI on error
    if (calendarIndex !== -1) {
      connectedCalendars.value[calendarIndex].is_selected = !newSelectionState;
    }
  }
}


onMounted(() => {
  // Initialize Bootstrap modal
  if (cancelModal.value) {
    bsCancelModal = new Modal(cancelModal.value);
  }

  // Check for Google callback query parameters ONCE on mount
  if (route.query.google_callback === 'success') {
    googleSuccessMessage.value = 'Google Calendar connected successfully!';
    setActiveTab('calendar'); // Switch to calendar tab on success
    // Data will be fetched by the fetchGoogleData call below
  } else if (route.query.google_callback === 'error') {
    googleErrorMessage.value = route.query.message || 'Failed to connect Google Calendar.';
    setActiveTab('calendar'); // Switch to calendar tab on error
    // Data will be fetched by the fetchGoogleData call below
  }

  // Set active tab based on hash
  if (route.hash) {
    const tabName = route.hash.substring(1);
    if (['account', 'notifications', 'subscription', 'calendar'].includes(tabName)) {
      activeTab.value = tabName;
    }
  }

  // Fetch initial data (account, notifications, subscription, calendar status)
  // TODO: Fetch actual data from API
  accountForm.name = 'John Doe'; // Placeholder
  accountForm.email = 'john.doe@example.com'; // Placeholder

  // Fetch calendar data on initial mount
  console.log("Component mounted, fetching initial data..."); // DEBUG
  fetchGoogleData();
});

// Watch for changes in route query parameters (e.g., after Google redirect)
// This handles the immediate refresh after the callback
watch(() => route.query, (newQuery, oldQuery) => {
  // Only react if the google_callback parameter actually changes or appears
  if (newQuery.google_callback && newQuery.google_callback !== oldQuery.google_callback) {
      console.log("Query parameters changed with google_callback:", newQuery); // DEBUG
      if (newQuery.google_callback === 'success') {
          googleSuccessMessage.value = 'Google Calendar connected successfully!';
          setActiveTab('calendar');
          console.log("Google callback success, fetching data via query watcher..."); // DEBUG
          fetchGoogleData(); // Re-fetch data after successful connection
      } else if (newQuery.google_callback === 'error') {
          googleErrorMessage.value = newQuery.message || 'Failed to connect Google Calendar.';
          setActiveTab('calendar');
          console.log("Google callback error, fetching data via query watcher..."); // DEBUG
          fetchGoogleData(); // Re-fetch data even on error to update status
      }
  }
}, { deep: true });

// Watch for navigation TO this component (Settings route)
// This handles SPA navigation (e.g., Home -> Settings)
watch(
  () => route.name,
  (newName, oldName) => {
    // Trigger fetch only when navigating TO the Settings route
    if (newName === 'Settings') { 
      // Avoid refetching if triggered by query param change handled by the other watcher
      if (!route.query.google_callback) {
        console.log("Navigated to Settings route (watcher), fetching data..."); // DEBUG: Updated log
        fetchGoogleData();
      } else {
        console.log("Navigated to Settings route (watcher), but callback watcher will handle fetch."); // DEBUG: Updated log
      }
    }
  }
);

</script>

<style scoped>
/* Add any specific styles for the settings page here */
.subscription-status {
  display: flex;
  align-items: center;
  padding: 1rem;
  border-radius: 0.375rem; /* Bootstrap default */
}

.subscription-status.active {
  background-color: #e9f7ef; /* Light green */
  border: 1px solid #d1e7dd; /* Bootstrap success border */
}

.subscription-status.inactive {
  background-color: #f8f9fa; /* Bootstrap light gray */
  border: 1px solid #dee2e6; /* Bootstrap default border */
}

.status-icon {
  font-size: 1.5rem;
  margin-right: 1rem;
}

.subscription-status.active .status-icon {
  color: #198754; /* Bootstrap success color */
}

.subscription-status.inactive .status-icon {
  color: #6c757d; /* Bootstrap secondary color */
}

.subscription-benefits li {
  margin-bottom: 0.5rem;
}

.subscription-benefits i {
  margin-right: 0.5rem;
}

.connected-calendars {
  margin-top: 1.5rem;
}

.list-group-item .form-check-input {
  cursor: pointer;
}

.list-group-item .form-check-label {
  cursor: pointer;
  margin-left: 0.5rem;
}
</style>

