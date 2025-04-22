<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Get the current user's subscription details
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $result = $this->stripeService->getSubscriptionDetails(Auth::user());
            
            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json(['error' => $result['error']], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error getting subscription details: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get subscription details'], 500);
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
            $result = $this->stripeService->createCheckoutSession(Auth::user());
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'session_id' => $result['session_id'],
                    'checkout_url' => $result['checkout_url'],
                ]);
            } else {
                return response()->json(['error' => $result['error']], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error creating checkout session: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create checkout session'], 500);
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
        // The actual subscription creation is handled by the webhook
        return redirect()->to('/dashboard?subscription=success');
    }

    /**
     * Handle subscription cancellation
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel()
    {
        // This is a redirect endpoint after cancelled checkout
        return redirect()->to('/dashboard?subscription=cancelled');
    }

    /**
     * Cancel the current subscription
     *
     * @return \Illuminate\Http\Response
     */
    public function cancelSubscription()
    {
        try {
            $result = $this->stripeService->cancelSubscription(Auth::user());
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                ]);
            } else {
                return response()->json(['error' => $result['error']], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error cancelling subscription: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to cancel subscription'], 500);
        }
    }

    /**
     * Handle Stripe webhooks
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $result = $this->stripeService->handleWebhook($payload, $sigHeader);
            
            if ($result) {
                return response()->json(['success' => true]);
            } else {
                return response()->json(['error' => 'Webhook handling failed'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Check if the user can grade their calendar
     * 
     * @return \Illuminate\Http\Response
     */
    public function canGradeCalendar()
    {
        try {
            $subscription = Subscription::where('user_id', Auth::id())->first();
            
            // If no subscription exists, create a free tier subscription
            if (!$subscription) {
                $subscription = Subscription::create([
                    'user_id' => Auth::id(),
                    'grades_limit' => 3,
                ]);
            }
            
            $canGrade = $subscription->hasGradesRemaining();
            
            return response()->json([
                'can_grade' => $canGrade,
                'grades_used' => $subscription->grades_used,
                'grades_limit' => $subscription->grades_limit,
                'is_premium' => $subscription->isActive() && !$subscription->onTrial(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking grade eligibility: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to check grade eligibility'], 500);
        }
    }

    /**
     * Increment the number of grades used
     * 
     * @return \Illuminate\Http\Response
     */
    public function incrementGradesUsed()
    {
        try {
            $subscription = Subscription::where('user_id', Auth::id())->first();
            
            if (!$subscription) {
                return response()->json(['error' => 'No subscription found'], 404);
            }
            
            if (!$subscription->hasGradesRemaining()) {
                return response()->json([
                    'error' => 'Grade limit reached',
                    'upgrade_required' => true,
                ], 403);
            }
            
            $subscription->incrementGradesUsed();
            
            return response()->json([
                'success' => true,
                'grades_used' => $subscription->grades_used,
                'grades_limit' => $subscription->grades_limit,
            ]);
        } catch (\Exception $e) {
            Log::error('Error incrementing grades used: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to increment grades used'], 500);
        }
    }
}
