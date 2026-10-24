<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Bandeja de notificaciones de la campana de la toolbar.
 *
 * Todas las acciones trabajan sobre las notificaciones del usuario autenticado:
 * Laravel garantiza que cada fila pertenece a su `notifiable`.
 */
class NotificationController extends Controller
{
    /**
     * Vista completa con el historial.
     */
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(25);

        return view('Admin.notifications.index', [
            'notifications' => $notifications,
            'unread' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Últimas notificaciones en JSON (para refrescar la campana sin recargar).
     */
    public function feed(Request $request): JsonResponse
    {
        $user = $request->user();

        $items = $user->notifications()
            ->latest()
            ->limit(12)
            ->get()
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'data' => $notification->data,
                'read' => $notification->read_at !== null,
                'created_at' => $notification->created_at?->toIso8601String(),
                'created_human' => $notification->created_at?->diffForHumans(),
            ]);

        return response()->json([
            'unread' => $user->unreadNotifications()->count(),
            'items' => $items,
        ]);
    }

    /**
     * Marca una notificación como leída.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->whereKey($id)->first();

        if (! $notification) {
            return response()->json(['message' => 'Notificación no encontrada.'], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'ok' => true,
            'unread' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Marca todas como leídas.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['ok' => true, 'unread' => 0]);
    }
}
