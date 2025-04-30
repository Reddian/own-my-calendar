<?php

namespace App\Http\Controllers;

use App\Models\GoogleCalendar;
use App\Services\GoogleCalendarService;
use App\Services\ErrorHandlerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class GoogleCalendarController extends Controller
{
    protected $googleCalendarService;
    protected $errorHandler;

    public function __construct(GoogleCalendarService $googleCalendarService, ErrorHandlerService $errorHandler)
    {
        $this->googleCalendarService = $googleCalendarService;
        $this->errorHandler = $errorHandler;
        // Apply auth middleware selectively if needed, or rely on the web route group
        // $this->middleware("auth"); 
    }

    /**
     * Redirect the user to the Google OAuth consent screen
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToGoogle()
    {
        try {
            $authUrl = $this->googleCalendarService->getAuthUrl();
            // Redirect directly to Google's auth URL
            return Redirect::away($authUrl);
        } catch (\Exception $e) {
            Log::error("Google Calendar redirect error: " . $e->getMessage());
            // Redirect back to settings page with error query parameter
            return Redirect::to("/settings?google_callback=error&message=" . urlencode("Failed to generate Google authorization URL.") . "#calendar");
        }
    }

    /**
     * Handle the callback from Google OAuth
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            if ($request->has("error")) {
                $errorMessage = "Google authorization was denied: " . $request->get("error");
                Log::warning($errorMessage);
                return Redirect::to("/settings?google_callback=error&message=" . urlencode($errorMessage) . "#calendar");
            }

            if (!$request->has("code")) {
                $errorMessage = "Authorization code is missing in Google callback.";
                Log::warning($errorMessage);
                return Redirect::to("/settings?google_callback=error&message=" . urlencode($errorMessage) . "#calendar");
            }

            // Process the authorization code
            $this->googleCalendarService->handleAuthCallback($request->get("code"));
            
            // Redirect to settings page with success query parameter
            return Redirect::to("/settings?google_callback=success#calendar");
            
        } catch (\Exception $e) {
            // Log the detailed error
            Log::error("Google Calendar callback handling error: " . $e->getMessage());
            
            // Redirect to settings page with generic error query parameter
            $errorMessage = "An error occurred while connecting your Google Calendar. Please try again.";
            // Optionally include more specific error details if safe
            // $errorMessage = "Error connecting Google Calendar: " . $e->getMessage(); 
            return Redirect::to("/settings?google_callback=error&message=" . urlencode($errorMessage) . "#calendar");
        }
    }

    // --- Other methods remain unchanged, ensure they use appropriate middleware (e.g., auth:sanctum for API calls from Vue) ---

    /**
     * Check if the user has a valid Google Calendar connection
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkConnection()
    {
        // This should likely use auth:sanctum if called from Vue
        try {
            $isConnected = $this->googleCalendarService->isConnected();
            return response()->json(["connected" => $isConnected]);
        } catch (\Exception $e) {
            return response()->json(
                $this->errorHandler->handleGoogleCalendarError($e, "check_connection"),
                500
            );
        }
    }

    /**
     * Get events from the user's Google Calendar for a specific date range
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEvents(Request $request)
    {
        // This should likely use auth:sanctum if called from Vue
        try {
            $validated = $request->validate([
                "start_date" => "required|date",
                "end_date" => "required|date|after_or_equal:start_date",
            ]);

            $events = $this->googleCalendarService->getEvents(
                $validated["start_date"],
                $validated["end_date"]
            );

            return response()->json(["events" => $events]);
        } catch (\Exception $e) {
            return response()->json(
                $this->errorHandler->handleGoogleCalendarError($e, "get_events"),
                500
            );
        }
    }

    /**
     * Disconnect the user's Google Calendar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function disconnectGoogle()
    {
        // This should likely use auth:sanctum if called from Vue
        try {
            $success = $this->googleCalendarService->revokeAccess();
            
            if ($success) {
                return response()->json([
                    "success" => true,
                    "message" => "Google Calendar disconnected successfully"
                ]);
            } else {
                return response()->json([
                    "success" => false,
                    "error" => [
                        "message" => "Failed to disconnect Google Calendar",
                        "code" => "disconnect_failed"
                    ]
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json(
                $this->errorHandler->handleGoogleCalendarError($e, "disconnect"),
                500
            );
        }
    }

    /**
     * Get the list of calendars for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCalendars()
    {
        // This should likely use auth:sanctum if called from Vue
        try {
            $calendars = $this->googleCalendarService->getUserCalendars(Auth::user());
            return response()->json(["calendars" => $calendars]);
        } catch (\Exception $e) {
            return response()->json(
                $this->errorHandler->handleGoogleCalendarError($e, "get_calendars"),
                500
            );
        }
    }

    /**
     * Get the Google Authentication URL.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAuthUrl()
    {
        // This might be called from Vue before redirecting, needs auth:sanctum
        try {
            $authUrl = $this->googleCalendarService->getAuthUrl();
            return response()->json(["auth_url" => $authUrl]);
        } catch (\Exception $e) {
            return response()->json(
                $this->errorHandler->handleGoogleCalendarError($e, "get_auth_url"),
                500
            );
        }
    }

    /**
     * Update the selection status of a calendar.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSelection(Request $request)
    {
        // This should likely use auth:sanctum if called from Vue
        try {
            $request->validate([
                "calendar_id" => "required|string",
                "is_selected" => "required|boolean",
            ]);

            $calendar = $this->googleCalendarService->updateCalendarSelection(
                $request->calendar_id,
                $request->is_selected
            );

            return response()->json(["success" => true]);
        } catch (\Exception $e) {
            return response()->json(
                $this->errorHandler->handleGoogleCalendarError($e, "update_selection"),
                500
            );
        }
    }

    /**
     * Update the visibility status of a calendar.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateVisibility(Request $request)
    {
        // This should likely use auth:sanctum if called from Vue
        try {
            $request->validate([
                "calendar_id" => "required|string",
                "is_visible" => "required|boolean",
            ]);

            $calendar = $this->googleCalendarService->updateCalendarVisibility(
                $request->calendar_id,
                $request->is_visible
            );

            return response()->json(["success" => true]);
        } catch (\Exception $e) {
            return response()->json(
                $this->errorHandler->handleGoogleCalendarError($e, "update_visibility"),
                500
            );
        }
    }

    /**
     * Disconnect a specific calendar.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function disconnectCalendar(Request $request)
    {
        // This should likely use auth:sanctum if called from Vue
        try {
            $request->validate([
                "calendar_id" => "required|string",
            ]);

            $this->googleCalendarService->disconnectCalendar($request->calendar_id);

            return response()->json(["success" => true]);
        } catch (\Exception $e) {
            return response()->json(
                $this->errorHandler->handleGoogleCalendarError($e, "disconnect_calendar"),
                500
            );
        }
    }

    /**
     * Disconnect all calendars for the user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function disconnectAll()
    {
        // This should likely use auth:sanctum if called from Vue
        try {
            $this->googleCalendarService->disconnectAllCalendars();

            return response()->json(["success" => true]);
        } catch (\Exception $e) {
            return response()->json(
                $this->errorHandler->handleGoogleCalendarError($e, "disconnect_all"),
                500
            );
        }
    }
}

