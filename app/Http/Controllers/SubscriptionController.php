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

    // ... other methods ...

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

            if ($subscription->canceled()) {
                $cancelAtDate = $subscription->ends_at ? $subscription->ends_at->format("Y-m-d") : null;
            }

            return response()->json([
                "isActive" => $isActive,
                "planName" => $planName,
                "nextBillingDate" => $nextBillingDate, // Placeholder
                "gradesRemaining" => $isActive ? null : max(0, $subscription->grades_limit - $subscription->grades_used),
                "cancelAtPeriodEnd" => $subscription->canceled(),
                "cancelAtDate" => $cancelAtDate,
            ]);

        } catch (\Exception $e) {
            Log::error("Error fetching local subscription status for user {$user->id}: " . $e->getMessage());
            return response()->json(["message" => "Failed to load subscription status."], 500);
        }
    }

    // ... other methods like createCheckoutSession, success, cancel, cancelSubscription, handleWebhook ...

    /**
     * Check if the user can grade their calendar based on local subscription status
     *
     * @return \Illuminate\Http\Response
     */
    public function canGradeCalendar()
    {
        $user = Auth::user();
        Log::info("Checking grading eligibility for user", ["user_id" => $user->id]);
        try {
            // Use firstOrCreate to ensure a record always exists
            $subscription = Subscription::firstOrCreate(
                ["user_id" => $user->id],
                [
                    "grades_limit" => 3, // Default free limit
                    "grades_used" => 0,
                    "stripe_status" => "incomplete", // Default status
                    // Ensure other potentially non-nullable fields have defaults if necessary
                ]
            );

            // Defensive check: Ensure grades_limit is not null
            if (is_null($subscription->grades_limit)) {
                Log::warning("Subscription grades_limit is null, defaulting to 0 for check.", ["user_id" => $user->id, "subscription_id" => $subscription->id]);
                $subscription->grades_limit = 0; // Assign a default for the check
            }
            
            // Defensive check: Ensure grades_used is not null
             if (is_null($subscription->grades_used)) {
                Log::warning("Subscription grades_used is null, defaulting to 0 for check.", ["user_id" => $user->id, "subscription_id" => $subscription->id]);
                $subscription->grades_used = 0; // Assign a default for the check
            }

            $canGrade = $subscription->hasGradesRemaining();
            $reason = !$canGrade ? "Grade limit reached for the current period." : null;

            Log::info("Grading eligibility check result", [
                "user_id" => $user->id,
                "can_grade" => $canGrade,
                "grades_used" => $subscription->grades_used,
                "grades_limit" => $subscription->grades_limit,
                "is_active" => $subscription->isActive(),
                "on_trial" => $subscription->onTrial()
            ]);

            return response()->json([
                "can_grade" => $canGrade,
                "reason" => $reason, // Add reason if cannot grade
                "grades_used" => $subscription->grades_used,
                "grades_limit" => $subscription->grades_limit,
                "is_premium" => $subscription->isActive() && !$subscription->onTrial(),
            ]);

        } catch (\Exception $e) {
            // Log the detailed error
            Log::error("Error checking grade eligibility for user {$user->id}: " . $e->getMessage(), [
                'exception' => $e
            ]);
            // Return a structured error response with 500 status
            return response()->json([
                "error" => "Could not verify grading ability due to a server error.",
                "can_grade" => false, // Default to false on error
                "reason" => "Server error during eligibility check."
            ], 500);
        }
    }

    /**
     * Increment the number of grades used in the local subscription record
     *
     * @return \Illuminate\Http\Response
     */
    public function incrementGradesUsed()
    {
        $user = Auth::user();
        Log::info("Attempting to increment grades used for user", ["user_id" => $user->id]);
        try {
            $subscription = Subscription::where("user_id", $user->id)->first();

            // Ensure subscription exists (should have been checked by canGradeCalendar)
            if (!$subscription) {
                 Log::error("Attempted to increment grades for user without subscription record", ["user_id" => $user->id]);
                 // Create one defensively, though this indicates a logic flaw elsewhere
                 $subscription = Subscription::create([
                    "user_id" => $user->id,
                    "grades_limit" => 3, 
                    "grades_used" => 0, 
                    "stripe_status" => "incomplete"
                ]);
                 // Do not proceed with increment if created here, force re-check
                 return response()->json(["error" => "Subscription record missing, please try grading again."], 400);
            }
            
            // Re-check if grades are remaining before incrementing (important race condition check)
            if (!$subscription->hasGradesRemaining()) {
                Log::warning("Attempted to increment grades beyond limit for user", ["user_id" => $user->id]);
                return response()->json([
                    "error" => "Grade limit reached",
                    "upgrade_required" => true,
                ], 403); // 403 Forbidden
            }

            // Perform the increment
            $subscription->increment("grades_used");
            Log::info("Successfully incremented grades used for user", ["user_id" => $user->id, "new_count" => $subscription->grades_used]);

            return response()->json([
                "success" => true,
                "grades_used" => $subscription->grades_used,
                "grades_limit" => $subscription->grades_limit,
            ]);
        } catch (\Exception $e) {
            Log::error("Error incrementing grades used for user {$user->id}: " . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(["error" => "Failed to update grade count due to a server error."], 500);
        }
    }
}

