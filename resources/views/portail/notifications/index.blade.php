@extends('layouts.portail')

@section('title', 'Notifications')

@section('content')
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 px-5 py-4 mb-5 flex items-center justify-between">
        <div>
            <h1 class="text-base font-bold text-gray-800">Notifications</h1>
            <p class="text-xs text-gray-400 mt-0.5">{{ $notifications->count() }} notification{{ $notifications->count() > 1 ? 's' : '' }}</p>
        </div>
        @if ($notifications->whereNull('read_at')->isNotEmpty())
            <form method="POST" action="{{ route('portail.notifications.tout-lire') }}">
                @csrf
                <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-700">Tout marquer comme lu</button>
            </form>
        @endif
    </div>

    @if ($notifications->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 text-center text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <p class="text-sm">Aucune notification pour le moment.</p>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden divide-y divide-gray-100">
            @foreach ($notifications as $notification)
                @php
                    $data = $notification->data;
                    $estPaiement = ($data['type'] ?? '') === 'paiement_valide';
                    $nonLue = is_null($notification->read_at);
                @endphp
                <a href="{{ route('portail.notifications.ouvrir', $notification->id) }}"
                    class="flex items-start gap-3 px-4 py-3.5 hover:bg-gray-50 transition-colors {{ $nonLue ? 'bg-red-50/40' : '' }}">
                    <div class="h-9 w-9 rounded-xl flex items-center justify-center shrink-0 {{ $estPaiement ? 'bg-green-50 text-green-600' : 'bg-blue-50 text-blue-600' }}">
                        @if ($estPaiement)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 002 2v6a2 2 0 002 2z"/></svg>
                        @else
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-semibold text-gray-800">{{ $data['titre'] ?? 'Notification' }}</p>
                            @if ($nonLue)
                                <span class="h-1.5 w-1.5 rounded-full bg-red-600 shrink-0"></span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $data['message'] ?? '' }}</p>
                        <p class="text-[10px] text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endsection
