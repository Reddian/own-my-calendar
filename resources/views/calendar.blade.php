@extends('layouts.dashboard')

@section('content')
<h1 class="page-title">Calendar</h1>

<div class="calendar-container">
    <div class="calendar-header">
        <div class="calendar-navigation">
            <button class="btn btn-outline-primary"><i class="fas fa-chevron-left"></i></button>
            <h2>April 2025</h2>
            <button class="btn btn-outline-primary"><i class="fas fa-chevron-right"></i></button>
        </div>
        <div class="calendar-view-options">
            <button class="btn btn-primary active">Month</button>
            <button class="btn btn-outline-primary">Week</button>
            <button class="btn btn-outline-primary">Day</button>
        </div>
    </div>
    
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
    
    .calendar-legend {
        display: flex;
        gap: 20px;
        margin-top: 20px;
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
    
    @media (max-width: 768px) {
        .calendar-day {
            height: 60px;
            font-size: 12px;
        }
        
        .event-indicator {
            font-size: 8px;
            padding: 2px 4px;
        }
    }
</style>
@endsection
