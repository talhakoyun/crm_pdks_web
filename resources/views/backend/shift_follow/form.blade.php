@extends('layout.layout')
@php
    $title = $container->title . (is_null($item->id) ? ' Ekle' : ' Düzenle');
    $subTitle = $container->title . (is_null($item->id) ? ' Ekle' : ' Düzenle');
@endphp

@section('content')
    <div class="row gy-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ $container->title }} {{ !is_null($item->id) ? 'Düzenle' : 'Ekle' }}</h5>
                    <a href="{{ route('backend.' . $container->page . '_list') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Listeye Dön
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.' . $container->page . '_save', ['unique' => $item->id]) }}"
                        method="POST">
                        @csrf
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Giriş/Çıkış Tipi</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:login"></iconify-icon>
                                    </span>
                                    <select class="form-select select2" name="shift_follow_type_id">
                                        <option value="">Tip Seçiniz</option>
                                        @foreach ($followTypes as $shiftFollowType)
                                            <option value="{{ $shiftFollowType->id }}"
                                                {{ (old('shift_follow_type_id') ?? ($item->shift_follow_type_id ?? '')) == $shiftFollowType->id ? 'selected' : '' }}>
                                                {{ $shiftFollowType->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <x-form-error field="shift_follow_type_id" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Personel</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:user"></iconify-icon>
                                    </span>
                                    <select class="form-select select2" name="user_id" id="user_id">
                                        <option value="">Personel Seçiniz</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}"
                                                {{ (old('user_id') ?? ($item->user_id ?? '')) == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} {{ $user->surname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <x-form-error field="user_id" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Giriş Yapılan Şube</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:building"></iconify-icon>
                                    </span>
                                    <select class="form-select select2" name="enter_branch_id" id="enter_branch_id">
                                        <option value="">Önce personel seçiniz</option>
                                        @if($item->enter_branch_id)
                                            @php
                                                $enterBranch = \App\Models\Branch::find($item->enter_branch_id);
                                            @endphp
                                            @if($enterBranch)
                                                <option value="{{ $enterBranch->id }}" selected>{{ $enterBranch->title }}</option>
                                            @endif
                                        @endif
                                    </select>
                                </div>
                                <x-form-error field="enter_branch_id" />
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Vardiya</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:calendar"></iconify-icon>
                                    </span>
                                    <select class="form-select select2" name="shift_id">
                                        <option value="">Vardiya Seçiniz</option>
                                        @foreach ($shifts as $shift)
                                            <option value="{{ $shift->id }}"
                                                {{ (old('shift_id') ?? ($item->shift_id ?? '')) == $shift->id ? 'selected' : '' }}>
                                                {{ $shift->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <x-form-error field="shift_id" />
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">İşlem Tarihi</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:calendar"></iconify-icon>
                                    </span>
                                    <input type="datetime-local" class="form-control" name="transaction_date"
                                        id="transaction_date" placeholder="Tarih ve saat seçiniz"
                                        value="{{ old('transaction_date') ?? ($item->transaction_date ?? '') }}">
                                </div>
                                <x-form-error field="transaction_date" />
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Açıklama</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="material-symbols:description-outline-sharp"></iconify-icon>
                                    </span>
                                    <textarea name="note" id="note" class="form-control" rows="3" placeholder="Açıklama giriniz">{{ old('note') ?? ($item->note ?? '') }}</textarea>
                                </div>
                                <x-form-error field="note" />
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary-600">Kaydet</button>
                                <a href="{{ route('backend.' . $container->page . '_list') }}" class="btn btn-secondary ms-2">
                                    <div class="d-flex align-items-center">
                                        <iconify-icon icon="carbon:close"></iconify-icon>
                                        <span>İptal</span>
                                    </div>
                                </a>
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
            console.log('Script yüklendi');

            if (typeof $ === 'undefined') {
                console.log('jQuery yüklenmemiş!');
                return;
            }

            console.log('jQuery yüklendi');
            console.log('Document ready çalıştı');

            // Select2 zaten layout'ta yükleniyor, sadece kontrol edelim
            console.log('Select2 kontrol ediliyor...');
            if (typeof $.fn.select2 !== 'undefined') {
                console.log('Select2 yüklü');
            } else {
                console.log('Select2 yüklenmemiş!');
            }

            // Personel seçildiğinde şubelerini yükle
            $('#user_id').on('change', function() {
                var userId = $(this).val();
                console.log('Personel seçildi, ID:', userId);
                var enterBranchSelect = $('#enter_branch_id');

                // Şube seçimini temizle
                enterBranchSelect.empty().append('<option value="">Şube seçiniz</option>');

                if (userId) {
                    console.log('AJAX isteği gönderiliyor...');
                    // AJAX ile personelin şubelerini getir
                    $.ajax({
                        url: '{{ route("backend.shift_follow_get_user_branches") }}',
                        type: 'POST',
                        data: {
                            user_id: userId,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            console.log('AJAX başarılı, response:', response);
                            if (response.branches && response.branches.length > 0) {
                                response.branches.forEach(function(branch) {
                                    enterBranchSelect.append('<option value="' + branch.id + '">' + branch.title + '</option>');
                                });

                                // Eğer düzenleme modundaysa ve mevcut şube varsa seç
                                var currentEnterBranchId = '{{ $item->enter_branch_id ?? "" }}';
                                if (currentEnterBranchId) {
                                    enterBranchSelect.val(currentEnterBranchId);
                                }
                            } else {
                                enterBranchSelect.append('<option value="">Bu personelin şubesi bulunamadı</option>');
                            }
                            enterBranchSelect.trigger('change');
                        },
                        error: function(xhr, status, error) {
                            console.log('AJAX hatası:', xhr.responseText);
                            enterBranchSelect.append('<option value="">Şube yüklenirken hata oluştu</option>');
                        }
                    });
                }
            });

            // Sayfa yüklendiğinde eğer personel seçiliyse şubelerini yükle
            var selectedUserId = $('#user_id').val();
            if (selectedUserId) {
                console.log('Sayfa yüklendi, personel seçili:', selectedUserId);
                $('#user_id').trigger('change');
            }
        });
    </script>
@endsection
