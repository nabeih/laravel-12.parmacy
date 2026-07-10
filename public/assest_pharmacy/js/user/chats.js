// User → Pharmacy real-time chat

let _activeChat = null, _rtdbRef = null, _chats = [];

async function initUserChats() {
  await _loadChats();
  _setupInput();

  const urlParams  = new URLSearchParams(window.location.search);
  const pharmacyId = urlParams.get('pharmacyId');
  if (pharmacyId) {
    const existing = _chats.find(c => c.pharmacyId === pharmacyId);
    if (existing) { openUserChat(existing.id); }
    else { _startNewChat(pharmacyId); }
  }
}

async function _loadChats() {
  const uid = window.currentUser?.uid || 'demo-user';

  if (DEMO_MODE) {
    _chats = mockChats.slice();
    _renderPharmacyList();
    return;
  }

  try {
    const snap = await db.collection('chats').where('userId','==',uid).orderBy('updatedAt','desc').get();
    _chats = snap.docs.map(d => ({ id: d.id, ...d.data() }));
    _renderPharmacyList();
  } catch(e) {
    _chats = mockChats.slice();
    _renderPharmacyList();
  }
}

function _renderPharmacyList() {
  const list = document.getElementById('pharmacy-list');
  if (!_chats.length) {
    list.innerHTML = `<div class="conv-empty"><span style="font-size:36px">🏪</span><p>لا توجد محادثات مع صيدليات بعد</p></div>`;
    return;
  }
  list.innerHTML = _chats.map(c => {
    const last   = (c.messages || []).slice(-1)[0];
    const preview = last ? esc(last.text.substring(0, 45)) + (last.text.length > 45 ? '...' : '') : '';
    const unread  = c.unreadByUser || 0;
    const active  = _activeChat?.id === c.id ? 'active' : '';
    return `
      <div class="conv-item ${active}" onclick="openUserChat('${c.id}')">
        <div class="conv-avatar" style="background:var(--primary-50);font-size:16px">🏪</div>
        <div class="conv-info">
          <div class="conv-name">${esc(c.pharmacyName || 'صيدلية')}</div>
          <div class="conv-preview">${preview || 'ابدأ المحادثة'}</div>
        </div>
        ${unread > 0 ? `<span class="conv-badge">${unread}</span>` : ''}
      </div>`;
  }).join('');
}

function openUserChat(chatId) {
  _activeChat = _chats.find(c => c.id === chatId);
  if (!_activeChat) return;

  _activeChat.unreadByUser = 0;
  _renderPharmacyList();

  document.getElementById('chat-empty').style.display    = 'none';
  document.getElementById('chat-header').style.display   = 'flex';
  document.getElementById('messages-area').style.display = 'flex';
  document.getElementById('chat-input-bar').style.display = 'flex';

  document.getElementById('chat-pharm-name').textContent = _activeChat.pharmacyName || 'الصيدلية';
  document.getElementById('chat-pharm-sub').textContent  = _activeChat.pharmacyAddress || '';

  if (DEMO_MODE) {
    _renderMessages(_activeChat.messages || []);
    return;
  }

  if (_rtdbRef) _rtdbRef.off();
  const uid = window.currentUser?.uid;
  _rtdbRef = database.ref(`chats/${_activeChat.pharmacyId}/${chatId}/messages`);
  _rtdbRef.on('value', snap => {
    const msgs = [];
    snap.forEach(child => msgs.push(child.val()));
    _renderMessages(msgs);
    database.ref(`chats/${_activeChat.pharmacyId}/${chatId}/unreadByUser`).set(0).catch(() => {});
  });
  window.addEventListener('beforeunload', () => { if (_rtdbRef) _rtdbRef.off(); });
}

function _renderMessages(messages) {
  const area = document.getElementById('messages-area');
  if (!messages.length) {
    area.innerHTML = `<div style="flex:1;display:flex;align-items:center;justify-content:center;color:var(--text-muted)"><p>ابدأ المحادثة مع الصيدلية</p></div>`;
    return;
  }
  area.innerHTML = messages.map(m => {
    const sent = m.senderType === 'user';
    return `
      <div class="message-wrap ${sent ? 'sent' : 'received'}">
        <div class="bubble ${sent ? 'bubble-sent' : 'bubble-received'}">
          ${esc(m.text)}
          <span class="bubble-time">${timeAgo(m.timestamp)}</span>
        </div>
      </div>`;
  }).join('');
  area.scrollTop = area.scrollHeight;
}

function _setupInput() {
  const input = document.getElementById('msg-input');
  const btn   = document.getElementById('btn-send');
  const send  = () => {
    const text = input?.value.trim();
    if (!text || !_activeChat) return;
    input.value = '';
    _sendMessage(text);
  };
  btn?.addEventListener('click', send);
  input?.addEventListener('keydown', e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); } });
}

async function _sendMessage(text) {
  const msg = { text, senderType: 'user', timestamp: Date.now() };

  if (DEMO_MODE) {
    const chat = mockChats.find(c => c.id === _activeChat.id);
    if (chat) { chat.messages = chat.messages || []; chat.messages.push(msg); }
    _activeChat.messages = (_activeChat.messages || []);
    _activeChat.messages.push(msg);
    _renderMessages(_activeChat.messages);
    return;
  }

  try {
    await database.ref(`chats/${_activeChat.pharmacyId}/${_activeChat.id}/messages`).push(msg);
    await database.ref(`chats/${_activeChat.pharmacyId}/${_activeChat.id}`).update({
      updatedAt: Date.now(),
      unreadByPharmacist: firebase.database.ServerValue.increment(1),
      userId: window.currentUser?.uid,
      userName: window.currentUser?.name || '',
    });
  } catch(e) { alert('فشل إرسال الرسالة: ' + e.message); }
}

async function _startNewChat(pharmacyId) {
  const newChat = {
    id: 'chat-' + Date.now(),
    pharmacyId,
    pharmacyName: 'صيدلية',
    userId: window.currentUser?.uid || 'demo-user',
    userName: window.currentUser?.name || '',
    messages: [],
    unreadByUser: 0,
    unreadByPharmacist: 0,
    updatedAt: Date.now(),
  };
  _chats.unshift(newChat);
  _renderPharmacyList();
  openUserChat(newChat.id);
}
