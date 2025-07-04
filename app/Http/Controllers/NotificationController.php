<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
public function index(Request $request)
{
    try {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!in_array($user->role, ['itian', 'employer'])) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $notifications = DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    } catch (\Exception $e) {
        \Log::error('Notification Fetch Error: ' . $e->getMessage());
        return response()->json(['error' => 'Server error'], 500);
    }
}


public function deleteAllNotifications(Request $request)
{
    try {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->delete();

        return response()->json(['message' => 'All notifications deleted successfully']);
    } catch (\Exception $e) {
        \Log::error('Notification Deletion Error: ' . $e->getMessage());
        return response()->json(['error' => 'Server error'], 500);
    }
}
}
