@extends('layouts.nav_admin')
@section('title', 'إضافة صيدلي')
@section('content')

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">إضافة صيدلي جديد</h1>
        <p class="admin-page-sub">بيانات الاعتماد والمستندات الرسمية للصيدلي</p>
    </div>
    <div>
        <a href="{{ route('pharmacist.index') }}" class="admin-btn admin-btn-outline">&larr; العودة إلى القائمة</a>
    </div>
</div>

<div class="admin-card">
    <form action="{{ route('pharmacist.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label class="admin-form-label">المستخدم (الحساب) *</label>
                <select name="user_id" class="admin-form-select @error('user_id') error @enderror" required>
                    <option value="">اختر المستخدم</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                @error('user_id')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">الرقم الوطني *</label>
                <input type="text" name="national_id" class="admin-form-input @error('national_id') error @enderror"
                    value="{{ old('national_id') }}" required>
                @error('national_id')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">رقم النقابة *</label>
                <input type="text" name="syndicate_number" class="admin-form-input @error('syndicate_number') error @enderror"
                    value="{{ old('syndicate_number') }}" required>
                @error('syndicate_number')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">رقم الترخيص *</label>
                <input type="text" name="license_number" class="admin-form-input @error('license_number') error @enderror"
                    value="{{ old('license_number') }}" required>
                @error('license_number')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">جامعة التخرج *</label>
                <input type="text" name="graduation_university" class="admin-form-input @error('graduation_university') error @enderror"
                    value="{{ old('graduation_university') }}" required>
                @error('graduation_university')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">سنة التخرج *</label>
                <input type="number" name="graduation_year" class="admin-form-input @error('graduation_year') error @enderror"
                    value="{{ old('graduation_year') }}" min="1950" max="{{ date('Y') }}" required>
                @error('graduation_year')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">الحالة *</label>
                <select name="status" class="admin-form-select @error('status') error @enderror" required>
                    <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>قيد المراجعة</option>
                    <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>معتمد</option>
                    <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                </select>
                @error('status')<span class="admin-form-error">{{ $message }}</span>@enderror
            </div>

            <div class="admin-form-group" style="display:flex;align-items:center;padding-top:24px">
                <label class="admin-form-check">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}>
                    حساب فعال
                </label>
            </div>
        </div>

        <div class="admin-section">
            <div class="admin-section-title">📎 المستندات الرسمية</div>
            <div class="admin-form-grid">
                <div class="admin-form-group">
                    <label class="admin-form-label">ملف الشهادة *</label>
                    <input type="file" name="certificate_file" class="admin-form-input @error('certificate_file') error @enderror" required>
                    @error('certificate_file')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">ملف النقابة *</label>
                    <input type="file" name="syndicate_file" class="admin-form-input @error('syndicate_file') error @enderror" required>
                    @error('syndicate_file')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">ملف الترخيص *</label>
                    <input type="file" name="license_file" class="admin-form-input @error('license_file') error @enderror" required>
                    @error('license_file')<span class="admin-form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="admin-form-group">
            <label class="admin-form-label">ملاحظات</label>
            <textarea name="notes" class="admin-form-textarea">{{ old('notes') }}</textarea>
        </div>

        <div class="admin-modal-footer" style="border-top:none;padding-top:0">
            <a href="{{ route('pharmacist.index') }}" class="admin-btn admin-btn-outline">إلغاء</a>
            <button type="submit" class="admin-btn admin-btn-primary">💾 حفظ</button>
        </div>
    </form>
</div>

@stop
