@extends($layout)
@section('title', 'الإشعارات')
@section('content')

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">الإشعارات</h1>
        <p class="admin-page-sub">جميع الإشعارات الخاصة بحسابك</p>
    </div>
    <div>
        <form action="{{ route('notifications.markAllRead') }}" method="POST">
            @csrf
            <button type="submit" class="admin-btn admin-btn-outline">✓ تعليم الكل كمقروء</button>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th></th>
                    <th>العنوان</th>
                    <th>التفاصيل</th>
                    <th>التاريخ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($notifications as $notification)
                    <tr style="{{ $notification->read_at ? '' : 'font-weight:600' }}">
                        <td>{{ $notification->read_at ? '' : '🔵' }}</td>
                        <td>{{ $notification->data['title'] ?? '-' }}</td>
                        <td>{{ $notification->data['body'] ?? '-' }}</td>
                        <td>{{ $notification->created_at->diffForHumans() }}</td>
                        <td>
                            <a href="{{ route('notifications.markRead', $notification->id) }}"
                                class="admin-btn admin-btn-sm admin-btn-outline">فتح</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="admin-empty">
                                <div class="admin-empty-icon">🔔</div>
                                <div class="admin-empty-title">لا يوجد إشعارات بعد</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="admin-pagination">
        {{ $notifications->links() }}
    </div>
</div>

@stop
