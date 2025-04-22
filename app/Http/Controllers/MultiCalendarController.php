<?php

namespace App\Http\Controllers;

use App\Models\GoogleCalendar;
use App\Services\MultiCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MultiCalendarController extends Controller
{
    protected $multiCalendarService;

    public function __construct(MultiCalendarService $multiCalendarService)
    {
        $this->multiCalendarService = $multiCalendarService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Get the Google OAuth URL for authorization
     *
     * @return \Illuminate\Http\Response
     */
    public function getAuthUrl()
    {
        try {
            $authUrl = $this->multiCalendarService->getAuthUrl();
            
            return response()->json([
                'success' => true,
                'auth_url' => $authUrl
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting auth URL: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get authorization URL'], 500);
        }
    }

    /**
     * Handle the OAuth callback from Google
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handleCallback(Request $request)
    {
        try {
            $code = $request->get('code');
            
            if (!$code) {
                return redirect('/dashboard?google_auth=failed&error=No authorization code received');
            }
            
            $result = $this->multiCalendarService->handleAuthCallback($code);
            
            if ($result['success']) {
                return redirect('/dashboard?google_auth=success');
            } else {
                return redirect('/dashboard?google_auth=failed&error=' . urlencode($result['error']));
            }
        } catch (\Exception $e) {
            Log::error('Error handling callback: ' . $e->getMessage());
            return redirect('/dashboard?google_auth=failed&error=' . urlencode('An error occurred during authorization'));
        }
    }

    /**
     * Get user's connected calendars
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserCalendars()
    {
        try {
            $calendars = $this->multiCalendarService->getUserCalendars(Auth::user());
            
            return response()->json([
                'success' => true,
                'calendars' => $calendars
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting user calendars: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get calendars'], 500);
        }
    }

    /**
     * Update calendar selection status
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateCalendarSelection(Request $request)
    {
        try {
            $request->validate([
                'calendar_id' => 'required|integer',
                'is_selected' => 'required|boolean'
            ]);
            
            $result = $this->multiCalendarService->updateCalendarSelection(
                $request->calendar_id,
                $request->is_selected
            );
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message']
                ]);
            } else {
                return response()->json(['error' => $result['error']], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error updating calendar selection: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update calendar selection'], 500);
        }
    }

    /**
     * Update calendar visibility
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateCalendarVisibility(Request $request)
    {
        try {
            $request->validate([
                'calendar_id' => 'required|integer',
                'is_visible' => 'required|boolean'
            ]);
            
            $result = $this->multiCalendarService->updateCalendarVisibility(
                $request->calendar_id,
                $request->is_visible
            );
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message']
                ]);
            } else {
                return response()->json(['error' => $result['error']], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error updating calendar visibility: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update calendar visibility'], 500);
        }
    }

    /**
     * Get events from selected calendars for a specific date range
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getEvents(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'required|date_format:Y-m-d',
                'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date'
            ]);
            
            $result = $this->multiCalendarService->getEventsFromSelectedCalendars(
                $request->start_date,
                $request->end_date
            );
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'events' => $result['events']
                ]);
            } else {
                return response()->json(['error' => $result['error']], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error getting events: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get events'], 500);
        }
    }

    /**
     * Disconnect a specific calendar
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function disconnectCalendar(Request $request)
    {
        try {
            $request->validate([
                'calendar_id' => 'required|integer'
            ]);
            
            $result = $this->multiCalendarService->disconnectCalendar($request->calendar_id);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message']
                ]);
            } else {
                return response()->json(['error' => $result['error']], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error disconnecting calendar: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to disconnect calendar'], 500);
        }
    }

    /**
     * Disconnect all calendars for a user
     *
     * @return \Illuminate\Http\Response
     */
    public function disconnectAllCalendars()
    {
        try {
            $result = $this->multiCalendarService->disconnectAllCalendars();
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message']
                ]);
            } else {
                return response()->json(['error' => $result['error']], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error disconnecting all calendars: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to disconnect all calendars'], 500);
        }
    }

    /**
     * Check if user has any connected calendars
     *
     * @return \Illuminate\Http\Response
     */
    public function checkConnection()
    {
        try {
            $hasCalendars = GoogleCalendar::where('user_id', Auth::id())->exists();
            
            return response()->json([
                'success' => true,
                'connected' => $hasCalendars
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking calendar connection: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to check calendar connection'], 500);
        }
    }
}
