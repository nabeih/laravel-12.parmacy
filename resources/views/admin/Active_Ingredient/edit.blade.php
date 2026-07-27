@extends('layouts.nav_admin')

@section('title', 'تعديل مادة فعالة')

@section('content')

    <div class="container-fluid">

        <div class="card shadow-sm">

            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-folder-plus"></i>
                    تعديل مادة فعالة
                </h4>
            </div>

            <div class="card-body">

                <form action="{{ route('activeingredient.update', $ingredient->id) }}" method="POST">

                    @csrf
                    @method('PUT')

                    <div class="row">

                        {{-- Arabic Name --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                الاسم بالعربي <span class="text-danger">*</span>
                            </label>

                            <input type="text" name="name_ar" class="form-control @error('name_ar') is-invalid @enderror"
                                value="{{ old('name_ar', $ingredient->name_ar) }}" required>

                            @error('name_ar')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- English Name --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                الاسم بالإنجليزية <span class="text-danger">*</span>
                            </label>

                            <input type="text" name="name_en" class="form-control @error('name_en') is-invalid @enderror"
                                value="{{ old('name_en', $ingredient->name_en) }}" required>

                            @error('name_en')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                    </div>

                    {{-- Status --}}
                    <div class="mb-4">

                        <label class="form-label d-block">
                            الحالة
                        </label>

                        <div class="form-check form-check-inline">

                            <input class="form-check-input" type="radio" name="is_active" id="active" value="1"
                                {{ old('is_active', $ingredient->is_active) == 1 ? 'checked' : '' }}>

                            <label class="form-check-label" for="active">
                                فعال
                            </label>

                        </div>

                        <div class="form-check form-check-inline">

                            <input class="form-check-input" type="radio" name="is_active" id="inactive" value="0"
                                {{ old('is_active', $ingredient->is_active) == 0 ? 'checked' : '' }}>

                            <label class="form-check-label" for="inactive">
                                غير فعال
                            </label>

                        </div>

                    </div>

                    {{-- Buttons --}}
                    <div class="d-flex justify-content-end gap-2">

                        <a href="{{ route('activeingredient.index') }}" class="btn btn-secondary">
                            إلغاء
                        </a>

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i>
                            حفظ التعديل
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

@endsection
