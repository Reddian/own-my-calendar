// Listen for extension installation
chrome.runtime.onInstalled.addListener(function() {
  // Set default values
  chrome.storage.sync.set({
    token: null
  });
});

// Listen for messages from content script
chrome.runtime.onMessage.addListener(function(request, sender, sendResponse) {
  if (request.action === 'checkAuth') {
    chrome.storage.sync.get(['token'], function(data) {
      sendResponse({ isAuthenticated: !!data.token });
    });
    return true;
  }

  if (request.action === 'authenticate') {
    // Open the authentication page
    chrome.tabs.create({
      url: 'http://localhost:8000/login'
    });
  }
});

// Listen for tab updates to check if we're on Google Calendar
chrome.tabs.onUpdated.addListener(function(tabId, changeInfo, tab) {
  if (changeInfo.status === 'complete' && tab.url.includes('calendar.google.com')) {
    chrome.scripting.executeScript({
      target: { tabId: tabId },
      files: ['content.js']
    });
    chrome.scripting.insertCSS({
      target: { tabId: tabId },
      files: ['content.css']
    });
  }
});

// Handle notifications
function showNotification(title, message) {
  chrome.notifications.create({
    type: 'basic',
    iconUrl: 'icons/icon128.png',
    title: title,
    message: message
  });
}

// Check for upcoming events periodically
chrome.alarms.create('checkEvents', {
  periodInMinutes: 15
});

chrome.alarms.onAlarm.addListener(function(alarm) {
  if (alarm.name === 'checkEvents') {
    checkUpcomingEvents();
  }
});

function checkUpcomingEvents() {
  chrome.storage.sync.get(['token', 'settings'], function(data) {
    if (data.token) {
      fetch('http://localhost:8000/api/events/upcoming', {
        headers: {
          'Authorization': `Bearer ${data.token}`,
          'Content-Type': 'application/json'
        }
      })
      .then(response => response.json())
      .then(events => {
        events.forEach(event => {
          const eventTime = new Date(event.start_time);
          const now = new Date();
          const minutesUntilEvent = Math.floor((eventTime - now) / 60000);

          if (minutesUntilEvent > 0 && minutesUntilEvent <= data.settings.notificationTime) {
            showNotification(
              'Upcoming Event',
              `${event.title} in ${minutesUntilEvent} minutes`
            );
          }
        });
      })
      .catch(error => {
        console.error('Error checking events:', error);
      });
    }
  });
} 