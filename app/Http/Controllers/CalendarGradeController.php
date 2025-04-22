<?php

namespace App\Http\Controllers;

use App\Models\CalendarGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalendarGradeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $grades = CalendarGrade::where('user_id', Auth::id())
            ->orderBy('week_start_date', 'desc')
            ->get();
            
        return response()->json([
            'grades' => $grades
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'week_start_date' => 'required|date',
            'week_end_date' => 'required|date|after_or_equal:week_start_date',
            'overall_grade' => 'required|numeric|min:0|max:100',
            'rule_grades' => 'required|array',
            'strengths' => 'nullable|string',
            'improvement_areas' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'calendar_data' => 'nullable|array',
        ]);

        $grade = CalendarGrade::create([
            'user_id' => Auth::id(),
            'week_start_date' => $validated['week_start_date'],
            'week_end_date' => $validated['week_end_date'],
            'overall_grade' => $validated['overall_grade'],
            'rule_grades' => $validated['rule_grades'],
            'strengths' => $request->strengths,
            'improvement_areas' => $request->improvement_areas,
            'recommendations' => $request->recommendations,
            'calendar_data' => $request->calendar_data,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Calendar graded successfully',
            'grade' => $grade
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $grade = CalendarGrade::where('user_id', Auth::id())
            ->findOrFail($id);
            
        return response()->json([
            'grade' => $grade
        ]);
    }

    /**
     * Get the latest grade for the current week.
     */
    public function getCurrentWeekGrade()
    {
        $now = Carbon::now();
        $startOfWeek = $now->startOfWeek()->format('Y-m-d');
        $endOfWeek = $now->endOfWeek()->format('Y-m-d');
        
        $grade = CalendarGrade::where('user_id', Auth::id())
            ->where('week_start_date', $startOfWeek)
            ->where('week_end_date', $endOfWeek)
            ->latest()
            ->first();
            
        return response()->json([
            'grade' => $grade,
            'has_grade' => $grade !== null,
            'week_start' => $startOfWeek,
            'week_end' => $endOfWeek
        ]);
    }

    /**
     * Get grades for a specific date range.
     */
    public function getGradesByDateRange(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        
        $grades = CalendarGrade::where('user_id', Auth::id())
            ->where('week_start_date', '>=', $validated['start_date'])
            ->where('week_end_date', '<=', $validated['end_date'])
            ->orderBy('week_start_date', 'desc')
            ->get();
            
        return response()->json([
            'grades' => $grades
        ]);
    }
}
