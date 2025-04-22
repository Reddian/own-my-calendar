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
                    <button class="btn btn-primary w-100">Apply Filters</button>
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
                <tbody>
                    <tr>
                        <td>Apr 15 - Apr 21, 2025</td>
                        <td>
                            <span class="badge badge-success">92%</span>
                        </td>
                        <td>Non-negotiables, Money-making activities</td>
                        <td>Reflection time, Learning time</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#gradeDetailsModal">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>Apr 8 - Apr 14, 2025</td>
                        <td>
                            <span class="badge badge-success">87%</span>
                        </td>
                        <td>Calendar adherence, Time protection</td>
                        <td>Planning time</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>Apr 1 - Apr 7, 2025</td>
                        <td>
                            <span class="badge badge-warning">78%</span>
                        </td>
                        <td>Money-making activities</td>
                        <td>Non-negotiables, Reflection time</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>Mar 25 - Mar 31, 2025</td>
                        <td>
                            <span class="badge badge-warning">75%</span>
                        </td>
                        <td>Learning time</td>
                        <td>Calendar adherence, Planning time</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>Mar 18 - Mar 24, 2025</td>
                        <td>
                            <span class="badge badge-danger">65%</span>
                        </td>
                        <td>Money-making activities</td>
                        <td>Non-negotiables, Time protection, Planning time</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="pagination-container mt-4 d-flex justify-content-between align-items-center">
            <div class="showing-entries">
                Showing 1 to 5 of 12 entries
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
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
                <h5 class="modal-title" id="gradeDetailsModalLabel">Week of Apr 15 - Apr 21, 2025</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="grade-overview d-flex justify-content-between mb-4">
                    <div class="grade-display">
                        <div class="grade-circle" style="--percentage: 92%;">
                            <div class="grade-value">92%</div>
                        </div>
                        <div class="grade-label">Overall Grade</div>
                    </div>
                    
                    <div class="rule-grades">
                        <h5>Rule Grades</h5>
                        <div class="rule-grade-item">
                            <div class="rule-name">Non-Negotiables</div>
                            <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 95%" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100">95%</div>
                            </div>
                        </div>
                        <div class="rule-grade-item">
                            <div class="rule-name">Money-Making Activities</div>
                            <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 90%" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100">90%</div>
                            </div>
                        </div>
                        <div class="rule-grade-item">
                            <div class="rule-name">Reflection Time</div>
                            <div class="progress">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: 70%" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">70%</div>
                            </div>
                        </div>
                        <div class="rule-grade-item">
                            <div class="rule-name">Learning Time</div>
                            <div class="progress">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100">85%</div>
                            </div>
                        </div>
                        <div class="rule-grade-item">
                            <div class="rule-name">Planning Time</div>
                            <div class="progress">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 80%" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100">80%</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="grade-details">
                    <h5>Strengths</h5>
                    <p>You've done an excellent job with your non-negotiables this week. All your important commitments were scheduled and protected. Your money-making activities were well-distributed throughout the week, with clear focus on your top priorities.</p>
                    
                    <h5>Areas for Improvement</h5>
                    <p>Your reflection time could use some improvement. You only scheduled 2 out of the recommended 5 reflection blocks this week. Your learning time was adequate but could be more consistent throughout the week rather than concentrated on a single day.</p>
                    
                    <h5>Recommendations</h5>
                    <ul>
                        <li>Schedule 30-minute reflection blocks at the end of each workday</li>
                        <li>Distribute learning time more evenly - try 30 minutes daily instead of 2 hours on one day</li>
                        <li>Continue protecting your Sunday planning block - this is working well</li>
                        <li>Consider adding more buffer time between meetings to prevent back-to-back scheduling</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Download Report</button>
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
</style>
@endsection
