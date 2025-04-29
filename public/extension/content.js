// Wait for the Google Calendar page to load
function waitForElement(selector) {
  return new Promise(resolve => {
    if (document.querySelector(selector)) {
      return resolve(document.querySelector(selector));
    }

    const observer = new MutationObserver(mutations => {
      if (document.querySelector(selector)) {
        observer.disconnect();
        resolve(document.querySelector(selector));
      }
    });

    observer.observe(document.body, {
      childList: true,
      subtree: true
    });
  });
}

// Inject the grading interface
async function injectGradingInterface() {
  // Wait for the calendar header to be available
  const header = await waitForElement('[role="banner"]');
  
  // Create the grading container
  const gradingContainer = document.createElement('div');
  gradingContainer.id = 'own-my-calendar-grading';
  gradingContainer.className = 'own-my-calendar-container';
  
  // Create the grade display
  const gradeDisplay = document.createElement('div');
  gradeDisplay.className = 'grade-display';
  
  // Create the grade value
  const gradeValue = document.createElement('div');
  gradeValue.className = 'grade-value';
  gradeValue.textContent = 'Not Graded';
  
  // Create the grade button
  const gradeButton = document.createElement('button');
  gradeButton.className = 'grade-button';
  gradeButton.textContent = 'Grade Week';
  gradeButton.addEventListener('click', () => {
    gradeButton.disabled = true;
    gradeButton.textContent = 'Grading...';
    fetchCurrentGrade(gradeValue).finally(() => {
      gradeButton.disabled = false;
      gradeButton.textContent = 'Grade Week';
    });
  });
  
  // Create the view more button
  const viewMoreButton = document.createElement('button');
  viewMoreButton.className = 'view-more-button';
  viewMoreButton.textContent = 'View More';
  viewMoreButton.addEventListener('click', () => {
    window.open('http://localhost:8000/history', '_blank');
  });
  
  // Assemble the components
  gradeDisplay.appendChild(gradeValue);
  gradingContainer.appendChild(gradeDisplay);
  gradingContainer.appendChild(gradeButton);
  gradingContainer.appendChild(viewMoreButton);
  
  // Insert the container into the calendar header
  header.appendChild(gradingContainer);
}

// Fetch the current grade from the API
async function fetchCurrentGrade(gradeElement) {
  try {
    // Get the auth token from storage
    const { token } = await chrome.storage.sync.get(['token']);
    
    if (!token) {
      gradeElement.textContent = 'Not Connected';
      return;
    }
    
    // Fetch the current grade
    const response = await fetch('http://localhost:8000/api/grades/current-week', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    });
    
    if (!response.ok) {
      throw new Error('Failed to fetch grade');
    }
    
    const data = await response.json();
    
    // Update the grade display
    gradeElement.textContent = `${data.grade}%`;
    gradeElement.className = `grade-value grade-${getGradeClass(data.grade)}`;
  } catch (error) {
    console.error('Error fetching grade:', error);
    gradeElement.textContent = 'Error';
    gradeElement.className = 'grade-value grade-error';
  }
}

// Helper function to determine grade class
function getGradeClass(grade) {
  if (grade >= 90) return 'excellent';
  if (grade >= 80) return 'good';
  if (grade >= 70) return 'fair';
  return 'poor';
}

// Initialize the extension
injectGradingInterface(); 