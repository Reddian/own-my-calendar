/* content.js */

// Configuration
const API_BASE_URL = 'https://ownmycalendar.com'; // Production domain

// Wait for the page to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
  // Check if we're on Google Calendar
  if (window.location.hostname === 'calendar.google.com') {
    console.log('Own My Calendar extension activated on Google Calendar');
    initializeExtension();
  }
});

// Initialize the extension
function initializeExtension() {
  // Create the grading button container
  const buttonContainer = document.createElement('div');
  buttonContainer.className = 'own-my-calendar-button-container';
  
  // Create the grading button
  const gradeButton = document.createElement('button');
  gradeButton.className = 'own-my-calendar-grade-button';
  gradeButton.textContent = 'Grade My Calendar';
  gradeButton.addEventListener('click', handleGradeButtonClick);
  
  // Append button to container
  buttonContainer.appendChild(gradeButton);
  
  // Add the button container to the Google Calendar UI
  // We'll try to insert it in the top toolbar
  setTimeout(insertButtonIntoCalendar, 1500, buttonContainer);
}

// Insert the button into the Google Calendar UI
function insertButtonIntoCalendar(buttonContainer) {
  // Try to find the toolbar in Google Calendar
  const toolbar = document.querySelector('.d6McF') || // Main toolbar
                 document.querySelector('.Kk7lMc-QWPxkf-LgbsSe') || // Alternative toolbar
                 document.querySelector('header'); // Fallback to header
  
  if (toolbar) {
    toolbar.appendChild(buttonContainer);
    console.log('Grade button inserted into Google Calendar');
  } else {
    // If we can't find the toolbar, retry after a delay
    console.log('Toolbar not found, retrying...');
    setTimeout(insertButtonIntoCalendar, 1500, buttonContainer);
  }
}

// Handle the grade button click
function handleGradeButtonClick() {
  // Get the current view dates from Google Calendar
  const dateInfo = extractDateInfoFromCalendar();
  
  if (dateInfo) {
    // Show loading state
    showLoadingState();
    
    // Send message to background script to open the grading page
    chrome.runtime.sendMessage({
      action: 'openGradingPage',
      data: {
        startDate: dateInfo.startDate,
        endDate: dateInfo.endDate
      }
    }, function(response) {
      // Hide loading state
      hideLoadingState();
      
      if (response && response.success) {
        console.log('Grading page opened successfully');
      } else {
        showError('Failed to open grading page. Please try again.');
      }
    });
  } else {
    showError('Could not determine the current calendar view dates.');
  }
}

// Extract date information from the Google Calendar UI
function extractDateInfoFromCalendar() {
  try {
    // Try to find date elements in the UI
    const dateHeader = document.querySelector('[data-dateheader]') || 
                      document.querySelector('.rSoRzd');
    
    if (dateHeader) {
      const dateText = dateHeader.textContent || dateHeader.innerText;
      
      // Parse the date text to determine the current view's date range
      const dates = parseDateRange(dateText);
      return dates;
    }
    
    // Fallback: Use current week
    const today = new Date();
    const startOfWeek = new Date(today);
    startOfWeek.setDate(today.getDate() - today.getDay()); // Start of week (Sunday)
    
    const endOfWeek = new Date(startOfWeek);
    endOfWeek.setDate(startOfWeek.getDate() + 6); // End of week (Saturday)
    
    return {
      startDate: formatDate(startOfWeek),
      endDate: formatDate(endOfWeek)
    };
  } catch (error) {
    console.error('Error extracting date info:', error);
    return null;
  }
}

// Parse date range from Google Calendar header text
function parseDateRange(dateText) {
  // This is a simplified implementation and may need to be adjusted
  // based on the actual format of Google Calendar's date header
  
  // Example: "April 21 – 27, 2025"
  const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                      'July', 'August', 'September', 'October', 'November', 'December'];
  
  try {
    const currentYear = new Date().getFullYear();
    let year = currentYear;
    
    // Check if year is in the text
    const yearMatch = dateText.match(/\d{4}/);
    if (yearMatch) {
      year = parseInt(yearMatch[0]);
    }
    
    // Try to extract month
    let month = -1;
    for (let i = 0; i < monthNames.length; i++) {
      if (dateText.includes(monthNames[i])) {
        month = i;
        break;
      }
    }
    
    if (month === -1) {
      throw new Error('Could not determine month');
    }
    
    // Extract day numbers
    const dayMatches = dateText.match(/\d{1,2}/g);
    if (!dayMatches || dayMatches.length < 1) {
      throw new Error('Could not determine days');
    }
    
    let startDay, endDay;
    
    if (dayMatches.length >= 2) {
      startDay = parseInt(dayMatches[0]);
      endDay = parseInt(dayMatches[1]);
    } else {
      // If only one day is found, assume it's a single day view
      startDay = parseInt(dayMatches[0]);
      endDay = startDay;
    }
    
    // Create date objects
    const startDate = new Date(year, month, startDay);
    const endDate = new Date(year, month, endDay);
    
    return {
      startDate: formatDate(startDate),
      endDate: formatDate(endDate)
    };
  } catch (error) {
    console.error('Error parsing date range:', error);
    return null;
  }
}

// Format date as YYYY-MM-DD
function formatDate(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

// Show loading state
function showLoadingState() {
  const button = document.querySelector('.own-my-calendar-grade-button');
  if (button) {
    button.textContent = 'Loading...';
    button.disabled = true;
  }
}

// Hide loading state
function hideLoadingState() {
  const button = document.querySelector('.own-my-calendar-grade-button');
  if (button) {
    button.textContent = 'Grade My Calendar';
    button.disabled = false;
  }
}

// Show error message
function showError(message) {
  // Create error toast
  const errorToast = document.createElement('div');
  errorToast.className = 'own-my-calendar-error-toast';
  errorToast.textContent = message;
  
  // Add to page
  document.body.appendChild(errorToast);
  
  // Remove after 5 seconds
  setTimeout(() => {
    if (errorToast.parentNode) {
      errorToast.parentNode.removeChild(errorToast);
    }
  }, 5000);
}
