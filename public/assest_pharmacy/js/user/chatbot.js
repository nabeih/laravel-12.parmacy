// AI Chatbot — uses openai.js → Laravel API → keyword fallback

const STORAGE_KEY = 'pharma_chat_history';
let _history = [];

function initChatbot() {
  _loadHistory();
  _renderMessages();
  _setupInput();
  document.getElementById('btn-clear-history')?.addEventListener('click', _clearHistory);

  if (!_history.length) {
    _appendBotMessage('مرحباً! أنا مساعد صيدلية PharmacyLink 🤖\nيمكنني مساعدتك في:\n• معلومات الأدوية والجرعات\n• التفاعلات الدوائية\n• الإرشادات الصحية العامة\n\nكيف يمكنني مساعدتك اليوم؟');
  }
}

function _loadHistory() {
  try { _history = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'); } catch { _history = []; }
}

function _saveHistory() {
  try { localStorage.setItem(STORAGE_KEY, JSON.stringify(_history.slice(-50))); } catch {}
}

function _renderMessages() {
  const area = document.getElementById('bot-messages');
  if (!_history.length) { area.innerHTML = ''; return; }
  area.innerHTML = _history.map(m => _msgHTML(m.text, m.role)).join('');
  area.scrollTop = area.scrollHeight;
}

function _msgHTML(text, role) {
  const isUser = role === 'user';
  const escaped = esc(text).replace(/\n/g, '<br>');
  return `<div class="message-wrap ${isUser ? 'sent' : 'received'}">
    ${!isUser ? '<div class="bubble-avatar">🤖</div>' : ''}
    <div class="bubble ${isUser ? 'bubble-sent' : 'bubble-bot'}">${escaped}</div>
  </div>`;
}

function _appendUserMessage(text) {
  _history.push({ role:'user', text, timestamp: Date.now() });
  const area = document.getElementById('bot-messages');
  area.insertAdjacentHTML('beforeend', _msgHTML(text, 'user'));
  area.scrollTop = area.scrollHeight;
  document.getElementById('suggestions').style.display = 'none';
}

function _appendBotMessage(text) {
  _history.push({ role:'assistant', text, timestamp: Date.now() });
  _saveHistory();
  const area = document.getElementById('bot-messages');
  area.insertAdjacentHTML('beforeend', _msgHTML(text, 'assistant'));
  area.scrollTop = area.scrollHeight;
}

function _showTyping() {
  const area = document.getElementById('bot-messages');
  const id   = 'typing-' + Date.now();
  area.insertAdjacentHTML('beforeend', `
    <div class="message-wrap received" id="${id}">
      <div class="bubble-avatar">🤖</div>
      <div class="bubble bubble-bot typing-indicator"><span></span><span></span><span></span></div>
    </div>`);
  area.scrollTop = area.scrollHeight;
  return id;
}

function _removeTyping(id) {
  document.getElementById(id)?.remove();
}

function _setupInput() {
  const input = document.getElementById('bot-input');
  const btn   = document.getElementById('bot-send');
  const send  = () => {
    const text = input?.value.trim();
    if (!text) return;
    input.value = '';
    _onUserSend(text);
  };
  btn?.addEventListener('click', send);
  input?.addEventListener('keydown', e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); } });
}

async function _onUserSend(text) {
  _appendUserMessage(text);
  const typingId = _showTyping();

  // Build context for API: last 10 exchanges
  const historyForApi = _history.slice(-10).map(h => ({ role: h.role, content: h.text }));
  const response = await chatWithBot(historyForApi, text);
  _removeTyping(typingId);
  _appendBotMessage(response);
}

function sendSuggestion(btn) {
  _onUserSend(btn.textContent);
}

function _clearHistory() {
  if (!confirm('مسح كامل تاريخ المحادثة؟')) return;
  _history = [];
  localStorage.removeItem(STORAGE_KEY);
  document.getElementById('bot-messages').innerHTML = '';
  document.getElementById('suggestions').style.display = 'flex';
  _appendBotMessage('تم مسح المحادثة. كيف يمكنني مساعدتك؟');
}
