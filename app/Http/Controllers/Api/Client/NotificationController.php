<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $client = $request->user();

        $notifications = $client->notifications()->orderByDesc('created_at')->get();

        return NotificationResource::collection($notifications);
    }

    public function ouvrir(Request $request, $id)
    {
        $client = $request->user();

        $notification = $client->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'contrat_id' => $notification->data['contrat_id'] ?? null,
        ]);
    }

    public function toutLire(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }
}
