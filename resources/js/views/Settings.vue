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
        <!-- <div v-if="successMessage" class="alert alert-success alert-dismissible fade show" role="alert">
          {{ successMessage }}
          <button type="button" class="btn-close" @click="clearMessages" aria-label="Close"></button>
        </div>
        <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
          {{ errorMessage }}
          <button type="button" class="btn-close" @click="clearMessages" aria-label="Close"></button>
        </div> -->

        <div class="card">
          <div class="card-header">
            <h1>Settings</h1>
          </div>
          <div class="card-body">
            <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
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
                      <!-- Update button based on connection status -->
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
                  <div v-if="!isGoogleConnected" class="alert alert-info">
                    No calendars connected yet. Click the "Connect Calendar" button to get started.
                  </div>
                  <div v-else>
                    <p>Successfully connected to Google Calendar.</p>
                    <!-- Optionally list calendars if fetched -->
                    <!-- <ul class="list-group">
                      <li v-for="calendar in connectedCalendars" :key="calendar.id" class="list-group-item d-flex justify-content-between align-items-center">
                        {{ calendar.name }}
                      </li>
                    </ul> -->
                  </div>
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

    <!-- Connect Calendar Modal (No longer needed as button directly links) -->
    <!-- <div class="modal fade" id="connectCalendarModal" tabindex="-1" ref="connectModal"> ... </div> -->

  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
// Assuming Bootstrap's JS is loaded globally or imported
// import { Modal, Tab } from 'bootstrap';

const route = useRoute();
const router = useRouter();

const activeTab = ref('account'); // Default tab

// --- Google Callback Messages ---
const googleSuccessMessage = ref('');
const googleErrorMessage = ref('');

// --- Form Data (Needs to be populated from store/API) ---
const accountForm = reactive({
  name: '',
  email: ''
});
const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: ''
});
const notificationForm = reactive({
  weekly_grade_email: true,
  planning_reminder: true,
  reminder_day: 'Sunday',
  reminder_time: '18:00'
});

// --- Subscription Data (Needs to be populated from store/API) ---
const isSubscribed = ref(true); // Placeholder
const nextBillingDate = ref('May 30, 2025'); // Placeholder
const gradesRemaining = ref(0); // Placeholder
const isCancelling = ref(false);

// --- Calendar Data (Needs to be populated from store/API) ---
const isGoogleConnected = ref(false); // Placeholder, fetch actual status
const connectedCalendars = ref([]); // Placeholder e.g., [{ id: 1, name: 'Work Calendar' }]

// --- Modals Refs ---
const cancelModal = ref(null);
// const connectModal = ref(null); // No longer needed
let bsCancelModal = null;
// let bsConnectModal = null; // No longer needed

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

async function updateAccount() {
  console.log('Updating account:', accountForm);
  // TODO: Call API to update account
}

async function updatePassword() {
  console.log('Updating password');
  // TODO: Call API to update password
}

async function updateNotifications() {
  console.log('Updating notifications:', notificationForm);
  // TODO: Call API to update notifications
}

function openCancelModal() {
  if (bsCancelModal) bsCancelModal.show();
}

function closeCancelModal() {
  if (bsCancelModal) bsCancelModal.hide();
}

async function confirmCancelSubscription() {
  isCancelling.value = true;
  console.log('Cancelling subscription...');
  // TODO: Call API to cancel subscription
  try {
    await new Promise(resolve => setTimeout(resolve, 1500));
    console.log('Subscription cancelled');
    isSubscribed.value = false;
    closeCancelModal();
  } catch (error) {
    console.error('Failed to cancel subscription:', error);
  } finally {
    isCancelling.value = false;
  }
}

function connectGoogleCalendar() {
  // Redirect to the Laravel route that handles Google OAuth redirect
  window.location.href = '/google/redirect';
}

async function disconnectGoogleCalendar() {
  console.log('Disconnecting Google Calendar...');
  // TODO: Call API to disconnect Google Calendar (/api/google/disconnect)
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000));
    isGoogleConnected.value = false;
    googleSuccessMessage.value = 'Google Calendar disconnected successfully.';
  } catch (error) {
    console.error('Failed to disconnect Google Calendar:', error);
    googleErrorMessage.value = 'Failed to disconnect Google Calendar. Please try again.';
  }
}

// --- Watcher for Route Query Params ---
watch(
  () => route.query,
  (newQuery) => {
    if (newQuery.google_callback === 'success') {
      googleSuccessMessage.value = 'Google Calendar connected successfully!';
      isGoogleConnected.value = true; // Assume success means connected
      // Optionally clear query params after showing message
      // setTimeout(clearGoogleMessages, 5000); 
    } else if (newQuery.google_callback === 'error') {
      googleErrorMessage.value = newQuery.message ? decodeURIComponent(newQuery.message) : 'An unknown error occurred during Google Calendar connection.';
      isGoogleConnected.value = false; // Assume error means not connected
      // Optionally clear query params after showing message
      // setTimeout(clearGoogleMessages, 5000);
    }
  },
  { immediate: true } // Check immediately when component mounts
);

// --- Lifecycle Hooks ---
onMounted(() => {
  // Initialize Bootstrap modals
  if (window.bootstrap && cancelModal.value) {
      bsCancelModal = new window.bootstrap.Modal(cancelModal.value);
  }

  // Activate tab based on URL hash
  const hash = route.hash;
  if (hash) {
    const tabName = hash.substring(1);
    if (['account', 'notifications', 'subscription', 'calendar'].includes(tabName)) {
      activeTab.value = tabName;
    }
  }
  // If callback params exist, ensure calendar tab is active
  if (route.query.google_callback) {
      setActiveTab('calendar');
  }

  // TODO: Fetch initial data (user, notifications, subscription, calendars, google connection status) from store/API
  accountForm.name = 'Test User';
  accountForm.email = 'test@example.com';
  // Fetch actual connection status
  // isGoogleConnected.value = await fetchGoogleConnectionStatus(); 
});

</script>

<style scoped>
/* Styles remain the same */
.subscription-status {
  display: flex;
  align-items: center;
}

.status-icon {
  font-size: 2.5rem;
  margin-right: 1rem;
}

.subscription-status.active .status-icon {
  color: var(--primary-teal);
}

.subscription-status.inactive .status-icon {
  color: #6c757d;
}

.status-details h4 {
  margin-bottom: 0.25rem;
  color: var(--primary-purple);
}

.subscription-benefits {
  list-style: none;
  padding-left: 0;
}

.subscription-benefits li {
  margin-bottom: 0.5rem;
}

.subscription-benefits i {
  margin-right: 0.5rem;
}

.nav-tabs .nav-link {
  color: #495057;
  cursor: pointer;
}

.nav-tabs .nav-link.active {
  color: var(--primary-purple);
  font-weight: 600;
  border-color: #dee2e6 #dee2e6 #fff; /* Match Bootstrap active tab style */
}

.modal {
    color: #333;
}

.modal-content {
    background-color: #fff;
}

.modal-header,
.modal-body,
.modal-footer {
    color: inherit;
}

.card-header h1 {
    font-size: 1.75rem;
    margin-bottom: 0;
}
</style>

