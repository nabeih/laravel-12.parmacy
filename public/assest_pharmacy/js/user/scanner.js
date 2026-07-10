// Prescription Scanner — uses openai.js → Laravel API → demo fallback

let _selectedFile = null;

function initScanner() {
  const zone  = document.getElementById('upload-zone');
  const input = document.getElementById('file-input');
  const btn   = document.getElementById('btn-scan');
  const clear = document.getElementById('btn-clear');

  zone.addEventListener('click', () => input.click());
  input.addEventListener('change', () => { if (input.files[0]) _setFile(input.files[0]); });

  zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
  zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
  zone.addEventListener('drop', e => {
    e.preventDefault(); zone.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file) _setFile(file);
  });

  btn.addEventListener('click', _doScan);
  clear.addEventListener('click', _clearAll);
}

function _setFile(file) {
  if (file.size > 10 * 1024 * 1024) { alert('حجم الملف كبير جداً (الحد الأقصى 10MB)'); return; }
  _selectedFile = file;

  if (file.type.startsWith('image/')) {
    const reader = new FileReader();
    reader.onload = e => {
      const img = document.getElementById('preview-img');
      img.src = e.target.result;
      img.style.display = 'block';
      document.getElementById('upload-zone-inner').style.display = 'none';
    };
    reader.readAsDataURL(file);
  } else {
    document.getElementById('upload-zone-inner').innerHTML = `<div style="padding:24px;text-align:center">📄<br><strong>${esc(file.name)}</strong></div>`;
  }

  document.getElementById('btn-scan').disabled = false;
  document.getElementById('btn-clear').style.display = 'inline-flex';
}

async function _doScan() {
  const btn = document.getElementById('btn-scan');
  const statusEl = document.getElementById('scan-status');
  const resultsEl = document.getElementById('medicine-results');

  btn.disabled = true;
  btn.textContent = 'جاري المعالجة...';
  statusEl.textContent = 'يتم استخراج الأدوية من الصورة...';
  resultsEl.innerHTML = `<div style="display:flex;justify-content:center;padding:24px"><div class="spinner"></div></div>`;

  let base64 = null;
  if (_selectedFile && _selectedFile.type.startsWith('image/')) {
    base64 = await _toBase64(_selectedFile);
    base64 = base64.split(',')[1];
  }

  const result = await scanPrescription(base64, _selectedFile?.type || 'image/jpeg');
  _renderResults(result);

  btn.disabled = false;
  btn.textContent = '🔍 استخراج الأدوية';
}

function _renderResults(result) {
  const statusEl  = document.getElementById('scan-status');
  const resultsEl = document.getElementById('medicine-results');
  const countEl   = document.getElementById('med-count');
  const medicines = result.medicines || [];

  countEl.textContent = medicines.length;

  if (result.source) {
    statusEl.innerHTML = result.source === 'demo'
      ? `<span style="color:var(--warning)">⚠️ نتيجة تجريبية — اتصل بـ Laravel API للمعالجة الفعلية</span>`
      : `<span style="color:var(--success)">✅ تم الاستخراج بواسطة ${result.source}</span>`;
  }

  if (!medicines.length) {
    resultsEl.innerHTML = `<div class="empty-state"><span class="empty-icon">📋</span><p>لم يتم التعرف على أي دواء</p></div>`;
    return;
  }

  resultsEl.innerHTML = medicines.map(m => `
    <div class="medicine-result-card">
      <div class="mrc-icon">💊</div>
      <div class="mrc-info">
        <div class="mrc-name">${esc(m.name)}</div>
        ${m.dosage    ? `<div class="mrc-detail">الجرعة: ${esc(m.dosage)}</div>`    : ''}
        ${m.frequency ? `<div class="mrc-detail">التكرار: ${esc(m.frequency)}</div>` : ''}
        ${m.duration  ? `<div class="mrc-detail">المدة: ${esc(m.duration)}</div>`   : ''}
        ${m.notes     ? `<div class="mrc-detail">📝 ${esc(m.notes)}</div>`           : ''}
      </div>
      <button class="btn btn-sm btn-outline" onclick="addMedToDoses('${esc(m.name)}','${esc(m.dosage||'')}')">+ إضافة للجرعات</button>
    </div>`).join('');
}

function addMedToDoses(name, dosage) {
  const params = new URLSearchParams({ name, dosage });
  window.location.href = `doses.html?${params.toString()}`;
}

function _clearAll() {
  _selectedFile = null;
  const img = document.getElementById('preview-img');
  img.style.display = 'none'; img.src = '';
  document.getElementById('upload-zone-inner').style.display = 'flex';
  document.getElementById('upload-zone-inner').innerHTML = `
    <div class="upload-icon">🔍</div>
    <p class="upload-title">اسحب صورة الروشتة هنا</p>
    <p class="upload-sub">أو اضغط لرفع ملف</p>
    <p class="upload-types">JPG, PNG, PDF حتى 10 ميجابايت</p>`;
  document.getElementById('btn-scan').disabled = true;
  document.getElementById('btn-clear').style.display = 'none';
  document.getElementById('medicine-results').innerHTML = `<div class="scan-placeholder"><div style="font-size:40px;margin-bottom:8px">📋</div><p>قم برفع صورة روشتة للبدء</p></div>`;
  document.getElementById('scan-status').textContent = '';
  document.getElementById('med-count').textContent = '0';
  document.getElementById('file-input').value = '';
}

function _toBase64(file) {
  return new Promise((res, rej) => {
    const r = new FileReader();
    r.onload = e => res(e.target.result);
    r.onerror = rej;
    r.readAsDataURL(file);
  });
}
