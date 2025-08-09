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
                                <label class="form-label">Şirket Adı</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:location-company"></iconify-icon>
                                    </span>
                                    <input type="text" class="form-control" name="title"
                                        placeholder="Lütfen şirket adı giriniz"
                                        value="{{ old('title') ?? ($item->title ?? '') }}">
                                    <x-form-error field="title" />
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Telefonu</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:location-company"></iconify-icon>
                                    </span>
                                    <input type="text" class="form-control" name="phone_number" id="phone"
                                        placeholder="Lütfen şirket telefonu giriniz"
                                        value="{{ old('phone_number') ?? ($item->phone_number ?? '') }}">
                                    <x-form-error field="phone_number" />
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Email</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:location-company"></iconify-icon>
                                    </span>
                                    <input type="email" class="form-control" name="email" id="email"
                                        placeholder="Lütfen şirket email giriniz"
                                        value="{{ old('email') ?? ($item->email ?? '') }}">
                                    <x-form-error field="email" />
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Yönetici <span class="text-danger">*</span></label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:location-company"></iconify-icon>
                                    </span>
                                    <select class="form-control" name="user_id" required>
                                        <option value="">Seçiniz</option>
                                        @foreach ($users as $user)
                                            <option {{ (old('user_id') ?? $item->user_id) == $user->id ? 'selected' : '' }}
                                                value="{{ $user->id }}">{{ $user->name }} {{ $user->surname }}</option>
                                        @endforeach
                                    </select>
                                    <x-form-error field="user_id" />
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Resim</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:location-company"></iconify-icon>
                                    </span>
                                    <input type="file" class="form-control" name="image" id="image">
                                    <x-form-error field="image" />
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Adresi</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:location-company"></iconify-icon>
                                    </span>
                                    <textarea class="form-control" name="address" placeholder="Lütfen şirket adresi giriniz">{{ old('address') ?? ($item->address ?? '') }}</textarea>
                                    <x-form-error field="address" />
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
