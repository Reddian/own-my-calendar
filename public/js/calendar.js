document.addEventListener('DOMContentLoaded', function() {
    // Initialize calendar state
    let currentDate = new Date();
    let currentMonth = currentDate.getMonth();
    let currentYear = currentDate.getFullYear();
    let currentView = 'week'; // Default view
    
    // DOM elements
    const prevBtn = document.querySelector('.prev-month-btn');
    const nextBtn = document.querySelector('.next-month-btn');
    const titleElement = document.querySelector('.month-title');
    const calendarGrid = document.querySelector('.calendar-grid');
    const weekHeader = document.querySelector('.week-header');
    const dayHeader = document.querySelector('.day-header').querySelector('h3');
    
    // Month names
    const monthNames = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    
    // Day names
    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const dayNamesShort = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    
    // View buttons
    const monthViewBtn = document.getElementById('month-view');
    const weekViewBtn = document.getElementById('week-view');
    const dayViewBtn = document.getElementById('day-view');
    
    // Calendar views
    const monthView = document.querySelector('.month-view');
    const weekView = document.querySelector('.week-view');
    const dayView = document.querySelector('.day-view');
    
    // Grade calendar button
    const gradeCalendarBtn = document.getElementById('grade-calendar-btn');
    
    // Initialize calendar
    updateCalendar();
    
    // Event listeners for navigation
    prevBtn.addEventListener('click', function() {
        navigatePrevious();
    });
    
    nextBtn.addEventListener('click', function() {
        navigateNext();
    });
    
    // Event listeners for view switching
    monthViewBtn.addEventListener('click', function() {
        setActiveView('month');
    });
    
    weekViewBtn.addEventListener('click', function() {
        setActiveView('week');
    });
    
    dayViewBtn.addEventListener('click', function() {
        setActiveView('day');
    });
    
    // Grade calendar button event listener
    if (gradeCalendarBtn) {
        gradeCalendarBtn.addEventListener('click', function() {
            gradeCurrentWeek();
        });
    }
    
    // Function to navigate to previous period based on current view
    function navigatePrevious() {
        if (currentView === 'month') {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
        } else if (currentView === 'week') {
            // Move back one week (7 days)
            currentDate.setDate(currentDate.getDate() - 7);
            currentMonth = currentDate.getMonth();
            currentYear = currentDate.getFullYear();
        } else if (currentView === 'day') {
            // Move back one day
            currentDate.setDate(currentDate.getDate() - 1);
            currentMonth = currentDate.getMonth();
            currentYear = currentDate.getFullYear();
        }
        updateCalendar();
    }
    
    // Function to navigate to next period based on current view
    function navigateNext() {
        if (currentView === 'month') {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
        } else if (currentView === 'week') {
            // Move forward one week (7 days)
            currentDate.setDate(currentDate.getDate() + 7);
            currentMonth = currentDate.getMonth();
            currentYear = currentDate.getFullYear();
        } else if (currentView === 'day') {
            // Move forward one day
            currentDate.setDate(currentDate.getDate() + 1);
            currentMonth = currentDate.getMonth();
            currentYear = currentDate.getFullYear();
        }
        updateCalendar();
    }
    
    // Function to update the calendar based on current view
    function updateCalendar() {
        if (currentView === 'month') {
            updateMonthView();
        } else if (currentView === 'week') {
            updateWeekView();
        } else if (currentView === 'day') {
            updateDayView();
        }
    }
    
    // Function to update month view
    function updateMonthView() {
        // Update title
        titleElement.textContent = `${monthNames[currentMonth]} ${currentYear}`;
        
        // Get first day of month and number of days in month
        const firstDay = new Date(currentYear, currentMonth, 1).getDay();
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
        
        // Get number of days in previous month
        const daysInPrevMonth = new Date(currentYear, currentMonth, 0).getDate();
        
        // Clear existing calendar grid content
        while (calendarGrid.children.length > 7) { // Keep the day headers
            calendarGrid.removeChild(calendarGrid.lastChild);
        }
        
        // Add days from previous month
        for (let i = 0; i < firstDay; i++) {
            const day = daysInPrevMonth - firstDay + i + 1;
            const dayElement = createDayElement(day, 'prev-month');
            calendarGrid.appendChild(dayElement);
        }
        
        // Add days from current month
        const today = new Date();
        const isCurrentMonth = today.getMonth() === currentMonth && today.getFullYear() === currentYear;
        const todayDate = today.getDate();
        
        for (let i = 1; i <= daysInMonth; i++) {
            let classes = '';
            if (isCurrentMonth && i === todayDate) {
                classes = 'today';
            }
            
            // Add sample events (in a real app, these would come from an API)
            if (i === 23 && currentMonth === 3 && currentYear === 2025) {
                classes += ' has-events';
                const dayElement = createDayElement(i, classes);
                const eventIndicator = document.createElement('div');
                eventIndicator.className = 'event-indicator';
                eventIndicator.style.backgroundColor = 'var(--primary-purple)';
                eventIndicator.textContent = 'Team Meeting';
                dayElement.appendChild(eventIndicator);
                calendarGrid.appendChild(dayElement);
            } else if (i === 25 && currentMonth === 3 && currentYear === 2025) {
                classes += ' has-events';
                const dayElement = createDayElement(i, classes);
                const eventIndicator = document.createElement('div');
                eventIndicator.className = 'event-indicator';
                eventIndicator.style.backgroundColor = 'var(--accent-yellow)';
                eventIndicator.textContent = 'Client Presentation';
                dayElement.appendChild(eventIndicator);
                calendarGrid.appendChild(dayElement);
            } else if (i === 28 && currentMonth === 3 && currentYear === 2025) {
                classes += ' has-events';
                const dayElement = createDayElement(i, classes);
                const eventIndicator = document.createElement('div');
                eventIndicator.className = 'event-indicator';
                eventIndicator.style.backgroundColor = 'var(--primary-teal)';
                eventIndicator.textContent = 'Project Deadline';
                dayElement.appendChild(eventIndicator);
                calendarGrid.appendChild(dayElement);
            } else {
                const dayElement = createDayElement(i, classes);
                calendarGrid.appendChild(dayElement);
            }
        }
        
        // Calculate how many days from next month to add
        const totalCells = 42; // 6 rows of 7 days
        const remainingCells = totalCells - (firstDay + daysInMonth);
        
        // Add days from next month
        for (let i = 1; i <= remainingCells; i++) {
            const dayElement = createDayElement(i, 'next-month');
            calendarGrid.appendChild(dayElement);
        }
    }
    
    // Function to update week view
    function updateWeekView() {
        // Get the start of the week (Sunday)
        const startOfWeek = new Date(currentDate);
        startOfWeek.setDate(currentDate.getDate() - currentDate.getDay());
        
        // Get the end of the week (Saturday)
        const endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(startOfWeek.getDate() + 6);
        
        // Update title to show week range
        const startMonth = monthNames[startOfWeek.getMonth()].substring(0, 3);
        const endMonth = monthNames[endOfWeek.getMonth()].substring(0, 3);
        
        if (startOfWeek.getMonth() === endOfWeek.getMonth()) {
            // Same month
            titleElement.textContent = `${startMonth} ${startOfWeek.getDate()} - ${endOfWeek.getDate()}, ${endOfWeek.getFullYear()}`;
        } else {
            // Different months
            titleElement.textContent = `${startMonth} ${startOfWeek.getDate()} - ${endMonth} ${endOfWeek.getDate()}, ${endOfWeek.getFullYear()}`;
        }
        
        // Update week header with correct dates
        const weekDays = weekHeader.querySelectorAll('.week-day');
        const today = new Date();
        
        for (let i = 0; i < 7; i++) {
            const day = new Date(startOfWeek);
            day.setDate(startOfWeek.getDate() + i);
            
            const dayElement = weekDays[i + 1]; // +1 because first element is time-column
            if (dayElement) {
                // Clear existing classes
                dayElement.className = 'week-day';
                
                // Check if this is today
                if (day.getDate() === today.getDate() && 
                    day.getMonth() === today.getMonth() && 
                    day.getFullYear() === today.getFullYear()) {
                    dayElement.classList.add('today');
                }
                
                // Update day text
                dayElement.innerHTML = `${dayNamesShort[i]}<br>${day.getDate()}`;
            }
        }
    }
    
    // Function to update day view
    function updateDayView() {
        // Format the day header
        const dayOfWeek = dayNames[currentDate.getDay()];
        const month = monthNames[currentDate.getMonth()];
        const day = currentDate.getDate();
        const year = currentDate.getFullYear();
        
        // Update title and day header
        titleElement.textContent = `${dayOfWeek}, ${month} ${day}`;
        dayHeader.textContent = `${dayOfWeek}, ${month} ${day}, ${year}`;
        
        // In a real app, you would update the events for this day here
    }
    
    // Helper function to create a day element
    function createDayElement(day, classes) {
        const dayElement = document.createElement('div');
        dayElement.className = `calendar-day ${classes}`;
        dayElement.textContent = day;
        return dayElement;
    }
    
    // Function to set active view
    function setActiveView(view) {
        currentView = view;
        
        // Update button states
        monthViewBtn.classList.remove('active', 'btn-primary');
        weekViewBtn.classList.remove('active', 'btn-primary');
        dayViewBtn.classList.remove('active', 'btn-primary');
        
        monthViewBtn.classList.add('btn-outline-primary');
        weekViewBtn.classList.add('btn-outline-primary');
        dayViewBtn.classList.add('btn-outline-primary');
        
        // Hide all views
        monthView.style.display = 'none';
        weekView.style.display = 'none';
        dayView.style.display = 'none';
        
        // Show selected view and update active button
        if (view === 'month') {
            monthView.style.display = 'block';
            monthViewBtn.classList.remove('btn-outline-primary');
            monthViewBtn.classList.add('active', 'btn-primary');
        } else if (view === 'week') {
            weekView.style.display = 'block';
            weekViewBtn.classList.remove('btn-outline-primary');
            weekViewBtn.classList.add('active', 'btn-primary');
        } else if (view === 'day') {
            dayView.style.display = 'block';
            dayViewBtn.classList.remove('btn-outline-primary');
            dayViewBtn.classList.add('active', 'btn-primary');
        }
        
        // Update calendar for the selected view
        updateCalendar();
    }
    
    // Function to grade the current week using OpenAI
    function gradeCurrentWeek() {
        // Show loading state in the button
        const originalButtonText = gradeCalendarBtn.innerHTML;
        gradeCalendarBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Grading...';
        gradeCalendarBtn.disabled = true;
        
        // Get the start and end dates of the current week
        const startOfWeek = new Date(currentDate);
        startOfWeek.setDate(currentDate.getDate() - currentDate.getDay());
        
        const endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(startOfWeek.getDate() + 6);
        
        // Format dates for API
        const startDate = formatDate(startOfWeek);
        const endDate = formatDate(endOfWeek);
        
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Make API call to grade calendar
        fetch('/calendar/grade', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                start_date: startDate,
                end_date: endDate
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            // Reset button state
            gradeCalendarBtn.innerHTML = originalButtonText;
            gradeCalendarBtn.disabled = false;
            
            // Update the modal with the grading results
            updateGradeResultModal(data.grade);
            
            // Show the modal
            const gradeResultModal = new bootstrap.Modal(document.getElementById('gradeResultModal'));
            gradeResultModal.show();
        })
        .catch(error => {
            console.error('Error grading calendar:', error);
            
            // Reset button state
            gradeCalendarBtn.innerHTML = originalButtonText;
            gradeCalendarBtn.disabled = false;
            
            // Show error message
            alert('Failed to grade calendar. Please make sure your Google Calendar is connected and try again.');
        });
    }
    
    // Helper function to format date as YYYY-MM-DD
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    // Function to update the grade result modal with data from API
    function updateGradeResultModal(gradeData) {
        // Get modal elements
        const gradeLetter = document.querySelector('.grade-letter');
        const gradeScore = document.querySelector('.grade-score');
        const gradeSummary = document.querySelector('.grade-summary p');
        const recommendationsList = document.querySelector('.recommendations-list');
        
        // Calculate letter grade based on overall grade
        const letterGrade = calculateLetterGrade(gradeData.overall_grade);
        
        // Update modal content
        gradeLetter.textContent = letterGrade;
        gradeScore.innerHTML = `${Math.round(gradeData.overall_grade)}<span>/100</span>`;
        
        // Update summary
        if (gradeData.strengths) {
            gradeSummary.textContent = gradeData.strengths.split('.')[0] + '.';
        }
        
        // Clear existing recommendations
        recommendationsList.innerHTML = '';
        
        // Add new recommendations
        if (gradeData.recommendations) {
            const recommendations = gradeData.recommendations.split('.');
            recommendations.forEach(recommendation => {
                const trimmedRecommendation = recommendation.trim();
                if (trimmedRecommendation) {
                    const li = document.createElement('li');
                    li.innerHTML = `
                        <div class="recommendation-title">${trimmedRecommendation}</div>
                        <div class="recommendation-description">${gradeData.improvement_areas ? gradeData.improvement_areas.split('.')[0] + '.' : ''}</div>
                    `;
                    recommendationsList.appendChild(li);
                }
            });
        }
    }
    
    // Helper function to calculate letter grade
    function calculateLetterGrade(score) {
        if (score >= 97) return 'A+';
        if (score >= 93) return 'A';
        if (score >= 90) return 'A-';
        if (score >= 87) return 'B+';
        if (score >= 83) return 'B';
        if (score >= 80) return 'B-';
        if (score >= 77) return 'C+';
        if (score >= 73) return 'C';
        if (score >= 70) return 'C-';
        if (score >= 67) return 'D+';
        if (score >= 63) return 'D';
        if (score >= 60) return 'D-';
        return 'F';
    }
});
