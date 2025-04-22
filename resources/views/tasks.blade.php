@extends('layouts.dashboard')

@section('content')
<h1 class="page-title">Tasks</h1>

<div class="tasks-container">
    <div class="tasks-header">
        <div class="tasks-filters">
            <button class="btn btn-primary active">All</button>
            <button class="btn btn-outline-primary">Today</button>
            <button class="btn btn-outline-primary">Upcoming</button>
            <button class="btn btn-outline-primary">Completed</button>
        </div>
        <div class="tasks-actions">
            <button class="btn btn-primary"><i class="fas fa-plus"></i> Add Task</button>
        </div>
    </div>
    
    <div class="tasks-list">
        <div class="task-item">
            <div class="task-checkbox">
                <input type="checkbox" id="task1">
                <label for="task1"></label>
            </div>
            <div class="task-content">
                <div class="task-title">Prepare presentation for client meeting</div>
                <div class="task-details">
                    <span class="task-date"><i class="far fa-calendar-alt"></i> Apr 24, 2025</span>
                    <span class="task-priority high"><i class="fas fa-flag"></i> High</span>
                </div>
            </div>
            <div class="task-actions">
                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
            </div>
        </div>
        
        <div class="task-item">
            <div class="task-checkbox">
                <input type="checkbox" id="task2">
                <label for="task2"></label>
            </div>
            <div class="task-content">
                <div class="task-title">Review project timeline</div>
                <div class="task-details">
                    <span class="task-date"><i class="far fa-calendar-alt"></i> Apr 23, 2025</span>
                    <span class="task-priority medium"><i class="fas fa-flag"></i> Medium</span>
                </div>
            </div>
            <div class="task-actions">
                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
            </div>
        </div>
        
        <div class="task-item completed">
            <div class="task-checkbox">
                <input type="checkbox" id="task3" checked>
                <label for="task3"></label>
            </div>
            <div class="task-content">
                <div class="task-title">Send weekly report to team</div>
                <div class="task-details">
                    <span class="task-date"><i class="far fa-calendar-alt"></i> Apr 22, 2025</span>
                    <span class="task-priority low"><i class="fas fa-flag"></i> Low</span>
                </div>
            </div>
            <div class="task-actions">
                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
            </div>
        </div>
        
        <div class="task-item">
            <div class="task-checkbox">
                <input type="checkbox" id="task4">
                <label for="task4"></label>
            </div>
            <div class="task-content">
                <div class="task-title">Finalize project deliverables</div>
                <div class="task-details">
                    <span class="task-date"><i class="far fa-calendar-alt"></i> Apr 28, 2025</span>
                    <span class="task-priority high"><i class="fas fa-flag"></i> High</span>
                </div>
            </div>
            <div class="task-actions">
                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
            </div>
        </div>
        
        <div class="task-item">
            <div class="task-checkbox">
                <input type="checkbox" id="task5">
                <label for="task5"></label>
            </div>
            <div class="task-content">
                <div class="task-title">Schedule team building activity</div>
                <div class="task-details">
                    <span class="task-date"><i class="far fa-calendar-alt"></i> May 5, 2025</span>
                    <span class="task-priority medium"><i class="fas fa-flag"></i> Medium</span>
                </div>
            </div>
            <div class="task-actions">
                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
            </div>
        </div>
    </div>
    
    <div class="tasks-summary">
        <div class="summary-item">
            <div class="summary-label">Total Tasks</div>
            <div class="summary-value">5</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Completed</div>
            <div class="summary-value">1</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Pending</div>
            <div class="summary-value">4</div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .tasks-container {
        background-color: var(--card-bg);
        border-radius: 15px;
        padding: 20px;
        box-shadow: var(--shadow);
    }
    
    .tasks-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .tasks-filters {
        display: flex;
        gap: 10px;
    }
    
    .task-item {
        display: flex;
        align-items: center;
        padding: 15px;
        border-radius: 10px;
        background-color: rgba(255, 255, 255, 0.1);
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }
    
    .task-item:hover {
        background-color: rgba(255, 255, 255, 0.2);
    }
    
    .task-item.completed {
        opacity: 0.7;
    }
    
    .task-item.completed .task-title {
        text-decoration: line-through;
    }
    
    .task-checkbox {
        margin-right: 15px;
    }
    
    .task-checkbox input[type="checkbox"] {
        display: none;
    }
    
    .task-checkbox label {
        display: inline-block;
        width: 24px;
        height: 24px;
        border: 2px solid var(--primary-purple);
        border-radius: 50%;
        position: relative;
        cursor: pointer;
    }
    
    .task-checkbox input[type="checkbox"]:checked + label:after {
        content: '\f00c';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: var(--primary-purple);
    }
    
    .task-content {
        flex: 1;
    }
    
    .task-title {
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .task-details {
        display: flex;
        gap: 15px;
        font-size: 12px;
    }
    
    .task-date {
        color: #888;
    }
    
    .task-priority {
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 11px;
    }
    
    .task-priority.high {
        background-color: rgba(255, 59, 48, 0.2);
        color: #ff3b30;
    }
    
    .task-priority.medium {
        background-color: rgba(255, 213, 79, 0.2);
        color: #ffd54f;
    }
    
    .task-priority.low {
        background-color: rgba(76, 217, 100, 0.2);
        color: #4cd964;
    }
    
    .task-actions {
        display: flex;
        gap: 5px;
    }
    
    .tasks-summary {
        display: flex;
        justify-content: space-around;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .summary-item {
        text-align: center;
    }
    
    .summary-label {
        font-size: 14px;
        color: #888;
        margin-bottom: 5px;
    }
    
    .summary-value {
        font-size: 24px;
        font-weight: bold;
    }
    
    @media (max-width: 768px) {
        .tasks-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }
        
        .tasks-filters {
            width: 100%;
            overflow-x: auto;
            padding-bottom: 10px;
        }
        
        .task-item {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .task-checkbox {
            margin-right: 0;
            margin-bottom: 10px;
        }
        
        .task-actions {
            margin-top: 10px;
            align-self: flex-end;
        }
    }
</style>
@endsection
