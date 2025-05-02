<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
        $this->middleware("auth:sanctum");
    }

    /**
     * Get the current user's subscription details (LEGACY - uses StripeService)
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // This method might still be used elsewhere, but getStatus should be preferred for Settings page
        try {
            $user = Auth::user();
            if (!$user->subscription_id) { // Check if user has a stripe subscription ID
                 // If no stripe_id, fetch from local table or return default free status
                 return $this->getStatus(request());
            }
            // Assuming subscription_id is the Stripe Subscription ID on the user model
            $result = $this->stripeService->getSubscriptionDetails($user->subscription_id);

            if ($result) {
                return response()->json($result);
            } else {
                // Fallback to local table if Stripe call fails?
                Log::warning("StripeService->getSubscriptionDetails failed for user {$user->id}, falling back to local status.");
                return $this->getStatus(request());
                // return response()->json(["error" => "Failed to get subscription details from Stripe"], 500);
            }
        } catch (\Exception $e) {
            Log::error("Error getting subscription details: " . $e->getMessage());
            return response()->json(["error" => "Failed to get subscription details"], 500);
        }
    }

    /**
     * Get the current user's subscription status from the local database.
     * This is the preferred method for the Settings page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatus(Request $request)
    {
        $user = Auth::user();
        Log::info("Fetching subscription status from local DB", ["user_id" => $user->id]);

        try {
            $subscription = Subscription::where("user_id", $user->id)->first();

            // If no subscription record, create a default free one
            if (!$subscription) {
                Log::info("No local subscription found for user {$user->id}, creating default free tier.");
                $subscription = Subscription::create([
                    "user_id" => $user->id,
                    "stripe_status" => "incomplete", // Or 'free'
                    "grades_limit" => 3, // Default free limit
                    "grades_used" => 0,
                ]);
            }

            $isActive = $subscription->isActive();
            $planName = $isActive ? ($subscription->stripe_price ? "Premium Plan" : "Trial Plan") : "Free Plan"; // Basic plan naming
            $nextBillingDate = null;
            $cancelAtDate = null;

            // Fetch details from Stripe ONLY if needed and we have a stripe_id
            // We might need this for the exact next billing date or cancellation date
            // For now, let's rely mostly on local data
            if ($subscription->stripe_id && $isActive && !$subscription->canceled()) {
                 // Potentially fetch from Stripe to get precise dates, but avoid if possible
                 // For now, we can estimate or use local 'ends_at' if it represents period end
                 // Let's assume 'ends_at' is only set on cancellation for now.
                 // If stripe_status is active, we might need Stripe API for next billing date.
                 // Let's skip the Stripe call for now to adhere to the request.
                 // We can refine this later if exact dates are crucial.
            }

            if ($subscription->canceled()) {
                $cancelAtDate = $subscription->ends_at ? $subscription->ends_at->format("Y-m-d") : null;
            }

            return response()->json([
                "isActive" => $isActive,
                "planName" => $planName,
                "nextBillingDate" => $nextBillingDate, // Placeholder - needs refinement if exact date required
                "gradesRemaining" => $isActive ? null : max(0, $subscription->grades_limit - $subscription->grades_used),
                "cancelAtPeriodEnd" => $subscription->canceled(),
                "cancelAtDate" => $cancelAtDate,
                // Add raw status for debugging if needed
                // "raw_stripe_status" => $subscription->stripe_status,
                // "raw_ends_at" => $subscription->ends_at,
                // "raw_trial_ends_at" => $subscription->trial_ends_at,
            ]);

        } catch (\Exception $e) {
            Log::error("Error fetching local subscription status for user {$user->id}: " . $e->getMessage());
            return response()->json(["message" => "Failed to load subscription status."], 500);
        }
    }


    /**
     * Create a checkout session for subscription
     *
     * @return \Illuminate\Http\Response
     */
    public function createCheckoutSession()
    {
        try {
            // Assuming StripeService handles creating the session URL
            $result = $this->stripeService->createCheckoutSession(
                Auth::user(),
                request("plan", "monthly"), // Default to monthly if not specified
                route("subscription.success"), // Use named routes
                route("subscription.cancel")
            );

            if ($result) {
                // The service should return the session URL or ID
                // Assuming it returns an array with 'checkout_url'
                return response()->json([
                    "success" => true,
                    "checkout_url" => $result, // Adjust based on StripeService actual return
                ]);
            } else {
                 Log::error("StripeService->createCheckoutSession returned null for user " . Auth::id());
                return response()->json(["error" => "Failed to create checkout session."], 500);
            }
        } catch (\Exception $e) {
            Log::error("Error creating checkout session: " . $e->getMessage());
            return response()->json(["error" => "Failed to create checkout session"], 500);
        }
    }

    /**
     * Handle subscription success
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function success(Request $request)
    {
        // This is a redirect endpoint after successful checkout
        // The actual subscription creation/update is handled by the webhook
        // Redirect to settings page with a success message
        return redirect()->to("/settings#subscription?stripe_checkout=success");
    }

    /**
     * Handle subscription cancellation during checkout
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel()
    {
        // This is a redirect endpoint after cancelled checkout
        // Redirect to settings page with a cancellation message
        return redirect()->to("/settings#subscription?stripe_checkout=cancel");
    }

    /**
     * Cancel the current subscription via Stripe API and update local DB via webhook (ideally)
     *
     * @return \Illuminate\Http\Response
     */
    public function cancelSubscription()
    {
        $user = Auth::user();
        Log::info("Attempting to cancel subscription for user", ["user_id" => $user->id]);
        try {
            $subscription = Subscription::where("user_id", $user->id)->whereNotNull("stripe_id")->first();

            if (!$subscription || !$subscription->stripe_id) {
                 Log::warning("No active Stripe subscription found locally to cancel for user", ["user_id" => $user->id]);
                return response()->json(["error" => "No active subscription found to cancel."], 404);
            }

            // Call Stripe to cancel at period end
            $stripeSub = $this->stripeService->stripe->subscriptions->update(
                $subscription->stripe_id,
                ["cancel_at_period_end" => true]
            );

            // Optionally update local status immediately, though webhook is preferred
            $subscription->update([
                "ends_at" => Carbon::createFromTimestamp($stripeSub->cancel_at),
                "stripe_status" => $stripeSub->status // Should remain 'active' until period end
            ]);
            Log::info("Stripe subscription set to cancel at period end for user", ["user_id" => $user->id, "stripe_id" => $subscription->stripe_id]);

            return response()->json([
                "success" => true,
                "message" => "Subscription successfully set to cancel at the end of the current period.",
            ]);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error("Stripe API error cancelling subscription for user {$user->id}: " . $e->getMessage());
            return response()->json(["error" => "Failed to cancel subscription via Stripe. Please contact support."], 500);
        } catch (\Exception $e) {
            Log::error("Error cancelling subscription for user {$user->id}: " . $e->getMessage());
            return response()->json(["error" => "An unexpected error occurred while cancelling the subscription."], 500);
        }
    }

    /**
     * Handle Stripe webhooks (Placeholder - Implementation likely in StripeService or dedicated controller)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handleWebhook(Request $request)
    {
        // It's generally better to have a dedicated WebhookController
        // Or delegate fully to StripeService
        Log::warning("handleWebhook called in SubscriptionController - should be handled elsewhere.");
        // $payload = $request->getContent();
        // $sigHeader = $request->header("Stripe-Signature");
        // try {
        //     $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, config("services.stripe.webhook_secret"));
        //     // Handle the event (e.g., customer.subscription.updated, invoice.payment_failed)
        //     // Update local Subscription model based on event data
        //     return response()->json(["success" => true]);
        // } catch (\UnexpectedValueException $e) {
        //     Log::error("Webhook Error: Invalid payload.");
        //     return response()->json(["error" => "Invalid payload"], 400);
        // } catch (\Stripe\Exception\SignatureVerificationException $e) {
        //     Log::error("Webhook Error: Invalid signature.");
        //     return response()->json(["error" => "Invalid signature"], 400);
        // } catch (\Exception $e) {
        //     Log::error("Webhook handling error: " . $e->getMessage());
        //     return response()->json(["error" => "Webhook handling failed"], 500);
        // }
        return response()->json(["message" => "Webhook received but not processed by this controller."]);
    }

    /**
     * Check if the user can grade their calendar based on local subscription status
     *
     * @return \Illuminate\Http\Response
     */
    public function canGradeCalendar()
    {
        try {
            $user = Auth::user();
            $subscription = Subscription::firstOrCreate(
                ["user_id" => $user->id],
                ["grades_limit" => 3, "grades_used" => 0, "stripe_status" => "incomplete"]
            );

            $canGrade = $subscription->hasGradesRemaining();

            return response()->json([
                "can_grade" => $canGrade,
                "grades_used" => $subscription->grades_used,
                "grades_limit" => $subscription->grades_limit,
                "is_premium" => $subscription->isActive() && !$subscription->onTrial(),
            ]);
        } catch (\Exception $e) {
            Log::error("Error checking grade eligibility: " . $e->getMessage());
            return response()->json(["error" => "Failed to check grade eligibility"], 500);
        }
    }

    /**
     * Increment the number of grades used in the local subscription record
     *
     * @return \Illuminate\Http\Response
     */
    public function incrementGradesUsed()
    {
        try {
            $user = Auth::user();
            $subscription = Subscription::where("user_id", $user->id)->first();

            if (!$subscription) {
                 // Should have been created by canGradeCalendar, but handle defensively
                 $subscription = Subscription::create([
                    "user_id" => $user->id,
                    "grades_limit" => 3, 
                    "grades_used" => 0, 
                    "stripe_status" => "incomplete"
                ]);
            }

            if (!$subscription->hasGradesRemaining()) {
                return response()->json([
                    "error" => "Grade limit reached",
                    "upgrade_required" => true,
                ], 403);
            }

            $subscription->incrementGradesUsed();

            return response()->json([
                "success" => true,
                "grades_used" => $subscription->grades_used,
                "grades_limit" => $subscription->grades_limit,
            ]);
        } catch (\Exception $e) {
            Log::error("Error incrementing grades used: " . $e->getMessage());
            return response()->json(["error" => "Failed to increment grades used"], 500);
        }
    }
}

