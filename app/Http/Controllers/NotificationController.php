<?php

namespace App\Http\Controllers;

use App\Models\NotificationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Update the user's notification settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'weekly_grade' => 'boolean',
            'weekly_reminder' => 'boolean',
            'weekly_reminder_day' => 'required|string|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'weekly_reminder_time' => 'required|date_format:H:i',
        ]);

        // Convert checkbox values to boolean
        $validated['weekly_grade'] = $request->has('weekly_grade');
        $validated['weekly_reminder'] = $request->has('weekly_reminder');
        $validated['weekly_reminder_day'] = $request->has('weekly_reminder_day');
        $validated['weekly_reminder_time'] = $request->has('weekly_reminder_time');

        // Update or create notification settings
        NotificationSetting::updateOrCreate(
            ['user_id' => Auth::id()],
            $validated
        );

        return redirect()->route('settings')->with('success', 'Notification settings saved successfully!');
    }
}
