@extends('layouts.user')
@section('title', 'المساعد الذكي')
@section('content')

<div class="card p-0" style="overflow:hidden;display:flex;flex-direction:column;height:calc(100vh - 140px)">
    <div class="chatbot-header">
        <div style="display:flex;align-items:center;gap:12px">
            <div class="bot-avatar">🤖</div>
            <div>
                <div class="fw-semibold">مساعد صيدلية PharmacyLink</div>
                <div class="text-muted small">مساعد ذكي — ليس بديلاً عن استشارة الصيدلاني</div>
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-ghost" id="btn-clear-history">🗑️ مسح المحادثة</button>
    </div>

    <div id="bot-messages" class="bot-messages-area"></div>

    <div id="suggestions" class="suggestions-row">
        <button type="button" class="suggestion-chip" onclick="sendSuggestion(this)">ما هي جرعة الباراسيتامول؟</button>
        <button type="button" class="suggestion-chip" onclick="sendSuggestion(this)">هل يوجد تفاعل بين الأدوية؟</button>
        <button type="button" class="suggestion-chip" onclick="sendSuggestion(this)">ما هي فوائد فيتامين D؟</button>
    </div>

    <div class="chat-input-bar chatbot-input-bar">
        <input type="text" id="bot-input" class="chat-input" placeholder="اكتب سؤالك هنا...">
        <button type="button" class="btn-send" id="bot-send">➤</button>
    </div>
</div>

@stop

@push('scripts')
    <script src="{{ asset('assest_pharmacy/js/ai-client.js') }}"></script>
    <script src="{{ asset('assest_pharmacy/js/user/chatbot.js') }}"></script>
    <script>document.addEventListener('DOMContentLoaded', initChatbot);</script>
@endpush
