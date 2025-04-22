@extends('layouts.dashboard')

@section('content')
<h1 class="page-title">Calendar</h1>

<div class="calendar-container">
    <div class="calendar-header">
        <div class="calendar-navigation">
            <button class="btn btn-outline-primary prev-month-btn"><i class="fas fa-chevron-left"></i></button>
            <h2 class="month-title">April 2025</h2>
            <button class="btn btn-outline-primary next-month-btn"><i class="fas fa-chevron-right"></i></button>
        </div>
        <div class="calendar-view-options">
            <button class="btn btn-outline-primary" id="month-view">Month</button>
            <button class="btn btn-primary active" id="week-view">Week</button>
            <button class="btn btn-outline-primary" id="day-view">Day</button>
        </div>
    </div>
    
    <!-- Month View -->
    <div class="calendar-view month-view" style="display: none;">
        <div class="calendar-grid">
            <!-- Calendar days header -->
            <div class="calendar-day-header">Sun</div>
            <div class="calendar-day-header">Mon</div>
            <div class="calendar-day-header">Tue</div>
            <div class="calendar-day-header">Wed</div>
            <div class="calendar-day-header">Thu</div>
            <div class="calendar-day-header">Fri</div>
            <div class="calendar-day-header">Sat</div>
            
            <!-- Calendar days -->
            <!-- Week 1 -->
            <div class="calendar-day prev-month">30</div>
            <div class="calendar-day prev-month">31</div>
            <div class="calendar-day">1</div>
            <div class="calendar-day">2</div>
            <div class="calendar-day">3</div>
            <div class="calendar-day">4</div>
            <div class="calendar-day">5</div>
            
            <!-- Week 2 -->
            <div class="calendar-day">6</div>
            <div class="calendar-day">7</div>
            <div class="calendar-day">8</div>
            <div class="calendar-day">9</div>
            <div class="calendar-day">10</div>
            <div class="calendar-day">11</div>
            <div class="calendar-day">12</div>
            
            <!-- Week 3 -->
            <div class="calendar-day">13</div>
            <div class="calendar-day">14</div>
            <div class="calendar-day">15</div>
            <div class="calendar-day">16</div>
            <div class="calendar-day">17</div>
            <div class="calendar-day">18</div>
            <div class="calendar-day">19</div>
            
            <!-- Week 4 -->
            <div class="calendar-day">20</div>
            <div class="calendar-day">21</div>
            <div class="calendar-day today">22</div>
            <div class="calendar-day has-events">
                23
                <div class="event-indicator" style="background-color: var(--primary-purple);">Team Meeting</div>
            </div>
            <div class="calendar-day">24</div>
            <div class="calendar-day has-events">
                25
                <div class="event-indicator" style="background-color: var(--accent-yellow);">Client Presentation</div>
            </div>
            <div class="calendar-day">26</div>
            
            <!-- Week 5 -->
            <div class="calendar-day">27</div>
            <div class="calendar-day has-events">
                28
                <div class="event-indicator" style="background-color: var(--primary-teal);">Project Deadline</div>
            </div>
            <div class="calendar-day">29</div>
            <div class="calendar-day">30</div>
            <div class="calendar-day next-month">1</div>
            <div class="calendar-day next-month">2</div>
            <div class="calendar-day next-month">3</div>
        </div>
    </div>
    
    <!-- Week View (Default) -->
    <div class="calendar-view week-view">
        <div class="week-header">
            <div class="time-column"></div>
            <div class="week-day">Sun<br>21</div>
            <div class="week-day">Mon<br>22</div>
            <div class="week-day today">Tue<br>23</div>
            <div class="week-day">Wed<br>24</div>
            <div class="week-day">Thu<br>25</div>
            <div class="week-day">Fri<br>26</div>
            <div class="week-day">Sat<br>27</div>
        </div>
        <div class="week-grid">
            <div class="time-slots">
                <div class="time-slot">8 AM</div>
                <div class="time-slot">9 AM</div>
                <div class="time-slot">10 AM</div>
                <div class="time-slot">11 AM</div>
                <div class="time-slot">12 PM</div>
                <div class="time-slot">1 PM</div>
                <div class="time-slot">2 PM</div>
                <div class="time-slot">3 PM</div>
                <div class="time-slot">4 PM</div>
                <div class="time-slot">5 PM</div>
            </div>
            <div class="day-column">
                <!-- Sunday -->
            </div>
            <div class="day-column">
                <!-- Monday -->
            </div>
            <div class="day-column">
                <!-- Tuesday -->
                <div class="week-event" style="top: 60px; height: 60px; background-color: var(--primary-purple);">
                    Team Meeting<br>10:00 - 11:00 AM
                </div>
            </div>
            <div class="day-column">
                <!-- Wednesday -->
            </div>
            <div class="day-column">
                <!-- Thursday -->
                <div class="week-event" style="top: 240px; height: 90px; background-color: var(--accent-yellow);">
                    Client Presentation<br>2:00 - 3:30 PM
                </div>
            </div>
            <div class="day-column">
                <!-- Friday -->
            </div>
            <div class="day-column">
                <!-- Saturday -->
            </div>
        </div>
    </div>
    
    <!-- Day View -->
    <div class="calendar-view day-view" style="display: none;">
        <div class="day-header">
            <h3>Tuesday, April 23, 2025</h3>
        </div>
        <div class="day-schedule">
            <div class="time-slots">
                <div class="time-slot">8 AM</div>
                <div class="time-slot">9 AM</div>
                <div class="time-slot">10 AM</div>
                <div class="time-slot">11 AM</div>
                <div class="time-slot">12 PM</div>
                <div class="time-slot">1 PM</div>
                <div class="time-slot">2 PM</div>
                <div class="time-slot">3 PM</div>
                <div class="time-slot">4 PM</div>
                <div class="time-slot">5 PM</div>
            </div>
            <div class="day-events">
                <div class="day-event" style="top: 60px; height: 60px; background-color: var(--primary-purple);">
                    <div class="event-time">10:00 - 11:00 AM</div>
                    <div class="event-title">Team Meeting</div>
                    <div class="event-location">Conference Room A</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Calendar Grading Interface (Week View Only) -->
    <div class="calendar-grading">
        <h3>Grade Your Week</h3>
        <div class="grading-form">
            <div class="form-group">
                <label>How productive was your week?</label>
                <div class="rating">
                    <button class="rating-star" data-value="1"><i class="fas fa-star"></i></button>
                    <button class="rating-star" data-value="2"><i class="fas fa-star"></i></button>
                    <button class="rating-star" data-value="3"><i class="fas fa-star"></i></button>
                    <button class="rating-star" data-value="4"><i class="fas fa-star"></i></button>
                    <button class="rating-star" data-value="5"><i class="fas fa-star"></i></button>
                </div>
            </div>
            <div class="form-group">
                <label>What went well?</label>
                <textarea class="form-control" rows="2" placeholder="Enter what went well this week..."></textarea>
            </div>
            <div class="form-group">
                <label>What could be improved?</label>
                <textarea class="form-control" rows="2" placeholder="Enter areas for improvement..."></textarea>
            </div>
            <button class="btn btn-primary submit-grade">Submit Grade</button>
        </div>
    </div>
    
    <div class="calendar-legend">
        <div class="legend-item">
            <div class="legend-color" style="background-color: var(--primary-purple);"></div>
            <span>Meetings</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background-color: var(--primary-teal);"></div>
            <span>Deadlines</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background-color: var(--accent-yellow);"></div>
            <span>Presentations</span>
        </div>
    </div>
</div>

<!-- Google Calendar Connection Modal -->
<div class="modal fade" id="connectGoogleModal" tabindex="-1" aria-labelledby="connectGoogleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="connectGoogleModalLabel">Connect Google Calendar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Connect your Google Calendar to sync events and manage your schedule in one place.</p>
                <button id="connectGoogleBtn" class="btn btn-primary">Connect Google Calendar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    /* Calendar Navigation Styling */
    .calendar-navigation {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
    }
    
    .month-title {
        margin: 0 15px;
        font-style: italic;
    }
    
    .prev-month-btn, .next-month-btn {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-color: var(--primary-purple);
        color: var(--primary-purple);
    }
    
    /* Calendar View Options */
    .calendar-view-options {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-bottom: 20px;
    }
    
    /* Month View Styling */
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 10px;
    }
    
    .calendar-day-header {
        text-align: center;
        font-weight: bold;
        padding: 10px;
    }
    
    .calendar-day {
        height: 100px;
        border-radius: 10px;
        padding: 10px;
        background-color: rgba(255, 255, 255, 0.1);
        position: relative;
    }
    
    .calendar-day.prev-month,
    .calendar-day.next-month {
        opacity: 0.5;
    }
    
    .calendar-day.today {
        background-color: rgba(126, 87, 255, 0.2);
        border: 2px solid var(--primary-purple);
    }
    
    .calendar-day.has-events {
        background-color: rgba(255, 255, 255, 0.2);
    }
    
    .event-indicator {
        font-size: 12px;
        padding: 3px 6px;
        border-radius: 4px;
        margin-top: 5px;
        color: white;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* Week View Styling */
    .week-header {
        display: grid;
        grid-template-columns: 60px repeat(7, 1fr);
        gap: 5px;
        margin-bottom: 10px;
    }
    
    .week-day {
        text-align: center;
        padding: 10px;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 5px;
        font-weight: bold;
    }
    
    .week-day.today {
        background-color: rgba(126, 87, 255, 0.2);
        border: 2px solid var(--primary-purple);
    }
    
    .week-grid {
        display: grid;
        grid-template-columns: 60px repeat(7, 1fr);
        height: 600px;
        position: relative;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        overflow: hidden;
    }
    
    .time-slots {
        display: flex;
        flex-direction: column;
        border-right: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .time-slot {
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: rgba(255, 255, 255, 0.7);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .day-column {
        position: relative;
        border-right: 1px solid rgba(255, 255, 255, 0.1);
        height: 600px;
    }
    
    .day-column:last-child {
        border-right: none;
    }
    
    .week-event {
        position: absolute;
        left: 5px;
        right: 5px;
        padding: 5px;
        border-radius: 5px;
        font-size: 12px;
        color: white;
        overflow: hidden;
        z-index: 1;
    }
    
    /* Day View Styling */
    .day-header {
        text-align: center;
        margin-bottom: 20px;
    }
    
    .day-schedule {
        display: grid;
        grid-template-columns: 60px 1fr;
        height: 600px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        overflow: hidden;
    }
    
    .day-events {
        position: relative;
        height: 600px;
    }
    
    .day-event {
        position: absolute;
        left: 10px;
        right: 10px;
        padding: 10px;
        border-radius: 5px;
        color: white;
        z-index: 1;
    }
    
    .event-time {
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .event-location {
        font-size: 12px;
        opacity: 0.8;
    }
    
    /* Calendar Grading Interface */
    .calendar-grading {
        margin-top: 30px;
        padding: 20px;
        background-color: rgba(255, 255, 255, 0.05);
        border-radius: 10px;
    }
    
    .calendar-grading h3 {
        margin-bottom: 20px;
        text-align: center;
        color: var(--primary-purple);
    }
    
    .grading-form {
        max-width: 600px;
        margin: 0 auto;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .rating {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }
    
    .rating-star {
        background: none;
        border: none;
        color: #ccc;
        font-size: 24px;
        cursor: pointer;
        transition: color 0.2s;
    }
    
    .rating-star:hover, .rating-star.active {
        color: var(--primary-teal);
    }
    
    .submit-grade {
        width: 100%;
        margin-top: 10px;
    }
    
    /* Calendar Legend */
    .calendar-legend {
        display: flex;
        gap: 20px;
        margin-top: 20px;
        justify-content: center;
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .legend-color {
        width: 15px;
        height: 15px;
        border-radius: 3px;
    }
    
    /* Responsive Styles */
    @media (max-width: 992px) {
        .week-header, .week-grid {
            grid-template-columns: 40px repeat(7, 1fr);
        }
        
        .time-slot {
            font-size: 10px;
        }
        
        .week-event {
            font-size: 10px;
        }
    }
    
    @media (max-width: 768px) {
        .calendar-day {
            height: 60px;
            font-size: 12px;
        }
        
        .event-indicator {
            font-size: 8px;
            padding: 2px 4px;
        }
        
        .week-header {
            grid-template-columns: 30px repeat(7, 1fr);
        }
        
        .week-day {
            font-size: 10px;
            padding: 5px;
        }
        
        .day-schedule {
            grid-template-columns: 30px 1fr;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // View switching
        const monthViewBtn = document.getElementById('month-view');
        const weekViewBtn = document.getElementById('week-view');
        const dayViewBtn = document.getElementById('day-view');
        
        const monthView = document.querySelector('.month-view');
        const weekView = document.querySelector('.week-view');
        const dayView = document.querySelector('.day-view');
        const calendarGrading = document.querySelector('.calendar-grading');
        
        monthViewBtn.addEventListener('click', function() {
            monthView.style.display = 'block';
            weekView.style.display = 'none';
            dayView.style.display = 'none';
            calendarGrading.style.display = 'none';
            
            monthViewBtn.classList.add('active');
            monthViewBtn.classList.remove('btn-outline-primary');
            monthViewBtn.classList.add('btn-primary');
            
            weekViewBtn.classList.remove('active');
            weekViewBtn.classList.add('btn-outline-primary');
            weekViewBtn.classList.remove('btn-primary');
            
            dayViewBtn.classList.remove('active');
            dayViewBtn.classList.add('btn-outline-primary');
            dayViewBtn.classList.remove('btn-primary');
        });
        
        weekViewBtn.addEventListener('click', function() {
            monthView.style.display = 'none';
            weekView.style.display = 'block';
            dayView.style.display = 'none';
            calendarGrading.style.display = 'block';
            
            monthViewBtn.classList.remove('active');
            monthViewBtn.classList.add('btn-outline-primary');
            monthViewBtn.classList.remove('btn-primary');
            
            weekViewBtn.classList.add('active');
            weekViewBtn.classList.remove('btn-outline-primary');
            weekViewBtn.classList.add('btn-primary');
            
            dayViewBtn.classList.remove('active');
            dayViewBtn.classList.add('btn-outline-primary');
            dayViewBtn.classList.remove('btn-primary');
        });
        
        dayViewBtn.addEventListener('click', function() {
            monthView.style.display = 'none';
            weekView.style.display = 'none';
            dayView.style.display = 'block';
            calendarGrading.style.display = 'none';
            
            monthViewBtn.classList.remove('active');
            monthViewBtn.classList.add('btn-outline-primary');
            monthViewBtn.classList.remove('btn-primary');
            
            weekViewBtn.classList.remove('active');
            weekViewBtn.classList.add('btn-outline-primary');
            weekViewBtn.classList.remove('btn-primary');
            
            dayViewBtn.classList.add('active');
            dayViewBtn.classList.remove('btn-outline-primary');
            dayViewBtn.classList.add('btn-primary');
        });
        
        // Rating stars
        const ratingStars = document.querySelectorAll('.rating-star');
        ratingStars.forEach(star => {
            star.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                
                // Reset all stars
                ratingStars.forEach(s => s.classList.remove('active'));
                
                // Activate stars up to the clicked one
                for (let i = 0; i < value; i++) {
                    ratingStars[i].classList.add('active');
                }
            });
        });
        
        // Connect Google Calendar
        const connectGoogleBtn = document.getElementById('connectGoogleBtn');
        if (connectGoogleBtn) {
            connectGoogleBtn.addEventListener('click', function() {
                // This would normally redirect to Google OAuth
                fetch('/google-calendar/redirect')
                    .then(response => response.json())
                    .then(data => {
                        if (data.auth_url) {
                            window.location.href = data.auth_url;
                        }
                    })
                    .catch(error => {
                        console.error('Error connecting to Google Calendar:', error);
                    });
            });
        }
        
        // Add Google Calendar connect button to navbar
        const navbarRight = document.querySelector('.navbar-nav.ms-auto');
        if (navbarRight) {
            const connectBtn = document.createElement('li');
            connectBtn.className = 'nav-item';
            connectBtn.innerHTML = `
                <button class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#connectGoogleModal">
                    <i class="fab fa-google"></i> Connect Calendar
                </button>
            `;
            navbarRight.appendChild(connectBtn);
        }
    });
</script>
@endsection
