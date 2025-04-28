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
        $this->middleware('auth:sanctum');
    }

    /**
     * Grade the user's calendar for a specific week using AI
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function gradeCalendar(Request $request)
    {
        try {
            // Get current week dates if not provided
            $now = Carbon::now();
            $startDate = $request->input('start_date', $now->startOfWeek()->format('Y-m-d'));
            $endDate = $request->input('end_date', $now->endOfWeek()->format('Y-m-d'));

            // Check if user has any connected calendars
            $hasCalendars = GoogleCalendar::where('user_id', Auth::id())->exists();
            if (!$hasCalendars) {
                return response()->json([
                    'error' => 'No calendars connected. Please connect at least one calendar first.'
                ], 400);
            }

            // Get user profile
            $userProfile = UserProfile::where('user_id', Auth::id())->first();
            if (!$userProfile) {
                return response()->json([
                    'error' => 'User profile not found. Please complete onboarding first.'
                ], 400);
            }

            // Fetch calendar events for the specified week from all selected calendars
            $eventsResult = $this->multiCalendarService->getEventsFromSelectedCalendars(
                $startDate,
                $endDate
            );

            if (!$eventsResult['success']) {
                return response()->json([
                    'error' => $eventsResult['error'] ?? 'Failed to fetch calendar events'
                ], 400);
            }

            $events = $eventsResult['events'];

            // Grade the calendar using OpenAI
            $gradingResult = $this->openAIService->gradeCalendar(
                $events,
                $userProfile,
                $startDate,
                $endDate
            );

            // Save the grade to the database
            $calendarGrade = CalendarGrade::create([
                'user_id' => Auth::id(),
                'week_start_date' => $startDate,
                'week_end_date' => $endDate,
                'overall_grade' => $gradingResult['overall_grade'],
                'rule_grades' => $gradingResult['rule_grades'],
                'strengths' => $gradingResult['strengths'],
                'improvement_areas' => $gradingResult['improvement_areas'],
                'recommendations' => $gradingResult['recommendations'],
                'calendar_data' => $events,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Calendar graded successfully',
                'grade' => $calendarGrade
            ]);
        } catch (\Exception $e) {
            Log::error('AI grading error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to grade calendar: ' . $e->getMessage()
            ], 500);
        }
    }
}
