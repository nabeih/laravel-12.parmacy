@extends('layouts.nav_admin')

@section('title', 'Catelog')

@section('content')

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<div class="admin-page-header">

    <div>
        <h1 class="admin-page-title"> صفحة الاقسام</h1>
        <p class="admin-page-sub">قاعدة بيانات الاقسام المرجعية للمنصة</p>
    </div>
    <div style="display:flex;gap:8px">
        <button class="admin-btn admin-btn-outline" onclick="_exportCatalog()">⬇ تصدير CSV</button>
        <a href="{{ route('category.trash') }}" class="admin-btn admin-btn-outline">🗑 سلة المهملات</a>
        <a href="{{ route('category.create') }}" class="admin-btn admin-btn-primary" onclick="_openAddModal()">+ إضافة
            قسم</a>
    </div>
</div>

<div class="admin-card">
    <!-- Toolbar -->
    <div class="admin-toolbar">
        <form method="GET" action="{{ route('category.index') }}" style="display:flex;gap:10px;flex:1">
            <input type="text" name="q" value="{{ $q ?? '' }}" class="admin-search"
                placeholder="ابحث بالاسم عربي أو إنجليزي...">
            <button type="submit" class="admin-btn admin-btn-primary">🔍 بحث</button>
            @if(($q ?? '') !== '')
                <a href="{{ route('category.index') }}" class="admin-btn admin-btn-outline">إلغاء</a>
            @endif
        </form>
    </div>

    <!-- Bulk action bar -->
    <div id="bulk-bar" style="display:none;padding:10px 0;border-bottom:1px solid var(--admin-border);
        margin-bottom:12px;display:none;align-items:center;gap:12px">
        <span id="bulk-count" style="font-size:13px;color:#64748b">hi</span>
        <button class="admin-btn admin-btn-outline admin-btn-sm" onclick="_exportSelected()">⬇ تصدير
            المحدد</button>
        <button class="admin-btn admin-btn-danger admin-btn-sm" onclick="_confirmBulkDelete()">🗑 حذف
            المحدد</button>
        <button class="admin-btn admin-btn-outline admin-btn-sm" onclick="_clearSelection()">إلغاء
            التحديد</button>
    </div>
    {{-- {{ dd(get_defined_vars()) }} --}}

    <div class="admin-table-wrap">
        <a href="{{ route('category.create') }}">اضافة قسم جديد</a>

        <table class="admin-table">
            <thead>
                <tr>
                    {{-- <th><input type="checkbox" id="select-all" onclick="_toggleAll(this)"></th> --}}
                    <th>الاسم بالانجليزي</th>
                    <th>الاسم بالعربي</th>
                    <th>هل فعال</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody id="cat-tbody">
                {{-- dd($categories); --}}
                @foreach ($categories as $category)
                    <tr>
                        <td>{{ $category->name_en }}</td>
                        <td>{{ $category->name_ar }}</td>
                        <td>{{ $category->is_active ? 'نعم' : 'لا' }}</td>
                        <td>
                            <a href="{{ route('category.edit', $category->id) }}"
                                class="admin-btn admin-btn-sm admin-btn-outline">تعديل</a>

                            <form action="{{ route('admin.catgory.destroy', $category->id) }}" method="POST"
                                style="display:inline"
                                onsubmit="return confirm('هل أنت متأكد أنك تريد حذف هذا القسم؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">حذف</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                < {{-- @empty <span>{{ 'لايوجد بيانات' }}</span>

                    @endforelse --}}
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
            <button class="admin-btn admin-btn-danger" onclick="_deleteCatalog()">حذف</button>
        </div>
    </div>
</div>
@stop
{{-- @section('script') --}}