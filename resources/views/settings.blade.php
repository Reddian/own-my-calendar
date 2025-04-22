@extends('layouts.dashboard')

@section('content')
<h1 class="page-title">Settings</h1>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="settings-container">
    <div class="card mb-4">
        <div class="card-body">
            <h3 class="card-title">Account Settings</h3>

            <form class="settings-form" action="{{ route('account.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ auth()->user()->email }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ auth()->user()->name }}">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Update Account</button>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h3 class="card-title">Change Password</h3>

            <form class="settings-form" action="{{ route('account.update-password') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group mb-3">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password">
                    @error('current_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" class="form-control @error('new_password') is-invalid @enderror" id="new_password" name="new_password">
                    @error('new_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation">
                </div>

                <button type="submit" class="btn btn-primary">Change Password</button>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h3 class="card-title">Google Calendar Integration</h3>

            <div class="google-calendar-integration">
                <p class="mb-4">Connect your Google Calendar to sync events and manage your schedule in one place.</p>

                @if(false) <!-- Replace with actual connection check -->
                <div class="connected-calendars mb-4">
                    <h5>Connected Calendars</h5>

                    <div class="calendar-list">
                        <div class="calendar-item">
                            <div class="calendar-info">
                                <div class="calendar-name">Work Calendar</div>
                                <div class="calendar-email">user@company.com</div>
                            </div>
                            <div class="calendar-actions">
                                <button class="btn btn-sm btn-outline-danger disconnect-calendar-btn">Disconnect</button>
                            </div>
                        </div>

                        <div class="calendar-item">
                            <div class="calendar-info">
                                <div class="calendar-name">Personal Calendar</div>
                                <div class="calendar-email">user@gmail.com</div>
                            </div>
                            <div class="calendar-actions">
                                <button class="btn btn-sm btn-outline-danger disconnect-calendar-btn">Disconnect</button>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="no-calendars-connected text-center mb-4">
                    <div class="no-calendars-icon mb-3">
                        <i class="fas fa-calendar-times fa-4x text-muted"></i>
                    </div>
                    <p>You don't have any Google Calendars connected yet.</p>
                </div>
                @endif

                <div class="text-center">
                    <button id="connect-google-btn" class="btn btn-primary">
                        <i class="fab fa-google me-2"></i> Connect Google Calendar
                    </button>
                </div>

                @if(false) <!-- Replace with actual connection check -->
                <div class="calendar-permissions mt-4">
                    <h5>Calendar Permissions</h5>
                    <p class="text-muted">Own My Calendar has the following permissions:</p>
                    <ul class="permissions-list">
                        <li><i class="fas fa-check text-success"></i> View your calendar events</li>
                        <li><i class="fas fa-check text-success"></i> See information about your calendars</li>
                        <li><i class="fas fa-times text-muted"></i> Create or modify events (read-only access)</li>
                    </ul>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h3 class="card-title">Notification Settings</h3>

            <form class="settings-form" action="{{ route('notification.update-settings') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input @error('weekly_grade') is-invalid @enderror" type="checkbox" id="weekly_grade" name="weekly_grade" value="1" {{ $notificationSettings->weekly_grade ?? true ? 'checked' : '' }}>
                    <label class="form-check-label" for="weekly_grade">
                        Weekly Grade Email
                    </label>
                    <div class="form-text">Receive your calendar grade and recommendations every week</div>
                    @error('weekly_grade')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input @error('weekly_reminder') is-invalid @enderror" type="checkbox" id="weekly_reminder" name="weekly_reminder" value="1" {{ $notificationSettings->weekly_reminder ?? true ? 'checked' : '' }}>
                    <label class="form-check-label" for="weekly_reminder">
                        Weekly Planning Reminder
                    </label>
                    <div class="form-text">Get a reminder to plan your week</div>
                    @error('weekly_reminder')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="weekly_reminder_day" class="form-label">Reminder Day</label>
                    <select class="form-control @error('weekly_reminder_day') is-invalid @enderror" id="weekly_reminder_day" name="weekly_reminder_day">
                        @php
                            $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                            $selectedDay = $notificationSettings->weekly_reminder_day ?? 'Sunday';
                        @endphp

                        @foreach($days as $day)
                            <option value="{{ $day }}" {{ $selectedDay == $day ? 'selected' : '' }}>{{ $day }}</option>
                        @endforeach
                    </select>
                    @error('weekly_reminder_day')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="weekly_reminder_time" class="form-label">Reminder Time</label>
                    <input type="time" class="form-control @error('weekly_reminder_time') is-invalid @enderror" id="weekly_reminder_time" name="weekly_reminder_time" value="{{ $notificationSettings->reminder_time ?? '18:00' }}">
                    @error('weekly_reminder_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Save Notification Settings</button>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h3 class="card-title">Subscription</h3>

            @if(auth()->user()->subscribed())
                <div class="subscription-info">
                    <div class="subscription-status">
                        <span class="badge bg-success">Active</span>
                        <h5 class="mt-2">Premium Plan</h5>
                        <p>Your subscription renews on May 22, 2025</p>
                    </div>

                    <div class="subscription-details">
                        <div class="detail-item">
                            <div class="detail-label">Plan</div>
                            <div class="detail-value">Monthly ($9/month)</div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Payment Method</div>
                            <div class="detail-value">Visa ending in 4242</div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Next Billing Date</div>
                            <div class="detail-value">May 22, 2025</div>
                        </div>
                    </div>

                    <div class="subscription-actions mt-4">
                        <button class="btn btn-outline-primary">Update Payment Method</button>
                        <button class="btn btn-outline-danger">Cancel Subscription</button>
                    </div>
                </div>
            @else
                <div class="subscription-upgrade">
                    <h5>Free Plan</h5>
                    <p>You have used {{ auth()->user()->gradesUsed() }} of 3 free calendar grades.</p>

                    <div class="progress mb-3">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ (auth()->user()->gradesUsed() / 3) * 100 }}%"
                            aria-valuenow="{{ auth()->user()->gradesUsed() }}" aria-valuemin="0" aria-valuemax="3"></div>
                    </div>

                    <div class="upgrade-benefits mb-4">
                        <h5>Upgrade to Premium for:</h5>
                        <ul class="benefits-list">
                            <li><i class="fas fa-check text-success"></i> Unlimited calendar grades</li>
                            <li><i class="fas fa-check text-success"></i> Weekly grade emails</li>
                            <li><i class="fas fa-check text-success"></i> Planning reminders</li>
                            <li><i class="fas fa-check text-success"></i> Detailed recommendations</li>
                        </ul>
                    </div>

                    <a href="{{ route('subscription') }}" class="btn btn-primary">Upgrade to Premium - $9/month</a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Google Calendar Auth Modal -->
<div class="modal fade" id="googleAuthModal" tabindex="-1" aria-labelledby="googleAuthModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="googleAuthModalLabel">Connect Google Calendar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <img src="{{ asset('images/google-calendar-logo.png') }}" alt="Google Calendar" width="80">
                </div>
                <p>You'll be redirected to Google to authorize access to your calendar. Own My Calendar will only have read-only access to your events.</p>
                <div class="d-grid gap-2">
                    <button id="proceed-google-auth" class="btn btn-primary">
                        <i class="fab fa-google me-2"></i> Continue to Google
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .settings-container {
        max-width: 800px;
        margin: 0 auto;
    }

    .settings-form {
        max-width: 500px;
    }

    /* Google Calendar Integration */
    .google-calendar-integration {
        padding: 10px 0;
    }

    .calendar-list {
        margin-top: 15px;
    }

    .calendar-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        background-color: rgba(0, 0, 0, 0.05);
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .calendar-name {
        font-weight: bold;
    }

    .calendar-email {
        font-size: 14px;
        color: #666;
    }

    .no-calendars-icon {
        color: #ccc;
    }

    .permissions-list {
        list-style: none;
        padding-left: 0;
    }

    .permissions-list li {
        margin-bottom: 8px;
    }

    .permissions-list i {
        margin-right: 10px;
        width: 16px;
    }

    /* Subscription Styles */
    .subscription-info {
        padding: 15px 0;
    }

    .subscription-details {
        margin-top: 20px;
    }

    .detail-item {
        display: flex;
        margin-bottom: 10px;
    }

    .detail-label {
        width: 150px;
        font-weight: bold;
    }

    .benefits-list {
        list-style: none;
        padding-left: 0;
    }

    .benefits-list li {
        margin-bottom: 10px;
    }

    .benefits-list i {
        margin-right: 10px;
    }

    /* Connect Button */
    #connect-google-btn {
        padding: 10px 20px;
        background: linear-gradient(to right, var(--primary-purple), var(--primary-teal));
        border: none;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    #connect-google-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Google Calendar Connection
        const connectGoogleBtn = document.getElementById('connect-google-btn');
        const googleAuthModal = new bootstrap.Modal(document.getElementById('googleAuthModal'));
        const proceedGoogleAuthBtn = document.getElementById('proceed-google-auth');

        if (connectGoogleBtn) {
            connectGoogleBtn.addEventListener('click', function() {
                googleAuthModal.show();
            });
        }

        if (proceedGoogleAuthBtn) {
            proceedGoogleAuthBtn.addEventListener('click', function() {
                // Show loading state
                proceedGoogleAuthBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Redirecting...';
                proceedGoogleAuthBtn.disabled = true;

                // Redirect to Google OAuth
                fetch('/google-calendar/redirect')
                    .then(response => response.json())
                    .then(data => {
                        if (data.auth_url) {
                            window.location.href = data.auth_url;
                        } else {
                            // Handle error
                            proceedGoogleAuthBtn.innerHTML = '<i class="fab fa-google me-2"></i> Continue to Google';
                            proceedGoogleAuthBtn.disabled = false;
                            alert('Failed to connect to Google. Please try again.');
                        }
                    })
                    .catch(error => {
                        console.error('Error connecting to Google Calendar:', error);
                        proceedGoogleAuthBtn.innerHTML = '<i class="fab fa-google me-2"></i> Continue to Google';
                        proceedGoogleAuthBtn.disabled = false;
                        alert('Failed to connect to Google. Please try again.');
                    });
            });
        }

        // Disconnect Calendar
        const disconnectBtns = document.querySelectorAll('.disconnect-calendar-btn');
        disconnectBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Are you sure you want to disconnect this calendar?')) {
                    // Show loading state
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    btn.disabled = true;

                    // Call disconnect endpoint
                    fetch('/google-calendar/disconnect', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reload page to show updated state
                            window.location.reload();
                        } else {
                            // Handle error
                            btn.innerHTML = 'Disconnect';
                            btn.disabled = false;
                            alert('Failed to disconnect calendar. Please try again.');
                        }
                    })
                    .catch(error => {
                        console.error('Error disconnecting calendar:', error);
                        btn.innerHTML = 'Disconnect';
                        btn.disabled = false;
                        alert('Failed to disconnect calendar. Please try again.');
                    });
                }
            });
        });
    });
</script>
@endsection
