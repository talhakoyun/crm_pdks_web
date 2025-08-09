@extends('backend.layout.layout')
@php
    $title = 'Profil';
    $subTitle = 'Profil';
    $activeTab = 'profile';

    // Şifre değiştirme ile ilgili hata varsa veya tab parametresi password ise şifre tab'ını aç
    if($errors->has('password') || $errors->has('confirm_password') || request()->get('tab') === 'password' || session('warning')) {
        $activeTab = 'password';
    }

$script = '<script>
    // ======================== Upload Image Start =====================
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $("#imagePreview").css("background-image", "url(" + e.target.result + ")");
                $("#imagePreview").hide();
                $("#imagePreview").fadeIn(650);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    $("#imageUpload").change(function() {
        readURL(this);
    });
    // ======================== Upload Image End =====================

    // ================== Password Show Hide Js Start ==========
    function initializePasswordToggle(toggleSelector) {
        $(toggleSelector).on("click", function() {
            $(this).toggleClass("ri-eye-off-line");
            var input = $($(this).attr("data-toggle"));
            if (input.attr("type") === "password") {
                input.attr("type", "text");
            } else {
                input.attr("type", "password");
            }
        });
    }
    // Call the function
    initializePasswordToggle(".toggle-password");
    // ========================= Password Show Hide Js End ===========================
</script>';
@endphp

@section('content')
    <div class="row gy-4">
        <div class="col-lg-4">
            <div class="user-grid-card position-relative border radius-16 overflow-hidden bg-base h-100">
                <img src="{{ asset('assets/images/user-grid/user-grid-bg1.png') }}" alt=""
                    class="w-100 object-fit-cover">
                <div class="pb-24 ms-16 mb-24 me-16  mt--100">
                    <div class="text-center border border-top-0 border-start-0 border-end-0">
                        <img src="{{ !is_null($item->image) ? asset('/upload/user/' . $item->image) : asset('assets/images/default_user_black.png') }}"
                            class="border br-white border-width-2-px w-200-px h-200-px rounded-circle object-fit-cover">
                        <h6 class="mb-0 mt-16">{{ $item->name }} {{ $item->surname }}</h6>
                        <span class="text-secondary-light mb-16">{{ $item->email }}</span>
                    </div>
                    <div class="mt-24">
                        <h6 class="text-xl mb-16">Kişisel Bilgiler</h6>
                        <ul>
                            <li class="d-flex align-items-center gap-1 mb-12">
                                <span class="w-30 text-md fw-semibold text-primary-light">Ad Soyad</span>
                                <span class="w-70 text-secondary-light fw-medium">: {{ $item->name ?? '' }}
                                    {{ $item->surname ?? '' }}</span>
                            </li>
                            <li class="d-flex align-items-center gap-1 mb-12">
                                <span class="w-30 text-md fw-semibold text-primary-light"> E-posta Adresi</span>
                                <span class="w-70 text-secondary-light fw-medium">: {{ $item->email ?? '' }}</span>
                            </li>
                            <li class="d-flex align-items-center gap-1 mb-12">
                                <span class="w-30 text-md fw-semibold text-primary-light"> Telefon Numarası</span>
                                <span class="w-70 text-secondary-light fw-medium">: {{ $item->phone ?? '' }}</span>
                            </li>
                            <li class="d-flex align-items-center gap-1 mb-12">
                                <span class="w-30 text-md fw-semibold text-primary-light"> Ünvan</span>
                                <span class="w-70 text-secondary-light fw-medium">: {{ $item->title ?? '' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-body p-24">
                    <ul class="nav border-gradient-tab nav-pills mb-20 d-inline-flex" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link d-flex align-items-center px-24 {{ $activeTab === 'profile' ? 'active' : '' }}"
                                id="pills-edit-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-edit-profile"
                                type="button" role="tab" aria-controls="pills-edit-profile" aria-selected="true">
                                Profili Düzenle
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link d-flex align-items-center px-24 {{ $activeTab === 'password' ? 'active' : '' }}"
                                id="pills-change-passwork-tab" data-bs-toggle="pill" data-bs-target="#pills-change-passwork"
                                type="button" role="tab" aria-controls="pills-change-passwork" aria-selected="false"
                                tabindex="-1">
                                Şifre Değiştir
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade {{ $activeTab === 'profile' ? 'show active' : '' }}"
                            id="pills-edit-profile" role="tabpanel" aria-labelledby="pills-edit-profile-tab" tabindex="0">
                            {{-- <h6 class="text-md text-primary-light mb-16">Profil Resmi</h6>
                            <!-- Upload Image Start -->
                            <div class="mb-24 mt-16">
                                <div class="avatar-upload">
                                    <div
                                        class="avatar-edit position-absolute bottom-0 end-0 me-24 mt-16 z-1 cursor-pointer">
                                        <input type='file' id="imageUpload" accept=".png, .jpg, .jpeg" hidden>
                                        <label for="imageUpload"
                                            class="w-32-px h-32-px d-flex justify-content-center align-items-center bg-primary-50 text-primary-600 border border-primary-600 bg-hover-primary-100 text-lg rounded-circle">
                                            <iconify-icon icon="solar:camera-outline" class="icon"></iconify-icon>
                                        </label>
                                    </div>
                                    <div class="avatar-preview">
                                        <div id="imagePreview">
                                        </div>
                                    </div>
                                </div>
                            </div> --}}
                            <!-- Upload Image End -->
                            <form action="{{ route('backend.profile_save') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="mb-20">
                                            <label for="name"
                                                class="form-label fw-semibold text-primary-light text-sm mb-8">Ad
                                                <span class="text-danger-600">*</span></label>
                                            <input type="text" class="form-control radius-8" id="name"
                                                name="name" placeholder="Lütfen adınızı giriniz"
                                                value="{{ $item->name ?? '' }}">
                                            <x-form-error field="name" />
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mb-20">
                                            <label for="surname"
                                                class="form-label fw-semibold text-primary-light text-sm mb-8">Soyad
                                                <span class="text-danger-600">*</span></label>
                                            <input type="text" class="form-control radius-8" id="surname"
                                                name="surname" placeholder="Lütfen soyadınızı giriniz"
                                                value="{{ $item->surname ?? '' }}">
                                            <x-form-error field="surname" />
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mb-20">
                                            <label for="email"
                                                class="form-label fw-semibold text-primary-light text-sm mb-8">E-posta
                                                Adresi <span class="text-danger-600">*</span></label>
                                            <input type="email" class="form-control radius-8" id="email"
                                                name="email" placeholder="Lütfen e-posta adresi giriniz"
                                                value="{{ $item->email ?? '' }}">
                                            <x-form-error field="email" />
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mb-20">
                                            <label for="number"
                                                class="form-label fw-semibold text-primary-light text-sm mb-8">Telefon
                                                Numarası</label>
                                            <input type="text" class="form-control radius-8" id="phone"
                                                name="phone" placeholder="Lütfen telefon numaranızı giriniz"
                                                value="{{ $item->phone ?? '' }}">
                                            <x-form-error field="phone" />
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="mb-20">
                                            <label for="title"
                                                class="form-label fw-semibold text-primary-light text-sm mb-8">Ünvan</label>
                                            <input type="text" class="form-control radius-8" id="title"
                                                name="title" placeholder="Lütfen ünvanınızı giriniz"
                                                value="{{ $item->title ?? '' }}">
                                            <x-form-error field="title" />
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-center gap-3">
                                    <button type="button"
                                        class="border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-56 py-11 radius-8">
                                        İptal
                                    </button>
                                    <button type="submit"
                                        class="btn btn-primary border border-primary-600 text-md px-56 py-12 radius-8">
                                        Kaydet
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade {{ $activeTab === 'password' ? 'show active' : '' }}"
                            id="pills-change-passwork" role="tabpanel" aria-labelledby="pills-change-passwork-tab"
                            tabindex="0">
                            <form action="{{ route('backend.profile_password') }}" method="POST">
                                @csrf
                                <div class="mb-20">
                                    <label for="your-password"
                                        class="form-label fw-semibold text-primary-light text-sm mb-8">Yeni Şifre <span
                                            class="text-danger-600">*</span></label>
                                    <div class="position-relative">
                                        <input type="password" class="form-control radius-8" id="your-password"
                                            name="password" placeholder="Yeni Şifre*">
                                        <x-form-error field="password" />
                                        <span
                                            class="toggle-password ri-eye-line cursor-pointer position-absolute end-0 top-50 translate-middle-y me-16 text-secondary-light"
                                            data-toggle="#your-password"></span>
                                    </div>
                                </div>
                                <div class="mb-20">
                                    <label for="confirm-password"
                                        class="form-label fw-semibold text-primary-light text-sm mb-8">Yeni Şifre Tekrarı
                                        <span class="text-danger-600">*</span></label>
                                    <div class="position-relative">
                                        <input type="password" class="form-control radius-8" id="confirm-password"
                                            name="confirm_password" placeholder="Yeni Şifre Tekrarı*">
                                        <x-form-error field="confirm_password" />
                                        <span
                                            class="toggle-password ri-eye-line cursor-pointer position-absolute end-0 top-50 translate-middle-y me-16 text-secondary-light"
                                            data-toggle="#confirm-password"></span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-center gap-3">
                                    <button type="button"
                                        class="border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-56 py-11 radius-8">
                                        İptal
                                    </button>
                                    <button type="submit"
                                        class="btn btn-primary border border-primary-600 text-md px-56 py-12 radius-8">
                                        Kaydet
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
