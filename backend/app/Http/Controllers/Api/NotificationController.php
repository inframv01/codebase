<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DatabaseNotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = max(1, min((int) $request->integer('per_page', 15), 100));

        return DatabaseNotificationResource::collection($request->user()->notifications()->latest()->paginate($perPage));
    }

    public function read(Request $request, string $notificationId): JsonResponse
    {
        /** @var DatabaseNotification|null $notification */
        $notification = $request->user()->notifications()->whereKey($notificationId)->first();
        abort_if($notification === null, 404);

        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function readAll(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
