<?php

namespace App\Services;

use Google_Client;
use Google_Service_Calendar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GoogleCalendarService
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
        $this->storeToken($accessToken);
        
        return $accessToken;
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
        if (!$this->hasValidToken()) {
            throw new \Exception('No valid Google Calendar token found');
        }
        
        $service = new Google_Service_Calendar($this->client);
        
        $startDateTime = Carbon::parse($startDate)->startOfDay()->toRfc3339String();
        $endDateTime = Carbon::parse($endDate)->endOfDay()->toRfc3339String();
        
        $optParams = [
            'timeMin' => $startDateTime,
            'timeMax' => $endDateTime,
            'singleEvents' => true,
            'orderBy' => 'startTime',
        ];
        
        try {
            $results = $service->events->listEvents('primary', $optParams);
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
            Log::error('Failed to fetch Google Calendar events: ' . $e->getMessage());
            throw $e;
        }
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
        $token = $this->getStoredToken();
        
        if (!$token) {
            return true; // Already no token
        }
        
        $this->client->setAccessToken($token);
        
        try {
            $this->client->revokeToken();
            $userId = Auth::id();
            Cache::forget("google_token_{$userId}");
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to revoke Google token: ' . $e->getMessage());
            return false;
        }
    }
}
