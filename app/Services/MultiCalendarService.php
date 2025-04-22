<?php

namespace App\Services;

use App\Models\GoogleCalendar;
use App\Models\User;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Calendar;
use Google_Service_Calendar_CalendarListEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class MultiCalendarService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setApplicationName('Own My Calendar');
        $this->client->setScopes(Google_Service_Calendar::CALENDAR_READONLY);
        $this->client->setAuthConfig(storage_path('app/google-calendar/credentials.json'));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');
        $this->client->setRedirectUri(config('app.url') . '/api/google/callback');
    }

    /**
     * Get the Google OAuth URL for authorization
     *
     * @return string
     */
    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Exchange authorization code for access token
     *
     * @param string $code
     * @return array
     */
    public function handleAuthCallback($code)
    {
        $accessToken = $this->client->fetchAccessTokenWithAuthCode($code);
        
        if (isset($accessToken['access_token'])) {
            // Set the access token on the client
            $this->client->setAccessToken($accessToken);
            
            // Get user's calendars
            $calendars = $this->fetchUserCalendars();
            
            // Store calendars in database
            $this->storeUserCalendars($calendars, $accessToken);
            
            return [
                'success' => true,
                'message' => 'Google Calendar connected successfully',
                'calendars' => $calendars
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Failed to get access token'
            ];
        }
    }

    /**
     * Fetch user's calendars from Google Calendar API
     *
     * @return array
     */
    protected function fetchUserCalendars()
    {
        $service = new Google_Service_Calendar($this->client);
        $calendarList = $service->calendarList->listCalendarList();
        
        $calendars = [];
        foreach ($calendarList->getItems() as $calendarListEntry) {
            $calendars[] = [
                'calendar_id' => $calendarListEntry->getId(),
                'name' => $calendarListEntry->getSummary(),
                'description' => $calendarListEntry->getDescription(),
                'color' => $calendarListEntry->getBackgroundColor(),
                'is_primary' => $calendarListEntry->getPrimary() ?? false,
            ];
        }
        
        return $calendars;
    }

    /**
     * Store user's calendars in database
     *
     * @param array $calendars
     * @param array $accessToken
     * @return void
     */
    protected function storeUserCalendars($calendars, $accessToken)
    {
        $userId = Auth::id();
        $expiresAt = Carbon::now()->addSeconds($accessToken['expires_in']);
        
        foreach ($calendars as $calendar) {
            GoogleCalendar::updateOrCreate(
                [
                    'user_id' => $userId,
                    'calendar_id' => $calendar['calendar_id']
                ],
                [
                    'name' => $calendar['name'],
                    'description' => $calendar['description'],
                    'color' => $calendar['color'],
                    'is_primary' => $calendar['is_primary'],
                    'is_selected' => true,
                    'is_visible' => true,
                    'access_token' => $accessToken,
                    'token_expires_at' => $expiresAt,
                    'refresh_token' => $accessToken['refresh_token'] ?? null,
                ]
            );
        }
    }

    /**
     * Get user's connected calendars
     *
     * @param User $user
     * @return array
     */
    public function getUserCalendars(User $user)
    {
        return GoogleCalendar::where('user_id', $user->id)->get();
    }

    /**
     * Update calendar selection status
     *
     * @param int $calendarId
     * @param bool $isSelected
     * @return array
     */
    public function updateCalendarSelection($calendarId, $isSelected)
    {
        try {
            $calendar = GoogleCalendar::where('id', $calendarId)
                ->where('user_id', Auth::id())
                ->first();
            
            if (!$calendar) {
                return [
                    'success' => false,
                    'error' => 'Calendar not found'
                ];
            }
            
            $calendar->update([
                'is_selected' => $isSelected
            ]);
            
            return [
                'success' => true,
                'message' => 'Calendar selection updated successfully'
            ];
        } catch (Exception $e) {
            Log::error('Error updating calendar selection: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update calendar visibility
     *
     * @param int $calendarId
     * @param bool $isVisible
     * @return array
     */
    public function updateCalendarVisibility($calendarId, $isVisible)
    {
        try {
            $calendar = GoogleCalendar::where('id', $calendarId)
                ->where('user_id', Auth::id())
                ->first();
            
            if (!$calendar) {
                return [
                    'success' => false,
                    'error' => 'Calendar not found'
                ];
            }
            
            $calendar->update([
                'is_visible' => $isVisible
            ]);
            
            return [
                'success' => true,
                'message' => 'Calendar visibility updated successfully'
            ];
        } catch (Exception $e) {
            Log::error('Error updating calendar visibility: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get events from selected calendars for a specific date range
     *
     * @param string $startDate Format: Y-m-d
     * @param string $endDate Format: Y-m-d
     * @return array
     */
    public function getEventsFromSelectedCalendars($startDate, $endDate)
    {
        $userId = Auth::id();
        $selectedCalendars = GoogleCalendar::where('user_id', $userId)
            ->where('is_selected', true)
            ->get();
        
        if ($selectedCalendars->isEmpty()) {
            return [
                'success' => false,
                'error' => 'No calendars selected'
            ];
        }
        
        $allEvents = [];
        $startDateTime = Carbon::parse($startDate)->startOfDay()->toRfc3339String();
        $endDateTime = Carbon::parse($endDate)->endOfDay()->toRfc3339String();
        
        foreach ($selectedCalendars as $calendar) {
            try {
                if (!$calendar->hasValidToken()) {
                    if ($calendar->hasRefreshToken()) {
                        $this->refreshCalendarToken($calendar);
                    } else {
                        continue; // Skip this calendar if token can't be refreshed
                    }
                }
                
                $this->client->setAccessToken($calendar->access_token);
                $service = new Google_Service_Calendar($this->client);
                
                $optParams = [
                    'timeMin' => $startDateTime,
                    'timeMax' => $endDateTime,
                    'singleEvents' => true,
                    'orderBy' => 'startTime',
                ];
                
                $results = $service->events->listEvents($calendar->calendar_id, $optParams);
                
                foreach ($results->getItems() as $event) {
                    $start = $event->start->dateTime ?? $event->start->date;
                    $end = $event->end->dateTime ?? $event->end->date;
                    
                    // Convert to Carbon instances for easier manipulation
                    $startCarbon = $start ? Carbon::parse($start) : null;
                    $endCarbon = $end ? Carbon::parse($end) : null;
                    
                    $allEvents[] = [
                        'id' => $event->id,
                        'calendar_id' => $calendar->id,
                        'calendar_name' => $calendar->name,
                        'calendar_color' => $calendar->color,
                        'title' => $event->getSummary(),
                        'description' => $event->getDescription(),
                        'start' => $startCarbon ? $startCarbon->toDateTimeString() : null,
                        'end' => $endCarbon ? $endCarbon->toDateTimeString() : null,
                        'all_day' => !$event->start->dateTime,
                        'location' => $event->getLocation(),
                        'creator' => $event->getCreator() ? $event->getCreator()->getEmail() : null,
                        'attendees' => $this->formatAttendees($event),
                    ];
                }
            } catch (Exception $e) {
                Log::error('Error fetching events for calendar ' . $calendar->name . ': ' . $e->getMessage());
                // Continue with other calendars even if one fails
            }
        }
        
        return [
            'success' => true,
            'events' => $allEvents
        ];
    }

    /**
     * Format attendees from a Google Calendar event
     *
     * @param \Google_Service_Calendar_Event $event
     * @return array
     */
    protected function formatAttendees($event)
    {
        $attendees = [];
        
        if ($event->getAttendees()) {
            foreach ($event->getAttendees() as $person) {
                $attendees[] = [
                    'email' => $person->getEmail(),
                    'name' => $person->getDisplayName(),
                    'response_status' => $person->getResponseStatus(),
                ];
            }
        }
        
        return $attendees;
    }

    /**
     * Refresh the access token for a calendar
     *
     * @param GoogleCalendar $calendar
     * @return bool
     */
    protected function refreshCalendarToken(GoogleCalendar $calendar)
    {
        try {
            if (!$calendar->refresh_token) {
                return false;
            }
            
            $this->client->setAccessToken([
                'refresh_token' => $calendar->refresh_token
            ]);
            
            $newToken = $this->client->fetchAccessTokenWithRefreshToken();
            
            if (isset($newToken['access_token'])) {
                $expiresAt = Carbon::now()->addSeconds($newToken['expires_in']);
                
                $calendar->update([
                    'access_token' => $newToken,
                    'token_expires_at' => $expiresAt,
                    'refresh_token' => $newToken['refresh_token'] ?? $calendar->refresh_token,
                ]);
                
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            Log::error('Error refreshing token: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Disconnect a specific calendar
     *
     * @param int $calendarId
     * @return array
     */
    public function disconnectCalendar($calendarId)
    {
        try {
            $calendar = GoogleCalendar::where('id', $calendarId)
                ->where('user_id', Auth::id())
                ->first();
            
            if (!$calendar) {
                return [
                    'success' => false,
                    'error' => 'Calendar not found'
                ];
            }
            
            $calendar->delete();
            
            return [
                'success' => true,
                'message' => 'Calendar disconnected successfully'
            ];
        } catch (Exception $e) {
            Log::error('Error disconnecting calendar: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Disconnect all calendars for a user
     *
     * @return array
     */
    public function disconnectAllCalendars()
    {
        try {
            GoogleCalendar::where('user_id', Auth::id())->delete();
            
            return [
                'success' => true,
                'message' => 'All calendars disconnected successfully'
            ];
        } catch (Exception $e) {
            Log::error('Error disconnecting all calendars: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
