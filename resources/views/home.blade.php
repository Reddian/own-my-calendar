@extends('layouts.dashboard')

@section('content')
<h1 class="page-title">Dashboard</h1>

<!-- Weekly Streak -->
<div class="streak-container">
    <div class="streak-flame">
        <i class="fas fa-fire"></i>
    </div>
    <div class="streak-count">8</div>
    <div class="streak-label">week streak</div>
</div>

<!-- Stats Cards -->
<div class="dashboard-stats">
    <div class="stat-card">
        <div class="title">Current Week Grade</div>
        <div class="value">92%</div>
        <div class="change positive">
            <i class="fas fa-arrow-up"></i> 5% from last week
        </div>
    </div>
    
    <div class="stat-card">
        <div class="title">Monthly Average</div>
        <div class="value">87%</div>
        <div class="change positive">
            <i class="fas fa-arrow-up"></i> 3% from last month
        </div>
    </div>
    
    <div class="stat-card">
        <div class="title">Connected Calendars</div>
        <div class="value">3</div>
        <div class="change positive">
            <i class="fas fa-plus"></i> Add more
        </div>
    </div>
    
    <div class="stat-card">
        <div class="title">Grades Remaining</div>
        <div class="value">{{ auth()->user()->subscribed() ? 'Unlimited' : (3 - auth()->user()->gradesUsed()) }}</div>
        <div class="change {{ auth()->user()->subscribed() ? 'positive' : 'neutral' }}">
            <i class="{{ auth()->user()->subscribed() ? 'fas fa-check-circle' : 'fas fa-info-circle' }}"></i> 
            {{ auth()->user()->subscribed() ? 'Premium' : 'Free Plan' }}
        </div>
    </div>
</div>

<!-- Weekly Grade Overview -->
<div class="chart-card">
    <div class="chart-title">
        <h3>Weekly Grade History</h3>
        <div class="chart-actions">
            <a href="{{ route('history') }}" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
    </div>
    <div class="chart-container">
        <canvas id="gradeHistoryChart"></canvas>
    </div>
</div>

<!-- Calendar Rules Performance -->
<div class="chart-card mt-4">
    <div class="chart-title">
        <h3>Calendar Rules Performance</h3>
        <div class="chart-actions">
            <button class="btn btn-sm btn-outline-primary">Details</button>
        </div>
    </div>
    <div class="chart-container">
        <canvas id="rulesPerformanceChart"></canvas>
    </div>
</div>

<!-- Recommendations -->
<div class="card mt-4">
    <div class="card-title">
        <h3>AI Recommendations</h3>
    </div>
    <div class="recommendations-list">
        <div class="recommendation-item">
            <i class="fas fa-lightbulb text-warning"></i>
            <div class="recommendation-content">
                <strong>Schedule more reflection time</strong>
                <p>You're missing dedicated reflection blocks in your calendar. Try adding 30 minutes each day for reflection.</p>
            </div>
        </div>
        
        <div class="recommendation-item">
            <i class="fas fa-lightbulb text-warning"></i>
            <div class="recommendation-content">
                <strong>Protect your planning time</strong>
                <p>Your Sunday planning block was interrupted last week. Make sure to protect this critical time.</p>
            </div>
        </div>
        
        <div class="recommendation-item">
            <i class="fas fa-check-circle text-success"></i>
            <div class="recommendation-content">
                <strong>Great job with money-making activities</strong>
                <p>You've allocated appropriate time for your top money-making activities this week.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Grade History Chart
        const gradeHistoryChart = document.getElementById('gradeHistoryChart');
        if (gradeHistoryChart) {
            new Chart(gradeHistoryChart, {
                type: 'line',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6', 'Current Week'],
                    datasets: [{
                        label: 'Weekly Grade',
                        data: [75, 78, 82, 85, 88, 87, 92],
                        borderColor: '#7e57ff',
                        backgroundColor: 'rgba(126, 87, 255, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: 50,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Rules Performance Chart
        const rulesPerformanceChart = document.getElementById('rulesPerformanceChart');
        if (rulesPerformanceChart) {
            new Chart(rulesPerformanceChart, {
                type: 'radar',
                data: {
                    labels: [
                        'Non-Negotiables',
                        'Money-Making Activities',
                        'Reflection Time',
                        'Learning Time',
                        'Planning Time',
                        'Time Protection',
                        'Calendar Adherence'
                    ],
                    datasets: [{
                        label: 'Current Week',
                        data: [95, 90, 70, 85, 80, 75, 95],
                        backgroundColor: 'rgba(126, 87, 255, 0.2)',
                        borderColor: '#7e57ff',
                        pointBackgroundColor: '#7e57ff'
                    },
                    {
                        label: 'Previous Week',
                        data: [90, 85, 65, 80, 75, 70, 90],
                        backgroundColor: 'rgba(67, 198, 185, 0.2)',
                        borderColor: '#43c6b9',
                        pointBackgroundColor: '#43c6b9'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            angleLines: {
                                display: true
                            },
                            suggestedMin: 50,
                            suggestedMax: 100
                        }
                    }
                }
            });
        }
    });
</script>

<style>
    .recommendation-item {
        display: flex;
        align-items: flex-start;
        padding: 15px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    .recommendation-item:last-child {
        border-bottom: none;
    }
    
    .recommendation-item i {
        font-size: 24px;
        margin-right: 15px;
        margin-top: 3px;
    }
    
    .recommendation-content strong {
        display: block;
        margin-bottom: 5px;
    }
    
    .recommendation-content p {
        margin: 0;
        color: #666;
    }
</style>
@endsection
