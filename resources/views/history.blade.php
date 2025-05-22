@extends('layouts.dashboard')

@section('content')
<h1 class="page-title">History</h1>

<div class="card">
    <div class="card-body">
        <div class="history-filters mb-4">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="date-range" class="form-label">Date Range</label>
                        <select id="date-range" class="form-control">
                            <option value="last-month">Last Month</option>
                            <option value="last-3-months" selected>Last 3 Months</option>
                            <option value="last-6-months">Last 6 Months</option>
                            <option value="last-year">Last Year</option>
                            <option value="all-time">All Time</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="grade-filter" class="form-label">Grade Filter</label>
                        <select id="grade-filter" class="form-control">
                            <option value="all" selected>All Grades</option>
                            <option value="excellent">Excellent (90-100%)</option>
                            <option value="good">Good (80-89%)</option>
                            <option value="average">Average (70-79%)</option>
                            <option value="needs-improvement">Needs Improvement (< 70%)</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button id="apply-filters" class="btn btn-primary w-100">Apply Filters</button>
                </div>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Week</th>
                        <th>Grade</th>
                        <th>Strengths</th>
                        <th>Areas for Improvement</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="grades-table-body">
                    <!-- Grades will be loaded dynamically -->
                    <tr id="loading-row">
                        <td colspan="5" class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="pagination-container mt-4 d-flex justify-content-between align-items-center">
            <div class="showing-entries" id="pagination-info">
                Loading entries...
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination" id="pagination-controls">
                    <!-- Pagination will be loaded dynamically -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Grade Details Modal -->
<div class="modal fade" id="gradeDetailsModal" tabindex="-1" aria-labelledby="gradeDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gradeDetailsModalLabel">Grade Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="grade-details-content">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="download-report">Download Report</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .rule-grade-item {
        margin-bottom: 10px;
    }
    
    .rule-name {
        margin-bottom: 5px;
        font-size: 14px;
    }
    
    .progress {
        height: 10px;
        border-radius: 5px;
        width: 200px;
    }
    
    .grade-circle {
        position: relative;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: conic-gradient(var(--primary-purple) var(--percentage), #e0e0e0 0);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .grade-circle::before {
        content: '';
        position: absolute;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background-color: white;
    }
    
    .grade-value {
        position: relative;
        font-size: 32px;
        font-weight: bold;
        z-index: 1;
    }
    
    .grade-label {
        text-align: center;
        margin-top: 10px;
    }
    
    .badge-success {
        background-color: #28a745;
        color: white;
    }
    
    .badge-warning {
        background-color: #ffc107;
        color: #212529;
    }
    
    .badge-danger {
        background-color: #dc3545;
        color: white;
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initial load of grades
        loadGrades();
        
        // Event listeners
        document.getElementById('apply-filters').addEventListener('click', function() {
            loadGrades();
        });
        
        // Handle grade details modal
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('view-grade-btn')) {
                const gradeId = e.target.getAttribute('data-grade-id');
                loadGradeDetails(gradeId);
            }
        });
    });
    
    // Function to load grades from API
    function loadGrades() {
        const dateRange = document.getElementById('date-range').value;
        const gradeFilter = document.getElementById('grade-filter').value;
        
        // Show loading state
        document.getElementById('grades-table-body').innerHTML = `
            <tr id="loading-row">
                <td colspan="5" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </td>
            </tr>
        `;
        
        // Calculate date range
        let startDate, endDate;
        const today = new Date();
        endDate = formatDate(today);
        
        switch(dateRange) {
            case 'last-month':
                startDate = formatDate(new Date(today.setMonth(today.getMonth() - 1)));
                break;
            case 'last-3-months':
                startDate = formatDate(new Date(today.setMonth(today.getMonth() - 3)));
                break;
            case 'last-6-months':
                startDate = formatDate(new Date(today.setMonth(today.getMonth() - 6)));
                break;
            case 'last-year':
                startDate = formatDate(new Date(today.setFullYear(today.getFullYear() - 1)));
                break;
            case 'all-time':
                startDate = '2000-01-01'; // Far in the past
                break;
            default:
                startDate = formatDate(new Date(today.setMonth(today.getMonth() - 3)));
        }
        
        // Fetch grades from API
        fetch(`/api/grades/date-range`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                start_date: startDate,
                end_date: endDate
            })
        })
        .then(response => response.json())
        .then(data => {
            displayGrades(data.grades, gradeFilter);
        })
        .catch(error => {
            console.error('Error fetching grades:', error);
            document.getElementById('grades-table-body').innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-danger">
                        Error loading grades. Please try again.
                    </td>
                </tr>
            `;
        });
    }
    
    // Function to display grades in the table
    function displayGrades(grades, gradeFilter) {
        const tableBody = document.getElementById('grades-table-body');
        
        // Filter grades based on selected filter
        let filteredGrades = grades;
        if (gradeFilter !== 'all') {
            filteredGrades = grades.filter(grade => {
                const gradeValue = parseFloat(grade.overall_grade);
                switch(gradeFilter) {
                    case 'excellent':
                        return gradeValue >= 90;
                    case 'good':
                        return gradeValue >= 80 && gradeValue < 90;
                    case 'average':
                        return gradeValue >= 70 && gradeValue < 80;
                    case 'needs-improvement':
                        return gradeValue < 70;
                    default:
                        return true;
                }
            });
        }
        
        // Update pagination info
        document.getElementById('pagination-info').textContent = 
            `Showing ${filteredGrades.length} of ${grades.length} entries`;
        
        // Clear loading state
        tableBody.innerHTML = '';
        
        if (filteredGrades.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center">
                        No grades found for the selected criteria.
                    </td>
                </tr>
            `;
            return;
        }
        
        // Add grades to table
        filteredGrades.forEach(grade => {
            const startDate = new Date(grade.week_start_date);
            const endDate = new Date(grade.week_end_date);
            
            const formattedStartDate = startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            const formattedEndDate = endDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            
            const gradeValue = parseFloat(grade.overall_grade);
            let badgeClass = 'badge-success';
            
            if (gradeValue < 70) {
                badgeClass = 'badge-danger';
            } else if (gradeValue < 80) {
                badgeClass = 'badge-warning';
            }
            
            // Parse rule_grades if it's a string
            let ruleGrades = grade.rule_grades;
            if (typeof ruleGrades === 'string') {
                try {
                    ruleGrades = JSON.parse(ruleGrades);
                } catch (e) {
                    console.error('Error parsing rule_grades:', e);
                    ruleGrades = {};
                }
            }
            
            // Get top strengths and improvement areas
            const strengths = grade.strengths || 'Not specified';
            const improvementAreas = grade.improvement_areas || 'Not specified';
            
            tableBody.innerHTML += `
                <tr>
                    <td>${formattedStartDate} - ${formattedEndDate}</td>
                    <td>
                        <span class="badge ${badgeClass}">${gradeValue.toFixed(0)}%</span>
                    </td>
                    <td>${strengths}</td>
                    <td>${improvementAreas}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary view-grade-btn" data-grade-id="${grade.id}" data-bs-toggle="modal" data-bs-target="#gradeDetailsModal">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </td>
                </tr>
            `;
        });
    }
    
    // Function to load grade details for the modal
    function loadGradeDetails(gradeId) {
        const modalContent = document.getElementById('grade-details-content');
        
        // Show loading state
        modalContent.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
        
        // Fetch grade details from API
        fetch(`/api/grades/${gradeId}`)
            .then(response => response.json())
            .then(data => {
                const grade = data.grade;
                
                // Parse rule_grades if it's a string
                let ruleGrades = grade.rule_grades;
                if (typeof ruleGrades === 'string') {
                    try {
                        ruleGrades = JSON.parse(ruleGrades);
                    } catch (e) {
                        console.error('Error parsing rule_grades:', e);
                        ruleGrades = {};
                    }
                }
                
                // Format dates
                const startDate = new Date(grade.week_start_date);
                const endDate = new Date(grade.week_end_date);
                const formattedDateRange = `${startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${endDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`;
                
                // Update modal title
                document.getElementById('gradeDetailsModalLabel').textContent = `Week of ${formattedDateRange}`;
                
                // Create rule grade HTML
                let ruleGradesHtml = '';
                if (ruleGrades && typeof ruleGrades === 'object') {
                    Object.entries(ruleGrades).forEach(([rule, value]) => {
                        let progressClass = 'bg-success';
                        if (value < 70) {
                            progressClass = 'bg-danger';
                        } else if (value < 80) {
                            progressClass = 'bg-warning';
                        }
                        
                        const ruleName = rule.replace(/([A-Z])/g, ' $1').replace(/^./, str => str.toUpperCase());
                        
                        ruleGradesHtml += `
                            <div class="rule-grade-item">
                                <div class="rule-name">${ruleName}</div>
                                <div class="progress">
                                    <div class="progress-bar ${progressClass}" role="progressbar" 
                                         style="width: ${value}%" aria-valuenow="${value}" 
                                         aria-valuemin="0" aria-valuemax="100">${value}%</div>
                                </div>
                            </div>
                        `;
                    });
                }
                
                // Parse recommendations if it's a string
                let recommendations = grade.recommendations;
                if (typeof recommendations === 'string') {
                    try {
                        recommendations = JSON.parse(recommendations);
                    } catch (e) {
                        // If it's not valid JSON, keep as is
                    }
                }
                
                // Format recommendations
                let recommendationsHtml = '';
                if (Array.isArray(recommendations)) {
                    recommendationsHtml = '<ul>' + recommendations.map(rec => `<li>${rec}</li>`).join('') + '</ul>';
                } else if (typeof recommendations === 'string') {
                    recommendationsHtml = `<p>${recommendations}</p>`;
                }
                
                // Update modal content
                modalContent.innerHTML = `
                    <div class="grade-overview d-flex justify-content-between mb-4">
                        <div class="grade-display">
                            <div class="grade-circle" style="--percentage: ${grade.overall_grade}%;">
                                <div class="grade-value">${parseFloat(grade.overall_grade).toFixed(0)}%</div>
                            </div>
                            <div class="grade-label">Overall Grade</div>
                        </div>
                        
                        <div class="rule-grades">
                            <h5>Rule Grades</h5>
                            ${ruleGradesHtml}
                        </div>
                    </div>
                    
                    <div class="grade-details">
                        <h5>Strengths</h5>
                        <p>${grade.strengths || 'No strengths specified.'}</p>
                        
                        <h5>Areas for Improvement</h5>
                        <p>${grade.improvement_areas || 'No improvement areas specified.'}</p>
                        
                        <h5>Recommendations</h5>
                        ${recommendationsHtml || '<p>No recommendations available.</p>'}
                    </div>
                `;
                
                // Set download button data
                document.getElementById('download-report').setAttribute('data-grade-id', grade.id);
            })
            .catch(error => {
                console.error('Error fetching grade details:', error);
                modalContent.innerHTML = `
                    <div class="alert alert-danger">
                        Error loading grade details. Please try again.
                    </div>
                `;
            });
    }
    
    // Helper function to format date as YYYY-MM-DD
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
</script>
@endsection
