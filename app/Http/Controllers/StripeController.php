<?php

namespace App\Http\Controllers;

use App\Services\StripeService;
use App\Services\ErrorHandlerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StripeController extends Controller
{
    protected $stripeService;
    protected $errorHandler;

    public function __construct(StripeService $stripeService, ErrorHandlerService $errorHandler)
    {
        $this->stripeService = $stripeService;
        $this->errorHandler = $errorHandler;
        $this->middleware('auth');
    }

    public function createCheckoutSession(Request $request)
    {
        try {
            $user = Auth::user();
            $plan = $request->input('plan');
            $successUrl = $request->input('success_url');
            $cancelUrl = $request->input('cancel_url');

            $session = $this->stripeService->createCheckoutSession(
                $user,
                $plan,
                $successUrl,
                $cancelUrl
            );

            return response()->json(['url' => $session->url]);
        } catch (\Exception $e) {
            return $this->errorHandler->handle($e);
        }
    }

    public function handleCheckoutSuccess()
    {
        try {
            $user = Auth::user();
            $sessionId = request()->get('session_id');

            if (!$sessionId) {
                throw new \Exception('No session ID provided');
            }

            $this->stripeService->handleCheckoutSessionCompleted($sessionId);

            return redirect()->route('dashboard')->with('success', 'Subscription created successfully!');
        } catch (\Exception $e) {
            return $this->errorHandler->handle($e);
        }
    }

    public function cancelSubscription()
    {
        try {
            $user = Auth::user();
            $this->stripeService->cancelSubscription($user);
            
            $user->update([
                'subscription_status' => 'canceled'
            ]);

            return response()->json(['message' => 'Subscription cancelled successfully']);
        } catch (\Exception $e) {
            return $this->errorHandler->handle($e);
        }
    }

    public function updatePaymentMethod(Request $request)
    {
        try {
            $user = Auth::user();
            $paymentMethodId = $request->input('payment_method_id');
            
            $this->stripeService->updatePaymentMethod($user, $paymentMethodId);
            
            return response()->json(['message' => 'Payment method updated successfully']);
        } catch (\Exception $e) {
            return $this->errorHandler->handle($e);
        }
    }

    public function getPaymentMethods()
    {
        try {
            $user = Auth::user();
            $paymentMethods = $this->stripeService->getPaymentMethods($user);
            
            return response()->json(['payment_methods' => $paymentMethods]);
        } catch (\Exception $e) {
            return $this->errorHandler->handle($e);
        }
    }

    public function getSubscriptionStatus()
    {
        try {
            $user = Auth::user();
            $status = $this->stripeService->getSubscriptionStatus($user);
            
            return response()->json(['status' => $status]);
        } catch (\Exception $e) {
            return $this->errorHandler->handle($e);
        }
    }
} 