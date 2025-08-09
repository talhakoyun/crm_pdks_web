@extends('layout.layout')
@php
    $title = $container->title . (is_null($item->id) ? ' Ekle' : ' Düzenle');
    $subTitle = $container->title . (is_null($item->id) ? ' Ekle' : ' Düzenle');
@endphp
<link rel="stylesheet" href="{{ asset('assets/css/custom/role.css') }}">

@section('content')
    <div class="row gy-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0 fs-6">{{ $container->title }} {{ !is_null($item->id) ? 'Düzenle' : 'Ekle' }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('backend.' . $container->page . '_save', ['unique' => $item->id]) }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <!-- Role Name Field -->
                            <div class="col-md-6">
                                <label class="form-label">Rol Adı</label>
                                <div class="input-wrapper position-relative">
                                    <span class="icon-wrapper position-absolute start-0 top-50 translate-middle-y ms-3">
                                        <iconify-icon icon="f7:person" class="text-primary"></iconify-icon>
                                    </span>
                                    <input type="text" class="form-control custom-input ps-5"
                                        name="name" placeholder="Lütfen rol adı giriniz"
                                        value="{{ old('name') ?? ($item->name ?? '') }}">
                                    <x-form-error field="name" />
                                </div>
                            </div>

                            <!-- Status Field -->
                            <div class="col-md-6">
                                <label class="form-label">Durum</label>
                                <div class="input-wrapper position-relative">
                                    <span class="icon-wrapper position-absolute start-0 top-50 translate-middle-y ms-3">
                                        <iconify-icon icon="carbon:badge" class="text-primary"></iconify-icon>
                                    </span>
                                    <select class="form-select custom-select ps-5" name="is_active">
                                        <option value="1" {{ $item->is_active == 1 || is_null($item->id) ? 'selected' : '' }}>
                                            Aktif
                                        </option>
                                        <option value="0" {{ $item->is_active == 0 && !is_null($item->id) ? 'selected' : '' }}>
                                            Pasif
                                        </option>
                                    </select>
                                    <x-form-error field="is_active" />
                                </div>
                            </div>

                            <!-- Permissions Section -->
                            <div class="col-12 mt-4">
                                <div class="permissions-container">
                                    <div class="row g-3">
                                        @foreach ($routes as $category => $categoryRoutes)
                                            <div class="col-lg-4 col-md-6">
                                                <div class="category-container" data-category="{{ $category }}">
                                                    <div class="category-header">
                                                        <div class="d-flex justify-content-between align-items-center w-100">
                                                            <h6 class="mb-0 category-title">
                                                                {{ $category }}
                                                            </h6>
                                                            <button type="button" class="btn-select-category" data-category="{{ $category }}">
                                                                Tümünü Seç
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="category-body">
                                                        @foreach ($categoryRoutes as $route)
                                                            <div class="permission-item">
                                                                <input type="checkbox"
                                                                    class="route-checkbox"
                                                                    data-category="{{ $category }}"
                                                                    name="permissions[]"
                                                                    value="{{ $route->route_name }}"
                                                                    id="route_{{ $route->route_name }}"
                                                                    @if (isset($item) &&
                                                                            is_array(json_decode($item->permissions)) &&
                                                                            in_array($route->route_name, json_decode($item->permissions))) checked @endif>
                                                                <label for="route_{{ $route->route_name }}">
                                                                    {{ $route->name }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
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
    document.addEventListener('DOMContentLoaded', function() {
        // Add animation to category containers
        const permissionCards = document.querySelectorAll('.category-container');
        permissionCards.forEach((card, index) => {
            // Ensure card is visible before animation
            card.style.display = 'block';
            card.style.opacity = '0';

            // Apply animation with delay
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });

        // Category-specific select all buttons
        const categoryButtons = document.querySelectorAll('.btn-select-category');
        categoryButtons.forEach(button => {
            const category = button.getAttribute('data-category');

            button.addEventListener('click', function() {
                const checkboxes = document.querySelectorAll(`.route-checkbox[data-category="${category}"]`);

                // Check current state - are all checkboxes checked?
                let allChecked = true;
                checkboxes.forEach(checkbox => {
                    if (!checkbox.checked) {
                        allChecked = false;
                    }
                });

                // Toggle based on current state
                if (allChecked) {
                    // If all are checked, uncheck all
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    button.innerHTML = '<iconify-icon icon="carbon:select-all" class="me-1"></iconify-icon> Tümünü Seç';
                    button.classList.remove('active');
                } else {
                    // If some or none are checked, check all
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = true;
                    });
                    button.innerHTML = '<iconify-icon icon="carbon:checkmark" class="me-1"></iconify-icon> Seçildi';
                    button.classList.add('active');
                }

                // Add pulse animation without affecting visibility
                const card = button.closest('.category-container');
                // Ensure card remains visible
                card.style.display = 'block';
                card.style.opacity = '1';

                // Apply a background flash effect
                card.style.transition = 'background-color 0.3s';
                card.style.backgroundColor = 'rgba(72, 127, 255, 0.1)';

                setTimeout(() => {
                    card.style.backgroundColor = '';
                }, 300);
            });

            // Initialize button state based on checkboxes
            initializeCategoryButtonState(category);

            // Add listeners to checkboxes to update button state
            const checkboxes = document.querySelectorAll(`.route-checkbox[data-category="${category}"]`);
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateCategoryButtonState(category);
                });
            });
        });

        // Function to initialize button state
        function initializeCategoryButtonState(category) {
            const checkboxes = document.querySelectorAll(`.route-checkbox[data-category="${category}"]`);
            const button = document.querySelector(`.btn-select-category[data-category="${category}"]`);

            let allChecked = true;
            let anyChecked = false;

            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    anyChecked = true;
                } else {
                    allChecked = false;
                }
            });

            if (allChecked && checkboxes.length > 0) {
                button.innerHTML = '<iconify-icon icon="carbon:checkmark" class="me-1"></iconify-icon> Seçildi';
                button.classList.add('active');
            } else {
                button.innerHTML = '<iconify-icon icon="carbon:select-all" class="me-1"></iconify-icon> Tümünü Seç';
                button.classList.remove('active');
            }
        }

        // Function to update category button state
        function updateCategoryButtonState(category) {
            const checkboxes = document.querySelectorAll(`.route-checkbox[data-category="${category}"]`);
            const button = document.querySelector(`.btn-select-category[data-category="${category}"]`);

            let allChecked = true;
            let anyChecked = false;

            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    anyChecked = true;
                } else {
                    allChecked = false;
                }
            });

            if (allChecked && checkboxes.length > 0) {
                button.innerHTML = '<iconify-icon icon="carbon:checkmark" class="me-1"></iconify-icon> Seçildi';
                button.classList.add('active');
            } else {
                button.innerHTML = '<iconify-icon icon="carbon:select-all" class="me-1"></iconify-icon> Tümünü Seç';
                button.classList.remove('active');
            }
        }

        // Add hover effect to input fields with enhanced animation
        const inputWrappers = document.querySelectorAll('.input-wrapper');
        inputWrappers.forEach(wrapper => {
            const input = wrapper.querySelector('input, select');
            const icon = wrapper.querySelector('iconify-icon');

            input.addEventListener('focus', function() {
                wrapper.style.zIndex = '1';
                icon.style.color = 'var(--primary-600)';
                icon.style.transform = 'scale(1.2) rotate(5deg)';
                this.style.transform = 'translateY(-2px)';
            });

            input.addEventListener('blur', function() {
                wrapper.style.zIndex = '0';
                icon.style.color = '';
                icon.style.transform = '';
                this.style.transform = '';
            });

            // Add pulse animation on hover
            wrapper.addEventListener('mouseenter', function() {
                icon.style.transform = 'scale(1.1)';
            });

            wrapper.addEventListener('mouseleave', function() {
                if (!input.matches(':focus')) {
                    icon.style.transform = '';
                }
            });
        });

        // Add animation to checkboxes
        const checkboxes = document.querySelectorAll('.permission-item');
        checkboxes.forEach(checkbox => {
            const input = checkbox.querySelector('input');
            const label = checkbox.querySelector('label');

            checkbox.addEventListener('mouseenter', function() {
                if (!input.checked) {
                    label.style.color = 'var(--primary-500)';
                }
            });

            checkbox.addEventListener('mouseleave', function() {
                if (!input.checked) {
                    label.style.color = '';
                }
            });

            input.addEventListener('change', function() {
                if (this.checked) {
                    label.style.color = 'var(--primary-600)';
                    checkbox.style.animation = 'pulse 0.3s';
                } else {
                    label.style.color = '';
                }
            });
        });
    });
</script>
@endsection
