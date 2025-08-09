@extends('layout.layout')
@php
    $title = 'Etkinlik Katılımcıları';
    $subTitle = $event->title . ' - Katılımcılar';
@endphp

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ $subTitle }}</h5>
            <div>
                <button type="button" class="btn btn-success btn-sm me-2" id="approveSelected">Seçilenleri Onayla</button>
                <button type="button" class="btn btn-danger btn-sm" id="rejectSelected">Seçilenleri Reddet</button>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                Toplam Kontenjan: {{ $event->quota }} |
                Onaylanan Katılımcı: {{ $event->approvedParticipants()->count() }} |
                Bekleyen İstek: {{ $event->pendingParticipants()->count() }}
            </div>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th width="50">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th>Katılımcı</th>
                            <th>İstek Tarihi</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($event->participants as $participant)
                            <tr>
                                <td>
                                    @if($participant->status === 'pending')
                                        <input type="checkbox" class="form-check-input participant-checkbox" value="{{ $participant->id }}">
                                    @endif
                                </td>
                                <td>{{ $participant->user?->name.' '.$participant->user?->surname}}</td>
                                <td>{{ $participant->created_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    @if($participant->status === 'pending')
                                        <span class="badge bg-warning">Beklemede</span>
                                    @elseif($participant->status === 'approved')
                                        <span class="badge bg-success">Onaylandı</span>
                                    @else
                                        <span class="badge bg-danger">Reddedildi</span>
                                    @endif
                                </td>
                                <td>
                                    @if($participant->status === 'pending')
                                        <button type="button"
                                            class="btn btn-success btn-sm status-btn"
                                            data-id="{{ $participant->id }}"
                                            data-status="approved">
                                            Onayla
                                        </button>
                                        <button type="button"
                                            class="btn btn-danger btn-sm status-btn"
                                            data-id="{{ $participant->id }}"
                                            data-status="rejected">
                                            Reddet
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    // SweetAlert varsayılan ayarları
    const SwalConfig = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success btn-sm ms-2',
            cancelButton: 'btn btn-danger btn-sm',
            popup: 'swal2-popup-square'
        },
        buttonsStyling: false,
        width: 'auto',
        padding: '1rem',
        showClass: {
            popup: 'animate__animated animate__fadeIn animate__faster'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOut animate__faster'
        }
    });

    // CSS stilini head'e ekle
    const style = document.createElement('style');
    style.textContent = `
        .swal2-popup-square {
            width: 520px !important;
            padding: 0.5rem !important;
        }
    `;
    document.head.appendChild(style);

    $(document).ready(function() {
        // Tekli onaylama/reddetme işlemi
        $('.status-btn').click(function() {
            const participantId = $(this).data('id');
            const status = $(this).data('status');
            const isApprove = status === 'approved';

            SwalConfig.fire({
                title: 'Emin misiniz?',
                html: `<small>${isApprove ? 'Bu katılımcının etkinliğe katılımı onaylanacaktır.' : 'Bu katılımcının etkinlik katılım isteği reddedilecektir.'}</small>`,
                icon: isApprove ? 'question' : 'warning',
                showCancelButton: true,
                confirmButtonText: isApprove ? 'Onayla' : 'Reddet',
                cancelButtonText: 'İptal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    updateParticipantStatus(participantId, status);
                }
            });
        });

        // Tümünü seç/kaldır
        $('#selectAll').change(function() {
            $('.participant-checkbox').prop('checked', $(this).is(':checked'));
        });

        // Toplu onaylama
        $('#approveSelected').click(function() {
            const selectedIds = getSelectedParticipants();
            if (selectedIds.length === 0) {
                SwalConfig.fire({
                    title: 'Uyarı',
                    html: '<small>Lütfen katılımcı seçiniz</small>',
                    icon: 'warning',
                    confirmButtonText: 'Tamam'
                });
                return;
            }

            SwalConfig.fire({
                title: 'Emin misiniz?',
                html: `<small>Seçili <b>${selectedIds.length}</b> katılımcının etkinliğe katılımı onaylanacaktır.</small>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Onayla',
                cancelButtonText: 'İptal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    bulkUpdateStatus(selectedIds, 'approved');
                }
            });
        });

        // Toplu reddetme
        $('#rejectSelected').click(function() {
            const selectedIds = getSelectedParticipants();
            if (selectedIds.length === 0) {
                SwalConfig.fire({
                    title: 'Uyarı',
                    html: '<small>Lütfen katılımcı seçiniz</small>',
                    icon: 'warning',
                    confirmButtonText: 'Tamam'
                });
                return;
            }

            SwalConfig.fire({
                title: 'Emin misiniz?',
                html: `<small>Seçili <b>${selectedIds.length}</b> katılımcının etkinlik katılım isteği reddedilecektir.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Reddet',
                cancelButtonText: 'İptal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    bulkUpdateStatus(selectedIds, 'rejected');
                }
            });
        });

        // Seçili katılımcıları al
        function getSelectedParticipants() {
            return $('.participant-checkbox:checked').map(function() {
                return $(this).val();
            }).get();
        }

        // Tekli durum güncelleme
        function updateParticipantStatus(participantId, status) {
            $.ajax({
                url: '{{ route("backend.event_participant_status") }}',
                type: 'POST',
                data: {
                    participant_id: participantId,
                    status: status,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        SwalConfig.fire({
                            title: 'Uyarı',
                            html: `<small>${response.message || 'Bir hata oluştu'}</small>`,
                            icon: 'warning',
                            confirmButtonText: 'Tamam'
                        });
                    }
                },
                error: function(xhr) {
                    SwalConfig.fire({
                        title: 'Uyarı',
                        html: `<small>${xhr.responseJSON?.message || 'Bir hata oluştu'}</small>`,
                        icon: 'warning',
                        confirmButtonText: 'Tamam'
                    });
                }
            });
        }

        // Toplu durum güncelleme
        function bulkUpdateStatus(participantIds, status) {
            $.ajax({
                url: '{{ route("backend.event_participant_bulk_status") }}',
                type: 'POST',
                data: {
                    participant_ids: participantIds,
                    status: status,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        SwalConfig.fire({
                            title: 'Uyarı',
                            html: `<small>${response.message || 'Bir hata oluştu'}</small>`,
                            icon: 'warning',
                            confirmButtonText: 'Tamam'
                        });
                    }
                },
                error: function(xhr) {
                    SwalConfig.fire({
                        title: 'Uyarı',
                        html: `<small>${xhr.responseJSON?.message || 'Bir hata oluştu'}</small>`,
                        icon: 'warning',
                        confirmButtonText: 'Tamam'
                    });
                }
            });
        }
    });
</script>
@endsection
