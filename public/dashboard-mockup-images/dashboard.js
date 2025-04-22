// Dashboard functionality
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }
    
    // Initialize charts
    initializeCharts();
});

// Chart initialization
function initializeCharts() {
    // Activity chart
    const activityChart = document.getElementById('activityChart');
    if (activityChart) {
        new Chart(activityChart, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [
                    {
                        label: 'Events',
                        data: [65, 59, 80, 81, 56, 55],
                        borderColor: '#7e57ff',
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#7e57ff'
                    },
                    {
                        label: 'Tasks',
                        data: [28, 48, 40, 19, 86, 27],
                        borderColor: '#43c6b9',
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#43c6b9'
                    },
                    {
                        label: 'Meetings',
                        data: [35, 25, 30, 52, 45, 40],
                        borderColor: '#ffd54f',
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#ffd54f'
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
    
    // Time distribution chart
    const timeChart = document.getElementById('timeChart');
    if (timeChart) {
        new Chart(timeChart, {
            type: 'doughnut',
            data: {
                labels: ['Meetings', 'Tasks', 'Personal'],
                datasets: [{
                    data: [30, 40, 30],
                    backgroundColor: ['#ffd54f', '#43c6b9', '#7e57ff'],
                    borderWidth: 0,
                    cutout: '70%'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
}
