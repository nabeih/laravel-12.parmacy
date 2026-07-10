// ── Laravel API Base URL ───────────────────────────────────
const LARAVEL_API = 'http://localhost:8000/api';
// TODO: Change to your production Laravel URL before deployment

// ── Prescription Scanner ───────────────────────────────────
async function scanPrescription(base64Image, mimeType) {
  // Strategy 1: Vision API via Laravel
  try {
    const res = await fetch(`${LARAVEL_API}/ai/scan-prescription`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ image: base64Image, mimeType })
    });
    if (res.ok) {
      const data = await res.json();
      if (data.medicines?.length) return data.medicines;
    }
  } catch (_) {}

  // Strategy 2: OCR → Extract fallback
  try {
    const res2 = await fetch(`${LARAVEL_API}/ai/ocr-then-extract`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ image: base64Image })
    });
    if (res2.ok) {
      const data2 = await res2.json();
      if (data2.medicines?.length) return data2.medicines;
    }
  } catch (_) {}

  // Demo mode: simulate 2s processing then return mock results
  await new Promise(r => setTimeout(r, 2000));
  return [
    { nameAr: 'أموكسيسيلين', nameEn: 'Amoxicillin',  availableIn: 4 },
    { nameAr: 'باراسيتامول', nameEn: 'Paracetamol',   availableIn: 12 },
    { nameAr: 'أوميبرازول',  nameEn: 'Omeprazole',    availableIn: 2 }
  ];
}

// ── Pharmacy Chatbot ───────────────────────────────────────
// System prompt (used by Laravel to prefix OpenAI calls):
// "أنت فارم، مساعد صيدلاني ذكي. ساعد المستخدمين في الاستفسار عن
//  الأدوية والجرعات والتفاعلات الدوائية. تحدث بالعربية. لا تشخّص
//  أمراضاً. انصح دائماً بمراجعة الصيدلاني للحالات الجدية."

async function chatWithBot(messagesHistory, newMessage) {
  // Try real API first
  try {
    const res = await fetch(`${LARAVEL_API}/ai/chat`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ messages: messagesHistory.slice(-10), message: newMessage })
    });
    if (res.ok) {
      const data = await res.json();
      if (data.reply) return data.reply;
    }
  } catch (_) {}

  // Demo mode: smart keyword matching
  await new Promise(r => setTimeout(r, 900 + Math.random() * 800));

  const msg = newMessage;
  if (msg.includes('باراسيتامول') || msg.includes('paracetamol'))
    return 'الباراسيتامول (أسيتامينوفين) مسكن للألم وخافض للحرارة. الجرعة المعتادة للبالغين 500–1000mg كل 4–6 ساعات، ولا تتجاوز 4 غرام يومياً. يؤخذ مع الطعام أو بدونه. 💊';
  if (msg.includes('أموكسيسيلين') || msg.includes('amoxicillin'))
    return 'الأموكسيسيلين مضاد حيوي من مجموعة البنسلين. يُستخدم لعلاج التهابات الجهاز التنفسي والأذن والجلد. الجرعة المعتادة 500mg كل 8 ساعات. يُكمل الدورة الكاملة حتى لو تحسنت الأعراض. ⚠️';
  if (msg.includes('تفاعل') || msg.includes('interaction'))
    return 'التفاعلات الدوائية مهمة جداً للسلامة. يُنصح دائماً بإخبار صيدلانيك بجميع الأدوية التي تتناولها (وصفة طبية، بدون وصفة، مكملات غذائية) قبل إضافة دواء جديد. هل تريد معرفة تفاعل بين أدوية محددة؟';
  if (msg.includes('جرعة') || msg.includes('dose') || msg.includes('كمية'))
    return 'الجرعة الصحيحة تعتمد على عوامل عدة: العمر، الوزن، الحالة الصحية، وشدة المرض. يُرجى دائماً اتباع تعليمات الطبيب أو الصيدلاني. هل يمكنك ذكر اسم الدواء المحدد لأقدم معلومات أدق؟';
  if (msg.includes('أعراض') || msg.includes('ألم') || msg.includes('مرض'))
    return '⚠️ لا يمكنني تشخيص الأمراض أو الأعراض الطبية. إذا كانت لديك أعراض مزعجة أو مستمرة، يُرجى مراجعة الطبيب فوراً. يمكنني مساعدتك في الاستفسار عن أدوية بعينها أو جرعاتها.';
  if (msg.includes('فيتامين') || msg.includes('vitamin'))
    return 'الفيتامينات مكملات غذائية مهمة. فيتامين D يُساعد في امتصاص الكالسيوم وصحة العظام. فيتامين C مضاد أكسدة ويدعم المناعة. يُفضل أخذها بعد الأكل. لا تُبالغ في الجرعات دون استشارة طبيب. 🌟';
  if (msg.includes('أوميبرازول') || msg.includes('omeprazole') || msg.includes('حموضة'))
    return 'الأوميبرازول (أومبراء) مثبط مضخة البروتون يُقلل إنتاج حمض المعدة. يُؤخذ قبل الوجبة بـ 30 دقيقة على معدة فارغة. مناسب للحموضة والقرحة الهضمية. لا يُوقف فجأة عند الاستخدام طويل الأمد. 💊';
  return 'شكراً على سؤالك! للحصول على معلومات دقيقة حول هذا الموضوع، أنصحك بمراجعة الصيدلاني المختص. يمكنني مساعدتك في الاستفسار عن الأدوية وجرعاتها والتفاعلات الدوائية. هل لديك سؤال محدد عن دواء معين؟ 😊';
}
