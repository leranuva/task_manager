<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Obtener notificaciones del usuario
     */
    public function index(Request $request)
    {
        $query = $request->user()->notifications()->latest();
        
        // Filtrar por leídas/no leídas
        if ($request->has('filter')) {
            if ($request->filter === 'unread') {
                $query->whereNull('read_at');
            } elseif ($request->filter === 'read') {
                $query->whereNotNull('read_at');
            }
        }
        
        $notifications = $query->paginate(50);
        
        // Agrupar notificaciones
        $grouped = $this->notificationService->groupNotifications($notifications->items());

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
            'grouped' => $grouped,
            'filters' => $request->only(['filter']),
        ]);
    }

    /**
     * Marcar notificaciones como leídas
     */
    public function markAsRead(Request $request)
    {
        $request->validate([
            'notification_ids' => ['nullable', 'array'],
            'notification_ids.*' => ['exists:notifications,id'],
        ]);

        $this->notificationService->markAsRead(
            $request->user(),
            $request->input('notification_ids')
        );

        return response()->json(['success' => true]);
    }

    /**
     * Obtener notificaciones no leídas (API)
     */
    public function unread(Request $request)
    {
        $notifications = $this->notificationService->getUnread($request->user(), 20);
        $grouped = $this->notificationService->groupNotifications($notifications->items());

        return response()->json([
            'notifications' => $notifications,
            'grouped' => $grouped,
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }
}
