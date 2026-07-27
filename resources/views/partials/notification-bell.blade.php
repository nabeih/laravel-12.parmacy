@php
    $__unreadCount = auth()->user()->unreadNotifications->count();
    $__recentNotifications = auth()->user()->notifications()->latest()->take(8)->get();
@endphp

<div class="notif-bell-wrap">
    <button type="button" id="notif-bell-toggle" class="notif-bell" aria-label="الإشعارات" aria-expanded="false">
        🔔
        @if($__unreadCount > 0)
            <span class="notif-bell-badge">{{ $__unreadCount > 9 ? '9+' : $__unreadCount }}</span>
        @endif
    </button>
    <div id="notif-bell-dropdown" class="notif-bell-dropdown">
        <div class="notif-bell-dropdown-header">
            <span>الإشعارات</span>
            <form action="{{ route('notifications.markAllRead') }}" method="POST">
                @csrf
                <button type="submit" class="notif-bell-mark-all">تعليم الكل كمقروء</button>
            </form>
        </div>
        <div class="notif-bell-list">
            @forelse ($__recentNotifications as $notification)
                <a href="{{ route('notifications.markRead', $notification->id) }}"
                    class="notif-bell-item {{ $notification->read_at ? '' : 'unread' }}">
                    <div class="notif-bell-item-title">{{ $notification->data['title'] ?? '-' }}</div>
                    <div class="notif-bell-item-body">{{ $notification->data['body'] ?? '-' }}</div>
                    <div class="notif-bell-item-time">{{ $notification->created_at->diffForHumans() }}</div>
                </a>
            @empty
                <div class="notif-bell-empty">لا يوجد إشعارات</div>
            @endforelse
        </div>
        <a href="{{ route('notifications.index') }}" class="notif-bell-viewall">عرض كل الإشعارات</a>
    </div>
</div>
