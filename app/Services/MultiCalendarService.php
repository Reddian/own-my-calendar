<?php
namespace App\Services;

use App\Models\GoogleCalendar;
use Carbon\Carbon;
use Exception;
use Google_Client;
use Google_Service_Calendar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MultiCalendarService
{
    protected $client;
    
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
            "https://www.googleapis.com/auth/calendar.readonly",
            "https://www.googleapis.com/auth/calendar.events.readonly",
        ]);
    }
    
    /**
     * Get the Google OAuth authorization URL
     *
     * @return array
     */
    public function getAuthUrl()
    {
        try {
            $authUrl = $this->client->createAuthUrl();
            
            return [
                "success" => true,
                "auth_url" => $authUrl
            ];
        } catch (Exception $e) {
            Log::error("Error generating auth URL: " . $e->getMessage());
            return [
                "success" => false,
                "error" => $e->getMessage()
            ];
        }
    }
    
    /**
     * Handle the OAuth callback and store the access token
     *
     * @param string $code
     * @return array
     */
    public function handleCallback($code)
    {
        try {
            $accessToken = $this->client->fetchAccessTokenWithAuthCode($code);
            
            if (isset($accessToken["access_token"])) {
                $this->client->setAccessToken($accessToken);
                $service = new Google_Service_Calendar($this->client);
                
                // Get the list of calendars
                $calendarList = $service->calendarList->listCalendarList();
                
                foreach ($calendarList->getItems() as $calendarListEntry) {
                    // Check if this calendar already exists for the user
                    $existingCalendar = GoogleCalendar::where("user_id", Auth::id())
                        ->where("calendar_id", $calendarListEntry->getId())
                        ->first();
                    
                    $expiresAt = Carbon::now()->addSeconds($accessToken["expires_in"]);
                    
                    if ($existingCalendar) {
                        // Update existing calendar
                        $existingCalendar->update([
                            "name" => $calendarListEntry->getSummary(),
                            "description" => $calendarListEntry->getDescription(),
                            "color" => $calendarListEntry->getBackgroundColor(),
                            "is_primary" => $calendarListEntry->getPrimary() ?? false,
                            "access_token" => $accessToken,
                            "token_expires_at" => $expiresAt,
                            "refresh_token" => $accessToken["refresh_token"] ?? $existingCalendar->refresh_token,
                        ]);
                    } else {
                        // Create new calendar entry
                        GoogleCalendar::create([
                            "user_id" => Auth::id(),
                            "calendar_id" => $calendarListEntry->getId(),
                            "name" => $calendarListEntry->getSummary(),
                            "description" => $calendarListEntry->getDescription(),
                            "color" => $calendarListEntry->getBackgroundColor(),
                            "is_primary" => $calendarListEntry->getPrimary() ?? false,
                            "is_selected" => true, // Default to selected
                            "is_visible" => true, // Default to visible
                            "access_token" => $accessToken,
                            "token_expires_at" => $expiresAt,
                            "refresh_token" => $accessToken["refresh_token"] ?? null,
                        ]);
                    }
                }
                
                return [
                    "success" => true,
                    "message" => "Calendars connected successfully"
                ];
            } else {
                return [
                    "success" => false,
                    "error" => "Failed to get access token"
                ];
            }
        } catch (Exception $e) {
            Log::error("Error handling callback: " . $e->getMessage());
            return [
                "success" => false,
                "error" => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get all calendars for a user
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserCalendars($user)
    {
        return GoogleCalendar::where("user_id", $user->id)->get();
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
            $calendar = GoogleCalendar::where("id", $calendarId)
                ->where("user_id", Auth::id())
                ->first();
            
            if (!$calendar) {
                return [
                    "success" => false,
                    "error" => "Calendar not found"
                ];
            }
            
            $calendar->update([
                "is_selected" => $isSelected
            ]);
            
            return [
                "success" => true,
                "message" => "Calendar selection updated successfully"
            ];
        } catch (Exception $e) {
            Log::error("Error updating calendar selection: " . $e->getMessage());
            return [
                "success" => false,
                "error" => $e->getMessage()
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
            $calendar = GoogleCalendar::where("id", $calendarId)
                ->where("user_id", Auth::id())
                ->first();
            
            if (!$calendar) {
                return [
                    "success" => false,
                    "error" => "Calendar not found"
                ];
            }
            
            $calendar->update([
                "is_visible" => $isVisible
            ]);
            
            return [
                "success" => true,
                "message" => "Calendar visibility updated successfully"
            ];
        } catch (Exception $e) {
            Log::error("Error updating calendar visibility: " . $e->getMessage());
            return [
                "success" => false,
                "error" => $e->getMessage()
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
        // Fetch calendars where is_selected is true
        $selectedCalendars = GoogleCalendar::where("user_id", $userId)
            ->where("is_selected", true) // Changed from is_visible to is_selected
            ->get();
        
        if ($selectedCalendars->isEmpty()) {
            // Return empty array if no calendars are selected, instead of an error
            return [
                "success" => true,
                "events" => []
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
                        Log::warning("Skipping calendar due to invalid token and no refresh token: " . $calendar->name);
                        continue; // Skip this calendar if token can"t be refreshed
                    }
                }
                
                $this->client->setAccessToken($calendar->access_token);
                $service = new Google_Service_Calendar($this->client);
                
                $optParams = [
                    "timeMin" => $startDateTime,
                    "timeMax" => $endDateTime,
                    "singleEvents" => true,
                    "orderBy" => "startTime",
                ];
                
                $results = $service->events->listEvents($calendar->calendar_id, $optParams);
                
                foreach ($results->getItems() as $event) {
                    $start = $event->start->dateTime ?? $event->start->date;
                    $end = $event->end->dateTime ?? $event->end->date;
                    
                    // Convert to Carbon instances for easier manipulation
                    $startCarbon = $start ? Carbon::parse($start) : null;
                    $endCarbon = $end ? Carbon::parse($end) : null;
                    
                    $allEvents[] = [
                        "id" => $event->id,
                        "calendar_id" => $calendar->id,
                        "calendar_name" => $calendar->name,
                        "calendar_color" => $calendar->color,
                        "title" => $event->getSummary(),
                        "description" => $event->getDescription(),
                        "start" => $startCarbon ? $startCarbon->toDateTimeString() : null,
                        "end" => $endCarbon ? $endCarbon->toDateTimeString() : null,
                        "all_day" => !$event->start->dateTime,
                        "location" => $event->getLocation(),
                        "creator" => $event->getCreator() ? $event->getCreator()->getEmail() : null,
                        "attendees" => $this->formatAttendees($event),
                    ];
                }
            } catch (Exception $e) {
                Log::error("Error fetching events for calendar " . $calendar->name . ": " . $e->getMessage());
                // Continue with other calendars even if one fails
            }
        }
        
        return [
            "success" => true,
            "events" => $allEvents
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
                    "email" => $person->getEmail(),
                    "name" => $person->getDisplayName(),
                    "response_status" => $person->getResponseStatus(),
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
                "refresh_token" => $calendar->refresh_token
            ]);
            
            $newToken = $this->client->fetchAccessTokenWithRefreshToken();
            
            if (isset($newToken["access_token"])) {
                $expiresAt = Carbon::now()->addSeconds($newToken["expires_in"]);
                
                $calendar->update([
                    "access_token" => $newToken,
                    "token_expires_at" => $expiresAt,
                    "refresh_token" => $newToken["refresh_token"] ?? $calendar->refresh_token,
                ]);
                
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            Log::error("Error refreshing token: " . $e->getMessage());
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
            $calendar = GoogleCalendar::where("id", $calendarId)
                ->where("user_id", Auth::id())
                ->first();
            
            if (!$calendar) {
                return [
                    "success" => false,
                    "error" => "Calendar not found"
                ];
            }
            
            $calendar->delete();
            
            return [
                "success" => true,
                "message" => "Calendar disconnected successfully"
            ];
        } catch (Exception $e) {
            Log::error("Error disconnecting calendar: " . $e->getMessage());
            return [
                "success" => false,
                "error" => $e->getMessage()
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
            GoogleCalendar::where("user_id", Auth::id())->delete();
            
            return [
                "success" => true,
                "message" => "All calendars disconnected successfully"
            ];
        } catch (Exception $e) {
            Log::error("Error disconnecting all calendars: " . $e->getMessage());
            return [
                "success" => false,
                "error" => $e->getMessage()
            ];
        }
    }
}

