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
            
            <div class="card subscription-card">
                <div class="card-header">
                    <h1 class="text-center">Upgrade to Premium</h1>
                </div>
                <div class="card-body">
                    <div class="subscription-plans">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="plan-card free">
                                    <div class="plan-header">
                                        <h2>Free Plan</h2>
                                        <div class="price">$0<span>/month</span></div>
                                    </div>
                                    <div class="plan-features">
                                        <ul>
                                            <li><i class="fas fa-check"></i> Connect 1 calendar</li>
                                            <li><i class="fas fa-check"></i> Basic calendar analytics</li>
                                            <li><i class="fas fa-check"></i> 3 calendar grades total</li>
                                            <li><i class="fas fa-times"></i> Advanced AI recommendations</li>
                                            <li><i class="fas fa-times"></i> Priority support</li>
                                        </ul>
                                    </div>
                                    <div class="plan-footer">
                                        <p>Your current plan</p>
                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-primary" role="progressbar" 
                                                style="width: {{ (auth()->user()->gradesUsed() / 3) * 100 }}%" 
                                                aria-valuenow="{{ auth()->user()->gradesUsed() }}" 
                                                aria-valuemin="0" aria-valuemax="3">
                                            </div>
                                        </div>
                                        <p class="grades-remaining">{{ 3 - auth()->user()->gradesUsed() }} grades remaining</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="plan-card premium">
                                    <div class="plan-header">
                                        <h2>Premium Plan</h2>
                                        <div class="billing-toggle">
                                            <div class="toggle-container">
                                                <span id="monthly-label" class="toggle-label active">Monthly</span>
                                                <label class="switch">
                                                    <input type="checkbox" id="billing-toggle">
                                                    <span class="slider round"></span>
                                                </label>
                                                <span id="yearly-label" class="toggle-label">Yearly</span>
                                            </div>
                                        </div>
                                        <div id="monthly-price" class="price-display active">
                                            <div class="price">$9<span>/month</span></div>
                                            <div class="price-note">Billed monthly</div>
                                        </div>
                                        <div id="yearly-price" class="price-display">
                                            <div class="price">$89<span>/year</span></div>
                                            <div class="price-note">Save $19 per year</div>
                                        </div>
                                    </div>
                                    <div class="plan-features">
                                        <ul>
                                            <li><i class="fas fa-check"></i> Connect unlimited calendars</li>
                                            <li><i class="fas fa-check"></i> Advanced calendar analytics</li>
                                            <li><i class="fas fa-check"></i> Unlimited calendar grades</li>
                                            <li><i class="fas fa-check"></i> Advanced AI recommendations</li>
                                            <li><i class="fas fa-check"></i> Priority support</li>
                                        </ul>
                                    </div>
                                    <div class="plan-footer">
                                        <button id="checkout-button" class="btn btn-primary btn-lg btn-block subscribe-btn" data-plan="monthly">Upgrade Now</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="subscription-benefits mt-5">
                        <h2>Why Upgrade to Premium?</h2>
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <div class="benefit-card">
                                    <div class="benefit-icon">
                                        <i class="fas fa-infinity"></i>
                                    </div>
                                    <h3>Unlimited Grades</h3>
                                    <p>Grade your calendar as often as you want to continuously improve your time management.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="benefit-card">
                                    <div class="benefit-icon">
                                        <i class="fas fa-brain"></i>
                                    </div>
                                    <h3>Advanced AI</h3>
                                    <p>Get personalized recommendations to optimize your calendar based on your unique patterns.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="benefit-card">
                                    <div class="benefit-icon">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                    <h3>Detailed Analytics</h3>
                                    <p>Access comprehensive insights about how you spend your time across all your calendars.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="subscription-faq mt-5">
                        <h2>Frequently Asked Questions</h2>
                        <div class="accordion mt-4" id="subscriptionFAQ">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        Can I cancel my subscription at any time?
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#subscriptionFAQ">
                                    <div class="accordion-body">
                                        Yes, you can cancel your subscription at any time from your account settings. Your subscription will remain active until the end of your current billing period.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        When will I be charged?
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#subscriptionFAQ">
                                    <div class="accordion-body">
                                        Your subscription will begin immediately upon sign-up. You'll be charged the monthly fee at the start of each billing cycle.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                        What payment methods do you accept?
                                    </button>
                                </h2>
                                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#subscriptionFAQ">
                                    <div class="accordion-body">
                                        We accept all major credit cards (Visa, Mastercard, American Express, Discover) as well as PayPal.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingFour">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                        Is my payment information secure?
                                    </button>
                                </h2>
                                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#subscriptionFAQ">
                                    <div class="accordion-body">
                                        Yes, all payment processing is handled securely by Stripe. We never store your full credit card information on our servers.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Spinner Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="loadingModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5>Processing your subscription...</h5>
                <p class="text-muted">Please don't close this window. You'll be redirected to Stripe to complete your payment.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .subscription-card {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }
    
    .card-header {
        background: linear-gradient(to right, var(--primary-purple), var(--primary-teal));
        color: white;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        padding: 15px;
    }
    
    .plan-card {
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 20px;
        height: 100%;
        display: flex;
        flex-direction: column;
        margin-bottom: 20px;
    }
    
    .plan-card.free {
        border: 2px solid #e0e0e0;
    }
    
    .plan-card.premium {
        border: 2px solid var(--primary-purple);
        position: relative;
        overflow: hidden;
    }
    
    .plan-card.premium::before {
        content: "RECOMMENDED";
        position: absolute;
        top: 10px;
        right: -30px;
        background: var(--primary-teal);
        color: white;
        padding: 5px 40px;
        font-size: 12px;
        transform: rotate(45deg);
    }
    
    .plan-header {
        text-align: center;
        margin-bottom: 20px;
    }
    
    .plan-header h2 {
        font-size: 1.5rem;
        margin-bottom: 10px;
    }
    
    /* New toggle switch styles */
    .billing-toggle {
        margin: 20px 0;
    }
    
    .toggle-container {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .toggle-label {
        font-size: 0.9rem;
        color: #666;
        margin: 0 10px;
        cursor: pointer;
    }
    
    .toggle-label.active {
        color: var(--primary-purple);
        font-weight: bold;
    }
    
    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }
    
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
    }
    
    .slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
    }
    
    input:checked + .slider {
        background: linear-gradient(to right, var(--primary-purple), var(--primary-teal));
    }
    
    input:focus + .slider {
        box-shadow: 0 0 1px var(--primary-purple);
    }
    
    input:checked + .slider:before {
        transform: translateX(26px);
    }
    
    .slider.round {
        border-radius: 34px;
    }
    
    .slider.round:before {
        border-radius: 50%;
    }
    
    .price-display {
        display: none;
    }
    
    .price-display.active {
        display: block;
    }
    
    .price {
        font-size: 2.5rem;
        font-weight: bold;
        color: var(--primary-purple);
    }
    
    .price span {
        font-size: 1rem;
        font-weight: normal;
    }
    
    .price-note {
        font-size: 0.8rem;
        color: #666;
    }
    
    .plan-features {
        flex-grow: 1;
    }
    
    .plan-features ul {
        list-style: none;
        padding: 0;
    }
    
    .plan-features li {
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .plan-features i.fa-check {
        color: var(--primary-teal);
        margin-right: 10px;
    }
    
    .plan-features i.fa-times {
        color: #ccc;
        margin-right: 10px;
    }
    
    .plan-footer {
        margin-top: 20px;
        text-align: center;
    }
    
    .grades-remaining {
        font-size: 0.9rem;
        color: #666;
    }
    
    .subscribe-btn {
        background: linear-gradient(to right, var(--primary-purple), var(--primary-teal));
        border: none;
        padding: 12px;
        font-size: 1.1rem;
        position: relative;
        z-index: 1;
        overflow: hidden;
    }
    
    .subscribe-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(to right, var(--primary-teal), var(--primary-purple));
        transition: left 0.3s ease;
        z-index: -1;
    }
    
    .subscribe-btn:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        transform: translateY(-2px);
    }
    
    .subscribe-btn:hover::before {
        left: 0;
    }
    
    .subscription-benefits h2, .subscription-faq h2 {
        text-align: center;
        color: var(--primary-purple);
        margin-bottom: 20px;
    }
    
    .benefit-card {
        text-align: center;
        padding: 20px;
        border-radius: 10px;
        background-color: #f9f9f9;
        height: 100%;
    }
    
    .benefit-icon {
        font-size: 2.5rem;
        color: var(--primary-teal);
        margin-bottom: 15px;
    }
    
    .benefit-card h3 {
        font-size: 1.2rem;
        margin-bottom: 10px;
        color: var(--primary-purple);
    }
    
    .accordion-button:not(.collapsed) {
        background-color: rgba(126, 87, 255, 0.1);
        color: var(--primary-purple);
    }
    
    .accordion-button:focus {
        box-shadow: 0 0 0 0.25rem rgba(126, 87, 255, 0.25);
    }
</style>
@endsection

@section('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Stripe
        const stripe = Stripe('{{ config('services.stripe.public') }}');
        
        // Handle plan selection with toggle switch
        const billingToggle = document.getElementById('billing-toggle');
        const monthlyLabel = document.getElementById('monthly-label');
        const yearlyLabel = document.getElementById('yearly-label');
        const monthlyPrice = document.getElementById('monthly-price');
        const yearlyPrice = document.getElementById('yearly-price');
        const checkoutButton = document.getElementById('checkout-button');
        
        // Set initial state
        checkoutButton.setAttribute('data-plan', 'monthly');
        
        // Toggle between monthly and yearly
        billingToggle.addEventListener('change', function() {
            if (this.checked) {
                // Yearly
                monthlyLabel.classList.remove('active');
                yearlyLabel.classList.add('active');
                monthlyPrice.classList.remove('active');
                yearlyPrice.classList.add('active');
                checkoutButton.setAttribute('data-plan', 'yearly');
            } else {
                // Monthly
                yearlyLabel.classList.remove('active');
                monthlyLabel.classList.add('active');
                yearlyPrice.classList.remove('active');
                monthlyPrice.classList.add('active');
                checkoutButton.setAttribute('data-plan', 'monthly');
            }
        });
        
        // Click handlers for labels to improve UX
        monthlyLabel.addEventListener('click', function() {
            billingToggle.checked = false;
            billingToggle.dispatchEvent(new Event('change'));
        });
        
        yearlyLabel.addEventListener('click', function() {
            billingToggle.checked = true;
            billingToggle.dispatchEvent(new Event('change'));
        });
        
        // Handle checkout button click
        checkoutButton.addEventListener('click', function() {
            const plan = this.getAttribute('data-plan');
            
            // Show loading modal
            const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
            loadingModal.show();
            
            // Create checkout session
            fetch('{{ route('checkout.create-session') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    plan: plan
                })
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(session) {
                if (session.id) {
                    // Redirect to Stripe Checkout
                    return stripe.redirectToCheckout({ sessionId: session.id });
                } else {
                    throw new Error('Failed to create checkout session');
                }
            })
            .then(function(result) {
                // If redirectToCheckout fails due to a Stripe error
                if (result.error) {
                    throw new Error(result.error.message);
                }
            })
            .catch(function(error) {
                // Hide loading modal
                loadingModal.hide();
                
                // Show error alert
                const alertHtml = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        ${error.message || 'An error occurred. Please try again.'}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                document.querySelector('.subscription-card').insertAdjacentHTML('beforebegin', alertHtml);
            });
        });
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    });
</script>
@endsection
