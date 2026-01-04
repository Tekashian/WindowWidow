<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get notifications for authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return new JsonResponse([
                'data' => [],
                'unread_count' => 0,
            ]);
        }
        
        $query = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Filter by read status
        if ($request->has('unread') && $request->unread) {
            $query->unread();
        }

        // Filter by type
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->byPriority($request->priority);
        }

        // Limit
        $limit = $request->get('limit', 50);
        $notifications = $query->limit($limit)->get();

        return new JsonResponse([
            'data' => $notifications,
            'unread_count' => $this->notificationService->getUnreadCount($user->id),
        ]);
    }

    /**
     * Get unread count
     */
    public function unreadCount(Request $request)
    {
        $count = $this->notificationService->getUnreadCount($request->user()->id);

        return new JsonResponse([
            'count' => $count,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $notification->markAsRead();

        return new JsonResponse([
            'message' => 'Notification marked as read',
            'data' => $notification,
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $count = $this->notificationService->markAllAsRead($request->user()->id);

        return new JsonResponse([
            'message' => 'All notifications marked as read',
            'count' => $count,
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy(Request $request, $id)
    {
        $notification = Notification::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $notification->delete();

        return new JsonResponse([
            'message' => 'Notification deleted',
        ]);
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllRead(Request $request)
    {
        $count = Notification::where('user_id', $request->user()->id)
            ->where('read', true)
            ->delete();

        return new JsonResponse([
            'message' => 'All read notifications deleted',
            'count' => $count,
        ]);
    }
}
