<?php
namespace App\Services;
use Google_Client;
use Google_Service_Calendar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\GoogleCalendar;
use App\Models\User;

class GoogleCalendarService
{
    protected $client;
    protected $service;
    protected $cacheDuration = 300; // 5 minutes
    
    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setApplicationName(config('services.google.application_name'));
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect_uri'));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        $this->client->setScopes([
            Google_Service_Calendar::CALENDAR_READONLY,
            Google_Service_Calendar::CALENDAR_EVENTS_READONLY
        ]);
        
        // Check if we have a stored token
        $token = $this->getStoredToken();
        if ($token) {
            $this->client->setAccessToken($token);
            
            // Refresh token if it's expired
            if ($this->client->isAccessTokenExpired() && $this->client->getRefreshToken()) {
                try {
                    $newToken = $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                    $this->storeToken($newToken);
                } catch (\Exception $e) {
                    Log::error('Failed to refresh Google token: ' . $e->getMessage());
                }
            }
        }
    }
    
    /**
     * Get the Google OAuth authorization URL
     *
     * @return string
     */
    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }
    
    /**
     * Handle the OAuth callback and store the access token
     *
     * @param string $code
     * @return array
     */
    public function handleAuthCallback($code)
    {
        try {
            $token = $this->client->fetchAccessTokenWithAuthCode($code);
            $this->client->setAccessToken($token);
            
            if ($this->client->isAccessTokenExpired()) {
                $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
            }
            
            // Store calendar information in the database
            $this->storeCalendarInformation($token);
            
            return $token;
        } catch (\Exception $e) {
            Log::error('Google Calendar auth error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Store calendar information in the database
     *
     * @param array $accessToken
     * @return void
     */
    protected function storeCalendarInformation($accessToken)
    {
        try {
            $this->client->setAccessToken($accessToken);
            $this->service = new Google_Service_Calendar($this->client);
            
            // Get the list of calendars
            $calendarList = $this->service->calendarList->listCalendarList();
            
            foreach ($calendarList->getItems() as $calendarListEntry) {
                // Check if this calendar already exists for the user
                $existingCalendar = GoogleCalendar::where('user_id', Auth::id())
                    ->where('calendar_id', $calendarListEntry->getId())
                    ->first();
                
                $expiresAt = Carbon::now()->addSeconds($accessToken['expires_in']);
                
                if ($existingCalendar) {
                    // Update existing calendar
                    $existingCalendar->update([
                        'name' => $calendarListEntry->getSummary(),
                        'description' => $calendarListEntry->getDescription(),
                        'color' => $calendarListEntry->getBackgroundColor(),
                        'is_primary' => $calendarListEntry->getPrimary() ?? false,
                        'access_token' => $accessToken,
                        'token_expires_at' => $expiresAt,
                        'refresh_token' => $accessToken['refresh_token'] ?? $existingCalendar->refresh_token,
                    ]);
                } else {
                    // Create new calendar entry
                    GoogleCalendar::create([
                        'user_id' => Auth::id(),
                        'calendar_id' => $calendarListEntry->getId(),
                        'name' => $calendarListEntry->getSummary(),
                        'description' => $calendarListEntry->getDescription(),
                        'color' => $calendarListEntry->getBackgroundColor(),
                        'is_primary' => $calendarListEntry->getPrimary() ?? false,
                        'is_selected' => true, // Default to selected
                        'is_visible' => true, // Default to visible
                        'access_token' => $accessToken,
                        'token_expires_at' => $expiresAt,
                        'refresh_token' => $accessToken['refresh_token'] ?? null,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to store calendar information: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Store the access token for the current user
     *
     * @param array $accessToken
     * @return void
     */
    protected function storeToken($accessToken)
    {
        $userId = Auth::id();
        Cache::put("google_token_{$userId}", $accessToken, 60 * 24 * 30); // Store for 30 days
        // In a production environment, you would store this in the database
        // associated with the user's account
    }
    
    /**
     * Get the stored access token for the current user
     *
     * @return array|null
     */
    protected function getStoredToken()
    {
        $userId = Auth::id();
        return Cache::get("google_token_{$userId}");
    }
    
    /**
     * Check if the current user has a valid token
     *
     * @return bool
     */
    public function hasValidToken()
    {
        $token = $this->getStoredToken();
        if (!$token) {
            return false;
        }
        
        $this->client->setAccessToken($token);
        if ($this->client->isAccessTokenExpired()) {
            if ($this->client->getRefreshToken()) {
                try {
                    $newToken = $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                    $this->storeToken($newToken);
                    return true;
                } catch (\Exception $e) {
                    Log::error('Failed to refresh Google token: ' . $e->getMessage());
                    return false;
                }
            }
            return false;
        }
        return true;
    }
    
    /**
     * Get events from the user's primary calendar for a specific date range
     *
     * @param string $startDate Format: Y-m-d
     * @param string $endDate Format: Y-m-d
     * @return array
     */
    public function getEvents($startDate, $endDate)
    {
        $cacheKey = 'google_calendar_events_' . auth()->id() . '_' . $startDate . '_' . $endDate;
        
        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($startDate, $endDate) {
            try {
                if (!$this->hasValidToken()) {
                    throw new \Exception('No valid Google Calendar token found');
                }
                
                $this->service = new Google_Service_Calendar($this->client);
                $startDateTime = Carbon::parse($startDate)->startOfDay()->toRfc3339String();
                $endDateTime = Carbon::parse($endDate)->endOfDay()->toRfc3339String();
                
                $optParams = [
                    'timeMin' => $startDateTime,
                    'timeMax' => $endDateTime,
                    'singleEvents' => true,
                    'orderBy' => 'startTime',
                ];
                
                $results = $this->service->events->listEvents('primary', $optParams);
                $events = [];
                
                foreach ($results->getItems() as $event) {
                    $start = $event->start->dateTime ?? $event->start->date;
                    $end = $event->end->dateTime ?? $event->end->date;
                    
                    // Convert to Carbon instances for easier manipulation
                    $startCarbon = $start ? Carbon::parse($start) : null;
                    $endCarbon = $end ? Carbon::parse($end) : null;
                    
                    $events[] = [
                        'id' => $event->id,
                        'title' => $event->getSummary(),
                        'description' => $event->getDescription(),
                        'start' => $startCarbon ? $startCarbon->toDateTimeString() : null,
                        'end' => $endCarbon ? $endCarbon->toDateTimeString() : null,
                        'all_day' => !$event->start->dateTime,
                        'location' => $event->getLocation(),
                        'creator' => $event->getCreator() ? $event->getCreator()->getEmail() : null,
                        'attendees' => $this->formatAttendees($event),
                        'color_id' => $event->getColorId(),
                    ];
                }
                
                return $events;
            } catch (\Exception $e) {
                Log::error('Google Calendar events error: ' . $e->getMessage());
                throw $e;
            }
        });
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
     * Revoke access to Google Calendar
     *
     * @return bool
     */
    public function revokeAccess()
    {
        try {
            $this->client->setAccessToken(auth()->user()->google_token);
            $this->client->revokeToken();
            $userId = Auth::id();
            Cache::forget("google_token_{$userId}");
            
            // Remove all calendars for this user
            GoogleCalendar::where('user_id', $userId)->delete();
            
            return true;
        } catch (\Exception $e) {
            Log::error('Google Calendar revoke error: ' . $e->getMessage());
            return false;
        }
    }

    public function isConnected()
    {
        return Cache::remember('google_calendar_connection', $this->cacheDuration, function () {
            try {
                $this->client->setAccessToken(auth()->user()->google_token);
                return !$this->client->isAccessTokenExpired();
            } catch (\Exception $e) {
                return false;
            }
        });
    }

    public function getUserCalendars(User $user)
    {
        $cacheKey = 'google_calendars_' . $user->id;
        
        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($user) {
            try {
                $this->client->setAccessToken($user->google_token);
                $this->service = new Google_Service_Calendar($this->client);
                
                $calendarList = $this->service->calendarList->listCalendarList();
                return $calendarList->getItems();
            } catch (\Exception $e) {
                Log::error('Google Calendar list error: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    public function updateCalendarSelection($calendarId, $isSelected)
    {
        $user = auth()->user();
        $calendar = $user->calendars()->where('calendar_id', $calendarId)->firstOrFail();
        $calendar->update(['is_selected' => $isSelected]);
        
        // Clear the cache for this user's calendars
        Cache::forget('google_calendars_' . $user->id);
        
        return $calendar;
    }

    public function updateCalendarVisibility($calendarId, $isVisible)
    {
        $user = auth()->user();
        $calendar = $user->calendars()->where('calendar_id', $calendarId)->firstOrFail();
        $calendar->update(['is_visible' => $isVisible]);
        
        // Clear the cache for this user's calendars
        Cache::forget('google_calendars_' . $user->id);
        
        return $calendar;
    }

    public function disconnectCalendar($calendarId)
    {
        $user = auth()->user();
        $calendar = $user->calendars()->where('calendar_id', $calendarId)->firstOrFail();
        $calendar->delete();
        
        // Clear the cache for this user's calendars
        Cache::forget('google_calendars_' . $user->id);
    }

    public function disconnectAllCalendars()
    {
        $user = auth()->user();
        $user->calendars()->delete();
        
        // Clear the cache for this user's calendars
        Cache::forget('google_calendars_' . $user->id);
    }
}
