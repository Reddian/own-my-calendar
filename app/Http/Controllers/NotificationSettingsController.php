<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\NotificationSetting; // Assuming a model exists at App\Models\NotificationSetting
use Illuminate\Support\Facades\DB; // Use DB facade as fallback if model assumption is wrong

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
        Log::info("Fetching notification settings from notification_settings table", ["user_id" => $user->id]);

        try {
            // Use updateOrCreate with user_id as the unique identifier
            // Provide default values for creation
            $settings = NotificationSetting::firstOrCreate(
                ["user_id" => $user->id],
                [
                    "weekly_grade_email" => true,
                    "planning_reminder" => true,
                    "reminder_day" => "Sunday",
                    "reminder_time" => "18:00",
                ]
            );

            // Return only the relevant settings fields
            return response()->json([
                "weekly_grade_email" => (bool) $settings->weekly_grade_email,
                "planning_reminder" => (bool) $settings->planning_reminder,
                "reminder_day" => $settings->reminder_day,
                "reminder_time" => $settings->reminder_time,
            ]);

        } catch (\Exception $e) {
            // Fallback using DB facade if Model assumption is wrong or other error occurs
            Log::error("Error fetching notification settings using Model, trying DB facade", ["user_id" => $user->id, "error" => $e->getMessage()]);
            try {
                $settings = DB::table("notification_settings")->where("user_id", $user->id)->first();
                if (!$settings) {
                    // Create default settings if none exist
                    DB::table("notification_settings")->insert([
                        "user_id" => $user->id,
                        "weekly_grade_email" => true,
                        "planning_reminder" => true,
                        "reminder_day" => "Sunday",
                        "reminder_time" => "18:00",
                        "created_at" => now(),
                        "updated_at" => now(),
                    ]);
                    $settings = DB::table("notification_settings")->where("user_id", $user->id)->first();
                }
                 return response()->json([
                    "weekly_grade_email" => (bool) $settings->weekly_grade_email,
                    "planning_reminder" => (bool) $settings->planning_reminder,
                    "reminder_day" => $settings->reminder_day,
                    "reminder_time" => $settings->reminder_time,
                ]);
            } catch (\Exception $dbE) {
                 Log::error("Error fetching notification settings using DB facade", ["user_id" => $user->id, "error" => $dbE->getMessage()]);
                 // Return default values on error to prevent frontend breaking
                 return response()->json([
                    "weekly_grade_email" => true,
                    "planning_reminder" => true,
                    "reminder_day" => "Sunday",
                    "reminder_time" => "18:00",
                ], 500); // Indicate server error
            }
        }
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
        Log::info("Updating notification settings in notification_settings table", ["user_id" => $user->id]);

        $validated = $request->validate([
            "weekly_grade_email" => ["required", "boolean"],
            "planning_reminder" => ["required", "boolean"],
            "reminder_day" => ["required", "string", "in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday"],
            "reminder_time" => ["required", "string", "regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/"]
        ]);

        try {
            // Use updateOrCreate to find by user_id and update or create
            NotificationSetting::updateOrCreate(
                ["user_id" => $user->id], // Attributes to find the record by
                $validated // Attributes to update or create with
            );

            Log::info("Notification settings updated successfully via Model", ["user_id" => $user->id]);
            return response()->json(["message" => "Notification settings updated successfully."]);

        } catch (\Exception $e) {
             // Fallback using DB facade if Model assumption is wrong or other error occurs
            Log::error("Error updating notification settings using Model, trying DB facade", ["user_id" => $user->id, "error" => $e->getMessage()]);
            try {
                 $affected = DB::table("notification_settings")
                    ->where("user_id", $user->id)
                    ->update($validated + ["updated_at" => now()]);

                // If no record was updated, try inserting (in case it didn't exist)
                if ($affected === 0) {
                     DB::table("notification_settings")->insert(
                        $validated + [
                            "user_id" => $user->id,
                            "created_at" => now(),
                            "updated_at" => now()
                        ]
                    );
                }
                Log::info("Notification settings updated successfully via DB facade", ["user_id" => $user->id]);
                return response()->json(["message" => "Notification settings updated successfully."]);
            } catch (\Exception $dbE) {
                Log::error("Notification settings update failed using DB facade", ["user_id" => $user->id, "error" => $dbE->getMessage()]);
                return response()->json(["message" => "Failed to update notification settings. Please try again."], 500);
            }
        }
    }
}

