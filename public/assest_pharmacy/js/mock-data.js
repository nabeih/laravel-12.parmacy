// ── Users ─────────────────────────────────────────────────
const mockPharmacistUser = {
  uid: 'demo-pharmacist',
  name: 'م. خالد العمر',
  email: 'pharmacist@demo.com',
  role: 'pharmacist',
  pharmacyName: 'صيدلية الشفاء',
  pharmacyId: 'pharm-001'
};
const mockUser = {
  uid: 'demo-user',
  name: 'أحمد محمود',
  email: 'user@demo.com',
  phone: '0599123456',
  role: 'user'
};

// ── Products ───────────────────────────────────────────────
const mockProducts = [
  { id:'p1', nameAr:'أموكسيسيلين',   nameEn:'Amoxicillin',   category:'مضادات حيوية', price:18.50, stock:45,  minStock:20, expiryDate:'2026-08-01', description:'مضاد حيوي واسع الطيف' },
  { id:'p2', nameAr:'باراسيتامول',   nameEn:'Paracetamol',   category:'مسكنات',        price:8.00,  stock:120, minStock:30, expiryDate:'2027-01-15', description:'مسكن للألم وخافض للحرارة' },
  { id:'p3', nameAr:'أوميبرازول',    nameEn:'Omeprazole',    category:'أمراض مزمنة',  price:22.00, stock:8,   minStock:15, expiryDate:'2025-12-01', description:'مثبط مضخة البروتون للحموضة' },
  { id:'p4', nameAr:'فيتامين C',     nameEn:'Vitamin C',     category:'فيتامينات',    price:15.00, stock:0,   minStock:10, expiryDate:'2026-06-30', description:'مكمل حمض الأسكوربيك' },
  { id:'p5', nameAr:'إيبوبروفين',    nameEn:'Ibuprofen',     category:'مسكنات',        price:12.50, stock:67,  minStock:25, expiryDate:'2026-11-20', description:'مضاد التهاب غير ستيرويدي' },
  { id:'p6', nameAr:'ميترونيدازول',  nameEn:'Metronidazole', category:'مضادات حيوية', price:14.00, stock:23,  minStock:20, expiryDate:'2026-09-10', description:'مضاد حيوي للبكتيريا اللاهوائية' },
  { id:'p7', nameAr:'سيتريزين',      nameEn:'Cetirizine',    category:'أخرى',          price:10.00, stock:55,  minStock:15, expiryDate:'2027-03-20', description:'مضاد هيستامين لعلاج الحساسية' },
  { id:'p8', nameAr:'فيتامين D',     nameEn:'Vitamin D3',    category:'فيتامينات',    price:25.00, stock:12,  minStock:10, expiryDate:'2026-12-01', description:'مكمل فيتامين د لصحة العظام' },
  { id:'p9', nameAr:'أسبرين',        nameEn:'Aspirin',       category:'مسكنات',        price:6.50,  stock:90,  minStock:30, expiryDate:'2027-04-01', description:'مسكن ومضاد تجلط الدم' },
  { id:'p10',nameAr:'ميتفورمين',     nameEn:'Metformin',     category:'أمراض مزمنة',  price:19.00, stock:35,  minStock:20, expiryDate:'2027-02-28', description:'دواء السكري من النوع الثاني' },
  { id:'p11',nameAr:'لوسارتان',      nameEn:'Losartan',      category:'أمراض مزمنة',  price:32.00, stock:18,  minStock:20, expiryDate:'2026-10-15', description:'دواء ضغط الدم المرتفع' },
  { id:'p12',nameAr:'أوميغا 3',      nameEn:'Omega-3',       category:'فيتامينات',    price:38.00, stock:25,  minStock:12, expiryDate:'2027-05-01', description:'مكمل أحماض أوميغا 3 الدهنية' },
];

// ── Pharmacist Orders ──────────────────────────────────────
const mockPharmacistOrders = [
  {
    id:'ORD-001', customerName:'أحمد محمود', customerPhone:'0599123456',
    items:[{nameAr:'باراسيتامول',qty:2,price:8},{nameAr:'أموكسيسيلين',qty:1,price:18.5}],
    total:34.5, payment:'cash', address:'نابلس - شارع الحمرا، بناية 5', status:'pending',
    date: new Date(Date.now()-3600000).toISOString()
  },
  {
    id:'ORD-002', customerName:'فاطمة العلي', customerPhone:'0598765432',
    items:[{nameAr:'فيتامين C',qty:3,price:15}],
    total:45, payment:'bank', address:'رام الله - البيرة - شارع الجامعة', status:'confirmed',
    date: new Date(Date.now()-7200000).toISOString()
  },
  {
    id:'ORD-003', customerName:'محمد سالم', customerPhone:'0597111222',
    items:[{nameAr:'إيبوبروفين',qty:2,price:12.5},{nameAr:'سيتريزين',qty:1,price:10}],
    total:35, payment:'cash', address:'جنين - شارع المركز', status:'delivered',
    date: new Date(Date.now()-86400000).toISOString()
  },
  {
    id:'ORD-004', customerName:'سارة خليل', customerPhone:'0596333444',
    items:[{nameAr:'أوميبرازول',qty:1,price:22}],
    total:22, payment:'bank', address:'الخليل - وسط البلد', status:'cancelled',
    date: new Date(Date.now()-172800000).toISOString()
  },
  {
    id:'ORD-005', customerName:'يوسف ناصر', customerPhone:'0595222333',
    items:[{nameAr:'فيتامين D',qty:2,price:25},{nameAr:'أوميغا 3',qty:1,price:38}],
    total:88, payment:'cash', address:'طولكرم - شارع النصر', status:'processing',
    date: new Date(Date.now()-1800000).toISOString()
  }
];

// ── User Orders ────────────────────────────────────────────
const mockUserOrders = [
  {
    id:'ORD-101', pharmacyName:'صيدلية الشفاء', pharmacyPhone:'09-2345678',
    items:[{nameAr:'باراسيتامول',qty:2,price:8},{nameAr:'أموكسيسيلين',qty:1,price:18.5}],
    total:34.5, payment:'cash', address:'نابلس - شارع الحمرا', status:'delivered',
    date:'2026-05-10'
  },
  {
    id:'ORD-102', pharmacyName:'صيدلية الرحمة', pharmacyPhone:'02-2981234',
    items:[{nameAr:'فيتامين C',qty:3,price:15}],
    total:45, payment:'bank', address:'رام الله - البيرة', status:'processing',
    date:'2026-06-18'
  },
  {
    id:'ORD-103', pharmacyName:'صيدلية النور', pharmacyPhone:'04-2501111',
    items:[{nameAr:'إيبوبروفين',qty:1,price:12.5}],
    total:12.5, payment:'cash', address:'جنين - المركز', status:'pending',
    date:'2026-06-19'
  }
];

// ── Doses ──────────────────────────────────────────────────
const mockDoses = [
  { id:'d1', nameAr:'أموكسيسيلين', nameEn:'Amoxicillin', dosage:'500mg', times:['08:00','16:00','00:00'], until:'2026-09-25', active:true, notes:'مع الطعام' },
  { id:'d2', nameAr:'فيتامين C',   nameEn:'Vitamin C',   dosage:'1000mg', times:['09:00'],               until:'2026-10-15', active:true, notes:'' },
  { id:'d3', nameAr:'أوميبرازول',  nameEn:'Omeprazole',  dosage:'20mg',   times:['07:30'],               until:'2026-11-01', active:true, notes:'قبل الإفطار' }
];

// ── Favorites ──────────────────────────────────────────────
const mockFavoriteMedicines = [
  { id:1, nameAr:'باراسيتامول', nameEn:'Paracetamol', price:8,  category:'مسكنات' },
  { id:2, nameAr:'فيتامين D',   nameEn:'Vitamin D3',  price:25, category:'فيتامينات' },
  { id:3, nameAr:'أوميبرازول',  nameEn:'Omeprazole',  price:22, category:'أمراض مزمنة' },
  { id:4, nameAr:'سيتريزين',    nameEn:'Cetirizine',  price:10, category:'أخرى' }
];
const mockFavoritePharmacies = [
  { id:'fp1', name:'صيدلية الشفاء',  governorate:'نابلس',    distance:'1.2 كم', isOpen:true,  rating:4.8 },
  { id:'fp2', name:'صيدلية الرحمة', governorate:'رام الله', distance:'3.5 كم', isOpen:false, rating:4.5 },
  { id:'fp3', name:'صيدلية النور',   governorate:'جنين',     distance:'0.8 كم', isOpen:true,  rating:4.9 }
];

// ── Addresses ──────────────────────────────────────────────
const mockAddresses = [
  { id:'a1', label:'المنزل', area:'نابلس البلد', governorate:'نابلس',    street:'شارع الحمرا، بناية 12', notes:'بجانب مسجد النور', isDefault:true  },
  { id:'a2', label:'العمل',  area:'البيرة',      governorate:'رام الله', street:'شارع الإرسال، مبنى الأعمال', notes:'مقابل بنك فلسطين', isDefault:false }
];

// ── Chats ──────────────────────────────────────────────────
let mockChats = [
  {
    id:'chat_001', pharmacyName:'صيدلية الشفاء', userName:'أحمد محمود', userPhone:'0599123456',
    pharmacyId:'pharm-001', userId:'demo-user',
    lastMessage:'هل يتوفر أموكسيسيلين 500mg؟', lastMessageTime: Date.now()-300000,
    unreadByPharmacist:2, unreadByUser:0,
    messages:[
      { id:'m1', senderType:'user',       text:'السلام عليكم',                     timestamp: Date.now()-600000 },
      { id:'m2', senderType:'pharmacist', text:'وعليكم السلام، كيف أقدر أساعدك؟', timestamp: Date.now()-540000 },
      { id:'m3', senderType:'user',       text:'هل يتوفر أموكسيسيلين 500mg؟',      timestamp: Date.now()-300000 },
      { id:'m4', senderType:'user',       text:'وكم سعره؟',                         timestamp: Date.now()-290000 }
    ]
  },
  {
    id:'chat_002', pharmacyName:'صيدلية الرحمة', userName:'فاطمة العلي', userPhone:'0598765432',
    pharmacyId:'pharm-002', userId:'demo-user-2',
    lastMessage:'شكراً جزيلاً', lastMessageTime: Date.now()-3600000,
    unreadByPharmacist:0, unreadByUser:1,
    messages:[
      { id:'m1', senderType:'user',       text:'عندكم باراسيتامول للأطفال؟', timestamp: Date.now()-7200000 },
      { id:'m2', senderType:'pharmacist', text:'نعم متوفر، سعره 8 شيكل',      timestamp: Date.now()-7100000 },
      { id:'m3', senderType:'user',       text:'شكراً جزيلاً',                timestamp: Date.now()-3600000 }
    ]
  }
];

// ── Weekly Revenue (dashboard charts) ─────────────────────
const mockWeeklyRevenue = [
  { day:'السبت', amount:1250 },
  { day:'الأحد', amount:980  },
  { day:'الاثنين', amount:1450 },
  { day:'الثلاثاء', amount:890  },
  { day:'الأربعاء', amount:1680 },
  { day:'الخميس', amount:2100 },
  { day:'الجمعة', amount:750  }
];

// ── Categories ─────────────────────────────────────────────
const PHARM_CATEGORIES = ['مضادات حيوية','مسكنات','فيتامينات','أمراض مزمنة','أخرى'];
const GOVERNORATES = ['نابلس','رام الله','جنين','الخليل','طولكرم','قلقيلية','أريحا','بيت لحم','طوباس'];

// ── Shared Helpers ─────────────────────────────────────────
function statusBadge(status) {
  const map = {
    'pending':    ['badge-yellow', 'في الانتظار'],
    'confirmed':  ['badge-blue',   'مؤكد'],
    'processing': ['badge-blue',   'قيد التحضير'],
    'ready':      ['badge-primary','جاهز للاستلام'],
    'delivered':  ['badge-green',  'تم التسليم'],
    'shipping':   ['badge-blue',   'جاري التوصيل'],
    'cancelled':  ['badge-red',    'ملغي'],
  };
  const [cls, label] = map[status] || ['badge-gray', status || '—'];
  return `<span class="badge ${cls}">${label}</span>`;
}

function stockBadge(stock, minStock) {
  if (stock === 0)        return '<span class="badge badge-red">نفذ المخزون</span>';
  if (stock <= minStock)  return '<span class="badge badge-yellow">مخزون منخفض</span>';
  return                         '<span class="badge badge-green">متوفر</span>';
}

function timeAgo(ts) {
  const d = (ts instanceof Date) ? ts : new Date(typeof ts === 'string' ? ts : (ts?.seconds ? ts.seconds * 1000 : ts));
  const diff = Math.floor((Date.now() - d.getTime()) / 1000);
  if (diff < 30)    return 'الآن';
  if (diff < 90)    return 'منذ دقيقة';
  if (diff < 3600)  return `منذ ${Math.floor(diff/60)} دقيقة`;
  if (diff < 7200)  return 'منذ ساعة';
  if (diff < 86400) return `منذ ${Math.floor(diff/3600)} ساعات`;
  return d.toLocaleDateString('ar-EG');
}

function fmt(n)    { return Number(n || 0).toFixed(2); }
function fmtDate(s){ return new Date(s).toLocaleDateString('ar-EG'); }
function esc(s)    { return String(s || '').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function debounce(fn, delay) {
  let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
}

// ── Pharmacies List ────────────────────────────────────────
const mockPharmaciesList = [
  { id:'ph1', name:'صيدلية الشفاء',  city:'نابلس',    address:'شارع الحمرا، مركز المدينة',       phone:'092345678', lat:32.2211, lng:35.2544, rating:4.8, isOpen:true,  workingHours:'8:00 - 22:00', totalMedicines:45 },
  { id:'ph2', name:'صيدلية الرحمة', city:'رام الله', address:'شارع الإرسال، البيرة',            phone:'022345678', lat:31.9022, lng:35.2034, rating:4.5, isOpen:true,  workingHours:'9:00 - 21:00', totalMedicines:38 },
  { id:'ph3', name:'صيدلية النور',   city:'جنين',     address:'شارع الجلزون',                    phone:'042345678', lat:32.4607, lng:35.2960, rating:4.9, isOpen:false, workingHours:'8:00 - 20:00', totalMedicines:29 },
  { id:'ph4', name:'صيدلية الأمل',   city:'الخليل',   address:'وسط البلد',                       phone:'022987654', lat:31.5326, lng:35.0998, rating:4.3, isOpen:true,  workingHours:'8:00 - 23:00', totalMedicines:52 },
  { id:'ph5', name:'صيدلية الحياة', city:'طولكرم',   address:'شارع النصر',                       phone:'092111333', lat:32.3107, lng:35.0286, rating:4.6, isOpen:true,  workingHours:'9:00 - 22:00', totalMedicines:33 }
];

// ── Products per pharmacy ──────────────────────────────────
const mockPharmacyProducts = {
  'ph1': [
    { id:'pp1', catalogId:'cat1', nameAr:'أموكسيسيلين', nameEn:'Amoxicillin', category:'مضادات حيوية',     price:18.50, stock:45,  minStock:20, expiryDate:'2026-08-01', addedAt: Date.now()-86400000*2 },
    { id:'pp2', catalogId:'cat2', nameAr:'باراسيتامول', nameEn:'Paracetamol', category:'مسكنات ألم',       price:7.50,  stock:120, minStock:30, expiryDate:'2027-01-15', addedAt: Date.now()-86400000*10 },
    { id:'pp3', catalogId:'cat5', nameAr:'إيبوبروفين',  nameEn:'Ibuprofen',   category:'مسكنات ألم',       price:11.00, stock:67,  minStock:25, expiryDate:'2026-11-20', addedAt: Date.now()-86400000*5 },
    { id:'pp4', catalogId:'cat8', nameAr:'فيتامين D3',  nameEn:'Vitamin D3',  category:'فيتامينات ومكملات', price:24.00, stock:12,  minStock:10, expiryDate:'2026-12-01', addedAt: Date.now()-86400000*1 }
  ],
  'ph2': [
    { id:'pp5', catalogId:'cat2', nameAr:'باراسيتامول', nameEn:'Paracetamol', category:'مسكنات ألم',       price:8.00,  stock:80,  minStock:30, expiryDate:'2027-01-15', addedAt: Date.now()-86400000*3 },
    { id:'pp6', catalogId:'cat3', nameAr:'أوميبرازول',  nameEn:'Omeprazole',  category:'أدوية جهاز هضمي', price:21.00, stock:8,   minStock:15, expiryDate:'2025-12-01', addedAt: Date.now()-86400000*7 },
    { id:'pp7', catalogId:'cat9', nameAr:'ميتفورمين',   nameEn:'Metformin',   category:'أدوية سكري',       price:19.00, stock:35,  minStock:15, expiryDate:'2026-09-01', addedAt: Date.now()-86400000*1 }
  ],
  'ph3': [
    { id:'pp8', catalogId:'cat1', nameAr:'أموكسيسيلين', nameEn:'Amoxicillin', category:'مضادات حيوية',     price:17.00, stock:30,  minStock:20, expiryDate:'2026-08-01', addedAt: Date.now()-86400000*0 },
    { id:'pp9', catalogId:'cat4', nameAr:'فيتامين C',   nameEn:'Vitamin C',   category:'فيتامينات ومكملات', price:14.00, stock:55,  minStock:10, expiryDate:'2026-06-30', addedAt: Date.now()-86400000*2 }
  ]
};

// ── New medicines (recently added, last 7 days) ────────────
const mockNewMedicines = [
  { id:'nm1', catalogId:'cat1', nameAr:'أموكسيسيلين',  nameEn:'Amoxicillin',  category:'مضادات حيوية',     price:17.00, stock:30, pharmacyId:'ph3', pharmacyName:'صيدلية النور',   pharmacyCity:'جنين',    addedAt: Date.now()-86400000*0 },
  { id:'nm2', catalogId:'cat8', nameAr:'فيتامين D3',   nameEn:'Vitamin D3',   category:'فيتامينات ومكملات', price:24.00, stock:12, pharmacyId:'ph1', pharmacyName:'صيدلية الشفاء',  pharmacyCity:'نابلس',   addedAt: Date.now()-86400000*1 },
  { id:'nm3', catalogId:'cat9', nameAr:'ميتفورمين',    nameEn:'Metformin',    category:'أدوية سكري',       price:19.00, stock:35, pharmacyId:'ph2', pharmacyName:'صيدلية الرحمة', pharmacyCity:'رام الله', addedAt: Date.now()-86400000*1 },
  { id:'nm4', catalogId:'cat11',nameAr:'أزيثروميسين',  nameEn:'Azithromycin', category:'مضادات حيوية',     price:33.00, stock:15, pharmacyId:'ph4', pharmacyName:'صيدلية الأمل',   pharmacyCity:'الخليل',  addedAt: Date.now()-86400000*3 },
  { id:'nm5', catalogId:'cat4', nameAr:'فيتامين C',    nameEn:'Vitamin C',    category:'فيتامينات ومكملات', price:14.00, stock:55, pharmacyId:'ph3', pharmacyName:'صيدلية النور',   pharmacyCity:'جنين',    addedAt: Date.now()-86400000*2 }
];

// ── Search results mock ────────────────────────────────────
const mockSearchResults = {
  'باراسيتامول': [
    { id:'pp2', nameAr:'باراسيتامول', nameEn:'Paracetamol', price:7.50, stock:120, minStock:30, pharmacyId:'ph1', pharmacyName:'صيدلية الشفاء',  pharmacyCity:'نابلس',    pharmacyLat:32.2211, pharmacyLng:35.2544, pharmacyPhone:'092345678' },
    { id:'pp5', nameAr:'باراسيتامول', nameEn:'Paracetamol', price:8.00, stock:80,  minStock:30, pharmacyId:'ph2', pharmacyName:'صيدلية الرحمة', pharmacyCity:'رام الله', pharmacyLat:31.9022, pharmacyLng:35.2034, pharmacyPhone:'022345678' }
  ]
};

// ── Notification Seeds ─────────────────────────────────────────
// Called once from sidebar.js after notifications.js loads.
// Each function guards against re-seeding.

function seedMockDoses() {
  if (localStorage.getItem('pharma_doses')) return;
  const doses = [
    { id:1, medicine:'أموكسيسيلين', dose:'500mg',  frequency:'كل 8 ساعات', times:['08:00','16:00','00:00'], endDate:'2026-12-25', active:true  },
    { id:2, medicine:'فيتامين C',   dose:'1000mg', frequency:'يومي',         times:['09:00'],                 endDate:'2027-01-15', active:true  },
    { id:3, medicine:'أوميبرازول',  dose:'20mg',   frequency:'يومي',         times:['07:30'],                 endDate:'2027-02-01', active:true  }
  ];
  localStorage.setItem('pharma_doses', JSON.stringify(doses));
}

function seedFavMedicines() {
  if (localStorage.getItem('pharma_fav_medicines')) return;
  const favs = [
    { nameAr:'باراسيتامول', nameEn:'Paracetamol' },
    { nameAr:'فيتامين D3',  nameEn:'Vitamin D3'  },
    { nameAr:'أوميبرازول',  nameEn:'Omeprazole'  }
  ];
  localStorage.setItem('pharma_fav_medicines', JSON.stringify(favs));
}

// ── Admin Mock Data ───────────────────────────────────────────

const mockAdminUser = {
  uid:   'admin-demo-001',
  email: 'admin@pharmalink.com',
  name:  'مدير النظام',
  role:  'admin'
};

const mockAdminStats = {
  totalPharmacies:  24,
  activePharmacies: 20,
  pendingPharmacies: 2,
  totalUsers:      315,
  activeUsers:     280,
  totalOrders:    1842,
  todayOrders:      47,
  totalRevenue:  52400,
  thisMonthRevenue: 8750,
  catalogItems:    380
};

const mockAllPharmacies = [
  { id:'ph1', name:'صيدلية الشفاء',   owner:'علي حسن',       city:'نابلس',    phone:'0599-111222', license:'LIC-001', status:'active',    orders:143, revenue:12400, joinedAt: Date.now()-86400000*160 },
  { id:'ph2', name:'صيدلية النور',    owner:'فاطمة خالد',    city:'رام الله', phone:'0598-333444', license:'LIC-002', status:'active',    orders:97,  revenue:8900,  joinedAt: Date.now()-86400000*120 },
  { id:'ph3', name:'صيدلية الأمل',    owner:'محمد سالم',     city:'جنين',     phone:'0597-555666', license:'LIC-003', status:'pending',   orders:0,   revenue:0,     joinedAt: Date.now()-86400000*4   },
  { id:'ph4', name:'صيدلية الحياة',   owner:'سارة ناصر',     city:'الخليل',   phone:'0596-777888', license:'LIC-004', status:'active',    orders:211, revenue:19800, joinedAt: Date.now()-86400000*230 },
  { id:'ph5', name:'صيدلية المدينة',  owner:'أحمد رضا',      city:'طولكرم',   phone:'0595-999000', license:'LIC-005', status:'suspended', orders:34,  revenue:2800,  joinedAt: Date.now()-86400000*80  },
  { id:'ph6', name:'صيدلية السلام',   owner:'خالد موسى',     city:'بيت لحم',  phone:'0594-112233', license:'LIC-006', status:'active',    orders:76,  revenue:6400,  joinedAt: Date.now()-86400000*95  },
  { id:'ph7', name:'صيدلية الهلال',   owner:'ريم سعيد',      city:'نابلس',    phone:'0593-445566', license:'LIC-007', status:'active',    orders:58,  revenue:5100,  joinedAt: Date.now()-86400000*70  },
  { id:'ph8', name:'صيدلية الرعاية',  owner:'كريم حداد',     city:'رام الله', phone:'0592-778899', license:'LIC-008', status:'pending',   orders:0,   revenue:0,     joinedAt: Date.now()-86400000*1   },
];

const mockAllUsers = [
  { id:'u1', name:'أحمد محمد',    email:'ahmed@example.com',  phone:'0599-100200', city:'نابلس',    orders:8,  joinedAt: Date.now()-86400000*105, status:'active',  lastLogin: Date.now()-86400000 },
  { id:'u2', name:'سارة خالد',   email:'sara@example.com',   phone:'0598-300400', city:'رام الله', orders:3,  joinedAt: Date.now()-86400000*32,  status:'active',  lastLogin: Date.now()-86400000*3 },
  { id:'u3', name:'محمد علي',     email:'mali@example.com',   phone:'0597-500600', city:'جنين',     orders:12, joinedAt: Date.now()-86400000*168, status:'active',  lastLogin: Date.now()-3600000*2 },
  { id:'u4', name:'فاطمة حسن',   email:'fhasan@example.com', phone:'0596-700800', city:'الخليل',   orders:1,  joinedAt: Date.now()-86400000*70,  status:'active',  lastLogin: Date.now()-86400000*13 },
  { id:'u5', name:'يوسف أمين',   email:'yamin@example.com',  phone:'0595-900010', city:'طولكرم',   orders:0,  joinedAt: Date.now()-86400000*23,  status:'blocked', lastLogin: Date.now()-86400000*23 },
  { id:'u6', name:'ليلى أبو عمر',email:'layla@example.com',  phone:'0594-200300', city:'نابلس',    orders:5,  joinedAt: Date.now()-86400000*50,  status:'active',  lastLogin: Date.now()-3600000*5 },
  { id:'u7', name:'عمر بدران',   email:'obadran@example.com',phone:'0593-400500', city:'بيت لحم',  orders:2,  joinedAt: Date.now()-86400000*15,  status:'active',  lastLogin: Date.now()-86400000*2 },
  { id:'u8', name:'نور السيد',   email:'norsayed@example.com',phone:'0592-600700',city:'رام الله', orders:7,  joinedAt: Date.now()-86400000*88,  status:'active',  lastLogin: Date.now()-3600000 },
];

const mockCatalog = [
  { id:'c1',  nameAr:'باراسيتامول',   nameEn:'Paracetamol',  category:'مسكن',      manufacturer:'تيفا',        price:7.50,  rx:false, stock:1240, pharmacies:18,
    route:'فموي', activeIngredient:'باراسيتامول 500 مجم',
    indicationsAndUsage:'تخفيف الألم الخفيف إلى المتوسط وخفض الحرارة.',
    dosageAndAdministration:'بالغون: 500–1000 مجم كل 4–6 ساعات، بحد أقصى 4 جرام يومياً.',
    warnings:'لا تتجاوز الجرعة الموصى بها. تجنب استخدامه مع الكحول أو أدوية أخرى تحتوي على باراسيتامول.',
    storageAndHandling:'يُحفظ في درجة حرارة أقل من 25°م بعيداً عن الضوء والرطوبة.' },

  { id:'c2',  nameAr:'أموكسيسيلين',  nameEn:'Amoxicillin',  category:'مضاد حيوي', manufacturer:'فايزر',       price:25.00, rx:true,  stock:430,  pharmacies:14,
    route:'فموي', activeIngredient:'أموكسيسيلين ثلاثي الماء 500 مجم',
    indicationsAndUsage:'علاج الالتهابات البكتيرية في الجهاز التنفسي والبولي والجلد.',
    dosageAndAdministration:'بالغون: 500 مجم كل 8 ساعات لمدة 7–10 أيام وفق وصف الطبيب.',
    warnings:'يستلزم وصفة طبية. قد يسبب حساسية في المرضى الحساسين للبنسيلين. أكمل الجرعة كاملة.',
    storageAndHandling:'يُحفظ في درجة حرارة أقل من 25°م في مكان جاف.' },

  { id:'c3',  nameAr:'فيتامين C',    nameEn:'Vitamin C',    category:'فيتامين',   manufacturer:'بيير فابر',   price:12.00, rx:false, stock:890,  pharmacies:20,
    route:'فموي', activeIngredient:'حمض الأسكوربيك 1000 مجم',
    indicationsAndUsage:'تعزيز المناعة والوقاية من نزلات البرد ومعالجة نقص فيتامين C.',
    dosageAndAdministration:'بالغون: 1000–2000 مجم يومياً مع الطعام أو بعده.',
    warnings:'الجرعات العالية قد تسبب اضطرابات هضمية. استشر الطبيب في حالات حصوات الكلى.',
    storageAndHandling:'يُحفظ في درجة حرارة أقل من 30°م بعيداً عن الضوء المباشر.' },

  { id:'c4',  nameAr:'أوميبرازول',   nameEn:'Omeprazole',   category:'هضم',       manufacturer:'أسترازينيكا', price:18.00, rx:true,  stock:320,  pharmacies:12,
    route:'فموي', activeIngredient:'أوميبرازول 20 مجم',
    indicationsAndUsage:'علاج قرحة المعدة والاثني عشر والارتداد المعدي المريئي وأعراض الحموضة.',
    dosageAndAdministration:'بالغون: 20–40 مجم مرة واحدة يومياً قبل الأكل.',
    warnings:'يستلزم وصفة طبية. الاستخدام المطوّل قد يقلل امتصاص فيتامين B12. أبلغ طبيبك عند ظهور أعراض غير معتادة.',
    storageAndHandling:'يُحفظ في درجة حرارة أقل من 25°م في مكان جاف.' },

  { id:'c5',  nameAr:'ميتفورمين',    nameEn:'Metformin',    category:'سكري',      manufacturer:'ميرك',        price:15.00, rx:true,  stock:210,  pharmacies:9,
    route:'فموي', activeIngredient:'ميتفورمين هيدروكلوريد 500 مجم',
    indicationsAndUsage:'علاج السكري من النوع الثاني وتنظيم مستويات الجلوكوز في الدم.',
    dosageAndAdministration:'بالغون: 500–1000 مجم مرتين يومياً مع الوجبات. يحدد الطبيب الجرعة المناسبة.',
    warnings:'يستلزم وصفة طبية. راقب أعراض الحماض اللبني. يُوقف قبل التدخلات الجراحية.',
    storageAndHandling:'يُحفظ في درجة حرارة أقل من 25°م في عبوة محكمة الإغلاق.' },

  { id:'c6',  nameAr:'أتورفاستاتين', nameEn:'Atorvastatin', category:'قلب',       manufacturer:'فايزر',       price:32.00, rx:true,  stock:180,  pharmacies:8,
    route:'فموي', activeIngredient:'أتورفاستاتين كالسيوم 20 مجم',
    indicationsAndUsage:'خفض الكوليسترول الضار والدهون الثلاثية والوقاية من أمراض القلب والشرايين.',
    dosageAndAdministration:'بالغون: 10–80 مجم مرة واحدة يومياً في أي وقت.',
    warnings:'يستلزم وصفة طبية. أبلغ الطبيب فوراً عن أي ألم عضلي غير مبرر. تجنب عصير الجريب فروت.',
    storageAndHandling:'يُحفظ في درجة حرارة أقل من 25°م في عبوة محكمة بعيداً عن الرطوبة.' },

  { id:'c7',  nameAr:'إيبوبروفين',   nameEn:'Ibuprofen',    category:'مسكن',      manufacturer:'رايش',        price:9.00,  rx:false, stock:950,  pharmacies:19,
    route:'فموي', activeIngredient:'إيبوبروفين 400 مجم',
    indicationsAndUsage:'تخفيف الألم المتوسط والالتهابات وخفض الحرارة.',
    dosageAndAdministration:'بالغون: 400 مجم كل 6–8 ساعات مع الطعام، بحد أقصى 1200 مجم يومياً بدون وصفة.',
    warnings:'تجنب عند الإصابة بقرحة المعدة أو مشاكل كلوية. لا تستخدم أكثر من 3 أيام دون استشارة طبيب.',
    storageAndHandling:'يُحفظ في درجة حرارة أقل من 25°م بعيداً عن الرطوبة.' },

  { id:'c8',  nameAr:'لوراتادين',    nameEn:'Loratadine',   category:'حساسية',    manufacturer:'شيرينج',      price:14.00, rx:false, stock:660,  pharmacies:15,
    route:'فموي', activeIngredient:'لوراتادين 10 مجم',
    indicationsAndUsage:'علاج أعراض الحساسية الموسمية والمزمنة كالعطس وسيلان الأنف والحكة.',
    dosageAndAdministration:'بالغون: 10 مجم مرة واحدة يومياً.',
    warnings:'لا يسبب النعاس في معظم الحالات. استشر الطبيب إذا كنت تعاني من أمراض الكبد.',
    storageAndHandling:'يُحفظ في درجة حرارة أقل من 25°م في مكان جاف بعيداً عن الضوء.' },

  { id:'c9',  nameAr:'سيترازين',     nameEn:'Cetirizine',   category:'حساسية',    manufacturer:'يو سي بي',    price:11.00, rx:false, stock:540,  pharmacies:13,
    route:'فموي', activeIngredient:'سيترازين هيدروكلوريد 10 مجم',
    indicationsAndUsage:'علاج الحساسية الموسمية وخلايا النحل والحكة الجلدية المزمنة.',
    dosageAndAdministration:'بالغون: 10 مجم مرة واحدة يومياً مساءً.',
    warnings:'قد يسبب النعاس. تجنب قيادة السيارات أو تشغيل الآلات إذا تأثرت. تجنب الكحول.',
    storageAndHandling:'يُحفظ في درجة حرارة بين 15–30°م في مكان جاف.' },

  { id:'c10', nameAr:'أزيثروميسين',  nameEn:'Azithromycin', category:'مضاد حيوي', manufacturer:'فايزر',       price:38.00, rx:true,  stock:190,  pharmacies:11,
    route:'فموي', activeIngredient:'أزيثروميسين 500 مجم',
    indicationsAndUsage:'علاج الالتهابات البكتيرية في الجهاز التنفسي والجلد والأنسجة اللينة.',
    dosageAndAdministration:'بالغون: 500 مجم مرة واحدة يومياً لمدة 3 أيام وفق وصف الطبيب.',
    warnings:'يستلزم وصفة طبية. أبلغ الطبيب إذا كان لديك أمراض قلبية. أكمل الجرعة المقررة.',
    storageAndHandling:'يُحفظ في درجة حرارة أقل من 25°م في عبوة محكمة.' },

  { id:'c11', nameAr:'فيتامين D3',   nameEn:'Vitamin D3',   category:'فيتامين',   manufacturer:'ناتشر مايد',  price:22.00, rx:false, stock:740,  pharmacies:17,
    route:'فموي', activeIngredient:'كوليكالسيفيرول (فيتامين D3) 1000 وحدة دولية',
    indicationsAndUsage:'علاج ومنع نقص فيتامين D، دعم صحة العظام والأسنان وتعزيز المناعة.',
    dosageAndAdministration:'بالغون: 1000–2000 وحدة دولية يومياً أو وفق توجيه الطبيب.',
    warnings:'الجرعات المفرطة قد تسبب تسمماً. لا تتجاوز الجرعة الموصى بها دون استشارة طبيب.',
    storageAndHandling:'يُحفظ في درجة حرارة أقل من 25°م بعيداً عن الضوء المباشر والرطوبة.' },

  { id:'c12', nameAr:'ليزينوبريل',   nameEn:'Lisinopril',   category:'ضغط',       manufacturer:'ميرك',        price:20.00, rx:true,  stock:260,  pharmacies:10,
    route:'فموي', activeIngredient:'ليزينوبريل 10 مجم',
    indicationsAndUsage:'علاج ارتفاع ضغط الدم وقصور القلب وحماية الكلى في مرضى السكري.',
    dosageAndAdministration:'بالغون: 10–40 مجم مرة واحدة يومياً. تُعدَّل الجرعة تدريجياً بإشراف الطبيب.',
    warnings:'يستلزم وصفة طبية. قد يسبب سعالاً جافاً. تجنب أثناء الحمل. راقب مستوى البوتاسيوم.',
    storageAndHandling:'يُحفظ في درجة حرارة أقل من 25°م في مكان جاف بعيداً عن الحرارة.' },
];

const mockAdminOrders = [
  { id:'ORD-201', pharmacyId:'ph1', pharmacyName:'صيدلية الشفاء',  userId:'u1', userName:'أحمد محمد',    userPhone:'0599-100200', items:[{name:'باراسيتامول',qty:2,price:7.50},{name:'فيتامين C',qty:1,price:12}],  total:32.00, paymentMethod:'cash',  status:'delivered', createdAt: Date.now()-86400000*2,  address:'نابلس، شارع الزيتون', flagged:false },
  { id:'ORD-202', pharmacyId:'ph2', pharmacyName:'صيدلية النور',   userId:'u2', userName:'سارة خالد',   userPhone:'0598-300400', items:[{name:'أموكسيسيلين',qty:1,price:25}],                                       total:30.00, paymentMethod:'ussd',  status:'confirmed', createdAt: Date.now()-86400000,    address:'رام الله، حي البلدة',  flagged:false },
  { id:'ORD-203', pharmacyId:'ph4', pharmacyName:'صيدلية الحياة',  userId:'u3', userName:'محمد علي',     userPhone:'0597-500600', items:[{name:'فيتامين C',qty:3,price:12}],                                        total:41.00, paymentMethod:'bank',  status:'pending',   createdAt: Date.now()-3600000*5,   address:'جنين، المدينة',         flagged:false },
  { id:'ORD-204', pharmacyId:'ph1', pharmacyName:'صيدلية الشفاء',  userId:'u4', userName:'فاطمة حسن',   userPhone:'0596-700800', items:[{name:'أوميبرازول',qty:2,price:18}],                                       total:41.00, paymentMethod:'cash',  status:'shipping',  createdAt: Date.now()-3600000*2,   address:'الخليل، وسط البلد',    flagged:false },
  { id:'ORD-205', pharmacyId:'ph2', pharmacyName:'صيدلية النور',   userId:'u1', userName:'أحمد محمد',    userPhone:'0599-100200', items:[{name:'إيبوبروفين',qty:1,price:9}],                                       total:14.00, paymentMethod:'cash',  status:'cancelled', createdAt: Date.now()-86400000*3,  address:'نابلس، حي العمال',     flagged:false },
  { id:'ORD-206', pharmacyId:'ph4', pharmacyName:'صيدلية الحياة',  userId:'u3', userName:'محمد علي',     userPhone:'0597-500600', items:[{name:'لوراتادين',qty:2,price:14}],                                       total:33.00, paymentMethod:'ussd',  status:'delivered', createdAt: Date.now()-86400000*4,  address:'جنين، الغرب',          flagged:false },
  { id:'ORD-207', pharmacyId:'ph1', pharmacyName:'صيدلية الشفاء',  userId:'u2', userName:'سارة خالد',   userPhone:'0598-300400', items:[{name:'ميتفورمين',qty:1,price:15}],                                       total:20.00, paymentMethod:'bank',  status:'pending',   createdAt: Date.now()-1800000,     address:'رام الله، البيرة',     flagged:false },
  { id:'ORD-208', pharmacyId:'ph6', pharmacyName:'صيدلية السلام',  userId:'u6', userName:'ليلى أبو عمر', userPhone:'0594-200300', items:[{name:'أتورفاستاتين',qty:1,price:32}],                                    total:37.00, paymentMethod:'cash',  status:'delivered', createdAt: Date.now()-86400000*5,  address:'نابلس، عين مريم',      flagged:false },
  { id:'ORD-209', pharmacyId:'ph7', pharmacyName:'صيدلية الهلال',  userId:'u8', userName:'نور السيد',    userPhone:'0592-600700', items:[{name:'سيترازين',qty:2,price:11},{name:'إيبوبروفين',qty:1,price:9}],    total:36.00, paymentMethod:'ussd',  status:'confirmed', createdAt: Date.now()-3600000*8,   address:'نابلس، الصفافير',      flagged:false },
  { id:'ORD-210', pharmacyId:'ph2', pharmacyName:'صيدلية النور',   userId:'u7', userName:'عمر بدران',   userPhone:'0593-400500', items:[{name:'أزيثروميسين',qty:1,price:38}],                                     total:43.00, paymentMethod:'bank',  status:'shipping',  createdAt: Date.now()-86400000*6,  address:'بيت لحم، بيت ساحور',  flagged:false },
];
