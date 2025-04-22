// background.js

// Configuration
const API_BASE_URL = 'https://ownmycalendar.com'; // Production domain

// Listen for messages from content script or popup
chrome.runtime.onMessage.addListener(function(request, sender, sendResponse) {
  if (request.action === 'openGradingPage') {
    // Extract date information from the request
    const startDate = request.data.startDate;
    const endDate = request.data.endDate;
    
    // Open the grading page in a new tab
    chrome.tabs.create({
      url: `${API_BASE_URL}/grade?start_date=${startDate}&end_date=${endDate}`
    }, function(tab) {
      if (tab) {
        sendResponse({success: true, tabId: tab.id});
      } else {
        sendResponse({success: false, error: 'Failed to open grading page'});
      }
    });
    
    // Return true to indicate we will send a response asynchronously
    return true;
  }
  
  // Handle authentication check
  if (request.action === 'checkAuth') {
    // In a real implementation, you would check if the user is authenticated
    // with the main application, possibly by making an API call
    
    // For prototype, assume authenticated
    sendResponse({authenticated: true});
    return true;
  }
});

// Listen for installation event
chrome.runtime.onInstalled.addListener(function(details) {
  if (details.reason === 'install') {
    // Open the onboarding page when the extension is first installed
    chrome.tabs.create({
      url: `${API_BASE_URL}/extension-welcome`
    });
  }
});
