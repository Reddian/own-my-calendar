<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationSettingsController extends Controller
{
    /**
     * Get the authenticated user's notification settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSettings(Request $request)
    {
        $user = Auth::user();
        Log::info("Fetching notification settings", ["user_id" => $user->id]);

        // Assuming user model has these attributes or a related settings model
        // Provide default values if attributes don't exist yet
        $settings = [
            // Use boolean casting for checkboxes
            'weekly_grade_email' => (bool) ($user->settings["notifications"]["weekly_grade_email"] ?? true),
            'planning_reminder' => (bool) ($user->settings["notifications"]["planning_reminder"] ?? true),
            'reminder_day' => $user->settings["notifications"]["reminder_day"] ?? 'Sunday',
            'reminder_time' => $user->settings["notifications"]["reminder_time"] ?? '18:00',
        ];

        return response()->json($settings);
    }

    /**
     * Update the authenticated user's notification settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        Log::info("Updating notification settings", ["user_id" => $user->id]);

        $validated = $request->validate([
            'weekly_grade_email' => ["required", "boolean"],
            'planning_reminder' => ["required", "boolean"],
            'reminder_day' => ["required", "string", "in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday"],
            // Basic time format validation (HH:MM)
            'reminder_time' => ["required", "string", "regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/"] 
        ]);

        try {
            // Assuming settings are stored in a JSON column named 'settings' on the User model
            $currentSettings = $user->settings ?? [];
            $currentSettings["notifications"] = $validated;
            $user->settings = $currentSettings;
            $user->save();

            Log::info("Notification settings updated successfully", ["user_id" => $user->id]);
            return response()->json(["message" => "Notification settings updated successfully."]);

        } catch (\Exception $e) {
            Log::error("Notification settings update failed", ["user_id" => $user->id, "error" => $e->getMessage()]);
            return response()->json(["message" => "Failed to update notification settings. Please try again."], 500);
        }
    }
}

