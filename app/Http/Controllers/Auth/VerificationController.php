<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn\t receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    // protected $redirectTo = 
    // Not used for API

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware("auth:sanctum"); // Use sanctum guard
        $this->middleware("signed")->only("verify"); // Keep for potential web verify route
        $this->middleware("throttle:6,1")->only("verify", "resendApi"); // Throttle API resend
    }

    /**
     * Resend the email verification notification (API version).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendApi(Request $request)
    {
        Log::info("API Resend Verification Email attempt", ["user_id" => $request->user()->id]);
        if ($request->user()->hasVerifiedEmail()) {
            Log::warning("API Resend Verification Email: Already verified", ["user_id" => $request->user()->id]);
            return response()->json(["message" => "Email already verified."], 400);
        }

        $request->user()->sendEmailVerificationNotification();

        Log::info("API Resend Verification Email successful", ["user_id" => $request->user()->id]);
        return response()->json(["status" => "Verification link sent!"], 202); // 202 Accepted
    }

    // Note: The standard `verify` method from VerifiesEmails trait might need adjustments
    // if you intend to handle the actual verification click via API instead of Laravel's default web route.
    // Typically, the user clicks a link in the email which hits a standard Laravel web route,
    // marks the user as verified, and then redirects. The SPA might need to check the user's
    // verification status periodically or after login.
}

