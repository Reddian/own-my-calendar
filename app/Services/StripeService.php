<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Exception;

class StripeService
{
    protected $stripe;
    protected $apiKey;
    protected $webhookSecret;
    protected $priceId;

    public function __construct()
    {
        $this->apiKey = config('services.stripe.secret');
        $this->webhookSecret = config('services.stripe.webhook_secret');
        $this->priceId = config('services.stripe.price_id');
        
        // Only initialize Stripe if we have an API key
        if ($this->apiKey) {
            $this->stripe = new StripeClient($this->apiKey);
        }
    }

    /**
     * Create a checkout session for subscription
     *
     * @param User $user
     * @return array
     */
    public function createCheckoutSession(User $user)
    {
        try {
            if (!$this->stripe) {
                throw new Exception('Stripe API key not configured');
            }
            
            $session = $this->stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'customer_email' => $user->email,
                'line_items' => [
                    [
                        'price' => $this->priceId,
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'subscription',
                'success_url' => config('app.url') . '/subscription/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => config('app.url') . '/subscription/cancel',
                'metadata' => [
                    'user_id' => $user->id,
                ],
            ]);

            return [
                'success' => true,
                'session_id' => $session->id,
                'checkout_url' => $session->url,
            ];
        } catch (Exception $e) {
            Log::error('Stripe checkout session creation error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle webhook events from Stripe
     *
     * @param string $payload
     * @param string $sigHeader
     * @return bool
     */
    public function handleWebhook($payload, $sigHeader)
    {
        try {
            if (!$this->stripe || !$this->webhookSecret) {
                throw new Exception('Stripe not properly configured');
            }
            
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, $this->webhookSecret
            );

            // Handle the event
            switch ($event->type) {
                case 'checkout.session.completed':
                    return $this->handleCheckoutSessionCompleted($event->data->object);
                
                case 'customer.subscription.created':
                    return $this->handleSubscriptionCreated($event->data->object);
                
                case 'customer.subscription.updated':
                    return $this->handleSubscriptionUpdated($event->data->object);
                
                case 'customer.subscription.deleted':
                    return $this->handleSubscriptionDeleted($event->data->object);
                
                default:
                    Log::info('Unhandled event type: ' . $event->type);
                    return true;
            }
        } catch (Exception $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle checkout.session.completed event
     *
     * @param object $session
     * @return bool
     */
    protected function handleCheckoutSessionCompleted($session)
    {
        try {
            $userId = $session->metadata->user_id;
            $user = User::find($userId);

            if (!$user) {
                Log::error('User not found for checkout session: ' . $session->id);
                return false;
            }

            // The subscription will be created by the customer.subscription.created event
            Log::info('Checkout session completed for user: ' . $userId);
            return true;
        } catch (Exception $e) {
            Log::error('Error handling checkout session completed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle customer.subscription.created event
     *
     * @param object $subscription
     * @return bool
     */
    protected function handleSubscriptionCreated($subscription)
    {
        try {
            if (!$this->stripe) {
                throw new Exception('Stripe not properly configured');
            }
            
            $customer = $this->stripe->customers->retrieve($subscription->customer);
            $user = User::where('email', $customer->email)->first();

            if (!$user) {
                Log::error('User not found for subscription: ' . $subscription->id);
                return false;
            }

            // Create or update subscription record
            Subscription::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'stripe_id' => $subscription->id,
                    'stripe_status' => $subscription->status,
                    'stripe_price' => $subscription->items->data[0]->price->id,
                    'quantity' => $subscription->items->data[0]->quantity,
                    'trial_ends_at' => $subscription->trial_end ? date('Y-m-d H:i:s', $subscription->trial_end) : null,
                    'ends_at' => null,
                    'grades_limit' => PHP_INT_MAX, // Unlimited grades for paid subscription
                ]
            );

            Log::info('Subscription created for user: ' . $user->id);
            return true;
        } catch (Exception $e) {
            Log::error('Error handling subscription created: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle customer.subscription.updated event
     *
     * @param object $subscription
     * @return bool
     */
    protected function handleSubscriptionUpdated($subscription)
    {
        try {
            $subscriptionRecord = Subscription::where('stripe_id', $subscription->id)->first();

            if (!$subscriptionRecord) {
                Log::error('Subscription not found: ' . $subscription->id);
                return false;
            }

            $subscriptionRecord->update([
                'stripe_status' => $subscription->status,
                'stripe_price' => $subscription->items->data[0]->price->id,
                'quantity' => $subscription->items->data[0]->quantity,
                'trial_ends_at' => $subscription->trial_end ? date('Y-m-d H:i:s', $subscription->trial_end) : null,
                'ends_at' => $subscription->cancel_at ? date('Y-m-d H:i:s', $subscription->cancel_at) : null,
            ]);

            Log::info('Subscription updated: ' . $subscription->id);
            return true;
        } catch (Exception $e) {
            Log::error('Error handling subscription updated: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle customer.subscription.deleted event
     *
     * @param object $subscription
     * @return bool
     */
    protected function handleSubscriptionDeleted($subscription)
    {
        try {
            $subscriptionRecord = Subscription::where('stripe_id', $subscription->id)->first();

            if (!$subscriptionRecord) {
                Log::error('Subscription not found: ' . $subscription->id);
                return false;
            }

            $subscriptionRecord->update([
                'stripe_status' => $subscription->status,
                'ends_at' => date('Y-m-d H:i:s', $subscription->ended_at),
                'grades_limit' => 3, // Reset to free tier limit
            ]);

            Log::info('Subscription deleted: ' . $subscription->id);
            return true;
        } catch (Exception $e) {
            Log::error('Error handling subscription deleted: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cancel a subscription
     *
     * @param User $user
     * @return array
     */
    public function cancelSubscription(User $user)
    {
        try {
            if (!$this->stripe) {
                throw new Exception('Stripe not properly configured');
            }
            
            $subscription = Subscription::where('user_id', $user->id)
                ->where('stripe_status', 'active')
                ->first();

            if (!$subscription || !$subscription->stripe_id) {
                return [
                    'success' => false,
                    'error' => 'No active subscription found',
                ];
            }

            $stripeSubscription = $this->stripe->subscriptions->cancel(
                $subscription->stripe_id,
                ['prorate' => true]
            );

            $subscription->update([
                'stripe_status' => $stripeSubscription->status,
                'ends_at' => date('Y-m-d H:i:s', $stripeSubscription->cancel_at),
            ]);

            return [
                'success' => true,
                'message' => 'Subscription cancelled successfully',
            ];
        } catch (Exception $e) {
            Log::error('Error cancelling subscription: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get subscription details for a user
     *
     * @param User $user
     * @return array
     */
    public function getSubscriptionDetails(User $user)
    {
        try {
            $subscription = Subscription::where('user_id', $user->id)->first();

            if (!$subscription) {
                return [
                    'success' => true,
                    'subscription' => null,
                    'plan' => 'free',
                    'grades_used' => 0,
                    'grades_limit' => 3,
                    'active' => false,
                ];
            }

            return [
                'success' => true,
                'subscription' => $subscription,
                'plan' => $subscription->isActive() && !$subscription->onTrial() ? 'premium' : 'free',
                'grades_used' => $subscription->grades_used,
                'grades_limit' => $subscription->grades_limit,
                'active' => $subscription->isActive(),
                'trial' => $subscription->onTrial(),
                'canceled' => $subscription->canceled(),
                'expired' => $subscription->expired(),
            ];
        } catch (Exception $e) {
            Log::error('Error getting subscription details: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
