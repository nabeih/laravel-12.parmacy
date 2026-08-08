<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmacyLink — جرعاتي</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-database-compat.js"></script>
</head>

<body>
    <div class="page-loading" id="loading-overlay">
        <div class="spinner"></div>
        <p>جاري التحميل...</p>
    </div>
    <div id="app-content" style="display:none">
        <div id="sidebar-container"></div>
        <button class="hamburger-btn" id="hamburger-btn"><span></span><span></span><span></span></button>
        <div class="sidebar-overlay" id="sidebar-overlay"></div>
        <main class="main-content">
            <div class="page-header">
                <h1>جرعاتي</h1>
                <p>جدول التذكير بمواعيد الدواء</p>
            </div>
            <div style="margin-bottom:16px;display:flex;justify-content:flex-end">
                <button id="btn-add-dose" class="btn btn-primary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    إضافة دواء
                </button>
            </div>
            <div id="doses-list" class="doses-list"></div>
        </main>
    </div>

    <!-- Add/Edit Dose Modal -->
    <div class="modal-overlay" id="dose-modal">
        <div class="modal">
            <div class="modal-header">
                <h3 id="dose-modal-title">إضافة دواء</h3><button class="modal-close" id="dose-modal-close">✕</button>
            </div>
            <div class="modal-body">
                <form id="dose-form" novalidate>
                    <div class="form-group"><label>اسم الدواء بالعربي <span class="required">*</span></label><input
                            type="text" id="df-nameAr" class="form-control" placeholder="أموكسيسيلين"></div>
                    <div class="form-group"><label>اسم الدواء بالإنجليزي</label><input type="text" id="df-nameEn"
                            class="form-control" placeholder="Amoxicillin"></div>
                    <div class="form-grid">
                        <div class="form-group"><label>الجرعة</label><input type="text" id="df-dosage"
                                class="form-control" placeholder="500 مجم"></div>
                        <div class="form-group"><label>حتى تاريخ</label><input type="date" id="df-until"
                                class="form-control"></div>
                    </div>
                    <div class="form-group">
                        <label>مواعيد الجرعة <span class="required">*</span></label>
                        <div id="times-container" style="display:flex;flex-direction:column;gap:8px"></div>
                        <button type="button" id="btn-add-time" class="btn btn-sm btn-ghost" style="margin-top:8px">+
                            إضافة موعد</button>
                    </div>
                    <div class="form-group"><label>ملاحظات</label><textarea id="df-notes" class="form-control"
                            placeholder="مع الطعام، بعد الإفطار..."></textarea></div>
                    <p id="dose-msg" style="min-height:18px"></p>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-ghost" id="dose-modal-cancel">إلغاء</button>
                <button class="btn btn-primary" id="dose-modal-save">حفظ</button>
            </div>
        </div>
    </div>

    <script src="../js/firebase-config.js"></script>
    <script src="../js/mock-data.js"></script>
    <script src="../js/auth.js"></script>
    <script src="../js/cart.js"></script>
    <script src="../js/user/sidebar.js"></script>
    <script src="../js/user/doses.js"></script>
    <script>initAuth('user', () => { renderUserSidebar(); initDoses(); });</script>
</body>

</html>