@extends('layout.layout')

@php
    $title = 'Tatil Günleri';
    $subTitle = 'Tatil Günleri / Takvim';
    $script = '<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
               <script src="' . asset('assets/js/flatpickr.js') . '"></script>
                 <script>
                     // Flat pickr or date picker js
                     function getDatePicker(receiveID) {
                         flatpickr(receiveID, {
                             enableTime: false,
                             dateFormat: "d/m/Y",
                         });
                     }
                     getDatePicker("#startDate");
                     getDatePicker("#endDate");

                     getDatePicker("#editstartDate");
                     getDatePicker("#editendDate");
                 </script>';
@endphp

<style>
    /* Takvimde tatil günlerinin daha iyi görüntülenmesi için özel CSS */
    .fc-event {
        padding: 2px 4px;
        margin: 1px 0;
        border-radius: 3px;
        font-size: 0.85em;
        font-weight: 500;
    }

    .fc-daygrid-event {
        white-space: normal !important;
        align-items: center;
    }

    .fc-daygrid-day-events {
        margin-top: 1px;
        margin-bottom: 1px;
    }

    .fc-h-event .fc-event-title {
        padding: 1px;
        display: block;
    }

    /* Kendine özgü tatil günleri renkleri */
    .holiday-official {
        background-color: #ea5455 !important;
        border-color: #ea5455 !important;
        color: white !important;
    }

    .holiday-weekend {
        background-color: #ff9f43 !important;
        border-color: #ff9f43 !important;
        color: white !important;
    }

    .holiday-half-day {
        background-color: #00cfe8 !important;
        border-color: #00cfe8 !important;
        color: white !important;
    }

    .holiday-custom {
        background-color: #28c76f !important;
        border-color: #28c76f !important;
        color: white !important;
    }

    /* Daha belirgin etkinlik gösterimi */
    .fc .fc-daygrid-event {
        z-index: 6;
        margin-top: 1px;
        font-weight: 600;
        min-height: 22px; /* Tatil türü için yer açalım */
    }

    /* Tatil türü metni için özel stil */
    .fc-event-content {
        padding: 1px 2px;
    }

    .fc-event-title {
        line-height: 1.2;
    }

    .fc-event-type {
        line-height: 1;
        font-style: italic;
    }

    .fc .fc-daygrid-day.fc-day-today {
        background-color: rgba(72, 127, 255, 0.1) !important;
    }

    .fc .fc-daygrid-day-number {
        font-weight: 500;
    }

    .fc .fc-col-header-cell {
        background-color: #f8f8f8;
        font-weight: 600;
    }
</style>

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Başarılı!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Hata!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row gy-4">
        <div class="col-xxl-3 col-lg-4">
            <div class="card h-100 p-0">
                <div class="card-body p-24">
                    <button type="button"
                        class="btn btn-primary text-sm btn-sm px-12 py-12 w-100 radius-8 d-flex align-items-center gap-2 mb-16"
                        data-bs-toggle="modal" data-bs-target="#exampleModal">
                        <iconify-icon icon="fa6-regular:square-plus" class="icon text-lg line-height-1"></iconify-icon>
                        Yeni Tatil Günü Ekle
                    </button>

                    <a href="{{ route('backend.official_holiday_list') }}"
                        class="btn btn-outline-secondary text-sm btn-sm px-12 py-12 w-100 radius-8 d-flex align-items-center gap-2 mb-32">
                        <iconify-icon icon="lucide:arrow-left" class="icon text-lg line-height-1"></iconify-icon>
                        Listeye Dön
                    </a>

                    <div class="mt-32">
                        <h5>Gelecek Tatiller</h3>
                            <div id="holiday-list">
                                @foreach ($holidays as $holiday)
                                    <div
                                        class="event-item d-flex align-items-center justify-content-between gap-4 pb-16 mb-16 border border-start-0 border-end-0 border-top-0">
                                        <div class="">
                                            <div class="d-flex align-items-center gap-10">
                                                <span
                                                    class="w-12-px h-12-px bg-success-600 rounded-circle fw-medium"></span>
                                                <span class="text-secondary-light">{{ $holiday->start_date }} -
                                                    {{ $holiday->end_date }}</span>
                                            </div>
                                            <span
                                                class="text-primary-light fw-semibold text-md mt-4">{{ $holiday->title }}</span>
                                        </div>
                                        <div class="dropdown">
                                            <button type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <iconify-icon icon="entypo:dots-three-vertical"
                                                    class="icon text-secondary-light"></iconify-icon>
                                            </button>
                                            <ul class="dropdown-menu p-12 border bg-base shadow">
                                                <li>
                                                    <a href="{{ route('backend.official_holiday_form', $holiday->id) }}"
                                                        class="dropdown-item px-16 py-8 rounded text-secondary-light bg-hover-success-100 text-hover-success-600 d-flex align-items-center gap-10">
                                                        <iconify-icon icon="lucide:edit"
                                                            class="icon text-lg line-height-1"></iconify-icon>
                                                        Düzenle
                                                    </a>
                                                </li>
                                                <li>
                                                    <button type="button"
                                                        class="delete-item dropdown-item px-16 py-8 rounded text-secondary-light bg-hover-danger-100 text-hover-danger-600 d-flex align-items-center gap-10"
                                                        data-id="{{ $holiday->id }}" onclick="deleteHoliday(this)">
                                                        <iconify-icon icon="fluent:delete-24-regular"
                                                            class="icon text-lg line-height-1"></iconify-icon>
                                                        Sil
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="col-xxl-9 col-lg-8">
            <div class="card h-100 p-0">
                <div class="card-body p-24">
                    <div id='wrap'>
                        <div id='calendar'></div>
                        <div style='clear:both'></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Add Event -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header py-16 px-24 border border-top-0 border-start-0 border-end-0">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Yeni Tatil Günü Ekle</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                                <div class="modal-body p-24">
                    <form id="holidayForm" action="{{ route('backend.official_holiday_save') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-12 mb-20">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Tatil Günü Başlığı :
                                </label>
                                <input type="text" name="title" class="form-control radius-8 @error('title') is-invalid @enderror"
                                    placeholder="Tatil Günü Başlığı" value="{{ old('title') }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-20">
                                <label for="startDate"
                                    class="form-label fw-semibold text-primary-light text-sm mb-8">Başlangıç Tarihi</label>
                                <div class="position-relative">
                                    <input class="form-control radius-8 bg-base @error('start_date') is-invalid @enderror"
                                        name="start_date" id="startDate" type="text" placeholder="03/12/2024"
                                        value="{{ old('start_date') }}" required>
                                    <span class="position-absolute end-0 top-50 translate-middle-y me-12 line-height-1">
                                        <iconify-icon icon="solar:calendar-linear" class="icon text-lg"></iconify-icon>
                                    </span>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 mb-20">
                                <label for="endDate" class="form-label fw-semibold text-primary-light text-sm mb-8">Bitiş
                                    Tarihi </label>
                                <div class="position-relative">
                                    <input class="form-control radius-8 bg-base @error('end_date') is-invalid @enderror"
                                        name="end_date" id="endDate" type="text" placeholder="03/12/2024"
                                        value="{{ old('end_date') }}" required>
                                    <span class="position-absolute end-0 top-50 translate-middle-y me-12 line-height-1">
                                        <iconify-icon icon="solar:calendar-linear" class="icon text-lg"></iconify-icon>
                                    </span>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12 mb-20">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Tatil Türü
                                </label>
                                @error('type_id')
                                    <div class="text-danger small mb-2">{{ $message }}</div>
                                @enderror
                                <div class="d-flex align-items-center flex-wrap gap-28">
                                    @foreach ($holidayTypes as $index => $holidayType)
                                        <div
                                            class="form-check checked-{{ $holidayType->color }} d-flex align-items-center gap-2">
                                            <input class="form-check-input" type="radio" name="type_id"
                                                value="{{ $holidayType->id }}" id="PersonalRadio{{ $holidayType->id }}"
                                                {{ ($index === 0 && !old('type_id')) || old('type_id') == $holidayType->id ? 'checked' : '' }}>
                                            <label
                                                class="text-{{ $holidayType->color }} form-check-label line-height-1 fw-medium text-sm d-flex align-items-center gap-1"
                                                for="PersonalRadio{{ $holidayType->id }}">
                                                <span
                                                    class="w-8-px h-8-px bg-{{ $holidayType->color }}-600 rounded-circle"></span>
                                                {{ $holidayType->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>



                            <div class="col-12 mb-20">
                                <label for="desc"
                                    class="form-label fw-semibold text-primary-light text-sm mb-8">Açıklama</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                    name="description" id="desc" rows="4" cols="50"
                                    placeholder="Lütfen açıklama giriniz">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <input type="hidden" name="is_active" value="1">

                            <div class="d-flex align-items-center justify-content-center gap-3 mt-24">
                                <button type="button" data-bs-dismiss="modal"
                                    class="border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-40 py-11 radius-8">
                                    İptal
                                </button>
                                <button type="submit"
                                    class="btn btn-primary border border-primary-600 text-md px-24 py-12 radius-8">
                                    Kaydet
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal View Event -->
    <div class="modal fade" id="exampleModalView" tabindex="-1" aria-labelledby="exampleModalViewLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header py-16 px-24 border border-top-0 border-start-0 border-end-0">
                    <h1 class="modal-title fs-5" id="exampleModalViewLabel">Detayları Görüntüle</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-24">
                    <div class="mb-12">
                        <span class="text-secondary-light txt-sm fw-medium">Başlık</span>
                        <h6 class="text-primary-light fw-semibold text-md mb-0 mt-4">Tatil Günü Başlığı</h6>
                    </div>
                    <div class="mb-12">
                        <span class="text-secondary-light txt-sm fw-medium">Başlangıç Tarihi</span>
                        <h6 class="text-primary-light fw-semibold text-md mb-0 mt-4">25 Jan 2024, 10:30AM</h6>
                    </div>
                    <div class="mb-12">
                        <span class="text-secondary-light txt-sm fw-medium">Bitiş Tarihi</span>
                        <h6 class="text-primary-light fw-semibold text-md mb-0 mt-4">25 Jan 2024, 2:30AM</h6>
                    </div>
                    <div class="mb-12">
                        <span class="text-secondary-light txt-sm fw-medium">Açıklama</span>
                        <h6 class="text-primary-light fw-semibold text-md mb-0 mt-4">N/A</h6>
                    </div>
                    <div class="mb-12">
                        <span class="text-secondary-light txt-sm fw-medium">Etiket</span>
                        <h6 class="text-primary-light fw-semibold text-md mb-0 mt-4 d-flex align-items-center gap-2">
                            <span class="w-8-px h-8-px bg-success-600 rounded-circle"></span>
                            İş
                        </h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Event -->
    <div class="modal fade" id="exampleModalEdit" tabindex="-1" aria-labelledby="exampleModalEditLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-header py-16 px-24 border border-top-0 border-start-0 border-end-0">
                    <h1 class="modal-title fs-5" id="exampleModalEditLabel">Tatil Günü Düzenle</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-24">
                    <form id="editHolidayForm" action="{{ route('backend.official_holiday_save') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" id="editHolidayId">
                        <input type="hidden" name="type_id" value="1" id="editTypeId">
                        <div class="row">
                            <div class="col-12 mb-20">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Tatil Günü Başlığı :
                                </label>
                                <input type="text" name="title" class="form-control radius-8" placeholder="Tatil Günü Başlığı ">
                            </div>
                            <div class="col-md-6 mb-20">
                                <label for="editstartDate"
                                    class="form-label fw-semibold text-primary-light text-sm mb-8">Başlangıç Tarihi</label>
                                <div class=" position-relative">
                                    <input class="form-control radius-8 bg-base" name="start_date" id="editstartDate" type="text"
                                        placeholder="03/12/2024, 10:30 AM">
                                    <span class="position-absolute end-0 top-50 translate-middle-y me-12 line-height-1">
                                        <iconify-icon icon="solar:calendar-linear" class="icon text-lg"></iconify-icon>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-20">
                                <label for="editendDate"
                                    class="form-label fw-semibold text-primary-light text-sm mb-8">Bitiş Tarihi </label>
                                <div class=" position-relative">
                                    <input class="form-control radius-8 bg-base" name="end_date" id="editendDate" type="text"
                                        placeholder="03/12/2024, 2:30 PM">
                                    <span class="position-absolute end-0 top-50 translate-middle-y me-12 line-height-1">
                                        <iconify-icon icon="solar:calendar-linear" class="icon text-lg"></iconify-icon>
                                    </span>
                                </div>
                            </div>
                            <div class="col-12 mb-20">
                                <label class="form-label fw-semibold text-primary-light text-sm mb-8">Etiket </label>
                                <div class="d-flex align-items-center flex-wrap gap-28">
                                    <div class="form-check checked-success d-flex align-items-center gap-2">
                                        <input class="form-check-input" type="radio" name="label" id="editPersonal">
                                        <label
                                            class="form-check-label line-height-1 fw-medium text-secondary-light text-sm d-flex align-items-center gap-1"
                                            for="editPersonal">
                                            <span class="w-8-px h-8-px bg-success-600 rounded-circle"></span>
                                            Personal
                                        </label>
                                    </div>
                                    <div class="form-check checked-primary d-flex align-items-center gap-2">
                                        <input class="form-check-input" type="radio" name="label" id="editBusiness">
                                        <label
                                            class="form-check-label line-height-1 fw-medium text-secondary-light text-sm d-flex align-items-center gap-1"
                                            for="editBusiness">
                                            <span class="w-8-px h-8-px bg-primary-600 rounded-circle"></span>
                                            Business
                                        </label>
                                    </div>
                                    <div class="form-check checked-warning d-flex align-items-center gap-2">
                                        <input class="form-check-input" type="radio" name="label" id="editFamily">
                                        <label
                                            class="form-check-label line-height-1 fw-medium text-secondary-light text-sm d-flex align-items-center gap-1"
                                            for="editFamily">
                                            <span class="w-8-px h-8-px bg-warning-600 rounded-circle"></span>
                                            Family
                                        </label>
                                    </div>
                                    <div class="form-check checked-secondary d-flex align-items-center gap-2">
                                        <input class="form-check-input" type="radio" name="label"
                                            id="editImportant">
                                        <label
                                            class="form-check-label line-height-1 fw-medium text-secondary-light text-sm d-flex align-items-center gap-1"
                                            for="editImportant">
                                            <span class="w-8-px h-8-px bg-lilac-600 rounded-circle"></span>
                                            Important
                                        </label>
                                    </div>
                                    <div class="form-check checked-danger d-flex align-items-center gap-2">
                                        <input class="form-check-input" type="radio" name="label" id="editHoliday">
                                        <label
                                            class="form-check-label line-height-1 fw-medium text-secondary-light text-sm d-flex align-items-center gap-1"
                                            for="editHoliday">
                                            <span class="w-8-px h-8-px bg-danger-600 rounded-circle"></span>
                                            Holiday
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mb-20">
                                <label for="desc"
                                    class="form-label fw-semibold text-primary-light text-sm mb-8">Açıklama</label>
                                <textarea class="form-control" name="description" id="editdesc" rows="4" cols="50" placeholder="Write some text"></textarea>
                            </div>

                            <div class="d-flex align-items-center justify-content-center gap-3 mt-24">
                                <button type="reset"
                                    class="border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-40 py-11 radius-8">
                                    İptal
                                </button>
                                <button type="submit"
                                    class="btn btn-primary border border-primary-600 text-md px-24 py-12 radius-8">
                                    Kaydet
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Delete Event -->
    <div class="modal fade" id="exampleModalDelete" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog modal-dialog-centered">
            <div class="modal-content radius-16 bg-base">
                <div class="modal-body p-24 text-center">
                    <span class="mb-16 fs-1 line-height-1 text-danger">
                        <iconify-icon icon="fluent:delete-24-regular" class="menu-icon"></iconify-icon>
                    </span>
                    <h6 class="text-lg fw-semibold text-primary-light mb-0">Bu tatili silmek istediğinize emin misiniz?
                    </h6>
                    <div class="d-flex align-items-center justify-content-center gap-3 mt-24">
                        <button type="reset"
                            class="w-50 border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-40 py-11 radius-8">
                            İptal
                        </button>
                        <button type="button" id="confirmDeleteBtn"
                            class="w-50 btn btn-primary border border-primary-600 text-md px-24 py-12 radius-8">
                            Sil
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function deleteHoliday(button) {
            const holidayId = button.getAttribute('data-id');
            if (confirm('Bu tatili silmek istediğinize emin misiniz?')) {
                fetch('{{ route('backend.official_holiday_delete') }}', {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: holidayId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload(); // Sayfayı yenile
                        } else {
                            alert(data.message || 'Silme işlemi başarısız oldu.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Silme işlemi sırasında bir hata oluştu.');
                    });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const holidayForm = document.getElementById('holidayForm');
            const editHolidayForm = document.getElementById('editHolidayForm');

            // Modal içinde validation hatalarını göster
            function showValidationErrors(errors, modalId) {
                const modal = document.querySelector(modalId + ' .modal-body');

                // Önceki hata mesajlarını temizle
                const existingErrors = modal.querySelector('.validation-errors');
                if (existingErrors) {
                    existingErrors.remove();
                }

                // Yeni hata mesajlarını oluştur
                if (errors && Object.keys(errors).length > 0) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger validation-errors';

                    let errorHtml = '<ul class="mb-0">';
                    Object.values(errors).forEach(fieldErrors => {
                        if (Array.isArray(fieldErrors)) {
                            fieldErrors.forEach(error => {
                                errorHtml += `<li>${error}</li>`;
                            });
                        } else {
                            errorHtml += `<li>${fieldErrors}</li>`;
                        }
                    });
                    errorHtml += '</ul>';

                    errorDiv.innerHTML = errorHtml;

                    // Form'dan önce hata mesajını ekle
                    const form = modal.querySelector('form');
                    form.parentNode.insertBefore(errorDiv, form);
                }
            }

            // Success mesajını göster
            function showSuccessMessage(message, modalId) {
                const modal = document.querySelector(modalId + ' .modal-body');

                // Önceki mesajları temizle
                const existingMessages = modal.querySelector('.alert');
                if (existingMessages) {
                    existingMessages.remove();
                }

                const successDiv = document.createElement('div');
                successDiv.className = 'alert alert-success';
                successDiv.innerHTML = `<strong>Başarılı!</strong> ${message}`;

                const form = modal.querySelector('form');
                form.parentNode.insertBefore(successDiv, form);

                // 2 saniye sonra modal'ı kapat ve sayfayı yenile
                setTimeout(() => {
                    const modalElement = document.querySelector(modalId);
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }
                    location.reload();
                }, 2000);
            }

            // Yeni tatil ekleme formu
            if (holidayForm) {
                holidayForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);

                    fetch('{{ route('backend.official_holiday_save') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showSuccessMessage(data.message || 'Tatil günü başarıyla eklendi.', '#exampleModal');
                            holidayForm.reset();
                        } else if (data.errors) {
                            showValidationErrors(data.errors, '#exampleModal');
                        } else {
                            showValidationErrors({'genel': [data.message || 'Bir hata oluştu.']}, '#exampleModal');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showValidationErrors({'genel': ['Bir hata oluştu. Lütfen tekrar deneyin.']}, '#exampleModal');
                    });
                });
            }

            // Düzenleme formu
            if (editHolidayForm) {
                editHolidayForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);

                    fetch('{{ route('backend.official_holiday_save') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showSuccessMessage(data.message || 'Tatil günü başarıyla güncellendi.', '#exampleModalEdit');
                            editHolidayForm.reset();
                        } else if (data.errors) {
                            showValidationErrors(data.errors, '#exampleModalEdit');
                        } else {
                            showValidationErrors({'genel': [data.message || 'Bir hata oluştu.']}, '#exampleModalEdit');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showValidationErrors({'genel': ['Bir hata oluştu. Lütfen tekrar deneyin.']}, '#exampleModalEdit');
                    });
                });
            }
        });

        // Calendar initialization
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            if (calendarEl) {
                // Takvimi oluştur
                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    locale: 'tr',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    events: {
                        url: '{{ route('backend.official_holiday_get_events') }}',
                        method: 'GET',
                        extraParams: function() {
                            return {};
                        },
                        failure: function() {
                            alert('Tatil günleri yüklenirken bir hata oluştu!');
                        }
                    },
                    displayEventTime: false,
                    eventDisplay: 'block',
                    fixedWeekCount: false,
                    showNonCurrentDates: false,

                    eventDidMount: function(info) {
                        const event = info.event;
                        const props = event.extendedProps;

                        // Tatil türüne göre sınıf ekle
                        info.el.classList.add('holiday-custom');

                        // Tatil türü rengine göre ek stil
                        if (props.type_color) {
                            const typeElement = info.el.querySelector('.fc-event-type');
                            if (typeElement) {
                                typeElement.style.color = 'rgba(255, 255, 255, 0.9)';
                            }
                        }
                    },

                    eventContent: function(arg) {
                        const event = arg.event;
                        const props = event.extendedProps;

                        // Tatil türü bilgisini al
                        const typeName = props.type_name || 'Tatil';

                        return {
                            html: `
                                <div class="fc-event-content">
                                    <div class="fc-event-title" style="font-weight: 600; font-size: 11px;">${event.title}</div>
                                    <div class="fc-event-type" style="font-size: 9px; opacity: 0.8; margin-top: 1px;">${typeName}</div>
                                </div>
                            `
                        };
                    },

                    datesSet: function(info) {
                        calendar.refetchEvents();
                    },

                    eventClick: function(info) {
                        const event = info.event;
                        const props = event.extendedProps;

                        document.querySelector('#exampleModalView .modal-title').textContent =
                            'Tatil Detayları';
                        document.querySelector('#exampleModalView .mb-12:nth-child(1) h6').textContent =
                            event.title;
                        document.querySelector('#exampleModalView .mb-12:nth-child(2) h6').textContent =
                            event.start.toLocaleDateString('tr-TR');
                        document.querySelector('#exampleModalView .mb-12:nth-child(3) h6').textContent =
                            event.end ? new Date(event.end.getTime() - 86400000).toLocaleDateString(
                                'tr-TR') : event.start.toLocaleDateString('tr-TR');
                        document.querySelector('#exampleModalView .mb-12:nth-child(4) h6').textContent =
                            props.description || 'Açıklama yok';

                        // Tatil türü bilgisini göster
                        let tagColor = 'bg-success-600';
                        let tagText = props.type_name || 'Özel Tatil';

                        // Tatil türüne göre renk belirle
                        if (props.type_color) {
                            switch(props.type_color) {
                                case 'danger':
                                    tagColor = 'bg-danger-600';
                                    break;
                                case 'primary':
                                    tagColor = 'bg-primary-600';
                                    break;
                                case 'warning':
                                    tagColor = 'bg-warning-600';
                                    break;
                                case 'info':
                                    tagColor = 'bg-info-600';
                                    break;
                                case 'secondary':
                                    tagColor = 'bg-secondary-600';
                                    break;
                                default:
                                    tagColor = 'bg-success-600';
                            }
                        }

                        const tagSpan = document.querySelector(
                            '#exampleModalView .mb-12:nth-child(5) h6 span');
                        tagSpan.className = `w-8-px h-8-px ${tagColor} rounded-circle`;
                        document.querySelector('#exampleModalView .mb-12:nth-child(5) h6').childNodes[2]
                            .textContent = ` ${tagText}`;

                        const viewModal = new bootstrap.Modal(document.getElementById(
                            'exampleModalView'));
                        viewModal.show();
                    }
                });

                calendar.render();
            }
        });
    </script>
@endsection
