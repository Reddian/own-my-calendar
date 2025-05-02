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
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use Carbon\Carbon;

class StripeService
{
    protected $stripe;

    /**
     * Initialize Stripe with the API key.
     */
    public function __construct()
    {
        Stripe::setApiKey(config("services.stripe.secret"));
        $this->stripe = new \Stripe\StripeClient(config("services.stripe.secret"));
    }

    /**
     * Handle incoming Stripe webhooks.
     *
     * @param string $payload Raw payload from Stripe.
     * @param string $sigHeader Stripe-Signature header.
     * @param string $webhookSecret Your Stripe webhook secret.
     * @return bool True if handled successfully, false otherwise.
     */
    public function handleWebhook(string $payload, string $sigHeader, string $webhookSecret): bool
    {
        try {
            $event = Webhook::constructEvent(
                $payload, $sigHeader, $webhookSecret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            Log::error("Stripe Webhook Error: Invalid payload.", ["exception" => $e->getMessage()]);
            return false;
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            Log::error("Stripe Webhook Error: Invalid signature.", ["exception" => $e->getMessage()]);
            return false;
        }

        Log::info("Stripe Webhook Received", ["type" => $event->type]);

        // Handle the event
        switch ($event->type) {
            case "checkout.session.completed":
                $session = $event->data->object; // contains a 
                Log::info("Handling checkout.session.completed", ["session_id" => $session->id]);
                return $this->handleCheckoutSessionCompleted($session);

            case "customer.subscription.updated":
                $stripeSubscription = $event->data->object; // contains a 
                Log::info("Handling customer.subscription.updated", ["subscription_id" => $stripeSubscription->id]);
                return $this->handleSubscriptionUpdated($stripeSubscription);

            case "customer.subscription.deleted":
                $stripeSubscription = $event->data->object; // contains a 
                Log::info("Handling customer.subscription.deleted", ["subscription_id" => $stripeSubscription->id]);
                return $this->handleSubscriptionDeleted($stripeSubscription);

            case "invoice.payment_failed":
                $invoice = $event->data->object; // contains an 
                Log::info("Handling invoice.payment_failed", ["invoice_id" => $invoice->id, "subscription_id" => $invoice->subscription]);
                // If it's for a subscription, update the status
                if ($invoice->subscription) {
                    return $this->handleSubscriptionPaymentFailed($invoice->subscription);
                }
                break;

            // ... handle other event types
            default:
                Log::info("Received unhandled Stripe event type: " . $event->type);
        }

        return true; // Return true even for unhandled events to acknowledge receipt
    }

    /**
     * Handle the checkout.session.completed event.
     *
     * @param \Stripe\Checkout\Session $session
     * @return bool
     */
    protected function handleCheckoutSessionCompleted(Session $session): bool
    {
        // Check if the session mode is subscription
        if ($session->mode !== "subscription") {
            Log::info("Checkout session {$session->id} is not a subscription.");
            return true; // Not an error, just not relevant
        }

        // Check if subscription ID exists
        if (!$session->subscription) {
            Log::error("Checkout session {$session->id} completed but no subscription ID found.");
            return false;
        }

        // Retrieve user ID from metadata
        $userId = $session->metadata->user_id ?? null;
        if (!$userId) {
            Log::error("User ID not found in metadata for checkout session: " . $session->id);
            return false;
        }

        $user = User::find($userId);
        if (!$user) {
            Log::error("User {$userId} not found for checkout session: " . $session->id);
            return false;
        }

        // Retrieve the subscription details from Stripe to get the current status and period end
        try {
            $stripeSubscription = $this->stripe->subscriptions->retrieve($session->subscription);
        } catch (ApiErrorException $e) {
            Log::error("Failed to retrieve Stripe subscription {$session->subscription} after checkout: " . $e->getMessage());
            return false;
        }

        // Create or update local subscription record
        try {
            Subscription::updateOrCreate(
                ["user_id" => $user->id],
                [
                    "stripe_id" => $stripeSubscription->id,
                    "stripe_status" => $stripeSubscription->status, // Use status from retrieved subscription
                    "stripe_price" => $stripeSubscription->items->data[0]->price->id ?? null,
                    "quantity" => $stripeSubscription->items->data[0]->quantity ?? 1,
                    "trial_ends_at" => $stripeSubscription->trial_end ? Carbon::createFromTimestamp($stripeSubscription->trial_end) : null,
                    "ends_at" => $stripeSubscription->cancel_at ? Carbon::createFromTimestamp($stripeSubscription->cancel_at) : null,
                    "grades_used" => 0, // Reset grades used on new/updated subscription
                    "grades_limit" => 999999, // Assuming paid plans are unlimited
                ]
            );
            Log::info("Local subscription created/updated for user {$user->id} from checkout session {$session->id}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to update local subscription for user {$user->id} after checkout: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle the customer.subscription.updated event.
     *
     * @param \Stripe\Subscription $stripeSubscription
     * @return bool
     */
    protected function handleSubscriptionUpdated(StripeSubscription $stripeSubscription): bool
    {
        $subscription = Subscription::where("stripe_id", $stripeSubscription->id)->first();

        if (!$subscription) {
            Log::warning("Received customer.subscription.updated webhook for unknown stripe_id: " . $stripeSubscription->id);
            // Optionally, try to find user by customer ID and create subscription if missing
            return true; // Acknowledge event even if no local record found
        }

        try {
            $subscription->update([
                "stripe_status" => $stripeSubscription->status,
                "stripe_price" => $stripeSubscription->items->data[0]->price->id ?? $subscription->stripe_price,
                "quantity" => $stripeSubscription->items->data[0]->quantity ?? $subscription->quantity,
                "trial_ends_at" => $stripeSubscription->trial_end ? Carbon::createFromTimestamp($stripeSubscription->trial_end) : null,
                "ends_at" => $stripeSubscription->cancel_at ? Carbon::createFromTimestamp($stripeSubscription->cancel_at) : ($stripeSubscription->ended_at ? Carbon::createFromTimestamp($stripeSubscription->ended_at) : null),
                // Reset grades limit if plan changes?
            ]);
            Log::info("Local subscription updated for stripe_id: " . $stripeSubscription->id);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to update local subscription for stripe_id {$stripeSubscription->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle the customer.subscription.deleted event.
     *
     * @param \Stripe\Subscription $stripeSubscription
     * @return bool
     */
    protected function handleSubscriptionDeleted(StripeSubscription $stripeSubscription): bool
    {
        $subscription = Subscription::where("stripe_id", $stripeSubscription->id)->first();

        if (!$subscription) {
            Log::warning("Received customer.subscription.deleted webhook for unknown stripe_id: " . $stripeSubscription->id);
            return true; // Acknowledge event
        }

        try {
            // Update status and ends_at based on the deleted subscription event
            $subscription->update([
                "stripe_status" => $stripeSubscription->status, // Should be 'canceled' or similar
                "ends_at" => $subscription->ends_at ?? Carbon::createFromTimestamp($stripeSubscription->ended_at ?? $stripeSubscription->canceled_at ?? time()),
            ]);
            Log::info("Local subscription marked as deleted/ended for stripe_id: " . $stripeSubscription->id);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to update local subscription on deletion for stripe_id {$stripeSubscription->id}: " . $e->getMessage());
            return false;
        }
    }

     /**
     * Handle subscription status change due to payment failure.
     *
     * @param string $stripeSubscriptionId
     * @return bool
     */
    protected function handleSubscriptionPaymentFailed(string $stripeSubscriptionId): bool
    {
        $subscription = Subscription::where("stripe_id", $stripeSubscriptionId)->first();

        if (!$subscription) {
            Log::warning("Received invoice.payment_failed webhook for unknown stripe_id: " . $stripeSubscriptionId);
            return true; // Acknowledge event
        }

        try {
            // Retrieve the subscription from Stripe to get the latest status after payment failure
            $stripeSubscription = $this->stripe->subscriptions->retrieve($stripeSubscriptionId);

            $subscription->update([
                "stripe_status" => $stripeSubscription->status, // e.g., 'past_due' or 'unpaid'
                "ends_at" => $stripeSubscription->cancel_at ? Carbon::createFromTimestamp($stripeSubscription->cancel_at) : ($stripeSubscription->ended_at ? Carbon::createFromTimestamp($stripeSubscription->ended_at) : null),
            ]);
            Log::info("Local subscription status updated due to payment failure for stripe_id: " . $stripeSubscriptionId, ["new_status" => $stripeSubscription->status]);
            return true;
        } catch (ApiErrorException $e) {
            Log::error("Stripe API Error retrieving subscription {$stripeSubscriptionId} after payment failure: " . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            Log::error("Failed to update local subscription status after payment failure for stripe_id {$stripeSubscriptionId}: " . $e->getMessage());
            return false;
        }
    }


    // --- Other existing methods --- (createCheckoutSession, getPriceIdForPlan, cancelSubscription, getSubscriptionDetails, etc.)
    // Note: Removed the old handleCheckoutSessionCompleted method as logic is now in the new one.

    /**
     * Create a checkout session for subscription.
     *
     * @param User $user
     * @param string $plan
     * @param string $successUrl
     * @param string $cancelUrl
     * @return string|null
     */
    public function createCheckoutSession(User $user, string $plan, string $successUrl, string $cancelUrl): ?string // Changed return type hint
    {
        try {
            $priceId = $this->getPriceIdForPlan($plan);
            if (!$priceId) {
                Log::error("Invalid plan selected: " . $plan);
                return null;
            }

            // Ensure Stripe customer exists
            if (!$user->stripe_id) {
                $customer = $this->createCustomer($user);
                $user->stripe_id = $customer->id;
            }

            $session = Session::create([
                "payment_method_types" => ["card"],
                "customer" => $user->stripe_id, // Use existing customer ID
                // "customer_email" => $user->email, // Can omit if using customer ID
                "line_items" => [[
                    "price" => $priceId,
                    "quantity" => 1,
                ]],
                "mode" => "subscription",
                "success_url" => $successUrl,
                "cancel_url" => $cancelUrl,
                "metadata" => [
                    "user_id" => $user->id,
                    // "plan" => $plan // Can be inferred from price ID later if needed
                ],
            ]);

            // Return the session URL instead of just the ID
            return $session->url;
        } catch (ApiErrorException $e) {
            Log::error("Stripe API Error creating checkout session: " . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error("General Error creating checkout session: " . $e->getMessage());
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
        $prices = [
            "monthly" => config("services.stripe.price_monthly"),
            "yearly" => config("services.stripe.price_yearly"),
        ];
        return $prices[strtolower($plan)] ?? null;
    }

    /**
     * Cancel a subscription (Called by user action, ideally webhook handles final state).
     *
     * @param User $user
     * @return bool
     */
    // public function cancelSubscription(User $user): bool // Keep original signature if needed elsewhere
    // {
    //     try {
    //         $subscription = $this->stripe->subscriptions->retrieve($user->subscription_id);
    //         $subscription->cancel();
    //         return true;
    //     } catch (ApiErrorException $e) {
    //         Log::error("Stripe subscription cancellation failed: " . $e->getMessage());
    //         throw $e;
    //     }
    // }

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
                "id" => $subscription->id,
                "status" => $subscription->status,
                "current_period_end" => $subscription->current_period_end,
                "cancel_at_period_end" => $subscription->cancel_at_period_end,
                "plan" => [
                    "id" => $subscription->plan->id,
                    "amount" => $subscription->plan->amount,
                    "interval" => $subscription->plan->interval,
                ]
            ];
        } catch (ApiErrorException $e) {
            Log::error("Stripe API Error in getSubscriptionDetails: " . $e->getMessage());
            return null;
        }
    }

    public function createCustomer(User $user)
    {
        // Avoid creating if already exists
        if ($user->stripe_id) {
            try {
                return Customer::retrieve($user->stripe_id);
            } catch (ApiErrorException $e) {
                Log::warning("Failed to retrieve existing Stripe customer {$user->stripe_id}, creating new one.", ["error" => $e->getMessage()]);
            }
        }

        try {
            $customer = Customer::create([
                "email" => $user->email,
                "name" => $user->name,
                "metadata" => [
                    "user_id" => $user->id
                ]
            ]);

            $user->update([
                "stripe_id" => $customer->id
            ]);
            Log::info("Created Stripe customer for user", ["user_id" => $user->id, "stripe_customer_id" => $customer->id]);
            return $customer;
        } catch (ApiErrorException $e) {
            Log::error("Stripe customer creation failed: " . $e->getMessage());
            throw $e;
        }
    }

    // createSubscription, updatePaymentMethod, getSubscriptionStatus, getPaymentMethods, createSetupIntent remain largely the same
    // ... (rest of the methods from the original file) ...

    public function createSubscription(User $user, string $paymentMethodId, string $priceId)
    {
        try {
            // Attach payment method to customer
            $this->stripe->paymentMethods->attach($paymentMethodId, [
                "customer" => $user->stripe_id
            ]);

            // Set as default payment method
            $this->stripe->customers->update($user->stripe_id, [
                "invoice_settings" => [
                    "default_payment_method" => $paymentMethodId
                ]
            ]);

            // Create subscription
            $subscription = $this->stripe->subscriptions->create([
                "customer" => $user->stripe_id,
                "items" => [
                    ["price" => $priceId]
                ],
                "payment_behavior" => "default_incomplete",
                "expand" => ["latest_invoice.payment_intent"]
            ]);

            return $subscription;
        } catch (ApiErrorException $e) {
            Log::error("Stripe subscription creation failed: " . $e->getMessage());
            throw $e;
        }
    }

    public function updatePaymentMethod(User $user, string $paymentMethodId)
    {
        try {
            // Attach new payment method
            $this->stripe->paymentMethods->attach($paymentMethodId, [
                "customer" => $user->stripe_id
            ]);

            // Set as default payment method
            $this->stripe->customers->update($user->stripe_id, [
                "invoice_settings" => [
                    "default_payment_method" => $paymentMethodId
                ]
            ]);

            return true;
        } catch (ApiErrorException $e) {
            Log::error("Stripe payment method update failed: " . $e->getMessage());
            throw $e;
        }
    }

    public function getSubscriptionStatus(User $user)
    {
        try {
            // This method still directly queries Stripe, might be needed for specific checks
            // but getStatus in SubscriptionController should use local DB for general status.
            $localSubscription = Subscription::where("user_id", $user->id)->first();
            if (!$localSubscription || !$localSubscription->stripe_id) {
                 Log::info("getSubscriptionStatus (StripeService): No local or Stripe ID found for user {$user->id}");
                return null; // Or return local status?
            }

            $subscription = $this->stripe->subscriptions->retrieve($localSubscription->stripe_id);
            return $subscription->status;
        } catch (ApiErrorException $e) {
            Log::error("Stripe subscription status check failed: " . $e->getMessage());
            // Fallback to local status?
            $localSubscription = Subscription::where("user_id", $user->id)->first();
            return $localSubscription ? $localSubscription->stripe_status : null;
            // throw $e;
        } catch (\Exception $e) {
             Log::error("General error in getSubscriptionStatus (StripeService) for user {$user->id}: " . $e->getMessage());
             $localSubscription = Subscription::where("user_id", $user->id)->first();
             return $localSubscription ? $localSubscription->stripe_status : null;
        }
    }

    public function getPaymentMethods(User $user)
    {
        try {
            if (!$user->stripe_id) return []; // Return empty if no stripe customer
            $paymentMethods = $this->stripe->paymentMethods->all([
                "customer" => $user->stripe_id,
                "type" => "card"
            ]);

            return $paymentMethods->data;
        } catch (ApiErrorException $e) {
            Log::error("Stripe payment methods retrieval failed: " . $e->getMessage());
            return []; // Return empty on error
            // throw $e;
        } catch (\Exception $e) {
             Log::error("General error retrieving payment methods for user {$user->id}: " . $e->getMessage());
             return [];
        }
    }

    public function createSetupIntent(User $user)
    {
        try {
             if (!$user->stripe_id) {
                $this->createCustomer($user);
             }
            $setupIntent = $this->stripe->setupIntents->create([
                "customer" => $user->stripe_id,
                "payment_method_types" => ["card"]
            ]);

            return $setupIntent;
        } catch (ApiErrorException $e) {
            Log::error("Stripe setup intent creation failed: " . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
             Log::error("General error creating setup intent for user {$user->id}: " . $e->getMessage());
             throw $e;
        }
    }

}

