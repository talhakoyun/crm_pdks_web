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
                    <form action="{{ route('backend.' . $container->page . '_save', ['unique' => $item->id]) }}"
                        method="POST">
                        @csrf
                        <div class="row gy-3">
                            <div class="col-6">
                                <label class="form-label">Adı</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:location-company"></iconify-icon>
                                    </span>
                                    <input type="text" class="form-control" name="title"
                                        placeholder="Lütfen menü adı giriniz"
                                        value="{{ old('title') ?? ($item->title ?? '') }}">
                                    <x-form-error field="title" />
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Durumu</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:badge"></iconify-icon>
                                    </span>
                                    <select class="form-control form-select" name="is_active">
                                        <option value="1"
                                            {{ old('is_active') ?? ($item->is_active ?? '') == 1 ? 'selected' : '' }}>Aktif
                                        </option>
                                        <option value="0"
                                            {{ old('is_active') ?? ($item->is_active ?? '') == 0 ? 'selected' : '' }}>Pasif
                                        </option>
                                    </select>
                                    <x-form-error field="is_active" />
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Kategori Mi?</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:category"></iconify-icon>
                                    </span>
                                    <select class="form-control form-select" name="is_category">
                                        <option value="">Seçiniz</option>
                                        <option value="1"
                                            {{ old('is_category') ?? ($item->is_category ?? '') == 1 ? 'selected' : '' }}>
                                            Evet
                                        </option>
                                        <option value="0"
                                            {{ old('is_category') ?? ($item->is_category ?? '') == 0 ? 'selected' : '' }}>
                                            Hayır
                                        </option>
                                        </option>
                                    </select>
                                    <x-form-error field="is_category" />
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Üst Kategorisi</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="lsicon:top-outline"></iconify-icon>
                                    </span>
                                    <select class="form-control form-select" name="top_id">
                                        <option value="">Seçiniz</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ old('top_id') ?? ($item->top_id ?? '') == $category->id ? 'selected' : '' }}>
                                                {{ $category->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-form-error field="top_id" />
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Sırası</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="pixelarticons:sort"></iconify-icon>
                                    </span>
                                    <input type="number" class="form-control" name="order"
                                        placeholder="Lütfen sıra giriniz"
                                        value="{{ old('order') ?? ($item->order ?? '') }}">
                                    <x-form-error field="order" />
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">İkon</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:location-company"></iconify-icon>
                                    </span>
                                    <input type="text" class="form-control" name="icon"
                                        placeholder="Lütfen ikon giriniz" value="{{ old('icon') ?? ($item->icon ?? '') }}">
                                    <x-form-error field="icon" />
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Route Adı</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="material-symbols-light:route"></iconify-icon>
                                    </span>
                                    <input type="text" class="form-control" name="route_name"
                                        placeholder="Lütfen route adı giriniz"
                                        value="{{ old('route_name') ?? ($item->route_name ?? '') }}">
                                    <x-form-error field="route_name" />
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
