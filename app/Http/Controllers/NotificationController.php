<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(10);
        return response()->json($notifications);
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read']);
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'All notifications marked as read']);
    }
		public function getUnreadCount()
    {
        $count = auth()->user()->unreadNotifications()->count();
        return response()->json(['count' => $count]);
    }
} 