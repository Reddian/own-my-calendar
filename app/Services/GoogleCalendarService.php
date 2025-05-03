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
    protected $cacheDuration = 300; // 5 minutes (Still used for token cache)

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setApplicationName(config("services.google.application_name"));
        $this->client->setClientId(config("services.google.client_id"));
        $this->client->setClientSecret(config("services.google.client_secret"));
        $this->client->setRedirectUri(config("services.google.redirect_uri"));
        $this->client->setAccessType("offline");
        $this->client->setPrompt("consent");
        $this->client->setScopes([
            Google_Service_Calendar::CALENDAR_READONLY,
            Google_Service_Calendar::CALENDAR_EVENTS_READONLY
        ]);
    }

    // ... (getAuthUrl, handleAuthCallback, storeCalendarInformation, storeToken, getStoredToken, hasValidToken, clearStoredToken, clearConnectionCache remain the same) ...

    /**
     * Get events from the user's selected calendars for a specific date range directly from Google API.
     *
     * @param string $startDate Format: Y-m-d
     * @param string $endDate Format: Y-m-d
     * @return array
     */
    public function getEvents($startDate, $endDate)
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception("User not authenticated.");
        }
        $userId = $user->id;
        // Get user's timezone, default to app timezone or UTC if not set
        $userTimezone = $user->timezone ?? config('app.timezone', 'UTC');
        Log::info("[GoogleCalendarService] Using timezone: {$userTimezone} for user {$userId}");

        // Parse start and end dates using the user's timezone
        try {
            $startDateTime = Carbon::parse($startDate, $userTimezone)->startOfDay();
            $endDateTime = Carbon::parse($endDate, $userTimezone)->endOfDay();
        } catch (\Exception $e) {
            Log::error("[GoogleCalendarService] Invalid date format or timezone for user {$userId}: {$startDate}, {$endDate}, {$userTimezone}. Error: " . $e->getMessage());
            throw new \Exception("Invalid date format or timezone provided.");
        }

        Log::info("[GoogleCalendarService] Getting events directly from API for user {$userId} from {$startDateTime->toIso8601String()} to {$endDateTime->toIso8601String()} ({$userTimezone}).");

        // 1. Get selected Google Calendar IDs
        $selectedGoogleCalendars = $user->calendars()
            ->where("is_selected", true)
            ->select("id", "calendar_id")
            ->get();

        if ($selectedGoogleCalendars->isEmpty()) {
            Log::info("[GoogleCalendarService] No calendars selected for user {$userId}. Returning empty array.");
            return [];
        }

        Log::info("[GoogleCalendarService] Selected Google Calendar IDs: " . $selectedGoogleCalendars->pluck("calendar_id")->implode(", "));

        // 2. Fetch directly from Google API, passing the user's timezone
        try {
            $googleEventsData = $this->fetchEventsFromGoogle(
                $startDateTime->toRfc3339String(),
                $endDateTime->toRfc3339String(),
                $selectedGoogleCalendars,
                $userTimezone // Pass the user's timezone
            );

            // 3. Format events for frontend
            $formattedEvents = [];
            foreach ($googleEventsData as $eventData) {
                $event = $eventData["google_event"];
                $calendarId = $eventData["google_calendar_id"]; // Use the Google Calendar ID

                // Google API returns times in the specified timezone (RFC3339 format)
                $start = $event->start->dateTime ?? $event->start->date;
                $end = $event->end->dateTime ?? $event->end->date;
                $isAllDay = !$event->start->dateTime;

                $formattedEvents[] = [
                    "id" => $event->id,
                    "title" => $event->getSummary(),
                    "start" => $start, // Keep as RFC3339 string with offset
                    "end" => $end,     // Keep as RFC3339 string with offset
                    "allDay" => $isAllDay,
                    "calendarId" => $calendarId,
                ];
            }
            Log::info("[GoogleCalendarService] Returning " . count($formattedEvents) . " formatted events for user {$userId}.");
            return $formattedEvents;

        } catch (\Exception $e) {
            Log::error("[GoogleCalendarService] Failed to fetch events directly from Google for user {$userId}: " . $e->getMessage());
            throw $e; // Re-throw the exception
        }
    }

    /**
     * Fetch events directly from Google API for the given calendars and date range.
     *
     * @param string $startDateTime RFC3339
     * @param string $endDateTime RFC3339
     * @param \Illuminate\Database\Eloquent\Collection $selectedCalendars Collection of GoogleCalendar models
     * @param string $userTimezone The IANA timezone identifier (e.g., 'America/New_York')
     * @return array Array of ["google_event" => Google_Service_Calendar_Event, "google_calendar_id" => string]
     */
    protected function fetchEventsFromGoogle($startDateTime, $endDateTime, $selectedCalendars, $userTimezone)
    {
        if (!$this->hasValidToken()) {
            throw new \Exception("No valid Google Calendar token found");
        }

        $this->service = new Google_Service_Calendar($this->client);
        $optParams = [
            "timeMin" => $startDateTime,
            "timeMax" => $endDateTime,
            "singleEvents" => true,
            "orderBy" => "startTime",
            "timeZone" => $userTimezone, // Use the provided user timezone
        ];

        Log::info("[GoogleCalendarService] Fetching Google events with params: " . json_encode($optParams));

        $allEventsData = [];
        foreach ($selectedCalendars as $calendar) {
            $googleCalendarId = $calendar->calendar_id;
            Log::info("[GoogleCalendarService] Fetching events from Google calendar: {$googleCalendarId}");
            try {
                $results = $this->service->events->listEvents($googleCalendarId, $optParams);
                foreach ($results->getItems() as $event) {
                    $allEventsData[] = [
                        "google_event" => $event, // Store the raw event object
                        "google_calendar_id" => $googleCalendarId // Store the Google Calendar ID
                    ];
                }
            } catch (\Google\Service\Exception $e) {
                if ($e->getCode() == 404) {
                    Log::warning("[GoogleCalendarService] Calendar not found on Google: {$googleCalendarId}. Skipping.");
                } else {
                    Log::error("[GoogleCalendarService] Google API error fetching events from {$googleCalendarId}: " . $e->getMessage());
                }
            } catch (\Exception $e) {
                 Log::error("[GoogleCalendarService] General error fetching events from {$googleCalendarId}: " . $e->getMessage());
            }
        }
        Log::info("[GoogleCalendarService] Fetched " . count($allEventsData) . " raw events from Google API.");
        return $allEventsData;
    }

    // ... (Other methods remain unchanged) ...

    /**
     * Get the Google Authentication URL.
     *
     * @return string
     */
    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Handle the callback from Google OAuth.
     *
     * @param string $authCode
     * @return void
     */
    public function handleAuthCallback($authCode)
    {
        $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);
        if (isset($accessToken["error"])) {
            throw new \Exception("Failed to fetch access token: " . $accessToken["error_description"]);
        }
        $this->storeToken($accessToken);
        $this->storeCalendarInformation(); // Store calendar list after successful auth
        $this->clearConnectionCache(); // Clear cache after successful connection
    }

    /**
     * Store basic calendar information for the user.
     *
     * @return void
     */
    protected function storeCalendarInformation()
    {
        if (!$this->hasValidToken()) {
            Log::warning("[GoogleCalendarService] Cannot store calendar info, no valid token.");
            return;
        }
        $user = Auth::user();
        if (!$user) {
            Log::warning("[GoogleCalendarService] Cannot store calendar info, user not authenticated.");
            return;
        }

        try {
            $this->service = new Google_Service_Calendar($this->client);
            $calendarList = $this->service->calendarList->listCalendarList();
            $existingCalendars = $user->calendars()->pluck('calendar_id')->toArray();
            $newCalendarsData = [];

            foreach ($calendarList->getItems() as $calendarItem) {
                if (!in_array($calendarItem->getId(), $existingCalendars)) {
                    $newCalendarsData[] = [
                        'user_id' => $user->id,
                        'calendar_id' => $calendarItem->getId(),
                        'summary' => $calendarItem->getSummary(),
                        'timezone' => $calendarItem->getTimeZone(),
                        'is_selected' => false, // Default to not selected
                        'is_visible' => true, // Default to visible
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($newCalendarsData)) {
                GoogleCalendar::insert($newCalendarsData);
                Log::info("[GoogleCalendarService] Stored " . count($newCalendarsData) . " new calendars for user {$user->id}.");
            }
        } catch (\Exception $e) {
            Log::error("[GoogleCalendarService] Error storing calendar information for user {$user->id}: " . $e->getMessage());
            // Decide if this should throw or just log
        }
    }

    /**
     * Store the Google API token.
     *
     * @param array $token
     * @return void
     */
    protected function storeToken(array $token)
    {
        $userId = Auth::id();
        if (!$userId) return;
        Cache::put("google_token_{$userId}", $token, $this->cacheDuration);
        Log::info("[GoogleCalendarService] Stored Google token for user {$userId}.");
    }

    /**
     * Retrieve the stored Google API token.
     *
     * @return array|null
     */
    protected function getStoredToken()
    {
        $userId = Auth::id();
        if (!$userId) return null;
        return Cache::get("google_token_{$userId}");
    }

    /**
     * Check if a valid token exists and set it on the client.
     *
     * @return bool
     */
    protected function hasValidToken()
    {
        $token = $this->getStoredToken();
        if (!$token) {
            Log::info("[GoogleCalendarService] No token found in cache for user " . Auth::id());
            return false;
        }

        $this->client->setAccessToken($token);

        if ($this->client->isAccessTokenExpired()) {
            Log::info("[GoogleCalendarService] Token expired for user " . Auth::id());
            if ($this->client->getRefreshToken()) {
                try {
                    Log::info("[GoogleCalendarService] Attempting to refresh token for user " . Auth::id());
                    $newToken = $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                    if (isset($newToken['error'])) {
                        Log::error("[GoogleCalendarService] Error refreshing token: " . $newToken['error_description']);
                        $this->clearStoredToken(); // Clear invalid token
                        return false;
                    }
                    $this->storeToken($newToken); // Store the refreshed token
                    $this->client->setAccessToken($newToken); // Use the new token immediately
                    Log::info("[GoogleCalendarService] Token refreshed successfully for user " . Auth::id());
                    return true;
                } catch (\Exception $e) {
                    Log::error("[GoogleCalendarService] Exception during token refresh: " . $e->getMessage());
                    $this->clearStoredToken(); // Clear token on refresh failure
                    return false;
                }
            } else {
                Log::warning("[GoogleCalendarService] Token expired and no refresh token available for user " . Auth::id());
                $this->clearStoredToken();
                return false;
            }
        }
        // Log::info("[GoogleCalendarService] Valid token found for user " . Auth::id());
        return true;
    }

    /**
     * Clear the stored Google API token.
     *
     * @return void
     */
    protected function clearStoredToken()
    {
        $userId = Auth::id();
        if (!$userId) return;
        Cache::forget("google_token_{$userId}");
        Log::info("[GoogleCalendarService] Cleared Google token for user {$userId}.");
    }

    /**
     * Clear the connection status cache.
     *
     * @return void
     */
    protected function clearConnectionCache()
    {
        $userId = Auth::id();
        if (!$userId) return;
        Cache::forget("google_connected_{$userId}");
        Log::info("[GoogleCalendarService] Cleared connection status cache for user {$userId}.");
    }

    /**
     * Check if the user has a valid, non-expired Google Calendar connection.
     *
     * @return bool
     */
    public function isConnected()
    {
        $userId = Auth::id();
        if (!$userId) return false;

        // Check cache first
        $cachedStatus = Cache::get("google_connected_{$userId}");
        if ($cachedStatus !== null) {
            // Log::info("[GoogleCalendarService] Connection status from cache for user {$userId}: " . ($cachedStatus ? 'true' : 'false'));
            return $cachedStatus;
        }

        $isConnected = $this->hasValidToken();
        // Cache the result
        Cache::put("google_connected_{$userId}", $isConnected, $this->cacheDuration);
        Log::info("[GoogleCalendarService] Connection status determined and cached for user {$userId}: " . ($isConnected ? 'true' : 'false'));
        return $isConnected;
    }

    /**
     * Revoke Google API access.
     *
     * @return bool Success status
     */
    public function revokeAccess()
    {
        $token = $this->getStoredToken();
        if ($token && isset($token["access_token"])) {
            try {
                $this->client->revokeToken($token["access_token"]);
                Log::info("[GoogleCalendarService] Google token revoked successfully for user " . Auth::id());
            } catch (\Exception $e) {
                Log::error("[GoogleCalendarService] Failed to revoke Google token for user " . Auth::id() . ": " . $e->getMessage());
                // Even if revocation fails, clear local data
            }
        }
        $this->clearStoredToken();
        $this->clearConnectionCache();
        // Also delete associated calendar records
        $user = Auth::user();
        if ($user) {
            $deletedCount = $user->calendars()->delete();
            Log::info("[GoogleCalendarService] Deleted {$deletedCount} calendar records for user {$user->id} after disconnection.");
        }
        return true; // Return true even if revocation failed, as local data is cleared
    }

    /**
     * Get the list of calendars associated with the user from the local database.
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserCalendars(User $user)
    {
        // Ensure calendar info is stored if missing (e.g., first time after auth)
        if ($user->calendars()->count() === 0 && $this->hasValidToken()) {
            Log::info("[GoogleCalendarService] No local calendars found for user {$user->id}, attempting to store from Google.");
            $this->storeCalendarInformation();
        }
        return $user->calendars()->orderBy('summary')->get();
    }

    /**
     * Update the selection status of a specific calendar in the local database.
     *
     * @param string $calendarId The Google Calendar ID
     * @param bool $isSelected
     * @return GoogleCalendar
     */
    public function updateCalendarSelection(string $calendarId, bool $isSelected)
    {
        $user = Auth::user();
        $calendar = $user->calendars()->where('calendar_id', $calendarId)->firstOrFail();
        $calendar->is_selected = $isSelected;
        $calendar->save();
        Log::info("[GoogleCalendarService] Updated selection for calendar {$calendarId} to " . ($isSelected ? 'true' : 'false') . " for user {$user->id}.");
        return $calendar;
    }

    /**
     * Update the visibility status of a specific calendar in the local database.
     *
     * @param string $calendarId The Google Calendar ID
     * @param bool $isVisible
     * @return GoogleCalendar
     */
    public function updateCalendarVisibility(string $calendarId, bool $isVisible)
    {
        $user = Auth::user();
        $calendar = $user->calendars()->where('calendar_id', $calendarId)->firstOrFail();
        $calendar->is_visible = $isVisible;
        $calendar->save();
        Log::info("[GoogleCalendarService] Updated visibility for calendar {$calendarId} to " . ($isVisible ? 'true' : 'false') . " for user {$user->id}.");
        return $calendar;
    }

    /**
     * Disconnect (delete) a specific calendar from the local database.
     *
     * @param string $calendarId The Google Calendar ID
     * @return void
     */
    public function disconnectCalendar(string $calendarId)
    {
        $user = Auth::user();
        $deletedCount = $user->calendars()->where('calendar_id', $calendarId)->delete();
        Log::info("[GoogleCalendarService] Disconnected calendar {$calendarId} (deleted {$deletedCount} records) for user {$user->id}.");
    }

    /**
     * Disconnect (delete) all calendars for the user from the local database.
     *
     * @return void
     */
    public function disconnectAllCalendars()
    {
        $user = Auth::user();
        $deletedCount = $user->calendars()->delete();
        Log::info("[GoogleCalendarService] Disconnected all calendars (deleted {$deletedCount} records) for user {$user->id}.");
    }
}

