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
                                <label class="form-label">Ad</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:devices-apps"></iconify-icon>
                                    </span>
                                    <input type="text" class="form-control" name="name"
                                        placeholder="Lütfen cihaz adı giriniz"
                                        value="{{ old('name') ?? ($item->name ?? '') }}">
                                    <x-form-error field="name" />
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Marka</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="f7:tag"></iconify-icon>
                                    </span>
                                    <input type="text" class="form-control" name="brand"
                                        placeholder="Lütfen marka giriniz"
                                        value="{{ old('brand') ?? ($item->brand ?? '') }}">
                                    <x-form-error field="brand" />
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Model</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:model-alt"></iconify-icon>
                                    </span>
                                    <input type="text" class="form-control" name="model"
                                        placeholder="Lütfen model giriniz"
                                        value="{{ old('model') ?? ($item->model ?? '') }}">
                                    <x-form-error field="model" />
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Seri No</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="f7:barcode"></iconify-icon>
                                    </span>
                                    <input type="text" class="form-control" name="serial_number"
                                        placeholder="Lütfen seri no giriniz"
                                        value="{{ old('serial_number') ?? ($item->serial_number ?? '') }}">
                                    <x-form-error field="serial_number" />
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Açıklama</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="material-symbols:description-outline-sharp"></iconify-icon>
                                    </span>
                                    <textarea class="form-control" name="description" placeholder="Lütfen açıklama giriniz" rows="3">{{ old('description') ?? ($item->description ?? '') }}</textarea>
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
