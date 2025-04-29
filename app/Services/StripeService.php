<?php

namespace App\Services;

use App\Models\User;
use App\Models\Subscription;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Log;
use Stripe\Customer;
use Stripe\Subscription as StripeSubscription;
use Stripe\PaymentMethod;

class StripeService
{
    protected $stripe;

    /**
     * Initialize Stripe with the API key.
     */
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $this->stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
    }

    /**
     * Create a checkout session for subscription.
     *
     * @param User $user
     * @param string $plan
     * @param string $successUrl
     * @param string $cancelUrl
     * @return string|null
     */
    public function createCheckoutSession(User $user, string $plan, string $successUrl, string $cancelUrl): ?string
    {
        try {
            // Determine price ID based on plan
            $priceId = $this->getPriceIdForPlan($plan);
            
            if (!$priceId) {
                Log::error('Invalid plan selected: ' . $plan);
                return null;
            }

            $session = Session::create([
                'payment_method_types' => ['card'],
                'customer_email' => $user->email,
                'line_items' => [[
                    'price' => $priceId,
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'metadata' => [
                    'user_id' => $user->id,
                    'plan' => $plan
                ],
            ]);

            return $session->id;
        } catch (ApiErrorException $e) {
            Log::error('Stripe API Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the Stripe price ID for the given plan.
     *
     * @param string $plan
     * @return string|null
     */
    private function getPriceIdForPlan(string $plan): ?string
    {
        // These would be stored in the .env file in a real application
        $prices = [
            'monthly' => env('STRIPE_PRICE_MONTHLY'),
            'yearly' => env('STRIPE_PRICE_YEARLY'),
        ];

        return $prices[$plan] ?? null;
    }

    /**
     * Handle the checkout session completion.
     *
     * @param string $sessionId
     * @return bool
     */
    public function handleCheckoutSessionCompleted(string $sessionId): bool
    {
        try {
            $session = Session::retrieve($sessionId);
            $userId = $session->metadata->user_id;
            $plan = $session->metadata->plan;
            
            $user = User::find($userId);
            if (!$user) {
                Log::error('User not found for checkout session: ' . $sessionId);
                return false;
            }

            // Create or update subscription
            $subscription = Subscription::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'stripe_id' => $session->subscription,
                    'stripe_status' => 'active',
                    'stripe_price' => $session->amount_total,
                    'grades_used' => 0,
                    'grades_limit' => $plan === 'yearly' ? 999999 : 999999, // Unlimited for both plans
                    'ends_at' => null,
                ]
            );

            return true;
        } catch (ApiErrorException $e) {
            Log::error('Stripe API Error in handleCheckoutSessionCompleted: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cancel a subscription.
     *
     * @param User $user
     * @return bool
     */
    public function cancelSubscription(User $user): bool
    {
        try {
            $subscription = $this->stripe->subscriptions->retrieve($user->subscription_id);
            $subscription->cancel();
            return true;
        } catch (ApiErrorException $e) {
            Log::error('Stripe subscription cancellation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get subscription details from Stripe.
     *
     * @param string $subscriptionId
     * @return array|null
     */
    public function getSubscriptionDetails(string $subscriptionId): ?array
    {
        try {
            $subscription = \Stripe\Subscription::retrieve($subscriptionId);
            
            return [
                'id' => $subscription->id,
                'status' => $subscription->status,
                'current_period_end' => $subscription->current_period_end,
                'cancel_at_period_end' => $subscription->cancel_at_period_end,
                'plan' => [
                    'id' => $subscription->plan->id,
                    'amount' => $subscription->plan->amount,
                    'interval' => $subscription->plan->interval,
                ]
            ];
        } catch (ApiErrorException $e) {
            Log::error('Stripe API Error in getSubscriptionDetails: ' . $e->getMessage());
            return null;
        }
    }

    public function createCustomer(User $user)
    {
        try {
            $customer = Customer::create([
                'email' => $user->email,
                'name' => $user->name,
                'metadata' => [
                    'user_id' => $user->id
                ]
            ]);

            $user->update([
                'stripe_id' => $customer->id
            ]);

            return $customer;
        } catch (ApiErrorException $e) {
            Log::error('Stripe customer creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createSubscription(User $user, string $paymentMethodId, string $priceId)
    {
        try {
            // Attach payment method to customer
            $this->stripe->paymentMethods->attach($paymentMethodId, [
                'customer' => $user->stripe_id
            ]);

            // Set as default payment method
            $this->stripe->customers->update($user->stripe_id, [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethodId
                ]
            ]);

            // Create subscription
            $subscription = $this->stripe->subscriptions->create([
                'customer' => $user->stripe_id,
                'items' => [
                    ['price' => $priceId]
                ],
                'payment_behavior' => 'default_incomplete',
                'expand' => ['latest_invoice.payment_intent']
            ]);

            return $subscription;
        } catch (ApiErrorException $e) {
            Log::error('Stripe subscription creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updatePaymentMethod(User $user, string $paymentMethodId)
    {
        try {
            // Attach new payment method
            $this->stripe->paymentMethods->attach($paymentMethodId, [
                'customer' => $user->stripe_id
            ]);

            // Set as default payment method
            $this->stripe->customers->update($user->stripe_id, [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethodId
                ]
            ]);

            return true;
        } catch (ApiErrorException $e) {
            Log::error('Stripe payment method update failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getSubscriptionStatus(User $user)
    {
        try {
            if (!$user->subscription_id) {
                return null;
            }

            $subscription = $this->stripe->subscriptions->retrieve($user->subscription_id);
            return $subscription->status;
        } catch (ApiErrorException $e) {
            Log::error('Stripe subscription status check failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getPaymentMethods(User $user)
    {
        try {
            $paymentMethods = $this->stripe->paymentMethods->all([
                'customer' => $user->stripe_id,
                'type' => 'card'
            ]);

            return $paymentMethods->data;
        } catch (ApiErrorException $e) {
            Log::error('Stripe payment methods retrieval failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createSetupIntent(User $user)
    {
        try {
            $setupIntent = $this->stripe->setupIntents->create([
                'customer' => $user->stripe_id,
                'payment_method_types' => ['card']
            ]);

            return $setupIntent;
        } catch (ApiErrorException $e) {
            Log::error('Stripe setup intent creation failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
