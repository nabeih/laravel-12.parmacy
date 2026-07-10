@extends('masterpage')

@section('title', 'Catelog')

@section('content')




<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">كتالوج الأدوية</h1>
        <p class="admin-page-sub">قاعدة بيانات الأدوية المرجعية للمنصة</p>
    </div>
    <div style="display:flex;gap:8px">
        <button class="admin-btn admin-btn-outline" onclick="_exportCatalog()">⬇ تصدير CSV</button>
        <button class="admin-btn admin-btn-primary" onclick="_openAddModal()">+ إضافة دواء</button>
    </div>
</div>

<div class="admin-card">
    <!-- Toolbar -->
    <div class="admin-toolbar">
        <input class="admin-search" id="cat-search" placeholder="ابحث بالاسم عربي أو إنجليزي..."
            oninput="_onCatSearch()">
        <select class="admin-select" id="cat-category" onchange="_onCatFilter()">
            <option value="">جميع التصنيفات</option>
            <option>مسكن</option>
            <option>مضاد حيوي</option>
            <option>فيتامين</option>
            <option>هضم</option>
            <option>سكري</option>
            <option>قلب</option>
            <option>حساسية</option>
            <option>ضغط</option>
        </select>
        <select class="admin-select" id="cat-rx" onchange="_onCatFilter()">
            <option value="">الكل</option>
            <option value="rx">بوصفة طبية</option>
            <option value="otc">بدون وصفة</option>
        </select>
    </div>

    <!-- Bulk action bar -->
    <div id="bulk-bar"
        style="display:none;padding:10px 0;border-bottom:1px solid var(--admin-border);margin-bottom:12px;display:none;align-items:center;gap:12px">
        <span id="bulk-count" style="font-size:13px;color:#64748b"></span>
        <button class="admin-btn admin-btn-outline admin-btn-sm" onclick="_exportSelected()">⬇ تصدير
            المحدد</button>
        <button class="admin-btn admin-btn-danger admin-btn-sm" onclick="_confirmBulkDelete()">🗑 حذف
            المحدد</button>
        <button class="admin-btn admin-btn-outline admin-btn-sm" onclick="_clearSelection()">إلغاء
            التحديد</button>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all" onclick="_toggleAll(this)"></th>
                    <th>الاسم</th>
                    <th>التصنيف</th>
                    <th>الشركة المصنعة</th>
                    <th>السعر (₪)</th>
                    <th>الوصفة</th>
                    <th>المخزون</th>
                    <th>الصيدليات</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody id="cat-tbody">
                <td>
                <th>ghgh</th>
                <th>ghgh</th>
                <th>ghgh</th>
                <th>ghgh</th>
                <th>ghgh</th>
                <th>ghgh</th>
                <th>ghgh</th>
                <th>ghgh</th>
                </td>
            </tbody>
        </table>
    </div>
    <div id="cat-pagination" class="admin-pagination"></div>
</div>

</main>
</div>
</div>

<!-- Add/Edit Modal -->
<div class="admin-modal-overlay" id="cat-form-modal">
    <div class="admin-modal" style="max-width:680px;max-height:90vh;overflow-y:auto">
        <div class="admin-modal-title" id="cat-form-title">إضافة دواء جديد</div>
        <button class="admin-modal-close" onclick="_closeModal('cat-form-modal')">✕</button>
        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label class="admin-form-label">الاسم بالعربية *</label>
                <input class="admin-form-input" id="cf-name-ar" placeholder="باراسيتامول">
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">الاسم بالإنجليزية *</label>
                <input class="admin-form-input" id="cf-name-en" placeholder="Paracetamol" dir="ltr">
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">التصنيف *</label>
                <select class="admin-form-select" id="cf-category">
                    <option>مسكن</option>
                    <option>مضاد حيوي</option>
                    <option>فيتامين</option>
                    <option>هضم</option>
                    <option>سكري</option>
                    <option>قلب</option>
                    <option>حساسية</option>
                    <option>ضغط</option>
                </select>
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">الشركة المصنعة</label>
                <input class="admin-form-input" id="cf-manufacturer" placeholder="فايزر">
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">طريقة الإعطاء</label>
                <select class="admin-form-select" id="cf-route">
                    <option value="فموي">فموي</option>
                    <option value="موضعي">موضعي</option>
                    <option value="حقن">حقن</option>
                    <option value="استنشاق">استنشاق</option>
                    <option value="أخرى">أخرى</option>
                </select>
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">المادة الفعّالة</label>
                <input class="admin-form-input" id="cf-active-ingredient" placeholder="باراسيتامول 500 مجم" dir="ltr">
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">السعر المرجعي (₪) *</label>
                <input class="admin-form-input" type="number" id="cf-price" placeholder="0.00" step="0.01" min="0"
                    dir="ltr">
            </div>
            <div class="admin-form-group" style="display:flex;align-items:center;padding-top:24px">
                <label class="admin-form-check">
                    <input type="checkbox" id="cf-rx">
                    يتطلب وصفة طبية
                </label>
            </div>
        </div>
        <div class="admin-form-group" style="margin-top:4px">
            <label class="admin-form-label">دواعي الاستعمال</label>
            <textarea class="admin-form-input" id="cf-indications" rows="2"
                placeholder="ما الحالات التي يعالجها هذا الدواء..."></textarea>
        </div>
        <div class="admin-form-group" style="margin-top:4px">
            <label class="admin-form-label">الجرعة وطريقة الاستخدام</label>
            <textarea class="admin-form-input" id="cf-dosage" rows="2"
                placeholder="الجرعة الموصى بها للبالغين والأطفال..."></textarea>
        </div>
        <div class="admin-form-group" style="margin-top:4px">
            <label class="admin-form-label">التحذيرات</label>
            <textarea class="admin-form-input" id="cf-warnings" rows="2"
                placeholder="موانع الاستخدام، التفاعلات الدوائية..."></textarea>
        </div>
        <div class="admin-form-group" style="margin-top:4px">
            <label class="admin-form-label">التخزين والمناولة</label>
            <input class="admin-form-input" id="cf-storage" placeholder="يُحفظ في درجة حرارة أقل من 25°م...">
        </div>
        <div class="admin-modal-footer">
            <button class="admin-btn admin-btn-outline" onclick="_closeModal('cat-form-modal')">إلغاء</button>
            <button class="admin-btn admin-btn-primary" onclick="_saveCatalog()">حفظ</button>
        </div>
    </div>
</div>

<!-- Delete confirm -->
<div class="admin-modal-overlay" id="cat-delete-modal">
    <div class="admin-modal" style="max-width:420px">
        <div class="admin-modal-title" id="cat-delete-title">⚠️ تأكيد الحذف</div>
        <button class="admin-modal-close" onclick="_closeModal('cat-delete-modal')">✕</button>
        <p id="cat-delete-msg" style="color:#374151;margin:0 0 20px"></p>
        <div class="admin-modal-footer">
            <button class="admin-btn admin-btn-outline" onclick="_closeModal('cat-delete-modal')">إلغاء</button>
            <button class="admin-btn admin-btn-danger" id="cat-delete-confirm-btn">حذف نهائياً</button>
        </div>
    </div>
</div>
@stop
{{-- @section('script') --}}
