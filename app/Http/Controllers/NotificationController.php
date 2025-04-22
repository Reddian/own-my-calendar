<?php

namespace App\Http\Controllers;

use App\Models\NotificationSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get the notification settings for the authenticated user.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSettings()
    {
        try {
            $user = Auth::user();
            $settings = NotificationSetting::where('user_id', $user->id)->first();
            
            if (!$settings) {
                // Create default settings if none exist
                $settings = NotificationSetting::create([
                    'user_id' => $user->id,
                    'weekly_grade' => false,
                    'weekly_grade_day' => 1, // Monday
                    'weekly_grade_time' => '09:00',
                    'weekly_reminder' => false,
                    'weekly_reminder_day' => 0, // Sunday
                    'weekly_reminder_time' => '18:00',
                    'timezone' => 'America/New_York'
                ]);
            }
            
            return response()->json([
                'success' => true,
                'settings' => $settings
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting notification settings: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get notification settings'], 500);
        }
    }

    /**
     * Update the notification settings for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateSettings(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'weekly_grade' => 'required|boolean',
                'weekly_grade_day' => 'required|integer|min:0|max:6',
                'weekly_grade_time' => 'required|string|regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/',
                'weekly_reminder' => 'required|boolean',
                'weekly_reminder_day' => 'required|integer|min:0|max:6',
                'weekly_reminder_time' => 'required|string|regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/',
                'timezone' => 'required|string|timezone'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $user = Auth::user();
            $settings = NotificationSetting::where('user_id', $user->id)->first();
            
            if (!$settings) {
                $settings = new NotificationSetting();
                $settings->user_id = $user->id;
            }
            
            $settings->weekly_grade = $request->weekly_grade;
            $settings->weekly_grade_day = $request->weekly_grade_day;
            $settings->weekly_grade_time = $request->weekly_grade_time;
            $settings->weekly_reminder = $request->weekly_reminder;
            $settings->weekly_reminder_day = $request->weekly_reminder_day;
            $settings->weekly_reminder_time = $request->weekly_reminder_time;
            $settings->timezone = $request->timezone;
            $settings->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Notification settings updated successfully',
                'settings' => $settings
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating notification settings: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update notification settings'], 500);
        }
    }
}
