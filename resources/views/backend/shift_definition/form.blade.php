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

                        @if($errors->has('general'))
                            <div class="alert alert-danger">
                                {{ $errors->first('general') }}
                            </div>
                        @endif

                        <div class="row gy-3">
                            <div class="col-12">
                                <label class="form-label">Vardiya Adı</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="icon-park-outline:branch-one"></iconify-icon>
                                    </span>
                                    <input type="text" class="form-control" name="title"
                                        placeholder="Lütfen vardiya adı giriniz"
                                        value="{{ old('title') ?? ($item->title ?? '') }}">
                                    <x-form-error field="title" />
                                </div>
                            </div>

                            <!-- Vardiya Geçerlilik Tarihleri -->
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Başlangıç Tarihi</label>
                                        <div class="icon-field">
                                            <span class="icon">
                                                <iconify-icon icon="solar:calendar-outline"></iconify-icon>
                                            </span>
                                            <input type="date" class="form-control" name="start_date"
                                                value="{{ old('start_date') ?? ($item->start_date ?? '') }}">
                                            <x-form-error field="start_date" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Bitiş Tarihi</label>
                                        <div class="icon-field">
                                            <span class="icon">
                                                <iconify-icon icon="solar:calendar-outline"></iconify-icon>
                                            </span>
                                            <input type="date" class="form-control" name="end_date"
                                                value="{{ old('end_date') ?? ($item->end_date ?? '') }}">
                                            <x-form-error field="end_date" />
                                        </div>
                                        <small class="form-text text-muted">Boş bırakırsanız vardiya süresiz olacaktır</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Haftalık Çalışma Saatleri -->
                            <div class="col-12">
                                <h6 class="mb-3">Haftalık Çalışma Saatleri</h6>

                                <!-- Hızlı Ayarlar -->
                                <div class="card bg-light mb-3">
                                    <div class="card-body p-3">
                                        <h6 class="mb-2">Hızlı Ayarlar</h6>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="setWeekdays">
                                                Hafta İçi (Pzt-Cum)
                                            </button>
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="setAllDays">
                                                Tüm Günler
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="clearAll">
                                                Tümünü Temizle
                                            </button>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-3">
                                                <input type="time" class="form-control form-control-sm" id="quickStart" placeholder="Başlangıç">
                                            </div>
                                            <div class="col-md-3">
                                                <input type="time" class="form-control form-control-sm" id="quickEnd" placeholder="Bitiş">
                                            </div>
                                            <div class="col-md-3">
                                                <button type="button" class="btn btn-success btn-sm" id="applyQuick">Uygula</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row gy-3">
                                    @php
                                        $weekdays = [
                                            'monday' => 'Pazartesi',
                                            'tuesday' => 'Salı',
                                            'wednesday' => 'Çarşamba',
                                            'thursday' => 'Perşembe',
                                            'friday' => 'Cuma',
                                            'saturday' => 'Cumartesi',
                                            'sunday' => 'Pazar'
                                        ];
                                    @endphp

                                    @foreach($weekdays as $day => $dayName)
                                    <div class="col-12">
                                        <div class="card border-light">
                                            <div class="card-body p-3">
                                                <div class="row align-items-center">
                                                    <div class="col-md-3">
                                                        <strong>{{ $dayName }}</strong>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small">Başlangıç Saati</label>
                                                        <input type="time" class="form-control"
                                                               name="{{ $day }}_start"
                                                               value="{{ old($day.'_start') ?? ($item->{$day.'_start'} ?? '') }}">
                                                        <x-form-error field="{{ $day }}_start" />
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small">Bitiş Saati</label>
                                                        <input type="time" class="form-control"
                                                               name="{{ $day }}_end"
                                                               value="{{ old($day.'_end') ?? ($item->{$day.'_end'} ?? '') }}">
                                                        <x-form-error field="{{ $day }}_end" />
                                                    </div>
                                                    <div class="col-md-1">
                                                        <div class="form-check form-switch mt-4">
                                                            <input class="form-check-input day-toggle" type="checkbox"
                                                                   id="{{ $day }}_toggle"
                                                                   data-day="{{ $day }}"
                                                                   {{ (old($day.'_start') ?? ($item->{$day.'_start'} ?? '')) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="{{ $day }}_toggle">
                                                                <small>Aktif</small>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                </div>
                            </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Eski Alanlar (Geriye uyumluluk için gizli) -->
                            <div class="col-6" style="display: none;">
                                <input type="time" name="start_time" value="{{ old('start_time') ?? ($item->start_time ?? '') }}">
                            </div>
                            <div class="col-6" style="display: none;">
                                <input type="time" name="end_time" value="{{ old('end_time') ?? ($item->end_time ?? '') }}">
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
        // Gün toggle'ları için işlevsellik
    $('.day-toggle').change(function() {
        const day = $(this).data('day');
        const isChecked = $(this).is(':checked');
        const startInput = $(`input[name="${day}_start"]`);
        const endInput = $(`input[name="${day}_end"]`);

        if (!isChecked) {
            // Toggle kapalıysa saatleri temizle ve input'ları deaktif et
            startInput.val('');
            endInput.val('');
            startInput.prop('readonly', true).addClass('bg-light');
            endInput.prop('readonly', true).addClass('bg-light');
        } else {
            // Toggle açıksa input'ları aktif et
            startInput.prop('readonly', false).removeClass('bg-light');
            endInput.prop('readonly', false).removeClass('bg-light');
        }
    });

    // Sayfa yüklendiğinde mevcut durumu kontrol et
    $('.day-toggle').each(function() {
        const day = $(this).data('day');
        const startInput = $(`input[name="${day}_start"]`);
        const endInput = $(`input[name="${day}_end"]`);
        const hasValue = startInput.val() || endInput.val();

                if (!hasValue) {
            $(this).prop('checked', false);
            startInput.prop('readonly', true).addClass('bg-light');
            endInput.prop('readonly', true).addClass('bg-light');
        } else {
            $(this).prop('checked', true);
            startInput.prop('readonly', false).removeClass('bg-light');
            endInput.prop('readonly', false).removeClass('bg-light');
        }
    });

        // Hızlı ayarlar buton işlevleri
    $('#setWeekdays').click(function() {
        // Önce tüm günleri temizle
        $('.day-toggle').prop('checked', false).trigger('change');

        // Sadece hafta içi günleri seç
        const weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        weekdays.forEach(day => {
            $(`#${day}_toggle`).prop('checked', true).trigger('change');
        });
    });

    $('#setAllDays').click(function() {
        $('.day-toggle').prop('checked', true).trigger('change');
    });

    $('#clearAll').click(function() {
        $('.day-toggle').prop('checked', false).trigger('change');
    });

    $('#applyQuick').click(function() {
        const startTime = $('#quickStart').val();
        const endTime = $('#quickEnd').val();

        if (!startTime || !endTime) {
            alert('Lütfen başlangıç ve bitiş saatini giriniz.');
            return;
        }

        // Sadece seçili (checked) günlere saatleri uygula
        $('.day-toggle:checked').each(function() {
            const day = $(this).data('day');
            $(`input[name="${day}_start"]`).val(startTime);
            $(`input[name="${day}_end"]`).val(endTime);
        });
    });

    // Form submit edilmeden önce readonly input'ları temizle
    $('form').submit(function() {
        $('input[readonly]').each(function() {
            if ($(this).hasClass('bg-light')) {
                $(this).val('');
            }
        });
    });
});
</script>
@endsection
