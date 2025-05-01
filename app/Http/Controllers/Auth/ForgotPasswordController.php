<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware("guest");
    }

    /**
     * Send a reset link to the given user (API version).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmailApi(Request $request)
    {
        Log::info("API Password Reset Link request", ["email" => $request->email]);
        $request->validate(["email" => "required|email"]);

        // We will send the password reset link email.
        $response = $this->broker()->sendResetLink(
            $request->only("email")
        );

        if ($response == Password::RESET_LINK_SENT) {
            Log::info("API Password Reset Link sent successfully", ["email" => $request->email]);
            return response()->json(["status" => trans($response)], 200);
        }

        // If an error was returned by the password broker...
        Log::warning("API Password Reset Link failed", ["email" => $request->email, "response" => $response]);
        return response()->json(["email" => [trans($response)]], 422); // Return as validation error
    }
}

