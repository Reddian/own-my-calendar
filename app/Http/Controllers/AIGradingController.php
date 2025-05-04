<?php
namespace App\Http\Controllers;

use App\Models\CalendarGrade;
use App\Models\GoogleCalendar;
use App\Models\UserProfile;
use App\Services\MultiCalendarService;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AIGradingController extends Controller
{
    protected $multiCalendarService;
    protected $openAIService;

    public function __construct(MultiCalendarService $multiCalendarService, OpenAIService $openAIService)
    {
        $this->multiCalendarService = $multiCalendarService;
        $this->openAIService = $openAIService;
        $this->middleware("auth:sanctum");
    }

    /**
     * Grade the user"s calendar for a specific week using AI
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function gradeCalendar(Request $request)
    {
        $user = Auth::user();
        Log::info("Starting AI calendar grading for user", ["user_id" => $user->id]);
        try {
            // Get current week dates if not provided
            $now = Carbon::now();
            $startDate = $request->input("start_date", $now->startOfWeek()->format("Y-m-d"));
            $endDate = $request->input("end_date", $now->endOfWeek()->format("Y-m-d"));

            // Check if user has any connected calendars
            $hasCalendars = GoogleCalendar::where("user_id", $user->id)->exists();
            if (!$hasCalendars) {
                Log::warning("AI Grading attempt failed: No calendars connected", ["user_id" => $user->id]);
                return response()->json([
                    "error" => "No calendars connected. Please connect at least one calendar first."
                ], 400);
            }

            // Get user profile, create a default one if it doesn"t exist
            $userProfile = UserProfile::firstOrCreate(
                ["user_id" => $user->id],
                [ // Default values if creating a new profile
                    "mt_everest" => "Not specified",
                    "money_making_activities" => "Not specified",
                    "energy_renewal_activities" => "Not specified",
                    "calendar_preferences" => [],
                ]
            );
            
            // Log if a default profile was created
            if ($userProfile->wasRecentlyCreated) {
                Log::info("Created default UserProfile during AI grading", ["user_id" => $user->id]);
            }

            // Fetch calendar events for the specified week from all selected calendars
            $eventsResult = $this->multiCalendarService->getEventsFromSelectedCalendars(
                $startDate,
                $endDate
            );

            if (!$eventsResult["success"]) {
                Log::error("AI Grading failed: Could not fetch events", ["user_id" => $user->id, "error" => $eventsResult["error"] ?? "Unknown error"]);
                return response()->json([
                    "error" => $eventsResult["error"] ?? "Failed to fetch calendar events"
                ], 400);
            }

            $events = $eventsResult["events"];
            Log::info("Fetched events for AI grading", ["user_id" => $user->id, "event_count" => count($events)]);

            // Grade the calendar using OpenAI
            $gradingResult = $this->openAIService->gradeCalendar(
                $events,
                $userProfile,
                $startDate,
                $endDate
            );
            Log::info("Received grading result from OpenAI service", ["user_id" => $user->id]);

            // Save the grade to the database, ensuring arrays are JSON encoded and removing calendar_data
            $calendarGrade = CalendarGrade::create([
                "user_id" => $user->id,
                "week_start_date" => $startDate,
                "week_end_date" => $endDate,
                "overall_grade" => $gradingResult["overall_grade"] ?? null,
                "rule_grades" => json_encode($gradingResult["rule_grades"] ?? []),
                "strengths" => json_encode($gradingResult["strengths"] ?? []),
                "improvement_areas" => json_encode($gradingResult["improvement_areas"] ?? []),
                "recommendations" => json_encode($gradingResult["recommendations"] ?? []),
                // "calendar_data" => json_encode($events), // Removed as requested
            ]);
            Log::info("Saved calendar grade to database (without raw event data)", ["user_id" => $user->id, "grade_id" => $calendarGrade->id]);

            // Return the grade object (Eloquent will handle casting JSON fields back to arrays/objects)
            return response()->json([
                "success" => true,
                "message" => "Calendar graded successfully",
                "grade" => $calendarGrade 
            ]);
        } catch (\Exception $e) {
            Log::error("AI grading error for user {$user->id}: " . $e->getMessage(), [
                "exception_message" => $e->getMessage(),
                "exception_trace" => $e->getTraceAsString() // Include trace for detailed debugging
            ]);
            return response()->json([
                "error" => "Failed to grade calendar due to a server error."
            ], 500);
        }
    }
}

