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
                            <div class="col-md-12">
                                <label class="form-label">Başlık</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:text-align-center"></iconify-icon>
                                    </span>
                                    <input type="text" class="form-control" name="title"
                                        placeholder="Lütfen başlık giriniz"
                                        value="{{ old('title') ?? ($item->title ?? '') }}">
                                    <x-form-error field="title" />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Konum</label>
                                    <input type="text" name="location" class="form-control"
                                        value="{{ old('location', $item?->location) }}" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Açıklama</label>
                                    <textarea name="description" class="form-control" rows="4" required>{{ old('description', $item?->description) }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Kontenjan</label>
                                    <input type="number" name="quota" class="form-control"
                                        value="{{ old('quota', $item?->quota) }}" required min="1">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Başlangıç Tarihi</label>
                                    <input type="datetime-local" name="start_date" class="form-control"
                                        value="{{ old('start_date', $item?->start_date?->format('Y-m-d\TH:i')) }}" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Bitiş Tarihi</label>
                                    <input type="datetime-local" name="end_date" class="form-control"
                                        value="{{ old('end_date', $item?->end_date?->format('Y-m-d\TH:i')) }}" required>
                                </div>
                            </div>
                            @if (!is_null($item->id))
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Durum</label>
                                        <select name="status" class="form-control" required>
                                            <option value="active" {{ $item->status == 'active' ? 'selected' : '' }}>Aktif
                                            </option>
                                            <option value="passive" {{ $item->status == 'passive' ? 'selected' : '' }}>
                                                Pasif</option>
                                            <option value="completed" {{ $item->status == 'completed' ? 'selected' : '' }}>
                                                Tamamlandı</option>
                                        </select>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-12 mt-3">
                            <button type="submit" class="btn btn-primary-600">Kaydet</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
