document.addEventListener('DOMContentLoaded', function() {
  const connectionStatus = document.getElementById('connectionStatus');
  const addEventBtn = document.getElementById('addEvent');
  const addTaskBtn = document.getElementById('addTask');
  const eventsList = document.getElementById('eventsList');

  // Check connection status
  chrome.storage.sync.get(['isConnected', 'token'], function(data) {
    updateConnectionStatus(data.isConnected);
  });

  // Update connection status
  function updateConnectionStatus(isConnected) {
    connectionStatus.textContent = isConnected ? 'Connected' : 'Disconnected';
    connectionStatus.parentElement.className = `status ${isConnected ? 'connected' : 'disconnected'}`;
  }

  // Add event button click handler
  addEventBtn.addEventListener('click', function() {
    chrome.tabs.create({
      url: chrome.runtime.getURL('add-event.html')
    });
  });

  // Add task button click handler
  addTaskBtn.addEventListener('click', function() {
    chrome.tabs.create({
      url: chrome.runtime.getURL('add-task.html')
    });
  });

  // Fetch upcoming events
  function fetchUpcomingEvents() {
    chrome.storage.sync.get(['token'], function(data) {
      if (data.token) {
        fetch('http://localhost:8000/api/events/upcoming', {
          headers: {
            'Authorization': `Bearer ${data.token}`,
            'Content-Type': 'application/json'
          }
        })
        .then(response => response.json())
        .then(events => {
          displayEvents(events);
        })
        .catch(error => {
          console.error('Error fetching events:', error);
        });
      }
    });
  }

  // Display events in the popup
  function displayEvents(events) {
    eventsList.innerHTML = '';
    events.forEach(event => {
      const eventElement = document.createElement('div');
      eventElement.className = 'event-item';
      eventElement.innerHTML = `
        <div class="title">${event.title}</div>
        <div class="time">${new Date(event.start_time).toLocaleString()}</div>
      `;
      eventsList.appendChild(eventElement);
    });
  }

  // Initial fetch of events
  fetchUpcomingEvents();

  // Set up alarm for periodic sync
  chrome.alarms.create('syncEvents', {
    periodInMinutes: 15
  });

  // Listen for alarm
  chrome.alarms.onAlarm.addListener(function(alarm) {
    if (alarm.name === 'syncEvents') {
      fetchUpcomingEvents();
    }
  });
}); 