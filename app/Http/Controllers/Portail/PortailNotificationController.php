<?php

namespace App\Http\Controllers\Portail;

use App\Http\Controllers\Controller;

class PortailNotificationController extends Controller
{
    public function index()
    {
        $client = auth('clients')->user();

        $notifications = $client->notifications()->orderByDesc('created_at')->get();

        return view('portail.notifications.index', compact('notifications'));
    }

    public function ouvrir($id)
    {
        $client = auth('clients')->user();

        $notification = $client->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        $contratId = $notification->data['contrat_id'] ?? null;

        return $contratId
            ? redirect()->route('portail.contrats.show', $contratId)
            : redirect()->route('portail.notifications.index');
    }

    public function toutLire()
    {
        $client = auth('clients')->user();
        $client->unreadNotifications->markAsRead();

        return back();
    }
}
