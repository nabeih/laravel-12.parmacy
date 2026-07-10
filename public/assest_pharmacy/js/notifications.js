// ── PharmacyLink Notification Engine ─────────────────────────
// Layer 1: localStorage (always works, all pages)
// Layer 2: Browser Push / FCM (requires permission)

const NOTIF_KEY = 'pharma_notifications';

// ── Layer 1: In-App (localStorage) ───────────────────────────

function getNotifications() {
  try { return JSON.parse(localStorage.getItem(NOTIF_KEY)) || []; }
  catch { return []; }
}

function saveNotifications(notifs) {
  localStorage.setItem(NOTIF_KEY, JSON.stringify(notifs));
  updateNotifBadge();
}

function addNotification({ type, title, body, link = null }) {
  const notifs   = getNotifications();
  const newNotif = {
    id:    'notif_' + Date.now() + '_' + Math.floor(Math.random() * 1000),
    type,
    title,
    body,
    time:  Date.now(),
    read:  false,
    link
  };
  notifs.unshift(newNotif);
  if (notifs.length > 50) notifs.pop();
  saveNotifications(notifs);

  showBrowserNotification(title, body, link);
  showToastNotification(title, body, type);  // type threaded through to fix spec bug
}

function markAsRead(id) {
  const notifs = getNotifications();
  const n      = notifs.find(n => n.id === id);
  if (n) n.read = true;
  saveNotifications(notifs);
}

function markAllAsRead() {
  const notifs = getNotifications().map(n => ({ ...n, read: true }));
  saveNotifications(notifs);
}

function deleteNotification(id) {
  const notifs = getNotifications().filter(n => n.id !== id);
  saveNotifications(notifs);
}

function getUnreadCount() {
  return getNotifications().filter(n => !n.read).length;
}

function updateNotifBadge() {
  const count = getUnreadCount();
  const badge = document.getElementById('notif-badge');
  if (badge) {
    badge.textContent    = count > 9 ? '9+' : count;
    badge.style.display  = count > 0 ? 'flex' : 'none';
  }
}

// In-page toast — bottom-left, auto-dismiss after 4s
function showToastNotification(title, body, type) {
  // Don't stack more than 2 toasts
  if (document.querySelectorAll('.notif-toast').length >= 2) return;

  const toast    = document.createElement('div');
  toast.className = 'notif-toast';
  toast.innerHTML = `
    <div class="notif-toast-icon">${getNotifIcon(type)}</div>
    <div style="flex:1;min-width:0">
      <div class="notif-toast-title">${_safeText(title)}</div>
      <div class="notif-toast-body">${_safeText(body)}</div>
    </div>
    <button onclick="this.closest('.notif-toast').remove()" class="notif-toast-close">✕</button>`;
  document.body.appendChild(toast);
  setTimeout(() => toast.classList.add('show'), 10);
  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 300);
  }, 4000);
}

function getNotifIcon(type) {
  return { order:'📦', dose:'💊', favorite:'🆕', chat:'💬' }[type] || '🔔';
}

function _safeText(str) {
  return String(str || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

// ── Layer 2: Browser Push Notifications ──────────────────────

const VAPID_KEY = 'YOUR_VAPID_PUBLIC_KEY';

async function requestNotificationPermission() {
  if (!('Notification' in window)) return false;
  if (Notification.permission === 'granted') return true;
  if (Notification.permission === 'denied')  return false;
  const result = await Notification.requestPermission();
  return result === 'granted';
}

async function showBrowserNotification(title, body, link) {
  if (!('Notification' in window) || Notification.permission !== 'granted') return;
  try {
    const n = new Notification(title, {
      body,
      icon:  '/assets/icon-192.png',
      badge: '/assets/badge-72.png',
      tag:   'pharmalink-' + Date.now()
    });
    if (link) {
      n.onclick = () => { window.focus(); window.location.href = link; n.close(); };
    }
    setTimeout(() => n.close(), 5000);
  } catch { /* browser blocked */ }
}

async function initFCM() {
  if (DEMO_MODE) return;
  if (typeof firebase === 'undefined') return;
  try {
    const messaging = firebase.messaging();
    const granted   = await requestNotificationPermission();
    if (!granted) return;

    const token = await messaging.getToken({ vapidKey: VAPID_KEY });
    if (token) {
      localStorage.setItem('pharma_fcm_token', token);
      // TODO: POST /api/user/fcm-token { token }
    }

    messaging.onMessage(payload => {
      const d = payload.data || payload.notification || {};
      addNotification({ type: d.type || 'general', title: d.title, body: d.body, link: d.link });
    });
  } catch {
    // FCM not available — Layer 1 only
  }
}

// ── Dose Reminder Engine ──────────────────────────────────────

function initDoseReminders() {
  checkDoseReminders();
  setInterval(checkDoseReminders, 60000);
}

function checkDoseReminders() {
  let doses = [];
  try { doses = JSON.parse(localStorage.getItem('pharma_doses') || '[]'); } catch { return; }

  const now         = new Date();
  const currentMins = now.getHours() * 60 + now.getMinutes();
  const todayKey    = 'pharma_dose_notified_' + now.toDateString();
  let   notified    = [];
  try { notified = JSON.parse(localStorage.getItem(todayKey) || '[]'); } catch {}

  doses.forEach(dose => {
    if (!dose.active) return;
    (dose.times || []).forEach(timeStr => {
      const [h, m]     = timeStr.split(':').map(Number);
      const doseMins   = h * 60 + m;
      const notifId    = (dose.id || dose.medicine || '') + '_' + timeStr;

      // 15-minute advance reminder
      if (doseMins - currentMins === 15 && !notified.includes(notifId + '_pre')) {
        addNotification({
          type:  'dose',
          title: '⏰ تذكير جرعة',
          body:  `موعد جرعة ${dose.medicine || dose.nameAr} (${dose.dose || dose.dosage}) بعد 15 دقيقة — ${timeStr}`,
          link:  'user/doses.html'
        });
        notified.push(notifId + '_pre');
        localStorage.setItem(todayKey, JSON.stringify(notified));
      }

      // Exact dose time
      if (doseMins === currentMins && !notified.includes(notifId)) {
        addNotification({
          type:  'dose',
          title: '💊 وقت الجرعة الآن!',
          body:  `حان وقت جرعة ${dose.medicine || dose.nameAr} — ${dose.dose || dose.dosage}`,
          link:  'user/doses.html'
        });
        notified.push(notifId);
        localStorage.setItem(todayKey, JSON.stringify(notified));
      }
    });
  });
}

// ── Order Status Watcher ──────────────────────────────────────

function watchOrderStatus() {
  if (DEMO_MODE) return;
  if (typeof database === 'undefined') return;
  const uid = window.currentUser?.uid;
  if (!uid) return;

  // TODO: uncomment when Laravel writes to Firebase RTDB
  // database.ref('userNotifications/' + uid + '/orders').on('child_added', snap => {
  //   const data = snap.val();
  //   addNotification({ type:'order', title: getOrderStatusTitle(data.status), body: data.message, link:'user/orders.html' });
  //   snap.ref.remove();
  // });
}

function getOrderStatusTitle(status) {
  return {
    confirmed: '✅ تم تأكيد طلبك',
    shipping:  '🚚 طلبك في الطريق',
    delivered: '🎉 تم تسليم طلبك',
    cancelled: '❌ تم إلغاء طلبك'
  }[status] || '📦 تحديث على طلبك';
}

// ── Chat Message Watcher ──────────────────────────────────────

function watchChatMessages() {
  if (DEMO_MODE) return;
  if (typeof database === 'undefined') return;
  const uid = window.currentUser?.uid;
  if (!uid) return;

  database.ref('chats').orderByChild('userId').equalTo(uid).on('child_changed', snap => {
    const chat = snap.val();
    if (chat.unreadByUser > 0) {
      addNotification({
        type:  'chat',
        title: '💬 رسالة جديدة',
        body:  `${chat.pharmacyName}: ${chat.lastMessage}`,
        link:  'user/chats.html?chat=' + snap.key
      });
    }
  });
}

// ── Favorite Medicine Watcher ─────────────────────────────────

function watchFavoriteMedicines() {
  checkFavoriteArrivals();
  setInterval(checkFavoriteArrivals, 30 * 60 * 1000);
}

function checkFavoriteArrivals() {
  let favorites = [];
  try { favorites = JSON.parse(localStorage.getItem('pharma_fav_medicines') || '[]'); } catch {}
  if (!favorites.length) return;

  // Fall back to in-memory mock if localStorage key not populated
  let newMeds = [];
  try {
    const stored = localStorage.getItem('pharma_new_medicines');
    newMeds = stored ? JSON.parse(stored) : (typeof mockNewMedicines !== 'undefined' ? mockNewMedicines : []);
  } catch {
    newMeds = typeof mockNewMedicines !== 'undefined' ? mockNewMedicines : [];
  }

  const lastCheck = parseInt(localStorage.getItem('pharma_fav_last_check') || '0');
  const now       = Date.now();

  newMeds.forEach(med => {
    const isFav = favorites.some(f =>
      (f.nameEn && med.nameEn && f.nameEn.toLowerCase() === med.nameEn.toLowerCase()) ||
      (f.nameAr && med.nameAr && f.nameAr === med.nameAr)
    );
    if (isFav && (med.addedAt || 0) > lastCheck) {
      addNotification({
        type:  'favorite',
        title: '🆕 دواء من مفضلتك وصل!',
        body:  `${med.nameAr} متوفر الآن في ${med.pharmacyName} بسعر ₪${med.price}`,
        link:  'user/new-medicines.html'
      });
    }
  });

  localStorage.setItem('pharma_fav_last_check', now.toString());
}

// ── Demo Seeds ────────────────────────────────────────────────

function seedDemoNotifications() {
  if (getNotifications().length > 0) return;
  const demos = [
    { id:'dn1', type:'order',    title:'✅ تم تأكيد طلبك',         body:'صيدلية الشفاء قبلت طلبك رقم ORD-103',              link:'orders.html',       time: Date.now()-3600000,    read:false },
    { id:'dn2', type:'dose',     title:'💊 وقت الجرعة!',           body:'حان وقت جرعة أموكسيسيلين — 500mg',                  link:'doses.html',        time: Date.now()-7200000,    read:false },
    { id:'dn3', type:'favorite', title:'🆕 دواء من مفضلتك وصل!',   body:'باراسيتامول متوفر الآن في صيدلية النور بسعر ₪7.50', link:'new-medicines.html', time: Date.now()-86400000,   read:true  },
    { id:'dn4', type:'chat',     title:'💬 رسالة جديدة',           body:'صيدلية الشفاء: نعم الدواء متوفر عندنا',             link:'chats.html',        time: Date.now()-172800000,  read:true  },
    { id:'dn5', type:'order',    title:'🚚 طلبك في الطريق',        body:'طلبك رقم ORD-101 خرج للتوصيل',                      link:'orders.html',       time: Date.now()-259200000,  read:true  }
  ];
  localStorage.setItem(NOTIF_KEY, JSON.stringify(demos));
  updateNotifBadge();
}

// ── Master Init ───────────────────────────────────────────────

let _notifInited = false;

function initNotifications() {
  if (_notifInited) { updateNotifBadge(); return; }
  _notifInited = true;

  updateNotifBadge();
  initDoseReminders();
  watchOrderStatus();
  watchChatMessages();
  watchFavoriteMedicines();
  initFCM();

  // Sync badge across tabs via storage event
  window.addEventListener('storage', e => {
    if (e.key === NOTIF_KEY) updateNotifBadge();
  });
}

/*
LARAVEL INTEGRATION — HOW TO TRIGGER NOTIFICATIONS:

1. Order status change:
   database.ref('userNotifications/' + userId + '/orders').push({
     status: 'confirmed', message: '...', time: ServerValue.TIMESTAMP
   });

2. Favorites / new medicine:
   database.ref('userNotifications/' + userId + '/favorites').push({ ... });

3. Chat: handled by existing RTDB chat watcher

4. Dose reminders: frontend-only, no Laravel needed

5. FCM push (tab closed):
   $messaging->send(CloudMessage::withTarget('token', $fcmToken)
     ->withNotification(Notification::create($title, $body))
     ->withData(['link' => $link, 'type' => $type]));
*/
