<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Extension;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExtensionController extends Controller
{
    public function getStatus()
    {
        try {
            $user = Auth::user();
            $extension = $user->extension ?? new Extension(['user_id' => $user->id]);

            return response()->json([
                'status' => [
                    'connected' => $extension->is_connected,
                    'lastSync' => $extension->last_sync
                ],
                'features' => [
                    'quickAdd' => $extension->quick_add_enabled,
                    'notifications' => $extension->notifications_enabled,
                    'autoSync' => $extension->auto_sync_enabled
                ],
                'settings' => [
                    'syncInterval' => $extension->sync_interval,
                    'notificationTime' => $extension->notification_time
                ],
                'stats' => [
                    'quickAdds' => $extension->quick_adds_count,
                    'notifications' => $extension->notifications_count,
                    'syncs' => $extension->syncs_count,
                    'lastSync' => $extension->last_sync ? $extension->last_sync->diffForHumans() : 'Never'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get extension status: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get extension status'], 500);
        }
    }

    public function toggleConnection(Request $request)
    {
        try {
            $user = Auth::user();
            $extension = $user->extension ?? new Extension(['user_id' => $user->id]);

            $extension->is_connected = $request->input('connected');
            $extension->save();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Failed to toggle extension connection: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to toggle connection'], 500);
        }
    }

    public function updateFeature(Request $request)
    {
        try {
            $user = Auth::user();
            $extension = $user->extension ?? new Extension(['user_id' => $user->id]);

            $feature = $request->input('feature');
            $enabled = $request->input('enabled');

            switch ($feature) {
                case 'quickAdd':
                    $extension->quick_add_enabled = $enabled;
                    break;
                case 'notifications':
                    $extension->notifications_enabled = $enabled;
                    break;
                case 'autoSync':
                    $extension->auto_sync_enabled = $enabled;
                    break;
                default:
                    return response()->json(['error' => 'Invalid feature'], 400);
            }

            $extension->save();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Failed to update feature: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update feature'], 500);
        }
    }

    public function updateSetting(Request $request)
    {
        try {
            $user = Auth::user();
            $extension = $user->extension ?? new Extension(['user_id' => $user->id]);

            $setting = $request->input('setting');
            $value = $request->input('value');

            switch ($setting) {
                case 'syncInterval':
                    $extension->sync_interval = $value;
                    break;
                case 'notificationTime':
                    $extension->notification_time = $value;
                    break;
                default:
                    return response()->json(['error' => 'Invalid setting'], 400);
            }

            $extension->save();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Failed to update setting: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update setting'], 500);
        }
    }
} 