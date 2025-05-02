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
use App\Models\CalendarEvent; // Import the new model
use Illuminate\Support\Facades\DB; // Import DB facade for transactions

class GoogleCalendarService
{
    protected $client;
    protected $service;
    protected $cacheDuration = 300; // 5 minutes (Still used for token cache)
    protected $eventSyncThresholdMinutes = 15; // How old local events can be before re-syncing

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
     * Get events from the user's selected calendars for a specific date range,
     * using local database cache first.
     *
     * @param string $startDate Format: Y-m-d
     * @param string $endDate Format: Y-m-d
     * @param bool $forceRefresh Force fetching from Google API, ignoring local cache
     * @return array
     */
    public function getEvents($startDate, $endDate, $forceRefresh = false)
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception("User not authenticated.");
        }
        $userId = $user->id;
        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        Log::info("[GoogleCalendarService] Getting events for user {$userId} from {$startDate} to {$endDate}. Force refresh: " . ($forceRefresh ? 'Yes' : 'No'));

        // 1. Get selected Google Calendar IDs and their corresponding local DB IDs
        $selectedGoogleCalendars = $user->calendars()
            ->where("is_selected", true)
            ->select("id", "calendar_id") // Select both local ID and Google ID
            ->get();

        if ($selectedGoogleCalendars->isEmpty()) {
            Log::info("[GoogleCalendarService] No calendars selected for user {$userId}. Returning empty array.");
            return [];
        }

        $selectedGoogleCalendarIds = $selectedGoogleCalendars->pluck("calendar_id")->toArray();
        $selectedLocalCalendarIds = $selectedGoogleCalendars->pluck("id")->toArray();

        Log::info("[GoogleCalendarService] Selected Google Calendar IDs: " . implode(", ", $selectedGoogleCalendarIds));

        // 2. Check local cache freshness
        $syncNeeded = $forceRefresh;
        if (!$forceRefresh) {
            $oldestSync = CalendarEvent::where("user_id", $userId)
                ->whereIn("google_calendar_id", $selectedLocalCalendarIds)
                ->whereBetween("start_time", [$startDateTime, $endDateTime]) // Check events within the range
                ->min("last_synced_at");

            if (!$oldestSync || Carbon::parse($oldestSync)->lt(Carbon::now()->subMinutes($this->eventSyncThresholdMinutes))) {
                Log::info("[GoogleCalendarService] Local event cache is stale or missing for user {$userId}. Sync needed.");
                $syncNeeded = true;
            }
        }

        // 3. Fetch from Google API if needed
        if ($syncNeeded) {
            Log::info("[GoogleCalendarService] Syncing events from Google API for user {$userId}.");
            try {
                $googleEvents = $this->fetchEventsFromGoogle($startDateTime->toRfc3339String(), $endDateTime->toRfc3339String(), $selectedGoogleCalendars);
                $this->storeEventsLocally($googleEvents, $userId, $selectedGoogleCalendars);
            } catch (\Exception $e) {
                Log::error("[GoogleCalendarService] Failed to sync events from Google for user {$userId}: " . $e->getMessage());
                // Decide: return potentially stale local data or throw error?
                // For now, let's try returning local data if available, otherwise throw.
                $localEvents = $this->fetchEventsFromLocal($userId, $selectedLocalCalendarIds, $startDateTime, $endDateTime);
                if (!empty($localEvents)) {
                    Log::warning("[GoogleCalendarService] Returning potentially stale local events for user {$userId} due to sync failure.");
                    return $localEvents;
                } else {
                    throw $e; // Re-throw if no local data exists either
                }
            }
        }

        // 4. Fetch from local database
        return $this->fetchEventsFromLocal($userId, $selectedLocalCalendarIds, $startDateTime, $endDateTime);
    }

    /**
     * Fetch events directly from Google API for the given calendars and date range.
     *
     * @param string $startDateTime RFC3339
     * @param string $endDateTime RFC3339
     * @param \Illuminate\Database\Eloquent\Collection $selectedCalendars Collection of GoogleCalendar models
     * @return array
     */
    protected function fetchEventsFromGoogle($startDateTime, $endDateTime, $selectedCalendars)
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
        ];

        $allEvents = [];
        foreach ($selectedCalendars as $calendar) {
            $googleCalendarId = $calendar->calendar_id;
            Log::info("[GoogleCalendarService] Fetching events from Google calendar: {$googleCalendarId}");
            try {
                $results = $this->service->events->listEvents($googleCalendarId, $optParams);
                foreach ($results->getItems() as $event) {
                    $allEvents[] = [
                        "google_event" => $event, // Store the raw event object
                        "local_calendar_id" => $calendar->id // Store the local DB ID of the calendar
                    ];
                }
            } catch (\Google\Service\Exception $e) {
                // Handle specific Google API errors (e.g., 404 Not Found if calendar was deleted)
                if ($e->getCode() == 404) {
                    Log::warning("[GoogleCalendarService] Calendar not found on Google: {$googleCalendarId}. Skipping.");
                    // Optionally mark this calendar as inactive in local DB?
                } else {
                    Log::error("[GoogleCalendarService] Google API error fetching events from {$googleCalendarId}: " . $e->getMessage());
                    // Decide whether to continue with other calendars or re-throw
                    // For now, continue to fetch from other calendars
                }
            } catch (\Exception $e) {
                 Log::error("[GoogleCalendarService] General error fetching events from {$googleCalendarId}: " . $e->getMessage());
                 // Continue for now
            }
        }
        Log::info("[GoogleCalendarService] Fetched " . count($allEvents) . " raw events from Google API.");
        return $allEvents;
    }

    /**
     * Store/Update events fetched from Google API into the local database.
     *
     * @param array $googleEvents Raw events from fetchEventsFromGoogle
     * @param int $userId
     * @param \Illuminate\Database\Eloquent\Collection $selectedCalendars
     * @return void
     */
    protected function storeEventsLocally($googleEvents, $userId, $selectedCalendars)
    {
        $now = Carbon::now();
        $eventIdsToKeep = [];

        DB::transaction(function () use ($googleEvents, $userId, $now, &$eventIdsToKeep) {
            foreach ($googleEvents as $eventData) {
                $event = $eventData['google_event'];
                $localCalendarId = $eventData['local_calendar_id'];
                $eventId = $event->getId();
                $eventIdsToKeep[] = $eventId;

                $start = $event->start->dateTime ?? $event->start->date;
                $end = $event->end->dateTime ?? $event->end->date;
                $isAllDay = !$event->start->dateTime;

                try {
                    $startTime = $start ? Carbon::parse($start) : null;
                    $endTime = $end ? Carbon::parse($end) : null;
                } catch (\Exception $e) {
                    Log::warning("[GoogleCalendarService] Could not parse date for event {$eventId}: {$e->getMessage()}. Skipping date fields.");
                    $startTime = null;
                    $endTime = null;
                }

                CalendarEvent::updateOrCreate(
                    [
                        "user_id" => $userId,
                        "event_id" => $eventId,
                    ],
                    [
                        "google_calendar_id" => $localCalendarId,
                        "title" => $event->getSummary(),
                        "start_time" => $startTime,
                        "end_time" => $endTime,
                        "is_all_day" => $isAllDay,
                        "description" => $event->getDescription(),
                        "location" => $event->getLocation(),
                        "attendees" => $event->attendees ? json_encode($event->attendees) : null,
                        "raw_data" => json_encode($event->toSimpleObject()), // Store simplified raw data
                        "last_synced_at" => $now,
                    ]
                );
            }

            // Optionally: Delete events from local DB that were not returned by Google API (for this user/calendars/time range)
            // This handles deleted events on Google. Be careful with the time range logic.
            // $localCalendarIds = $selectedCalendars->pluck('id')->toArray();
            // CalendarEvent::where('user_id', $userId)
            //     ->whereIn('google_calendar_id', $localCalendarIds)
            //     // ->whereBetween('start_time', [$startDateTime, $endDateTime]) // Add appropriate time range check
            //     ->whereNotIn('event_id', $eventIdsToKeep)
            //     ->delete();
            // Log::info("[GoogleCalendarService] Cleaned up potentially deleted events.");

        });
        Log::info("[GoogleCalendarService] Stored/Updated " . count($googleEvents) . " events locally for user {$userId}.");
    }

    /**
     * Fetch events from the local database cache.
     *
     * @param int $userId
     * @param array $localCalendarIds
     * @param Carbon $startDateTime
     * @param Carbon $endDateTime
     * @return array
     */
    protected function fetchEventsFromLocal($userId, $localCalendarIds, $startDateTime, $endDateTime)
    {
        Log::info("[GoogleCalendarService] Fetching events from local DB for user {$userId}.");
        $localEvents = CalendarEvent::where("user_id", $userId)
            ->whereIn("google_calendar_id", $localCalendarIds)
            ->where(function ($query) use ($startDateTime, $endDateTime) {
                // Fetch events that overlap with the requested range
                $query->where("start_time", "<", $endDateTime)
                      ->where("end_time", ">", $startDateTime);
            })
            ->orderBy("start_time", "asc")
            ->get();

        // Format for frontend consistency
        $formattedEvents = $localEvents->map(function ($event) {
            return [
                "id" => $event->event_id, // Use Google Event ID as primary ID
                "title" => $event->title,
                "start" => $event->start_time ? $event->start_time->toIso8601String() : null,
                "end" => $event->end_time ? $event->end_time->toIso8601String() : null,
                "allDay" => $event->is_all_day,
                "calendarId" => $event->googleCalendar->calendar_id, // Get the Google ID from relation
                // Add other fields if needed by frontend
                // "description" => $event->description,
                // "location" => $event->location,
            ];
        })->toArray();

        Log::info("[GoogleCalendarService] Found " . count($formattedEvents) . " events in local DB for user {$userId}.");
        return $formattedEvents;
    }

    // ... (isConnected, revokeAccess, getUserCalendars, updateCalendarSelection, updateCalendarVisibility, disconnectCalendar, disconnectAll remain the same) ...

}

