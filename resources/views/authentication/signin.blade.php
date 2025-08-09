<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en" data-theme="light">

<x-head />

<body>

    <section class="auth bg-base">
        <div class="max-w-464-px mx-auto w-100">
            <div>
                <a href="{{ route('backend.index') }}" class="max-w-290-px">
                    <img src="{{ asset('assets/images/logo/logo.png') }}"  class="offset-6" alt="">
                </a>
                <h4 class="mb-12">Giriş Yap</h4>
                <p class="mb-32 text-secondary-light text-lg">Hoşgeldiniz.Lütfen giriş için bilgilerinizi giriniz.
                </p>
            </div>
            <form action="{{ route('signin.post') }}" method="POST">
                @csrf
                <div class="icon-field mb-16">
                    <span class="icon top-50 translate-middle-y">
                        <iconify-icon icon="mage:email"></iconify-icon>
                    </span>
                    <input name="email" type="email" class="form-control h-56-px bg-neutral-50 radius-12"
                        placeholder="E-posta Adresi">
                </div>
                <div class="position-relative mb-20">
                    <div class="icon-field">
                        <span class="icon top-50 translate-middle-y">
                            <iconify-icon icon="solar:lock-password-outline"></iconify-icon>
                        </span>
                        <input name="password" type="password" class="form-control h-56-px bg-neutral-50 radius-12"
                            id="your-password" placeholder="Şifre">
                    </div>
                    <span
                        class="toggle-password ri-eye-line cursor-pointer position-absolute end-0 top-50 translate-middle-y me-16 text-secondary-light"
                        data-toggle="#your-password"></span>
                </div>
                {{-- <div class="">
                    <div class="d-flex justify-content-between gap-2">
                        <div class="form-check style-check d-flex align-items-center">
                            <input name="rememberme" class="form-check-input border border-neutral-300" type="checkbox"
                                value="" id="remeber">
                            <label class="form-check-label" for="remeber">Beni Hatırla </label>
                        </div>
                        <a href="javascript:void(0)" class="text-primary-600 fw-medium">Şifremi Unuttum?</a>
                    </div>
                </div> --}}

                <button type="submit" class="btn btn-primary text-sm btn-sm px-12 py-16 w-100 radius-12 mt-32">
                    Giriş Yap</button>
            </form>
        </div>
    </section>

    @php
        $script = '<script>
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

    <x-script />

</body>

</html>
