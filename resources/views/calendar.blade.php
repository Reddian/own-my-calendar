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
    
    <!-- Calendar Grading Button (Week View Only) -->
    <div class="calendar-grading-action">
        <button id="grade-calendar-btn" class="btn btn-primary btn-lg">
            <i class="fas fa-check-circle"></i> Grade This Week
        </button>
    </div>
    
    <!-- Calendar Grade Result Modal -->
    <div class="modal fade" id="gradeResultModal" tabindex="-1" aria-labelledby="gradeResultModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="gradeResultModalLabel">Your Calendar Grade</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="grade-result">
                        <div class="grade-header">
                            <div class="grade-letter">B+</div>
                            <div class="grade-score">87<span>/100</span></div>
                        </div>
                        
                        <div class="grade-summary">
                            <p>Your calendar is well-organized but has some room for improvement.</p>
                        </div>
                        
                        <div class="grade-recommendations">
                            <h4>Recommendations</h4>
                            <ul class="recommendations-list">
                                <li>
                                    <div class="recommendation-title">Add buffer time between meetings</div>
                                    <div class="recommendation-description">Your back-to-back meetings on Tuesday could lead to burnout. Try adding 15-minute buffers.</div>
                                </li>
                                <li>
                                    <div class="recommendation-title">Schedule focused work blocks</div>
                                    <div class="recommendation-description">Add 90-minute blocks of uninterrupted time for deep work.</div>
                                </li>
                                <li>
                                    <div class="recommendation-title">Balance your meeting load</div>
                                    <div class="recommendation-description">Thursday has too many meetings. Consider spreading them throughout the week.</div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save Recommendations</button>
                </div>
            </div>
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
    
    /* Calendar Grading Action */
    .calendar-grading-action {
        margin-top: 30px;
        text-align: center;
    }
    
    #grade-calendar-btn {
        padding: 12px 30px;
        font-size: 18px;
        background: linear-gradient(to right, var(--primary-purple), var(--primary-teal));
        border: none;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    #grade-calendar-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
    }
    
    /* Grade Result Modal Styling */
    .grade-header {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 40px;
        margin-bottom: 20px;
    }
    
    .grade-letter {
        font-size: 72px;
        font-weight: bold;
        color: var(--primary-purple);
        background: linear-gradient(to right, var(--primary-purple), var(--primary-teal));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .grade-score {
        font-size: 48px;
        font-weight: bold;
    }
    
    .grade-score span {
        font-size: 24px;
        opacity: 0.7;
    }
    
    .grade-summary {
        text-align: center;
        font-size: 18px;
        margin-bottom: 30px;
        padding: 0 20px;
    }
    
    .grade-recommendations {
        background-color: rgba(0, 0, 0, 0.05);
        border-radius: 10px;
        padding: 20px;
    }
    
    .grade-recommendations h4 {
        margin-bottom: 15px;
        color: var(--primary-purple);
    }
    
    .recommendations-list {
        list-style: none;
        padding: 0;
    }
    
    .recommendations-list li {
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .recommendations-list li:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }
    
    .recommendation-title {
        font-weight: bold;
        margin-bottom: 5px;
        color: var(--primary-teal);
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
        const calendarGradingAction = document.querySelector('.calendar-grading-action');
        
        monthViewBtn.addEventListener('click', function() {
            monthView.style.display = 'block';
            weekView.style.display = 'none';
            dayView.style.display = 'none';
            calendarGradingAction.style.display = 'none';
            
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
            calendarGradingAction.style.display = 'block';
            
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
            calendarGradingAction.style.display = 'none';
            
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
        
        // Grade Calendar Button
        const gradeCalendarBtn = document.getElementById('grade-calendar-btn');
        const gradeResultModal = new bootstrap.Modal(document.getElementById('gradeResultModal'));
        
        if (gradeCalendarBtn) {
            gradeCalendarBtn.addEventListener('click', function() {
                // Show loading state
                gradeCalendarBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Grading...';
                gradeCalendarBtn.disabled = true;
                
                // Simulate API call to OpenAI for grading
                setTimeout(function() {
                    // Reset button state
                    gradeCalendarBtn.innerHTML = '<i class="fas fa-check-circle"></i> Grade This Week';
                    gradeCalendarBtn.disabled = false;
                    
                    // Show grade result modal
                    gradeResultModal.show();
                }, 2000);
            });
        }
    });
</script>
@endsection
