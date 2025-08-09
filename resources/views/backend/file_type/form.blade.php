@extends('layout.layout')
@php
    $title = $container->title . (is_null($item->id) ? ' Ekle' : ' Düzenle');
    $subTitle = $container->title . (is_null($item->id) ? ' Ekle' : ' Düzenle');
@endphp

@section('content')
    <div class="row gy-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0 fs-6">{{ $container->title }} {{ !is_null($item->id) ? 'Düzenle' : 'Ekle' }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.' . $container->page . '_save', ['unique' => $item->id]) }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <!-- Dosya Tipi Adı -->
                            <div class="col-md-6">
                                <label class="form-label">Dosya Tipi Adı</label>
                                <div class="input-wrapper position-relative">
                                    <span class="icon-wrapper position-absolute start-0 top-50 translate-middle-y ms-3">
                                        <iconify-icon icon="carbon:document" class="text-primary"></iconify-icon>
                                    </span>
                                    <input type="text" class="form-control custom-input ps-5"
                                        name="name" placeholder="Lütfen dosya tipi adı giriniz"
                                        value="{{ old('name') ?? ($item->name ?? '') }}">
                                    <x-form-error field="name" />
                                </div>
                            </div>

                            <!-- Durum -->
                            <div class="col-md-6">
                                <label class="form-label">Durum</label>
                                <div class="input-wrapper position-relative">
                                    <span class="icon-wrapper position-absolute start-0 top-50 translate-middle-y ms-3">
                                        <iconify-icon icon="carbon:badge" class="text-primary"></iconify-icon>
                                    </span>
                                    <select class="form-select custom-select ps-5" name="is_active">
                                        <option value="1" {{ $item->is_active == 1 || is_null($item->id) ? 'selected' : '' }}>
                                            Aktif
                                        </option>
                                        <option value="0" {{ $item->is_active == 0 && !is_null($item->id) ? 'selected' : '' }}>
                                            Pasif
                                        </option>
                                    </select>
                                    <x-form-error field="is_active" />
                                </div>
                            </div>

                            <!-- İzin Verilen Uzantılar -->
                            <div class="col-md-12">
                                <label class="form-label">İzin Verilen Dosya Uzantıları <small class="text-muted">(Virgülle ayırarak giriniz, örn: pdf,doc,docx,jpg)</small></label>
                                <div class="input-wrapper position-relative">
                                    <span class="icon-wrapper position-absolute start-0 top-50 translate-middle-y ms-3">
                                        <iconify-icon icon="carbon:document-attachment" class="text-primary"></iconify-icon>
                                    </span>
                                    <input type="text" class="form-control custom-input ps-5"
                                        name="allowed_extensions" placeholder="pdf,doc,docx,jpg,jpeg,png"
                                        value="{{ old('allowed_extensions') ?? ($item->allowed_extensions ?? '') }}">
                                    <x-form-error field="allowed_extensions" />
                                    <small class="form-text text-muted mt-2">Boş bırakırsanız tüm dosya türleri kabul edilecektir.</small>
                                </div>
                            </div>

                            <!-- Açıklama -->
                            <div class="col-md-12">
                                <label class="form-label">Açıklama</label>
                                <div class="input-wrapper position-relative">
                                    <span class="icon-wrapper position-absolute start-0 top-0 translate-middle-y ms-3 mt-4">
                                        <iconify-icon icon="carbon:text-annotation" class="text-primary"></iconify-icon>
                                    </span>
                                    <textarea class="form-control custom-input ps-5" name="description"
                                        placeholder="Dosya tipi hakkında açıklama giriniz" rows="3">{{ old('description') ?? ($item->description ?? '') }}</textarea>
                                    <x-form-error field="description" />
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary-600">Kaydet</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
