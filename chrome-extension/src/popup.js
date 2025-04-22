// popup.js

document.addEventListener('DOMContentLoaded', function() {
  // Get references to UI elements
  const gradeButton = document.getElementById('grade-button');
  const viewDashboardButton = document.getElementById('view-dashboard-button');
  const openAppLink = document.getElementById('open-app-link');
  const statusMessage = document.getElementById('status-message');
  
  // Configuration
  const API_BASE_URL = 'https://ownmycalendar.com'; // Production domain
  
  // Check if we're on Google Calendar
  chrome.tabs.query({active: true, currentWindow: true}, function(tabs) {
    const currentTab = tabs[0];
    const isGoogleCalendar = currentTab.url.includes('calendar.google.com');
    
    if (!isGoogleCalendar) {
      statusMessage.textContent = 'Please navigate to Google Calendar to use this extension.';
      gradeButton.disabled = true;
    }
  });
  
  // Handle grade button click
  gradeButton.addEventListener('click', function() {
    chrome.tabs.query({active: true, currentWindow: true}, function(tabs) {
      const currentTab = tabs[0];
      
      // Send message to content script to extract date info and initiate grading
      chrome.tabs.sendMessage(currentTab.id, {action: 'initiateGrading'}, function(response) {
        if (response && response.success) {
          statusMessage.textContent = 'Grading initiated. Please wait...';
        } else {
          statusMessage.textContent = 'Failed to initiate grading. Please try again.';
        }
      });
    });
  });
  
  // Handle view dashboard button click
  viewDashboardButton.addEventListener('click', function() {
    // Open the dashboard in a new tab
    chrome.tabs.create({url: `${API_BASE_URL}/dashboard`});
  });
  
  // Handle open app link click
  openAppLink.addEventListener('click', function(e) {
    e.preventDefault();
    // Open the main app in a new tab
    chrome.tabs.create({url: API_BASE_URL});
  });
  
  // Check authentication status
  checkAuthStatus();
  
  // Function to check authentication status with the main app
  function checkAuthStatus() {
    // This would typically make an API call to check if the user is logged in
    // For now, we'll just assume the user is authenticated
    
    // In a real implementation, you would make an API call like:
    /*
    fetch(`${API_BASE_URL}/api/auth/check`, {
      method: 'GET',
      credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
      if (data.authenticated) {
        statusMessage.textContent = 'Connected to Own My Calendar. Ready to grade.';
      } else {
        statusMessage.textContent = 'Please log in to the Own My Calendar web app first.';
        gradeButton.disabled = true;
      }
    })
    .catch(error => {
      console.error('Error checking auth status:', error);
      statusMessage.textContent = 'Could not connect to Own My Calendar. Please try again later.';
      gradeButton.disabled = true;
    });
    */
    
    // For prototype, assume authenticated
    statusMessage.textContent = 'Connected to Own My Calendar. Ready to grade.';
  }
});
