@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
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
                                        <div class="price">$9<span>/month</span></div>
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
                                        <button class="btn btn-primary btn-lg btn-block subscribe-btn">Upgrade Now</button>
                                        <p class="trial-note">Includes 7-day free trial</p>
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
                                        How does the 7-day free trial work?
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#subscriptionFAQ">
                                    <div class="accordion-body">
                                        When you subscribe to the Premium plan, you'll get a 7-day free trial. You won't be charged until the trial period ends. You can cancel anytime during the trial period and you won't be charged.
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
    
    .price {
        font-size: 2.5rem;
        font-weight: bold;
        color: var(--primary-purple);
    }
    
    .price span {
        font-size: 1rem;
        font-weight: normal;
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
    }
    
    .trial-note {
        margin-top: 10px;
        font-size: 0.9rem;
        color: #666;
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // This is a placeholder for subscription processing
        // In a real implementation, this would connect to Stripe or another payment processor
        const subscribeBtn = document.querySelector('.subscribe-btn');
        if (subscribeBtn) {
            subscribeBtn.addEventListener('click', function() {
                alert('This would connect to a payment processor in a production environment. For now, consider yourself upgraded to Premium!');
            });
        }
    });
</script>
@endsection
