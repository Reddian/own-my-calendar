<?php

namespace App\Http\Controllers;

use App\Models\CalendarGrade;
use App\Models\UserProfile;
use App\Services\GoogleCalendarService;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AIGradingController extends Controller
{
    protected $googleCalendarService;
    protected $openAIService;

    public function __construct(GoogleCalendarService $googleCalendarService, OpenAIService $openAIService)
    {
        $this->googleCalendarService = $googleCalendarService;
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
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            // Check if Google Calendar is connected
            if (!$this->googleCalendarService->hasValidToken()) {
                return response()->json([
                    'error' => 'Google Calendar is not connected. Please connect your calendar first.'
                ], 400);
            }

            // Get user profile
            $userProfile = UserProfile::where('user_id', Auth::id())->first();
            if (!$userProfile) {
                return response()->json([
                    'error' => 'User profile not found. Please complete onboarding first.'
                ], 400);
            }

            // Fetch calendar events for the specified week
            $events = $this->googleCalendarService->getEvents(
                $validated['start_date'],
                $validated['end_date']
            );

            // Grade the calendar using OpenAI
            $gradingResult = $this->openAIService->gradeCalendar(
                $events,
                $userProfile,
                $validated['start_date'],
                $validated['end_date']
            );

            // Save the grade to the database
            $calendarGrade = CalendarGrade::create([
                'user_id' => Auth::id(),
                'week_start_date' => $validated['start_date'],
                'week_end_date' => $validated['end_date'],
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
