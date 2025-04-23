<?php

namespace App\Services;

use App\Models\User;
use App\Models\Subscription;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Log;

class StripeService
{
    /**
     * Initialize Stripe with the API key.
     */
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
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
            $subscription = $user->subscription;
            
            if (!$subscription || !$subscription->stripe_id) {
                return false;
            }

            $stripeSubscription = \Stripe\Subscription::retrieve($subscription->stripe_id);
            $stripeSubscription->cancel();

            // Update local subscription
            $subscription->update([
                'stripe_status' => 'canceled',
                'ends_at' => now()->addDays(30), // Subscription remains active until the end of the billing period
            ]);

            return true;
        } catch (ApiErrorException $e) {
            Log::error('Stripe API Error in cancelSubscription: ' . $e->getMessage());
            return false;
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
}
