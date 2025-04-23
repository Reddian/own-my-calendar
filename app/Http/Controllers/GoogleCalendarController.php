<?php

namespace App\Http\Controllers;

use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GoogleCalendarController extends Controller
{
    protected $googleCalendarService;

    public function __construct(GoogleCalendarService $googleCalendarService)
    {
        $this->googleCalendarService = $googleCalendarService;
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
                return redirect()->route('settings')
                    ->with('error', 'Google authorization was denied: ' . $request->get('error'));
            }

            if (!$request->has('code')) {
                return redirect()->route('settings')
                    ->with('error', 'Authorization code is missing');
            }

            $this->googleCalendarService->handleAuthCallback($request->get('code'));
            
            // Redirect to settings page with success message
            return redirect()->route('settings')
                ->with('success', 'Google Calendar connected successfully');
        } catch (\Exception $e) {
            Log::error('Google Calendar callback error: ' . $e->getMessage());
            return redirect()->route('settings')
                ->with('error', 'Failed to connect Google Calendar: ' . $e->getMessage());
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
            $hasValidToken = $this->googleCalendarService->hasValidToken();
            return response()->json(['connected' => $hasValidToken]);
        } catch (\Exception $e) {
            Log::error('Google Calendar connection check error: ' . $e->getMessage());
            return response()->json(['connected' => false, 'error' => $e->getMessage()]);
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
            Log::error('Google Calendar events fetch error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch calendar events: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Disconnect the user's Google Calendar
     *
     * @return \Illuminate\Http\Response
     */
    public function disconnect()
    {
        try {
            $success = $this->googleCalendarService->revokeAccess();
            
            if ($success) {
                return response()->json(['success' => true, 'message' => 'Google Calendar disconnected successfully']);
            } else {
                return response()->json(['error' => 'Failed to disconnect Google Calendar'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Google Calendar disconnect error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to disconnect Google Calendar: ' . $e->getMessage()], 500);
        }
    }
}
