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
            
            // Store the token using our method
            $this->storeToken($token);
            
            // Store calendar information in the database
            if (Auth::check()) {
                $this->storeCalendarInformation($token);
            } else {
                Log::error("User not authenticated during handleAuthCallback");
                throw new \Exception("User authentication required to store calendar information.");
            }
            
            return $token;
        } catch (\Exception $e) {
            Log::error("Google Calendar auth error: " . $e->getMessage());
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
            $user = Auth::user();
            if (!$user) {
                Log::error("Attempted to store calendar info without authenticated user.");
                return; // Or throw exception
            }
            
            $this->client->setAccessToken($accessToken);
            $this->service = new Google_Service_Calendar($this->client);
            
            // Get the list of calendars
            $calendarList = $this->service->calendarList->listCalendarList();
            
            foreach ($calendarList->getItems() as $calendarListEntry) {
                // Check if this calendar already exists for the user
                $existingCalendar = GoogleCalendar::where("user_id", $user->id)
                    ->where("calendar_id", $calendarListEntry->getId())
                    ->first();
                
                $expiresAt = isset($accessToken["expires_in"]) ? Carbon::now()->addSeconds($accessToken["expires_in"]) : null;
                $refreshToken = $accessToken["refresh_token"] ?? null;

                $calendarData = [
                    "name" => $calendarListEntry->getSummary(),
                    "description" => $calendarListEntry->getDescription(),
                    "color" => $calendarListEntry->getBackgroundColor(),
                    "is_primary" => $calendarListEntry->getPrimary() ?? false,
                    "access_token" => json_encode($accessToken), // Store the whole token
                    "token_expires_at" => $expiresAt,
                    // Only update refresh token if a new one is provided
                    "refresh_token" => $refreshToken ?? ($existingCalendar ? $existingCalendar->refresh_token : null),
                ];

                if ($existingCalendar) {
                    // Update existing calendar
                    $existingCalendar->update($calendarData);
                } else {
                    // Create new calendar entry
                    GoogleCalendar::create(array_merge([
                        "user_id" => $user->id,
                        "calendar_id" => $calendarListEntry->getId(),
                        "is_selected" => false, // *** CHANGED: Default to NOT selected ***
                        "is_visible" => true, // Default to visible
                    ], $calendarData));
                }
            }
            // Clear cache after updating
            Cache::forget("google_calendars_" . $user->id);
            Cache::forget("google_calendar_connection_" . $user->id);

        } catch (\Exception $e) {
            Log::error("Failed to store calendar information: " . $e->getMessage());
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
        $user = Auth::user();
        if (!$user) {
            Log::error("Attempted to store token without authenticated user.");
            return;
        }
        $userId = $user->id;
        Log::info("[GoogleCalendarService] Storing token for user ID: {$userId}"); // DEBUG
        // Store the full token structure
        Cache::put("google_token_{$userId}", $accessToken, Carbon::now()->addDays(30)); // Store for 30 days
        // Clear connection status cache whenever a new token is stored
        $this->clearConnectionCache();
    }
    
    /**
     * Get the stored access token for the current user
     *
     * @return array|null
     */
    protected function getStoredToken()
    {
        $user = Auth::user();
        if (!$user) {
            Log::warning("[GoogleCalendarService] Attempted to get token without authenticated user."); // DEBUG
            return null;
        }
        $userId = $user->id;
        $cacheKey = "google_token_{$userId}";
        Log::info("[GoogleCalendarService] Getting token for user ID: {$userId}"); // DEBUG
        
        // 1. Check cache first
        $token = Cache::get($cacheKey);
        if ($token) {
            Log::info("[GoogleCalendarService] Token retrieved from cache for user {$userId}: " . json_encode($token)); // DEBUG
            return $token;
        }

        Log::info("[GoogleCalendarService] Token not found in cache for user {$userId}, checking database..."); // DEBUG

        // 2. If not in cache, check the database (e.g., from the first available calendar record)
        $calendarRecord = $user->calendars()->whereNotNull("access_token")->orderBy("updated_at", "desc")->first();

        if ($calendarRecord && $calendarRecord->access_token) {
            try {
                $token = json_decode($calendarRecord->access_token, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($token)) {
                    Log::info("[GoogleCalendarService] Token retrieved from database for user {$userId}: " . json_encode($token)); // DEBUG
                    // 3. Store it back in cache for future requests
                    Cache::put($cacheKey, $token, Carbon::now()->addHours(1)); 
                    return $token;
                } else {
                    Log::error("[GoogleCalendarService] Failed to decode token from database for user {$userId}. JSON Error: " . json_last_error_msg()); // DEBUG
                }
            } catch (\Exception $e) {
                 Log::error("[GoogleCalendarService] Exception decoding token from database for user {$userId}: " . $e->getMessage()); // DEBUG
            }
        }

        Log::warning("[GoogleCalendarService] Token not found in cache or database for user {$userId}."); // DEBUG
        // 4. If not found in cache or database, return null
        return null;
    }
    
    /**
     * Check if the current user has a valid token
     *
     * @return bool
     */
    public function hasValidToken()
    {
        Log::info("[GoogleCalendarService] Checking hasValidToken..."); // DEBUG
        $token = $this->getStoredToken();
        if (!$token) {
            Log::info("[GoogleCalendarService] No token found in cache."); // DEBUG
            return false;
        }
        
        // Ensure token is an array
        if (!is_array($token)) {
            Log::error("[GoogleCalendarService] Stored token is not an array."); // DEBUG
            return false;
        }

        try {
            $this->client->setAccessToken($token);
            Log::info("[GoogleCalendarService] Token set on client."); // DEBUG

            if ($this->client->isAccessTokenExpired()) {
                Log::info("[GoogleCalendarService] Access token expired."); // DEBUG
                $refreshToken = $this->client->getRefreshToken(); // This gets refresh token from the $token array if present
                
                if ($refreshToken) {
                    Log::info("[GoogleCalendarService] Refresh token found, attempting refresh..."); // DEBUG
                    try {
                        $newToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                        Log::info("[GoogleCalendarService] Token refreshed successfully."); // DEBUG
                        $mergedToken = array_merge($token, $newToken);
                        if (!isset($newToken["refresh_token"]) && isset($token["refresh_token"])) {
                            $mergedToken["refresh_token"] = $token["refresh_token"];
                        }
                        $this->storeToken($mergedToken); // Store the updated token
                        $this->client->setAccessToken($mergedToken); // Update client with refreshed token
                        $this->clearConnectionCache(); 
                        return true;
                    } catch (\Exception $e) {
                        Log::error("[GoogleCalendarService] Failed to refresh Google token: " . $e->getMessage()); // DEBUG
                        $this->clearStoredToken();
                        return false;
                    }
                } else {
                    Log::warning("[GoogleCalendarService] Access token expired, but no refresh token available."); // DEBUG
                    $this->clearStoredToken();
                    return false;
                }
            }
            Log::info("[GoogleCalendarService] Access token is valid."); // DEBUG
            return true;
        } catch (\Exception $e) {
            Log::error("[GoogleCalendarService] Error setting access token: " . $e->getMessage()); // DEBUG
            return false;
        }
    }

    /**
     * Clear the stored token for the current user.
     */
    protected function clearStoredToken()
    {
        $user = Auth::user();
        if ($user) {
            $userId = $user->id;
            Log::info("[GoogleCalendarService] Clearing token for user ID: {$userId}"); // DEBUG
            Cache::forget("google_token_{$userId}");
            Cache::forget("google_calendar_connection_{$userId}"); // Also clear connection status cache
        }
    }

    /**
     * Clear the connection status cache for the current user.
     */
    protected function clearConnectionCache()
    {
        $user = Auth::user();
        if ($user) {
            $userId = $user->id;
            Log::info("[GoogleCalendarService] Clearing connection status cache for user ID: {$userId}"); // DEBUG
            Cache::forget("google_calendar_connection_{$userId}");
        }
    }
    
    /**
     * Get events from the user's selected calendars for a specific date range
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
        $cacheKey = "google_calendar_events_{$userId}_{$startDate}_{$endDate}";
        
        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($startDate, $endDate, $userId, $user) {
            Log::info("[GoogleCalendarService] Getting events for user {$userId} from {$startDate} to {$endDate}"); // DEBUG
            try {
                if (!$this->hasValidToken()) {
                    Log::warning("[GoogleCalendarService] No valid token for getEvents for user {$userId}"); // DEBUG
                    throw new \Exception("No valid Google Calendar token found");
                }
                
                $this->service = new Google_Service_Calendar($this->client);
                $startDateTime = Carbon::parse($startDate)->startOfDay()->toRfc3339String();
                $endDateTime = Carbon::parse($endDate)->endOfDay()->toRfc3339String();
                
                $optParams = [
                    "timeMin" => $startDateTime,
                    "timeMax" => $endDateTime,
                    "singleEvents" => true,
                    "orderBy" => "startTime",
                ];
                
                // Get selected calendars for the user
                $selectedCalendars = $user->calendars()->where("is_selected", true)->pluck("calendar_id");
                Log::info("[GoogleCalendarService] Selected calendars for user {$userId}: " . $selectedCalendars->implode(", ")); // DEBUG

                $allEvents = [];
                foreach ($selectedCalendars as $calendarId) {
                    Log::info("[GoogleCalendarService] Fetching events from calendar: {$calendarId}"); // DEBUG
                    $results = $this->service->events->listEvents($calendarId, $optParams);
                    
                    foreach ($results->getItems() as $event) {
                        $start = $event->start->dateTime ?? $event->start->date;
                        $end = $event->end->dateTime ?? $event->end->date;
                        
                        $startCarbon = $start ? Carbon::parse($start) : null;
                        $endCarbon = $end ? Carbon::parse($end) : null;
                        
                        $allEvents[] = [
                            "id" => $event->id,
                            "title" => $event->getSummary(),
                            "start" => $start,
                            "end" => $end,
                            "allDay" => !$event->start->dateTime, // If no dateTime, it's an all-day event
                            "calendarId" => $calendarId,
                            // Add other relevant event properties if needed
                            // "description" => $event->getDescription(),
                            // "location" => $event->getLocation(),
                        ];
                    }
                }
                Log::info("[GoogleCalendarService] Found " . count($allEvents) . " events for user {$userId}"); // DEBUG
                return $allEvents;
            } catch (\Exception $e) {
                Log::error("[GoogleCalendarService] Error getting events for user {$userId}: " . $e->getMessage()); // DEBUG
                // Clear cache on error?
                Cache::forget($cacheKey);
                throw $e; // Re-throw the exception to be handled by the controller
            }
        });
    }

    /**
     * Check if the user is connected to Google Calendar (cached)
     *
     * @return bool
     */
    public function isConnected()
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        $userId = $user->id;
        $cacheKey = "google_calendar_connection_{$userId}";

        // Cache the connection status to avoid repeated token checks
        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($userId) {
            Log::info("[GoogleCalendarService] Checking connection status (not cached) for user ID: {$userId}"); // DEBUG
            return $this->hasValidToken();
        });
    }

    /**
     * Revoke Google access token
     *
     * @return bool
     */
    public function revokeAccess()
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception("User not authenticated.");
        }
        $userId = $user->id;
        Log::info("[GoogleCalendarService] Revoking access for user ID: {$userId}"); // DEBUG
        
        $token = $this->getStoredToken();
        if (!$token || !isset($token["access_token"])) {
            Log::warning("[GoogleCalendarService] No token found to revoke for user {$userId}."); // DEBUG
            // If no token, maybe clear local DB records anyway?
            $this->disconnectAllCalendars(); // Clear local calendar data
            return true; // Consider it success if there was nothing to revoke
        }

        try {
            $this->client->revokeToken($token["access_token"]);
            Log::info("[GoogleCalendarService] Token revoked successfully via Google API for user {$userId}."); // DEBUG
        } catch (\Exception $e) {
            Log::error("[GoogleCalendarService] Failed to revoke token via Google API for user {$userId}: " . $e->getMessage()); // DEBUG
            // Continue even if revocation fails, to clear local data
        }
        
        // Clear stored token and local calendar data regardless of API success
        $this->clearStoredToken();
        $this->disconnectAllCalendars();
        
        return true;
    }

    /**
     * Get the list of calendars for the user from the database.
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserCalendars(User $user)
    {
        $userId = $user->id;
        $cacheKey = "google_calendars_{$userId}";

        // Cache the list to avoid frequent DB queries
        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($user) {
            Log::info("[GoogleCalendarService] Fetching calendars from DB for user ID: {$user->id}"); // DEBUG
            return $user->calendars()->orderBy("is_primary", "desc")->orderBy("name", "asc")->get();
        });
    }

    /**
     * Update the selection status of a calendar in the database.
     *
     * @param string $calendarId
     * @param bool $isSelected
     * @return GoogleCalendar
     */
    public function updateCalendarSelection(string $calendarId, bool $isSelected)
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception("User not authenticated.");
        }
        $userId = $user->id;
        Log::info("[GoogleCalendarService] Updating selection for calendar {$calendarId} to {$isSelected} for user {$userId}"); // DEBUG

        $calendar = GoogleCalendar::where("user_id", $userId)
            ->where("calendar_id", $calendarId)
            ->firstOrFail();

        $calendar->update(["is_selected" => $isSelected]);

        // Clear relevant caches
        Cache::forget("google_calendars_{$userId}");
        // Potentially clear event caches if selection changes
        // Cache::tags(["google_events_{$userId}"])->flush();

        return $calendar;
    }

    /**
     * Update the visibility status of a calendar (Placeholder - if needed).
     *
     * @param string $calendarId
     * @param bool $isVisible
     * @return GoogleCalendar
     */
    public function updateCalendarVisibility(string $calendarId, bool $isVisible)
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception("User not authenticated.");
        }
        $userId = $user->id;
        Log::info("[GoogleCalendarService] Updating visibility for calendar {$calendarId} to {$isVisible} for user {$userId}"); // DEBUG

        $calendar = GoogleCalendar::where("user_id", $userId)
            ->where("calendar_id", $calendarId)
            ->firstOrFail();

        $calendar->update(["is_visible" => $isVisible]);

        // Clear relevant caches
        Cache::forget("google_calendars_{$userId}");

        return $calendar;
    }

    /**
     * Disconnect a specific calendar (remove from local DB).
     *
     * @param string $calendarId
     * @return void
     */
    public function disconnectCalendar(string $calendarId)
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception("User not authenticated.");
        }
        $userId = $user->id;
        Log::info("[GoogleCalendarService] Disconnecting calendar {$calendarId} for user {$userId}"); // DEBUG

        GoogleCalendar::where("user_id", $userId)
            ->where("calendar_id", $calendarId)
            ->delete();

        // Clear relevant caches
        Cache::forget("google_calendars_{$userId}");
        // Potentially clear event caches
        // Cache::tags(["google_events_{$userId}"])->flush();
    }

    /**
     * Disconnect all calendars for the user (remove from local DB).
     *
     * @return void
     */
    public function disconnectAllCalendars()
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception("User not authenticated.");
        }
        $userId = $user->id;
        Log::info("[GoogleCalendarService] Disconnecting all calendars for user {$userId}"); // DEBUG

        GoogleCalendar::where("user_id", $userId)->delete();

        // Clear relevant caches
        Cache::forget("google_calendars_{$userId}");
        Cache::forget("google_calendar_connection_{$userId}");
        // Potentially clear event caches
        // Cache::tags(["google_events_{$userId}"])->flush();
    }
}

