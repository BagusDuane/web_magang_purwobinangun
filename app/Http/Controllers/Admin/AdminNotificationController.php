<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    /**
     * Mengembalikan daftar notifikasi terbaru untuk admin (JSON + rendered partial)
     */
    public function feed(Request $request): JsonResponse
    {
        $notifications = AdminNotificationService::getNotificationsForAdmin();

        return response()->json([
            'count' => $notifications->count(),
            'notifications' => $notifications->values(),
            'html' => view('admin.partials.notification-items', [
                'adminNotifications' => $notifications,
            ])->render(),
        ]);
    }
}

