<?php

namespace App\Http\Controllers;

use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class CheckoutController extends Controller
{
    protected $stripeService;

    /**
     * Create a new controller instance.
     *
     * @param StripeService $stripeService
     * @return void
     */
    public function __construct(StripeService $stripeService)
    {
        $this->middleware('auth')->except(['handleWebhook']);
        $this->stripeService = $stripeService;
    }

    /**
     * Create a checkout session and redirect to Stripe.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createCheckoutSession(Request $request)
    {
        $plan = $request->input('plan', 'monthly');
        
        // Validate plan
        if (!in_array($plan, ['monthly', 'yearly'])) {
            return response()->json(['error' => 'Invalid subscription plan selected.'], 400);
        }

        $user = Auth::user();
        
        // Check if user already has an active subscription
        if ($user->subscribed()) {
            return response()->json(['error' => 'You already have an active subscription.'], 400);
        }

        // Create success and cancel URLs
        $successUrl = route('checkout.success', ['session_id' => '{CHECKOUT_SESSION_ID}']);
        $cancelUrl = route('subscription');

        // Create checkout session
        $sessionId = $this->stripeService->createCheckoutSession(
            $user,
            $plan,
            $successUrl,
            $cancelUrl
        );

        if (!$sessionId) {
            Log::error('Failed to create checkout session for user: ' . $user->id);
            return response()->json(['error' => 'Unable to process your request. Please try again later.'], 500);
        }

        // Return session ID for frontend to redirect
        return response()->json(['id' => $sessionId]);
    }

    /**
     * Handle successful checkout.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function success(Request $request)
    {
        $sessionId = $request->get('session_id');
        
        if (!$sessionId) {
            return redirect()->route('subscription')
                ->with('error', 'Invalid checkout session.');
        }

        // Process the successful checkout
        $success = $this->stripeService->handleCheckoutSessionCompleted($sessionId);
        
        if ($success) {
            return redirect()->route('dashboard')
                ->with('success', 'Thank you for subscribing to Premium! Your account has been upgraded.');
        } else {
            return redirect()->route('subscription')
                ->with('error', 'There was an issue processing your subscription. Please contact support.');
        }
    }

    /**
     * Cancel subscription.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancelSubscription()
    {
        $user = Auth::user();
        
        if (!$user->subscribed()) {
            return redirect()->route('settings')
                ->with('info', 'You do not have an active subscription to cancel.');
        }

        $success = $this->stripeService->cancelSubscription($user);
        
        if ($success) {
            return redirect()->route('settings')
                ->with('success', 'Your subscription has been canceled. You will have access until the end of your current billing period.');
        } else {
            return redirect()->route('settings')
                ->with('error', 'There was an issue canceling your subscription. Please contact support.');
        }
    }

    /**
     * Handle Stripe webhook events.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            // Verify the webhook signature
            $event = Webhook::constructEvent(
                $payload, $sigHeader, $webhookSecret
            );

            // Handle the event
            switch ($event->type) {
                case 'checkout.session.completed':
                    $session = $event->data->object;
                    $this->stripeService->handleCheckoutSessionCompleted($session->id);
                    break;
                    
                case 'customer.subscription.updated':
                    $subscription = $event->data->object;
                    // Handle subscription updates if needed
                    break;
                    
                case 'customer.subscription.deleted':
                    $subscription = $event->data->object;
                    // Handle subscription cancellation if needed
                    break;
                    
                case 'invoice.payment_failed':
                    $invoice = $event->data->object;
                    // Handle failed payment if needed
                    break;
                    
                default:
                    // Unexpected event type
                    Log::info('Unhandled event type: ' . $event->type);
            }

            return response()->json(['status' => 'success']);
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            Log::error('Webhook signature verification failed: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\Exception $e) {
            // Other errors
            Log::error('Webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook error'], 500);
        }
    }
}
