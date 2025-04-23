document.addEventListener('DOMContentLoaded', function() {
    // Initialize calendar state
    let currentDate = new Date();
    let currentMonth = currentDate.getMonth();
    let currentYear = currentDate.getFullYear();
    
    // DOM elements
    const prevMonthBtn = document.querySelector('.prev-month-btn');
    const nextMonthBtn = document.querySelector('.next-month-btn');
    const monthTitle = document.querySelector('.month-title');
    const calendarGrid = document.querySelector('.calendar-grid');
    
    // Month names
    const monthNames = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    
    // Day names
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    
    // View buttons
    const monthViewBtn = document.getElementById('month-view');
    const weekViewBtn = document.getElementById('week-view');
    const dayViewBtn = document.getElementById('day-view');
    
    // Calendar views
    const monthView = document.querySelector('.month-view');
    const weekView = document.querySelector('.week-view');
    const dayView = document.querySelector('.day-view');
    
    // Initialize calendar
    updateCalendar();
    
    // Event listeners for month navigation
    prevMonthBtn.addEventListener('click', function() {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        updateCalendar();
    });
    
    nextMonthBtn.addEventListener('click', function() {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        updateCalendar();
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
    
    // Grade calendar button
    const gradeCalendarBtn = document.getElementById('grade-calendar-btn');
    if (gradeCalendarBtn) {
        gradeCalendarBtn.addEventListener('click', function() {
            const gradeResultModal = new bootstrap.Modal(document.getElementById('gradeResultModal'));
            gradeResultModal.show();
        });
    }
    
    // Function to update the calendar
    function updateCalendar() {
        // Update month title
        monthTitle.textContent = `${monthNames[currentMonth]} ${currentYear}`;
        
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
    
    // Helper function to create a day element
    function createDayElement(day, classes) {
        const dayElement = document.createElement('div');
        dayElement.className = `calendar-day ${classes}`;
        dayElement.textContent = day;
        return dayElement;
    }
    
    // Function to set active view
    function setActiveView(view) {
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
    }
});
