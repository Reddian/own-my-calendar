<template>
  <div class="container">
    <div class="row">
      <div class="col-12">
        <div class="card extension-card">
          <div class="card-header">
            <h1 class="text-center">Chrome Extension</h1>
          </div>
          <div class="card-body">
            <!-- Extension Status -->
            <div class="extension-status mb-4">
              <div class="row align-items-center">
                <div class="col-md-8">
                  <h3>Extension Status</h3>
                  <p class="text-muted">Manage your Chrome extension settings and connection</p>
                </div>
                <div class="col-md-4 text-end">
                  <button 
                    class="btn btn-lg"
                    :class="extensionStatus.connected ? 'btn-success' : 'btn-danger'"
                    @click="toggleExtension"
                  >
                    <i class="fas" :class="extensionStatus.connected ? 'fa-check-circle' : 'fa-times-circle'"></i>
                    {{ extensionStatus.connected ? 'Connected' : 'Disconnected' }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Extension Features -->
            <div class="extension-features mb-4">
              <h3>Features</h3>
              <div class="row">
                <div class="col-md-4 mb-3">
                  <div class="feature-card">
                    <div class="feature-icon">
                      <i class="fas fa-calendar-plus"></i>
                    </div>
                    <div class="feature-content">
                      <h4>Quick Add</h4>
                      <p>Add events and tasks directly from your browser</p>
                    </div>
                    <div class="feature-toggle">
                      <div class="form-check form-switch">
                        <input 
                          class="form-check-input" 
                          type="checkbox" 
                          id="quickAdd"
                          v-model="features.quickAdd"
                          @change="updateFeature('quickAdd')"
                        >
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-4 mb-3">
                  <div class="feature-card">
                    <div class="feature-icon">
                      <i class="fas fa-bell"></i>
                    </div>
                    <div class="feature-content">
                      <h4>Notifications</h4>
                      <p>Get browser notifications for upcoming events</p>
                    </div>
                    <div class="feature-toggle">
                      <div class="form-check form-switch">
                        <input 
                          class="form-check-input" 
                          type="checkbox" 
                          id="notifications"
                          v-model="features.notifications"
                          @change="updateFeature('notifications')"
                        >
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-4 mb-3">
                  <div class="feature-card">
                    <div class="feature-icon">
                      <i class="fas fa-sync"></i>
                    </div>
                    <div class="feature-content">
                      <h4>Auto-Sync</h4>
                      <p>Automatically sync your calendar data</p>
                    </div>
                    <div class="feature-toggle">
                      <div class="form-check form-switch">
                        <input 
                          class="form-check-input" 
                          type="checkbox" 
                          id="autoSync"
                          v-model="features.autoSync"
                          @change="updateFeature('autoSync')"
                        >
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Extension Settings -->
            <div class="extension-settings mb-4">
              <h3>Settings</h3>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <div class="form-group">
                    <label for="syncInterval">Sync Interval</label>
                    <select 
                      id="syncInterval" 
                      class="form-select" 
                      v-model="settings.syncInterval"
                      @change="updateSetting('syncInterval')"
                    >
                      <option value="5">Every 5 minutes</option>
                      <option value="15">Every 15 minutes</option>
                      <option value="30">Every 30 minutes</option>
                      <option value="60">Every hour</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <div class="form-group">
                    <label for="notificationTime">Notification Time</label>
                    <select 
                      id="notificationTime" 
                      class="form-select" 
                      v-model="settings.notificationTime"
                      @change="updateSetting('notificationTime')"
                    >
                      <option value="5">5 minutes before</option>
                      <option value="15">15 minutes before</option>
                      <option value="30">30 minutes before</option>
                      <option value="60">1 hour before</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <!-- Extension Stats -->
            <div class="extension-stats">
              <h3>Usage Statistics</h3>
              <div class="row">
                <div class="col-md-3">
                  <div class="stat-card">
                    <div class="stat-icon">
                      <i class="fas fa-calendar-plus"></i>
                    </div>
                    <div class="stat-content">
                      <h4>{{ stats.quickAdds }}</h4>
                      <p>Quick Adds</p>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="stat-card">
                    <div class="stat-icon">
                      <i class="fas fa-bell"></i>
                    </div>
                    <div class="stat-content">
                      <h4>{{ stats.notifications }}</h4>
                      <p>Notifications</p>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="stat-card">
                    <div class="stat-icon">
                      <i class="fas fa-sync"></i>
                    </div>
                    <div class="stat-content">
                      <h4>{{ stats.syncs }}</h4>
                      <p>Syncs</p>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="stat-card">
                    <div class="stat-icon">
                      <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                      <h4>{{ stats.lastSync }}</h4>
                      <p>Last Sync</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Extension Help -->
            <div class="extension-help mt-4">
              <h3>Need Help?</h3>
              <div class="row">
                <div class="col-md-6">
                  <div class="help-card">
                    <h4><i class="fas fa-question-circle me-2"></i>FAQ</h4>
                    <ul class="help-list">
                      <li v-for="(faq, index) in faqs" :key="index">
                        <a href="#" @click.prevent="showFaq(faq)">{{ faq.question }}</a>
                      </li>
                    </ul>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="help-card">
                    <h4><i class="fas fa-life-ring me-2"></i>Support</h4>
                    <p>Having issues with the extension? Contact our support team.</p>
                    <button class="btn btn-primary" @click="contactSupport">
                      <i class="fas fa-envelope me-2"></i>Contact Support
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- FAQ Modal -->
    <div v-if="selectedFaq" class="modal fade show" style="display: block;" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ selectedFaq.question }}</h5>
            <button type="button" class="btn-close" @click="selectedFaq = null"></button>
          </div>
          <div class="modal-body">
            <p>{{ selectedFaq.answer }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';

export default {
  name: 'Extension',
  setup() {
    const extensionStatus = ref({
      connected: false,
      lastSync: null
    });

    const features = ref({
      quickAdd: false,
      notifications: false,
      autoSync: false
    });

    const settings = ref({
      syncInterval: '15',
      notificationTime: '15'
    });

    const stats = ref({
      quickAdds: 0,
      notifications: 0,
      syncs: 0,
      lastSync: 'Never'
    });

    const faqs = ref([
      {
        question: 'How do I install the extension?',
        answer: 'Visit the Chrome Web Store and click "Add to Chrome". Follow the installation prompts.'
      },
      {
        question: 'How do I connect my calendar?',
        answer: 'Click the extension icon and follow the authentication process to connect your calendar.'
      },
      {
        question: 'How do I enable notifications?',
        answer: 'Go to extension settings and enable the notifications feature. Make sure to allow browser notifications.'
      }
    ]);

    const selectedFaq = ref(null);

    const fetchExtensionData = async () => {
      try {
        const response = await axios.get('/api/extension/status');
        const data = response.data;
        
        extensionStatus.value = data.status;
        features.value = data.features;
        settings.value = data.settings;
        stats.value = data.stats;
      } catch (error) {
        console.error('Failed to fetch extension data:', error);
      }
    };

    const toggleExtension = async () => {
      try {
        const newStatus = !extensionStatus.value.connected;
        await axios.post('/api/extension/toggle', { connected: newStatus });
        extensionStatus.value.connected = newStatus;
      } catch (error) {
        console.error('Failed to toggle extension:', error);
      }
    };

    const updateFeature = async (feature) => {
      try {
        await axios.post('/api/extension/features', {
          feature,
          enabled: features.value[feature]
        });
      } catch (error) {
        console.error('Failed to update feature:', error);
        // Revert the change on error
        features.value[feature] = !features.value[feature];
      }
    };

    const updateSetting = async (setting) => {
      try {
        await axios.post('/api/extension/settings', {
          setting,
          value: settings.value[setting]
        });
      } catch (error) {
        console.error('Failed to update setting:', error);
      }
    };

    const showFaq = (faq) => {
      selectedFaq.value = faq;
    };

    const contactSupport = () => {
      // Implementation for contacting support
      // This could open a modal or redirect to a support page
    };

    onMounted(() => {
      fetchExtensionData();
    });

    return {
      extensionStatus,
      features,
      settings,
      stats,
      faqs,
      selectedFaq,
      toggleExtension,
      updateFeature,
      updateSetting,
      showFaq,
      contactSupport
    };
  }
};
</script>

<style scoped>
.extension-card {
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.card-header {
  background: linear-gradient(to right, var(--primary-purple), var(--primary-teal));
  color: white;
  border-top-left-radius: 10px;
  border-top-right-radius: 10px;
  padding: 20px;
}

.feature-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  display: flex;
  align-items: center;
  height: 100%;
}

.feature-icon {
  width: 50px;
  height: 50px;
  background: #f8f9fa;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
}

.feature-icon i {
  font-size: 1.5rem;
  color: var(--primary-purple);
}

.feature-content {
  flex: 1;
}

.feature-content h4 {
  margin: 0 0 5px 0;
  font-size: 1.1rem;
}

.feature-content p {
  margin: 0;
  color: #666;
  font-size: 0.9rem;
}

.feature-toggle {
  margin-left: 15px;
}

.stat-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  display: flex;
  align-items: center;
  height: 100%;
}

.stat-icon {
  width: 50px;
  height: 50px;
  background: #f8f9fa;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
}

.stat-icon i {
  font-size: 1.5rem;
  color: var(--primary-purple);
}

.stat-content h4 {
  margin: 0 0 5px 0;
  font-size: 1.5rem;
  color: var(--primary-purple);
}

.stat-content p {
  margin: 0;
  color: #666;
  font-size: 0.9rem;
}

.help-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  height: 100%;
}

.help-card h4 {
  margin: 0 0 15px 0;
  color: var(--primary-purple);
}

.help-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.help-list li {
  margin-bottom: 10px;
}

.help-list a {
  color: #333;
  text-decoration: none;
}

.help-list a:hover {
  color: var(--primary-purple);
}

.modal {
  background-color: rgba(0, 0, 0, 0.5);
}
</style> 