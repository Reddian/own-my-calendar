<?php

namespace App\Http\Controllers;

use App\Models\GoogleCalendar;
use App\Services\GoogleCalendarService;
use App\Services\ErrorHandlerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GoogleCalendarController extends Controller
{
    protected $googleCalendarService;
    protected $errorHandler;

    public function __construct(GoogleCalendarService $googleCalendarService, ErrorHandlerService $errorHandler)
    {
        $this->googleCalendarService = $googleCalendarService;
        $this->errorHandler = $errorHandler;
        $this->middleware('auth:sanctum');
    }

    /**
     * Redirect the user to the Google OAuth consent screen
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGoogle()
    {
        try {
            $authUrl = $this->googleCalendarService->getAuthUrl();
            
            // Instead of returning JSON, redirect directly to Google's auth URL
            return redirect()->away($authUrl);
        } catch (\Exception $e) {
            Log::error('Google Calendar redirect error: ' . $e->getMessage());
            return redirect()->route('settings')->with('error', 'Failed to generate Google authorization URL: ' . $e->getMessage());
        }
    }

    /**
     * Handle the callback from Google OAuth
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            if ($request->has('error')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'message' => 'Google authorization was denied: ' . $request->get('error'),
                        'code' => 'authorization_denied'
                    ]
                ], 400);
            }

            if (!$request->has('code')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'message' => 'Authorization code is missing',
                        'code' => 'missing_code'
                    ]
                ], 400);
            }

            $this->googleCalendarService->handleAuthCallback($request->get('code'));
            
            return response()->json([
                'success' => true,
                'message' => 'Google Calendar connected successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(
                $this->errorHandler->handleGoogleCalendarError($e, 'handle_callback'),
                500
            );
        }
    }

    /**
     * Check if the user has a valid Google Calendar connection
     *
     * @return \Illuminate\Http\Response
     */
    public function checkConnection()
    {
        try {
            $isConnected = $this->googleCalendarService->isConnected();
            return response()->json(['connected' => $isConnected]);
        } catch (\Exception $e) {
            return response()->json(
                $this->errorHandler->handleGoogleCalendarError($e, 'check_connection'),
                500
            );
        }
    }

    /**
     * Get events from the user's Google Calendar for a specific date range
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getEvents(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $events = $this->googleCalendarService->getEvents(
                $validated['start_date'],
                $validated['end_date']
            );

            return response()->json(['events' => $events]);
        } catch (\Exception $e) {
            return response()->json(
                $this->errorHandler->handleGoogleCalendarError($e, 'get_events'),
                500
            );
        }
    }

    /**
     * Disconnect the user's Google Calendar
     *
     * @return \Illuminate\Http\Response
     */
    public function disconnectGoogle()
    {
        try {
            $success = $this->googleCalendarService->revokeAccess();
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Google Calendar disconnected successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'message' => 'Failed to disconnect Google Calendar',
                        'code' => 'disconnect_failed'
                    ]
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json(
                $this->errorHandler->handleGoogleCalendarError($e, 'disconnect'),
                500
            );
        }
    }

    public function getCalendars()
    {
        try {
            $calendars = $this->googleCalendarService->getUserCalendars(Auth::user());
            return response()->json(['calendars' => $calendars]);
        } catch (\Exception $e) {
            return response()->json(
                $this->errorHandler->handleGoogleCalendarError($e, 'get_calendars'),
                500
            );
        }
    }

    public function getAuthUrl()
    {
        try {
            $authUrl = $this->googleCalendarService->getAuthUrl();
            return response()->json(['auth_url' => $authUrl]);
        } catch (\Exception $e) {
            return response()->json(
                $this->errorHandler->handleGoogleCalendarError($e, 'get_auth_url'),
                500
            );
        }
    }

    public function updateSelection(Request $request)
    {
        try {
            $request->validate([
                'calendar_id' => 'required|string',
                'is_selected' => 'required|boolean',
            ]);

            $calendar = $this->googleCalendarService->updateCalendarSelection(
                $request->calendar_id,
                $request->is_selected
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(
                $this->errorHandler->handleGoogleCalendarError($e, 'update_selection'),
                500
            );
        }
    }

    public function updateVisibility(Request $request)
    {
        try {
            $request->validate([
                'calendar_id' => 'required|string',
                'is_visible' => 'required|boolean',
            ]);

            $calendar = $this->googleCalendarService->updateCalendarVisibility(
                $request->calendar_id,
                $request->is_visible
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(
                $this->errorHandler->handleGoogleCalendarError($e, 'update_visibility'),
                500
            );
        }
    }

    public function disconnectCalendar(Request $request)
    {
        try {
            $request->validate([
                'calendar_id' => 'required|string',
            ]);

            $this->googleCalendarService->disconnectCalendar($request->calendar_id);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(
                $this->errorHandler->handleGoogleCalendarError($e, 'disconnect_calendar'),
                500
            );
        }
    }

    public function disconnectAll()
    {
        try {
            $this->googleCalendarService->disconnectAllCalendars();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(
                $this->errorHandler->handleGoogleCalendarError($e, 'disconnect_all'),
                500
            );
        }
    }
}
