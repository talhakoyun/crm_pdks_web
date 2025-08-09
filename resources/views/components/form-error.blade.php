@props(['field'])

@error($field)
    <div class="alert alert-danger bg-danger-600 text-white border-danger-600 px-2 py-2 mt-2 mb-0 fw-semibold text-sm radius-8 d-flex align-items-center justify-content-between"
        role="alert">
        {{ $message }}
    </div>
@enderror
