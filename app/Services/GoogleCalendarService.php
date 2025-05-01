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
        
        // Initialize token on demand rather than in constructor
        // $token = $this->getStoredToken();
        // if ($token) {
        //     $this->client->setAccessToken($token);
        //     if ($this->client->isAccessTokenExpired() && $this->client->getRefreshToken()) {
        //         try {
        //             $newToken = $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
        //             $this->storeToken($newToken);
        //         } catch (\Exception $e) {
        //             Log::error("Failed to refresh Google token in constructor: " . $e->getMessage());
        //         }
        //     }
        // }
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
            // This needs the user context, ensure Auth::user() is available here
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
                        "is_selected" => true, // Default to selected
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
                    // Use a reasonable duration, maybe shorter than the original 30 days if fetched from DB
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
                        // The fetched token might not include a new refresh token, merge to preserve it
                        $mergedToken = array_merge($token, $newToken);
                        // Ensure refresh token isn't overwritten if not present in $newToken
                        if (!isset($newToken["refresh_token"]) && isset($token["refresh_token"])) {
                            $mergedToken["refresh_token"] = $token["refresh_token"];
                        }
                        $this->storeToken($mergedToken); // Store the updated token
                        $this->client->setAccessToken($mergedToken); // Update client with refreshed token
                        // Clear connection status cache after successful refresh
                        $this->clearConnectionCache(); 
                        return true;
                    } catch (\Exception $e) {
                        Log::error("[GoogleCalendarService] Failed to refresh Google token: " . $e->getMessage()); // DEBUG
                        // If refresh fails, clear the invalid token
                        $this->clearStoredToken();
                        return false;
                    }
                } else {
                    Log::warning("[GoogleCalendarService] Access token expired, but no refresh token available."); // DEBUG
                    // Clear the expired token if no refresh token
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
                        
                        // Convert to Carbon instances for easier manipulation
                        $startCarbon = $start ? Carbon::parse($start) : null;
                        $endCarbon = $end ? Carbon::parse($end) : null;
                        
                        $allEvents[] = [
                            "id" => $event->id,
                            "title" => $event->getSummary(),
                            "description" => $event->getDescription(),
                            "start" => $startCarbon ? $startCarbon->toDateTimeString() : null,
                            "end" => $endCarbon ? $endCarbon->toDateTimeString() : null,
                            "all_day" => !$event->start->dateTime,
                            "location" => $event->getLocation(),
                            "creator" => $event->getCreator() ? $event->getCreator()->getEmail() : null,
                            "attendees" => $this->formatAttendees($event),
                            "color_id" => $event->getColorId(),
                            "calendar_id" => $calendarId, // Add calendar ID for reference
                        ];
                    }
                }
                
                Log::info("[GoogleCalendarService] Total events fetched for user {$userId}: " . count($allEvents)); // DEBUG
                return $allEvents;
            } catch (\Exception $e) {
                Log::error("[GoogleCalendarService] Google Calendar events error for user {$userId}: " . $e->getMessage()); // DEBUG
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
                    "email" => $person->getEmail(),
                    "name" => $person->getDisplayName(),
                    "response_status" => $person->getResponseStatus(),
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
        $user = Auth::user();
        if (!$user) {
            Log::error("[GoogleCalendarService] Attempted revokeAccess without authenticated user."); // DEBUG
            return false;
        }
        $userId = $user->id;
        Log::info("[GoogleCalendarService] Revoking access for user {$userId}"); // DEBUG
        try {
            $token = $this->getStoredToken();
            if ($token) {
                $this->client->setAccessToken($token);
                $this->client->revokeToken(); // Attempt to revoke with Google
            }
            $this->clearStoredToken(); // Clear local token regardless
            
            // Remove all calendar entries for this user
            GoogleCalendar::where("user_id", $userId)->delete();
            
            return true;
        } catch (\Exception $e) {
            Log::error("[GoogleCalendarService] Google Calendar revoke error for user {$userId}: " . $e->getMessage()); // DEBUG
            // Still clear local token even if revoke fails
            $this->clearStoredToken();
            GoogleCalendar::where("user_id", $userId)->delete();
            return false;
        }
    }

    /**
     * Check if the current user is connected (has a valid token)
     *
     * @return bool
     */
    public function isConnected()
    {
        $user = Auth::user();
        if (!$user) {
            Log::warning("[GoogleCalendarService] isConnected check failed: User not authenticated."); // DEBUG
            return false;
        }
        $userId = $user->id;
        $cacheKey = "google_calendar_connection_{$userId}";
        
        // Check cache first
        $cachedStatus = Cache::get($cacheKey);
        if ($cachedStatus !== null) {
            Log::info("[GoogleCalendarService] isConnected returning cached status for user {$userId}: " . ($cachedStatus ? "true" : "false")); // DEBUG
            return $cachedStatus;
        }

        Log::info("[GoogleCalendarService] isConnected checking token validity for user {$userId}..."); // DEBUG
        $isValid = $this->hasValidToken(); // This now has its own logging
        Log::info("[GoogleCalendarService] isConnected result for user {$userId}: " . ($isValid ? "true" : "false")); // DEBUG
        
        // Store the result in cache
        Cache::put($cacheKey, $isValid, $this->cacheDuration);
        
        return $isValid;
    }

    /**
     * Get the list of calendars for the authenticated user.
     *
     * @param User $user
     * @return array
     */
    public function getUserCalendars(User $user)
    {
        $userId = $user->id;
        $cacheKey = "google_calendars_{$userId}";
        Log::info("[GoogleCalendarService] Getting user calendars for user {$userId}"); // DEBUG
        
        // Check cache first
        $cachedCalendars = Cache::get($cacheKey);
        if ($cachedCalendars !== null) {
            Log::info("[GoogleCalendarService] Returning cached calendars for user {$userId}"); // DEBUG
            return $cachedCalendars;
        }

        try {
            if (!$this->hasValidToken()) { // Ensure token is valid before proceeding
                 Log::warning("[GoogleCalendarService] No valid token for getUserCalendars for user {$userId}"); // DEBUG
                 throw new \Exception("No valid Google Calendar token found");
            }
            
            $this->service = new Google_Service_Calendar($this->client); // Client has token set by hasValidToken
            
            $calendarList = $this->service->calendarList->listCalendarList();
            $googleItems = $calendarList->getItems();
            Log::info("[GoogleCalendarService] Fetched " . count($googleItems) . " calendars from Google API for user {$userId}"); // DEBUG

            // Fetch corresponding database records to get selection state
            $dbCalendars = $user->calendars()->get()->keyBy("calendar_id");

            // Merge Google API data with database selection state
            $mergedCalendars = [];
            foreach ($googleItems as $item) {
                $dbRecord = $dbCalendars->get($item->getId());
                $mergedCalendars[] = (
                    // Convert Google_Service_Calendar_CalendarListEntry to an array or stdClass
                    // and add the is_selected property
                    (object)array_merge((array)$item->toSimpleObject(), [
                        "is_selected" => $dbRecord ? $dbRecord->is_selected : false, // Default to false if no DB record (shouldn't happen ideally)
                        // Ensure other relevant fields from Google object are present if needed
                        "id" => $item->getId(), // Ensure ID is correctly mapped
                        "summary" => $item->getSummary(),
                        "backgroundColor" => $item->getBackgroundColor(),
                        "primary" => $item->getPrimary(),
                        // Add other fields as needed by the frontend
                    ])
                );
            }
            
            // Store the merged items in cache
            Cache::put($cacheKey, $mergedCalendars, $this->cacheDuration);
            
            return $mergedCalendars;
        } catch (\Exception $e) {
            Log::error("[GoogleCalendarService] Google Calendar list error for user {$userId}: " . $e->getMessage()); // DEBUG
            throw $e;
        }
    }

    // --- Selection/Visibility/Disconnect methods remain largely the same, ensure Auth::user() is valid ---

    public function updateCalendarSelection($calendarId, $isSelected)
    {
        $user = Auth::user();
        if (!$user) throw new \Exception("User not authenticated.");
        $calendar = $user->calendars()->where("calendar_id", $calendarId)->firstOrFail();
        $calendar->update(["is_selected" => $isSelected]);
        
        // Clear the cache for this user's calendars
        Cache::forget("google_calendars_" . $user->id);
        
        return $calendar;
    }

    public function updateCalendarVisibility($calendarId, $isVisible)
    {
        $user = Auth::user();
        if (!$user) throw new \Exception("User not authenticated.");
        $calendar = $user->calendars()->where("calendar_id", $calendarId)->firstOrFail();
        $calendar->update(["is_visible" => $isVisible]);
        
        // Clear the cache for this user's calendars
        Cache::forget("google_calendars_" . $user->id);
        
        return $calendar;
    }

    public function disconnectCalendar($calendarId)
    {
        $user = Auth::user();
        if (!$user) throw new \Exception("User not authenticated.");
        $calendar = $user->calendars()->where("calendar_id", $calendarId)->firstOrFail();
        $calendar->delete();
        
        // Clear the cache for this user's calendars
        Cache::forget("google_calendars_" . $user->id);
        Cache::forget("google_calendar_connection_" . $user->id); // Also clear connection status
    }

    public function disconnectAllCalendars()
    {
        $user = Auth::user();
        if (!$user) throw new \Exception("User not authenticated.");
        $user->calendars()->delete();
        
        // Clear the cache for this user's calendars and connection status
        Cache::forget("google_calendars_" . $user->id);
        Cache::forget("google_calendar_connection_" . $user->id);
    }
}

