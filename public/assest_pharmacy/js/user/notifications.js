// ── Notifications Page Logic ──────────────────────────────────

function initNotificationsPage() {
  let currentFilter = 'all';

  // Permission banner
  if ('Notification' in window && Notification.permission === 'default') {
    document.getElementById('permission-banner').style.display = 'flex';
  }
  document.getElementById('btn-enable-notif')?.addEventListener('click', async () => {
    const granted = await requestNotificationPermission();
    document.getElementById('permission-banner').style.display = 'none';
    if (granted) showToastNotification('تم تفعيل الإشعارات ✓', 'ستصلك الإشعارات حتى عند إغلاق الصفحة', 'order');
    else         showToastNotification('لم يتم السماح بالإشعارات', 'يمكنك تفعيلها لاحقاً من إعدادات المتصفح', 'chat');
  });
  document.getElementById('btn-dismiss-banner')?.addEventListener('click', () => {
    document.getElementById('permission-banner').style.display = 'none';
  });

  // Mark all read
  document.getElementById('btn-mark-all')?.addEventListener('click', () => {
    markAllAsRead();
    _render();
  });

  // Delete all
  document.getElementById('btn-delete-all')?.addEventListener('click', () => {
    if (!confirm('هل تريد حذف جميع الإشعارات؟')) return;
    localStorage.removeItem('pharma_notifications');
    updateNotifBadge();
    _render();
  });

  // Filter tabs
  document.querySelectorAll('.notif-filter-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.notif-filter-tab').forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      currentFilter = tab.dataset.filter;
      _render();
    });
  });

  // Keep page in sync when other tabs add notifications
  window.addEventListener('storage', e => {
    if (e.key === 'pharma_notifications') _render();
  });

  _render();

  // ── Inner render ────────────────────────────────────────────
  function _render() {
    const list = document.getElementById('notif-list');
    if (!list) return;

    let notifs = getNotifications();
    if (currentFilter !== 'all') notifs = notifs.filter(n => n.type === currentFilter);

    // Unread count label
    const total   = getUnreadCount();
    const countEl = document.getElementById('unread-count');
    if (countEl) {
      countEl.textContent = total > 0
        ? `${total} إشعار${total === 1 ? '' : ' غير مقروءة'}`
        : 'جميع الإشعارات مقروءة';
    }

    if (!notifs.length) {
      list.innerHTML = `
        <div class="notif-empty">
          <div class="notif-empty-icon">🔔</div>
          <div style="font-size:16px;font-weight:600;color:var(--text);margin-bottom:8px">لا توجد إشعارات</div>
          <div style="font-size:14px">ستظهر هنا إشعارات طلباتك وجرعاتك ورسائلك</div>
        </div>`;
      return;
    }

    list.innerHTML = notifs.map(n => {
      const safeLink = n.link ? String(n.link) : '';
      return `
        <div class="notif-item ${n.read ? 'read' : 'unread'}"
             onclick="handleNotifClick('${n.id}','${safeLink}')"
             id="notif-${n.id}">
          <div class="notif-icon ${n.type}">${getNotifIcon(n.type)}</div>
          <div class="notif-content">
            <div class="notif-title">${_safe(n.title)}</div>
            <div class="notif-body">${_safe(n.body)}</div>
            <div class="notif-time">${_formatTime(n.time)}</div>
          </div>
          <div style="display:flex;flex-direction:column;align-items:center;gap:8px;flex-shrink:0">
            ${!n.read ? '<div class="notif-unread-dot"></div>' : '<div style="width:8px"></div>'}
            <button class="notif-delete"
                    onclick="event.stopPropagation();deleteAndRemove('${n.id}')"
                    title="حذف">🗑️</button>
          </div>
        </div>`;
    }).join('');
  }
}

// ── Global handlers (called from onclick in dynamic HTML) ─────

function handleNotifClick(id, link) {
  markAsRead(id);
  const el = document.getElementById('notif-' + id);
  if (el) {
    el.classList.replace('unread', 'read');
    el.querySelector('.notif-unread-dot')?.remove();
    el.querySelector('.notif-title') && (el.querySelector('.notif-title').style.fontWeight = '400');
  }
  updateNotifBadge();
  if (link && link !== 'null' && link.trim() !== '') {
    setTimeout(() => window.location.href = link, 150);
  }
}

function deleteAndRemove(id) {
  deleteNotification(id);
  const el = document.getElementById('notif-' + id);
  if (el) {
    el.style.opacity = '0';
    const h = el.offsetHeight;
    el.style.height  = h + 'px';
    requestAnimationFrame(() => {
      el.style.height  = '0';
      el.style.padding = '0';
      el.style.borderBottomWidth = '0';
      setTimeout(() => el.remove(), 250);
    });
  }
  updateNotifBadge();
  // Show empty state if last item
  const list = document.getElementById('notif-list');
  if (list && !list.querySelector('.notif-item:not([style*="height: 0"])')) {
    setTimeout(() => {
      if (!document.querySelector('.notif-item')) {
        list.innerHTML = `
          <div class="notif-empty">
            <div class="notif-empty-icon">🔔</div>
            <div style="font-size:16px;font-weight:600;color:var(--text);margin-bottom:8px">لا توجد إشعارات</div>
            <div style="font-size:14px">ستظهر هنا إشعارات طلباتك وجرعاتك ورسائلك</div>
          </div>`;
      }
    }, 300);
  }
}

// ── Helpers ───────────────────────────────────────────────────

function _safe(str) {
  return String(str || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function _formatTime(ts) {
  const diff    = Date.now() - ts;
  const minutes = Math.floor(diff / 60000);
  const hours   = Math.floor(diff / 3600000);
  const days    = Math.floor(diff / 86400000);

  if (minutes < 1)  return 'الآن';
  if (minutes < 60) return `منذ ${minutes} دقيقة`;
  if (hours   < 24) return `منذ ${hours} ساعة`;
  if (days    === 1) return 'أمس';
  if (days    < 7)   return `منذ ${days} أيام`;
  return new Date(ts).toLocaleDateString('ar-EG');
}
