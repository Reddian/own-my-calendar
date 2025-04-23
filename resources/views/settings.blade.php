@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Alert Messages -->
            @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            
            @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            
            @if (session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            
            <div class="card">
                <div class="card-header">
                    <h1>Settings</h1>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="account-tab" data-bs-toggle="tab" data-bs-target="#account" type="button" role="tab" aria-controls="account" aria-selected="true">Account</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab" aria-controls="notifications" aria-selected="false">Notifications</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="subscription-tab" data-bs-toggle="tab" data-bs-target="#subscription" type="button" role="tab" aria-controls="subscription" aria-selected="false">Subscription</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar" type="button" role="tab" aria-controls="calendar" aria-selected="false">Calendar Integration</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content p-3" id="settingsTabsContent">
                        <!-- Account Settings -->
                        <div class="tab-pane fade show active" id="account" role="tabpanel" aria-labelledby="account-tab">
                            <h3>Account Information</h3>
                            <form action="{{ route('account.update') }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', auth()->user()->name) }}">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', auth()->user()->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Update Account</button>
                            </form>
                            
                            <hr class="my-4">
                            
                            <h3>Change Password</h3>
                            <form action="{{ route('account.password') }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password">
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Update Password</button>
                            </form>
                        </div>
                        
                        <!-- Notification Settings -->
                        <div class="tab-pane fade" id="notifications" role="tabpanel" aria-labelledby="notifications-tab">
                            <h3>Notification Preferences</h3>
                            <form action="{{ route('notifications.update') }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="mb-3 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="weekly_grade_email" name="weekly_grade_email" value="1" {{ old('weekly_grade_email', $notificationSettings->weekly_grade_email ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="weekly_grade_email">Send me weekly grade emails</label>
                                </div>
                                
                                <div class="mb-3 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="planning_reminder" name="planning_reminder" value="1" {{ old('planning_reminder', $notificationSettings->planning_reminder ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="planning_reminder">Send me weekly planning reminders</label>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="reminder_day" class="form-label">Reminder Day</label>
                                    <select class="form-select" id="reminder_day" name="reminder_day">
                                        <option value="Sunday" {{ old('reminder_day', $notificationSettings->reminder_day ?? 'Sunday') == 'Sunday' ? 'selected' : '' }}>Sunday</option>
                                        <option value="Monday" {{ old('reminder_day', $notificationSettings->reminder_day ?? 'Sunday') == 'Monday' ? 'selected' : '' }}>Monday</option>
                                        <option value="Tuesday" {{ old('reminder_day', $notificationSettings->reminder_day ?? 'Sunday') == 'Tuesday' ? 'selected' : '' }}>Tuesday</option>
                                        <option value="Wednesday" {{ old('reminder_day', $notificationSettings->reminder_day ?? 'Sunday') == 'Wednesday' ? 'selected' : '' }}>Wednesday</option>
                                        <option value="Thursday" {{ old('reminder_day', $notificationSettings->reminder_day ?? 'Sunday') == 'Thursday' ? 'selected' : '' }}>Thursday</option>
                                        <option value="Friday" {{ old('reminder_day', $notificationSettings->reminder_day ?? 'Sunday') == 'Friday' ? 'selected' : '' }}>Friday</option>
                                        <option value="Saturday" {{ old('reminder_day', $notificationSettings->reminder_day ?? 'Sunday') == 'Saturday' ? 'selected' : '' }}>Saturday</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="reminder_time" class="form-label">Reminder Time</label>
                                    <input type="time" class="form-control" id="reminder_time" name="reminder_time" value="{{ old('reminder_time', $notificationSettings->reminder_time ?? '18:00') }}">
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Save Notification Settings</button>
                            </form>
                        </div>
                        
                        <!-- Subscription Settings -->
                        <div class="tab-pane fade" id="subscription" role="tabpanel" aria-labelledby="subscription-tab">
                            <h3>Subscription Status</h3>
                            
                            <div class="card mb-4">
                                <div class="card-body">
                                    @if(auth()->user()->subscribed())
                                        <div class="subscription-status active">
                                            <div class="status-icon">
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                            <div class="status-details">
                                                <h4>Premium Plan</h4>
                                                <p>Your subscription is active.</p>
                                                <p class="text-muted">Next billing date: {{ auth()->user()->subscription && auth()->user()->subscription->ends_at ? auth()->user()->subscription->ends_at->format('F j, Y') : 'N/A' }}</p>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-4">
                                            <form action="{{ route('checkout.cancel-subscription') }}" method="POST" id="cancelSubscriptionForm">
                                                @csrf
                                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelSubscriptionModal">
                                                    Cancel Subscription
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="subscription-status inactive">
                                            <div class="status-icon">
                                                <i class="fas fa-info-circle"></i>
                                            </div>
                                            <div class="status-details">
                                                <h4>Free Plan</h4>
                                                <p>You are currently on the free plan.</p>
                                                <p class="text-muted">{{ 3 - auth()->user()->gradesUsed() }} grades remaining</p>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-4">
                                            <a href="{{ route('subscription') }}" class="btn btn-primary">Upgrade to Premium</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            @if(auth()->user()->subscribed())
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Subscription Benefits</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="subscription-benefits">
                                            <li><i class="fas fa-check text-success"></i> Connect unlimited calendars</li>
                                            <li><i class="fas fa-check text-success"></i> Unlimited calendar grades</li>
                                            <li><i class="fas fa-check text-success"></i> Advanced AI recommendations</li>
                                            <li><i class="fas fa-check text-success"></i> Priority support</li>
                                            <li><i class="fas fa-check text-success"></i> Detailed analytics</li>
                                        </ul>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Calendar Integration Settings -->
                        <div class="tab-pane fade" id="calendar" role="tabpanel" aria-labelledby="calendar-tab">
                            <h3>Google Calendar Integration</h3>
                            
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5>Connect Your Calendar</h5>
                                            <p class="text-muted">Connect your Google Calendar to start grading and improving your schedule.</p>
                                        </div>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#connectCalendarModal">
                                            Connect Calendar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="connected-calendars">
                                <h5>Connected Calendars</h5>
                                
                                <!-- This would be populated dynamically with actual connected calendars -->
                                <div class="alert alert-info">
                                    No calendars connected yet. Click the "Connect Calendar" button to get started.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Subscription Modal -->
<div class="modal fade" id="cancelSubscriptionModal" tabindex="-1" aria-labelledby="cancelSubscriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelSubscriptionModalLabel">Cancel Subscription</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel your subscription?</p>
                <p>You will still have access to premium features until the end of your current billing period.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Subscription</button>
                <button type="button" class="btn btn-danger" id="confirmCancelSubscription">Yes, Cancel Subscription</button>
            </div>
        </div>
    </div>
</div>

<!-- Connect Calendar Modal -->
<div class="modal fade" id="connectCalendarModal" tabindex="-1" aria-labelledby="connectCalendarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="connectCalendarModalLabel">Connect Google Calendar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>To connect your Google Calendar, you'll need to authorize Own My Calendar to access your calendar data.</p>
                <p>We only request read access to your calendar events to provide grading and recommendations.</p>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Your calendar data is only used for grading and recommendations. We never share your data with third parties.
                </div>
                
                @if(!auth()->user()->subscribed() && auth()->user()->gradesUsed() >= 3)
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> You've used all your free grades. Upgrade to Premium for unlimited grades.
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="{{ route('google.redirect') }}" class="btn btn-primary">Connect with Google</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .subscription-status {
        display: flex;
        align-items: center;
    }
    
    .status-icon {
        font-size: 2.5rem;
        margin-right: 1rem;
    }
    
    .subscription-status.active .status-icon {
        color: var(--primary-teal);
    }
    
    .subscription-status.inactive .status-icon {
        color: #6c757d;
    }
    
    .status-details h4 {
        margin-bottom: 0.25rem;
        color: var(--primary-purple);
    }
    
    .subscription-benefits {
        list-style: none;
        padding-left: 0;
    }
    
    .subscription-benefits li {
        margin-bottom: 0.5rem;
    }
    
    .subscription-benefits i {
        margin-right: 0.5rem;
    }
    
    .nav-tabs .nav-link {
        color: #495057;
    }
    
    .nav-tabs .nav-link.active {
        color: var(--primary-purple);
        font-weight: 600;
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle subscription cancellation
        const confirmCancelBtn = document.getElementById('confirmCancelSubscription');
        const cancelForm = document.getElementById('cancelSubscriptionForm');
        
        if (confirmCancelBtn && cancelForm) {
            confirmCancelBtn.addEventListener('click', function() {
                // Show loading state
                confirmCancelBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
                confirmCancelBtn.disabled = true;
                
                // Submit the form
                cancelForm.submit();
            });
        }
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert.alert-success, .alert.alert-info, .alert.alert-danger');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Activate tab based on hash in URL
        const hash = window.location.hash;
        if (hash) {
            const tab = document.querySelector(`a[href="${hash}"]`);
            if (tab) {
                const bsTab = new bootstrap.Tab(tab);
                bsTab.show();
            }
        }
    });
</script>
@endsection
