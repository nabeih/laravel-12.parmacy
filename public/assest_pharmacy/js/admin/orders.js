// ── Admin Orders ──────────────────────────────────────────────

const ORD_PAGE_SIZE = 10;
let _ordPage       = 1;
let _ordSearch     = '';
let _ordStatus     = '';
let _ordPharmacy   = '';
let _ordFlagged    = false;
let _ordStatusChart = null;

function initAdminOrders() {
  _populatePharmFilter();
  _renderOrdersTable();
  _renderOrdStatusChart();
}

function _renderOrdStatusChart() {
  const ctx = document.getElementById('ord-status-chart');
  if (!ctx) return;
  const statusAr = { pending:'قيد الانتظار', confirmed:'مؤكد', shipping:'جارٍ التوصيل', delivered:'تم التسليم', cancelled:'ملغى' };
  const colorMap  = { pending:'#F59E0B', confirmed:'#3B82F6', shipping:'#8B5CF6', delivered:'#10B981', cancelled:'#EF4444' };
  const counts = {};
  mockAdminOrders.forEach(o => { if (o.status) counts[o.status] = (counts[o.status] || 0) + 1; });
  const active = Object.entries(counts).filter(([, v]) => v > 0);

  document.getElementById('ord-status-legend').innerHTML = active
    .map(([k, v]) => `<div><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:${colorMap[k] || '#94a3b8'};margin-left:6px"></span>${statusAr[k] || k}: <strong>${v}</strong></div>`)
    .join('');

  if (_ordStatusChart) _ordStatusChart.destroy();
  _ordStatusChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels:   active.map(([k]) => statusAr[k] || k),
      datasets: [{ data: active.map(([, v]) => v), backgroundColor: active.map(([k]) => colorMap[k] || '#94a3b8'), borderColor: 'white', borderWidth: 2, hoverOffset: 3 }]
    },
    options: { responsive: true, maintainAspectRatio: true, cutout: '75%', plugins: { legend: { display: false } } }
  });
}

function _populatePharmFilter() {
  const sel   = document.getElementById('ord-pharmacy');
  const names = [...new Set(mockAdminOrders.map(o => o.pharmacyName))];
  sel.innerHTML = `<option value="">جميع الصيدليات</option>` +
    names.map(n => `<option value="${n}">${esc(n)}</option>`).join('');
}

function _onOrdSearch() {
  _ordSearch = document.getElementById('ord-search').value.trim();
  _ordPage   = 1;
  _renderOrdersTable();
}

function _onOrdFilter() {
  _ordStatus   = document.getElementById('ord-status').value;
  _ordPharmacy = document.getElementById('ord-pharmacy').value;
  _ordFlagged  = document.getElementById('ord-flagged-only').checked;
  _ordPage     = 1;
  _renderOrdersTable();
}

function _getFilteredOrders() {
  const s = _ordSearch.toLowerCase();
  return [...mockAdminOrders]
    .sort((a, b) => b.createdAt - a.createdAt)
    .filter(o => {
      const matchSearch   = !s || o.id.toLowerCase().includes(s) || o.userName.includes(s) || o.pharmacyName.includes(s);
      const matchStatus   = !_ordStatus   || o.status === _ordStatus;
      const matchPharmacy = !_ordPharmacy || o.pharmacyName === _ordPharmacy;
      const matchFlagged  = !_ordFlagged  || o.flagged;
      return matchSearch && matchStatus && matchPharmacy && matchFlagged;
    });
}

function _renderOrdersTable() {
  const filtered = _getFilteredOrders();
  const start    = (_ordPage - 1) * ORD_PAGE_SIZE;
  const page     = filtered.slice(start, start + ORD_PAGE_SIZE);
  const tbody    = document.getElementById('ord-tbody');

  if (!page.length) {
    tbody.innerHTML = `<tr><td colspan="8" class="admin-empty" style="padding:30px">لا توجد طلبات</td></tr>`;
    document.getElementById('ord-pagination').innerHTML = '';
    return;
  }

  tbody.innerHTML = page.map(o => `
    <tr style="${o.flagged ? 'background:#fff5f5;border-right:3px solid #ef4444' : ''}">
      <td>
        <span style="font-weight:600">${esc(o.id)}</span>
        ${o.flagged ? ' <span title="مُبلَّغ عنه" style="color:#ef4444">🚩</span>' : ''}
      </td>
      <td>
        <div>${esc(o.userName)}</div>
        <div style="font-size:12px;color:#64748b" dir="ltr">${esc(o.userPhone)}</div>
      </td>
      <td>${esc(o.pharmacyName)}</td>
      <td>₪${fmtMoney(o.total)}</td>
      <td>${paymentLabel(o.paymentMethod)}</td>
      <td>${orderStatusBadge(o.status)}</td>
      <td>${timeAgoAdmin(o.createdAt)}</td>
      <td>
        <div style="display:flex;gap:4px">
          <button class="admin-btn admin-btn-outline admin-btn-sm" onclick="_viewOrder('${o.id}')">عرض</button>
          <button class="admin-btn admin-btn-sm" style="background:${o.flagged?'#fef2f2':'#fff7ed'};color:${o.flagged?'#991b1b':'#c2410c'};border:1px solid ${o.flagged?'#fca5a5':'#fed7aa'}"
                  onclick="_toggleFlag('${o.id}')">${o.flagged ? '✓ إلغاء التبليغ' : '🚩 تبليغ'}</button>
        </div>
      </td>
    </tr>`).join('');

  // Pagination
  const total      = filtered.length;
  const totalPages = Math.ceil(total / ORD_PAGE_SIZE);
  const pg         = document.getElementById('ord-pagination');
  if (totalPages <= 1) { pg.innerHTML = ''; return; }

  let html = `<span style="font-size:13px;color:#64748b;margin-left:8px">إجمالي: ${total}</span>`;
  html += `<button class="admin-page-btn" ${_ordPage<=1?'disabled':''} onclick="_ordGoPage(${_ordPage-1})">›</button>`;
  for (let i = 1; i <= totalPages; i++) {
    html += `<button class="admin-page-btn${i===_ordPage?' active':''}" onclick="_ordGoPage(${i})">${i}</button>`;
  }
  html += `<button class="admin-page-btn" ${_ordPage>=totalPages?'disabled':''} onclick="_ordGoPage(${_ordPage+1})">‹</button>`;
  pg.innerHTML = html;
}

function _ordGoPage(p) { _ordPage = p; _renderOrdersTable(); }

function _viewOrder(id) {
  const o = mockAdminOrders.find(x => x.id === id);
  if (!o) return;
  document.getElementById('ord-view-title').textContent = `تفاصيل الطلب ${o.id}`;
  document.getElementById('ord-view-body').innerHTML = `
    <div class="admin-info-row"><span class="admin-info-label">المستخدم</span><span class="admin-info-value">${esc(o.userName)}</span></div>
    <div class="admin-info-row"><span class="admin-info-label">الهاتف</span><span class="admin-info-value" dir="ltr">${esc(o.userPhone)}</span></div>
    <div class="admin-info-row"><span class="admin-info-label">الصيدلية</span><span class="admin-info-value">${esc(o.pharmacyName)}</span></div>
    <div class="admin-info-row"><span class="admin-info-label">عنوان التوصيل</span><span class="admin-info-value">${esc(o.address)}</span></div>
    <div class="admin-info-row"><span class="admin-info-label">طريقة الدفع</span><span class="admin-info-value">${paymentLabel(o.paymentMethod)}</span></div>
    <div class="admin-info-row"><span class="admin-info-label">الحالة</span><span class="admin-info-value">${orderStatusBadge(o.status)}</span></div>
    <div class="admin-info-row"><span class="admin-info-label">التاريخ</span><span class="admin-info-value">${fmtDate(o.createdAt)}</span></div>
    <div style="margin-top:16px">
      <div style="font-weight:600;margin-bottom:10px;color:#1e293b">الأصناف المطلوبة:</div>
      <table class="admin-table" style="margin:0">
        <thead><tr><th>الدواء</th><th>الكمية</th><th>السعر</th><th>المجموع</th></tr></thead>
        <tbody>
          ${o.items.map(it => `
            <tr>
              <td>${esc(it.name)}</td>
              <td>${it.qty}</td>
              <td>₪${fmtMoney(it.price)}</td>
              <td>₪${fmtMoney(it.qty * it.price)}</td>
            </tr>`).join('')}
          <tr style="background:#f8fafc;font-weight:700">
            <td colspan="3">الإجمالي</td>
            <td>₪${fmtMoney(o.total)}</td>
          </tr>
        </tbody>
      </table>
    </div>`;
  _openModal('ord-view-modal');
}

function _toggleFlag(id) {
  const o = mockAdminOrders.find(x => x.id === id);
  if (o) o.flagged = !o.flagged;
  _renderOrdersTable();
}

function _exportOrders() {
  const rows = _getFilteredOrders().map(o => [
    o.id, o.userName, o.userPhone, o.pharmacyName,
    o.total, paymentLabel(o.paymentMethod),
    { pending:'قيد الانتظار', confirmed:'مؤكد', shipping:'جارٍ التوصيل', delivered:'تم التسليم', cancelled:'ملغى' }[o.status] || o.status,
    fmtDate(o.createdAt), o.address, o.flagged ? 'نعم' : 'لا'
  ]);
  exportCsv('orders.csv',
    ['رقم الطلب','المستخدم','الهاتف','الصيدلية','الإجمالي','طريقة الدفع','الحالة','التاريخ','العنوان','مُبلَّغ'],
    rows);
}
