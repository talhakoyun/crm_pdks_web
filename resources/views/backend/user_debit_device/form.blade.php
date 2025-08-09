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
                                <label class="form-label">Kullanıcı</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="tabler:user"></iconify-icon>
                                    </span>
                                    <select class="form-select" name="user_id" required>
                                        <option value="">Kullanıcı Seçiniz</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}"
                                                {{ (old('user_id') ?? ($item->user_id ?? '')) == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} {{ $user->surname }}
                                                @if($user->department)
                                                    ({{ $user->department->title }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-form-error field="user_id" />
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Zimmet Cihazı</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="tabler:devices"></iconify-icon>
                                    </span>
                                    <select class="form-select" name="debit_device_id" required>
                                        <option value="">Cihaz Seçiniz</option>
                                        @foreach ($devices as $device)
                                            <option value="{{ $device->id }}"
                                                {{ (old('debit_device_id') ?? ($item->debit_device_id ?? '')) == $device->id ? 'selected' : '' }}>
                                                {{ $device->name }} - {{ $device->brand }} {{ $device->model }} ({{ $device->serial_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-form-error field="debit_device_id" />
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Başlangıç Tarihi</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="tabler:calendar"></iconify-icon>
                                    </span>
                                    <input type="date" class="form-control" name="start_date" id="start_date" required
                                        value="{{ old('start_date') ?? ($item->start_date ?? date('Y-m-d')) }}">
                                    <x-form-error field="start_date" />
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notlar</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="tabler:notes"></iconify-icon>
                                    </span>
                                    <textarea class="form-control" name="notes" placeholder="Notlar (opsiyonel)" rows="3">{{ old('notes') ?? ($item->notes ?? '') }}</textarea>
                                    <x-form-error field="notes" />
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

@section('script')
    <script>
        $(document).ready(function() {
            // Başlangıç tarihi seçildiğinde bitiş tarihini de aynı tarih yap
            $("#start_date").on("change", function() {
                var selectedDate = $(this).val();
                if (selectedDate) {
                    $("#end_date").val(selectedDate);
                }
            });
        });
    </script>
@endsection
