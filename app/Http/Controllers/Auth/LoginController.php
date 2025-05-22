<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request; // Add Request
use Illuminate\Support\Facades\Auth; // Add Auth
use Illuminate\Validation\ValidationException; // Add ValidationException
use Illuminate\Support\Facades\Log; // Add Log for debugging

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login (for traditional web routes, if any remain).
     *
     * @var string
     */
    protected $redirectTo = "/";

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Apply guest middleware to apiLogin, allow auth middleware for apiLogout
        $this->middleware("guest")->except(["logout", "apiLogout"]); 
        $this->middleware("auth:sanctum")->only("apiLogout"); // Use sanctum guard for API logout
    }

    /**
     * Handle a login request from the API (Vue component).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function apiLogin(Request $request)
    {
        Log::info("API Login attempt", ["email" => $request->email]); // DEBUG

        $request->validate([
            $this->username() => "required|string",
            "password" => "required|string",
        ]);

        // Attempt to log the user in using the 'web' guard for session-based auth
        if (Auth::guard("web")->attempt(
            $request->only($this->username(), "password"), 
            $request->filled("remember")
        )) {
            // Regenerate session ID to prevent session fixation
            $request->session()->regenerate();
            Log::info("API Login successful", ["user_id" => Auth::guard("web")->id()]); // DEBUG
            
            // IMPORTANT: Return a JSON response, NOT a redirect.
            // The SPA will handle fetching user data and redirection.
            return response()->json(["message" => "Login successful"], 200); 
        }

        Log::warning("API Login failed", ["email" => $request->email]); // DEBUG
        // If the login attempt was unsuccessful, throw validation exception
        // This will automatically be converted to a 422 JSON response by Laravel
        throw ValidationException::withMessages([
            $this->username() => [trans("auth.failed")],
        ]);
    }

    /**
     * Log the user out of the application via API request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function apiLogout(Request $request)
    {
        Log::info("API Logout attempt", ["user_id" => Auth::id()]); // DEBUG
        Auth::guard("web")->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        Log::info("API Logout successful"); // DEBUG
        return response()->json(["message" => "Logged out successfully."], 204); // 204 No Content is often used for successful logout
    }

    /**
     * Override the standard logout method from AuthenticatesUsers trait
     * to ensure it redirects to the SPA login page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Log::info("Web Logout attempt", ["user_id" => Auth::id()]); // DEBUG
        
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        Log::info("Web Logout successful, redirecting to SPA login"); // DEBUG
        
        // Redirect to the login route which serves the SPA
        return redirect()->route('login');
    }
}

