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
                                <label class="form-label">Departman Şubesi</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:location-company"></iconify-icon>
                                    </span>
                                    <select class="form-control" name="branch_id">
                                        <option value="">Seçiniz</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}"
                                                {{ old('branch_id') ?? ($item->branch_id ?? '') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->title }}</option>
                                        @endforeach
                                    </select>
                                    <x-form-error field="branch_id" />
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Departman Adı</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="icon-park-outline:branch-one"></iconify-icon>
                                    </span>
                                    <input type="text" class="form-control" name="title"
                                        placeholder="Lütfen departman adı giriniz"
                                        value="{{ old('title') ?? ($item->title ?? '') }}">
                                    <x-form-error field="title" />
                                </div>
                            </div>

                            <div class="col-6">
                                <label class="form-label">Departman Yöneticisi</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:location-company"></iconify-icon>
                                    </span>
                                    <select class="form-control" name="manager_id">
                                        <option value="">Seçiniz</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}"
                                                {{ old('manager_id') ?? ($item->manager_id ?? '') == $user->id ? 'selected' : '' }}>
                                                {{ $user->fullname }}</option>
                                        @endforeach
                                    </select>
                                    <x-form-error field="manager_id" />
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Durumu</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="fluent:status-16-regular"></iconify-icon>
                                    </span>
                                    <select class="form-control" name="is_active">
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
