@extends('layouts.dashboard')

@section('content')
<h1 class="page-title">Analytics</h1>

<div class="analytics-container">
    <!-- Time Period Selector -->
    <div class="time-period-selector">
        <button class="btn btn-outline-primary">Week</button>
        <button class="btn btn-primary active">Month</button>
        <button class="btn btn-outline-primary">Quarter</button>
        <button class="btn btn-outline-primary">Year</button>
        <div class="date-range-picker">
            <i class="fas fa-calendar-alt"></i>
            <span>April 2025</span>
            <i class="fas fa-chevron-down"></i>
        </div>
    </div>
    
    <!-- Analytics Overview Cards -->
    <div class="analytics-overview">
        <div class="stat-card">
            <div class="title">Total Events</div>
            <div class="value">248</div>
            <div class="chart-mini">
                <canvas id="eventsChart"></canvas>
            </div>
            <div class="change positive">
                <i class="fas fa-arrow-up"></i> 12% from last month
            </div>
        </div>
        
        <div class="stat-card">
            <div class="title">Task Completion Rate</div>
            <div class="value">78%</div>
            <div class="chart-mini">
                <canvas id="tasksChart"></canvas>
            </div>
            <div class="change positive">
                <i class="fas fa-arrow-up"></i> 5% from last month
            </div>
        </div>
        
        <div class="stat-card">
            <div class="title">Productivity Score</div>
            <div class="value">92</div>
            <div class="chart-mini">
                <canvas id="productivityChart"></canvas>
            </div>
            <div class="change positive">
                <i class="fas fa-arrow-up"></i> 8% from last month
            </div>
        </div>
        
        <div class="stat-card">
            <div class="title">Time Saved</div>
            <div class="value">24h</div>
            <div class="chart-mini">
                <canvas id="timeChart"></canvas>
            </div>
            <div class="change positive">
                <i class="fas fa-arrow-up"></i> 15% from last month
            </div>
        </div>
    </div>
    
    <!-- Detailed Analytics Charts -->
    <div class="analytics-details">
        <div class="chart-card">
            <div class="chart-title">
                <h3>Activity Breakdown</h3>
                <div class="chart-actions">
                    <button class="btn btn-sm btn-outline-primary">Export</button>
                    <i class="fas fa-ellipsis-h"></i>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="activityBreakdownChart"></canvas>
            </div>
        </div>
        
        <div class="chart-card">
            <div class="chart-title">
                <h3>Time Distribution</h3>
                <div class="chart-actions">
                    <button class="btn btn-sm btn-outline-primary">Export</button>
                    <i class="fas fa-ellipsis-h"></i>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="timeDistributionChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Productivity Insights -->
    <div class="productivity-insights">
        <h3>Productivity Insights</h3>
        
        <div class="insights-grid">
            <div class="insight-card">
                <div class="insight-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="insight-content">
                    <h4>Peak Productivity Hours</h4>
                    <p>Your most productive hours are between <strong>9 AM and 11 AM</strong>. Consider scheduling important tasks during this time.</p>
                </div>
            </div>
            
            <div class="insight-card">
                <div class="insight-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="insight-content">
                    <h4>Most Productive Day</h4>
                    <p>You complete the most tasks on <strong>Tuesday</strong>, making it your most productive day of the week.</p>
                </div>
            </div>
            
            <div class="insight-card">
                <div class="insight-icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="insight-content">
                    <h4>Average Task Duration</h4>
                    <p>Your average task completion time is <strong>45 minutes</strong>, which is 15% faster than last month.</p>
                </div>
            </div>
            
            <div class="insight-card">
                <div class="insight-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="insight-content">
                    <h4>Productivity Trend</h4>
                    <p>Your productivity has <strong>increased by 8%</strong> compared to last month. Keep up the good work!</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .analytics-container {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }
    
    .time-period-selector {
        display: flex;
        align-items: center;
        gap: 10px;
        background-color: var(--card-bg);
        border-radius: 15px;
        padding: 15px 20px;
        box-shadow: var(--shadow);
    }
    
    .date-range-picker {
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 15px;
        border-radius: 8px;
        background-color: rgba(255, 255, 255, 0.1);
        cursor: pointer;
    }
    
    .analytics-overview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .chart-mini {
        height: 50px;
        margin: 10px 0;
    }
    
    .analytics-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    
    .chart-container {
        height: 300px;
    }
    
    .productivity-insights {
        background-color: var(--card-bg);
        border-radius: 15px;
        padding: 20px;
        box-shadow: var(--shadow);
    }
    
    .insights-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .insight-card {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        padding: 15px;
    }
    
    .insight-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--primary-purple);
        display: flex;
        justify-content: center;
        align-items: center;
        color: white;
        font-size: 18px;
    }
    
    .insight-content h4 {
        margin: 0 0 5px 0;
        font-size: 16px;
    }
    
    .insight-content p {
        margin: 0;
        font-size: 14px;
        line-height: 1.4;
    }
    
    @media (max-width: 992px) {
        .analytics-details {
            grid-template-columns: 1fr;
        }
    }
    
    @media (max-width: 768px) {
        .time-period-selector {
            flex-wrap: wrap;
        }
        
        .date-range-picker {
            margin-left: 0;
            margin-top: 10px;
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mini charts for overview cards
        const eventsChart = document.getElementById('eventsChart');
        if (eventsChart) {
            new Chart(eventsChart, {
                type: 'line',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                    datasets: [{
                        data: [50, 65, 70, 63],
                        borderColor: '#7e57ff',
                        backgroundColor: 'transparent',
                        tension: 0.4,
                        pointRadius: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { display: false },
                        y: { display: false }
                    },
                    elements: {
                        line: { borderWidth: 2 }
                    }
                }
            });
        }
        
        const tasksChart = document.getElementById('tasksChart');
        if (tasksChart) {
            new Chart(tasksChart, {
                type: 'line',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                    datasets: [{
                        data: [65, 70, 75, 78],
                        borderColor: '#43c6b9',
                        backgroundColor: 'transparent',
                        tension: 0.4,
                        pointRadius: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { display: false },
                        y: { display: false }
                    },
                    elements: {
                        line: { borderWidth: 2 }
                    }
                }
            });
        }
        
        const productivityChart = document.getElementById('productivityChart');
        if (productivityChart) {
            new Chart(productivityChart, {
                type: 'line',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                    datasets: [{
                        data: [85, 88, 90, 92],
                        borderColor: '#ffd54f',
                        backgroundColor: 'transparent',
                        tension: 0.4,
                        pointRadius: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { display: false },
                        y: { display: false }
                    },
                    elements: {
                        line: { borderWidth: 2 }
                    }
                }
            });
        }
        
        const timeChart = document.getElementById('timeChart');
        if (timeChart) {
            new Chart(timeChart, {
                type: 'line',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                    datasets: [{
                        data: [15, 18, 22, 24],
                        borderColor: '#4cd964',
                        backgroundColor: 'transparent',
                        tension: 0.4,
                        pointRadius: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { display: false },
                        y: { display: false }
                    },
                    elements: {
                        line: { borderWidth: 2 }
                    }
                }
            });
        }
        
        // Activity Breakdown Chart
        const activityBreakdownChart = document.getElementById('activityBreakdownChart');
        if (activityBreakdownChart) {
            new Chart(activityBreakdownChart, {
                type: 'bar',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [
                        {
                            label: 'Meetings',
                            data: [5, 8, 6, 7, 4, 1, 0],
                            backgroundColor: '#ffd54f'
                        },
                        {
                            label: 'Tasks',
                            data: [10, 12, 9, 8, 7, 3, 2],
                            backgroundColor: '#43c6b9'
                        },
                        {
                            label: 'Events',
                            data: [3, 5, 4, 6, 8, 2, 1],
                            backgroundColor: '#7e57ff'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        }
                    }
                }
            });
        }
        
        // Time Distribution Chart
        const timeDistributionChart = document.getElementById('timeDistributionChart');
        if (timeDistributionChart) {
            new Chart(timeDistributionChart, {
                type: 'doughnut',
                data: {
                    labels: ['Meetings', 'Tasks', 'Personal', 'Learning'],
                    datasets: [{
                        data: [30, 40, 20, 10],
                        backgroundColor: ['#ffd54f', '#43c6b9', '#7e57ff', '#ff3b30'],
                        borderWidth: 0,
                        cutout: '70%'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection
