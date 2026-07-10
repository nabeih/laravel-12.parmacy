// Real-time chat — Firebase RTDB (demo: mockChats)

let _activeChat = null, _rtdbRef = null, _chats = [];

async function initPharmacistChats() {
  await _loadConversations();
  _setupInput();

  const urlParams = new URLSearchParams(window.location.search);
  const chatId = urlParams.get('chatId');
  if (chatId) {
    const c = _chats.find(x => x.id === chatId);
    if (c) openChat(c.id);
  }
}

async function _loadConversations() {
  const pharmacyId = window.currentUser?.pharmacyId || window.currentUser?.uid || 'pharm-001';

  if (DEMO_MODE) {
    _chats = mockChats.filter(c => c.pharmacyId === pharmacyId);
    _renderConvList();
    return;
  }

  try {
    const snap = await db.collection('chats').where('pharmacyId','==',pharmacyId).orderBy('updatedAt','desc').get();
    _chats = snap.docs.map(d => ({ id: d.id, ...d.data() }));
    _renderConvList();
  } catch(e) {
    _chats = mockChats;
    _renderConvList();
  }
}

function _renderConvList() {
  const list  = document.getElementById('conv-list');
  const total = _chats.reduce((s, c) => s + (c.unreadByPharmacist || 0), 0);
  document.getElementById('total-unread').textContent = total;
  document.getElementById('total-unread').style.display = total > 0 ? 'inline-flex' : 'none';

  if (!_chats.length) {
    list.innerHTML = `<div class="conv-empty"><span style="font-size:36px">💬</span><p>لا توجد محادثات</p></div>`;
    return;
  }

  list.innerHTML = _chats.map(c => {
    const lastMsg  = (c.messages || []).slice(-1)[0];
    const preview  = lastMsg ? esc(lastMsg.text.substring(0, 45)) + (lastMsg.text.length > 45 ? '...' : '') : '';
    const unread   = c.unreadByPharmacist || 0;
    const active   = _activeChat?.id === c.id ? 'active' : '';
    return `
      <div class="conv-item ${active}" onclick="openChat('${c.id}')">
        <div class="conv-avatar">${(c.userName || 'ع')[0]}</div>
        <div class="conv-info">
          <div class="conv-name">${esc(c.userName || 'عميل')}</div>
          <div class="conv-preview">${preview || 'لا توجد رسائل'}</div>
        </div>
        ${unread > 0 ? `<span class="conv-badge">${unread}</span>` : ''}
      </div>`;
  }).join('');
}

function openChat(chatId) {
  _activeChat = _chats.find(c => c.id === chatId);
  if (!_activeChat) return;

  // Mark as read
  _activeChat.unreadByPharmacist = 0;
  _renderConvList();

  document.getElementById('chat-empty').style.display  = 'none';
  document.getElementById('chat-header').style.display = 'flex';
  document.getElementById('messages-area').style.display = 'flex';
  document.getElementById('chat-input-bar').style.display = 'flex';

  document.getElementById('chat-avatar').textContent   = (_activeChat.userName || 'ع')[0];
  document.getElementById('chat-user-name').textContent = _activeChat.userName || 'عميل';
  document.getElementById('chat-user-sub').textContent  = _activeChat.userPhone || '';

  if (DEMO_MODE) {
    _renderMessages(_activeChat.messages || []);
  } else {
    if (_rtdbRef) _rtdbRef.off();
    const pharmacyId = window.currentUser?.uid || 'pharm-001';
    _rtdbRef = database.ref(`chats/${pharmacyId}/${chatId}/messages`);
    _rtdbRef.on('value', snap => {
      const msgs = [];
      snap.forEach(child => msgs.push(child.val()));
      _renderMessages(msgs);
      // Mark read in RTDB
      database.ref(`chats/${pharmacyId}/${chatId}/unreadByPharmacist`).set(0).catch(() => {});
    });
  }
}

function _renderMessages(messages) {
  const area = document.getElementById('messages-area');
  if (!messages.length) {
    area.innerHTML = `<div style="flex:1;display:flex;align-items:center;justify-content:center;color:var(--text-muted)"><p>لا توجد رسائل بعد</p></div>`;
    return;
  }
  area.innerHTML = messages.map(m => {
    const sent = m.senderType === 'pharmacist';
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
  const msg = { text, senderType: 'pharmacist', timestamp: Date.now() };

  if (DEMO_MODE) {
    const chat = mockChats.find(c => c.id === _activeChat.id);
    if (chat) { chat.messages = chat.messages || []; chat.messages.push(msg); }
    _activeChat.messages = (_activeChat.messages || []);
    _activeChat.messages.push(msg);
    _renderMessages(_activeChat.messages);
    return;
  }

  const pharmacyId = window.currentUser?.uid;
  try {
    await database.ref(`chats/${pharmacyId}/${_activeChat.id}/messages`).push(msg);
    await database.ref(`chats/${pharmacyId}/${_activeChat.id}`).update({
      updatedAt: Date.now(),
      unreadByUser: firebase.database.ServerValue.increment(1),
    });
  } catch(e) { alert('فشل إرسال الرسالة: ' + e.message); }
}
