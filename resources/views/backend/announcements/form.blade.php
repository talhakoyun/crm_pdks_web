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
                        method="POST" id="announcementForm">
                        @csrf
                        <div class="row gy-3">
                            <!-- Başlık -->
                            <div class="col-6">
                                <label class="form-label">Başlık</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:text-heading"></iconify-icon>
                                    </span>
                                    <input type="text" class="form-control" name="title"
                                        placeholder="Lütfen duyuru başlığı giriniz"
                                        value="{{ old('title') ?? ($item->title ?? '') }}">
                                    <x-form-error field="title" />
                                </div>
                            </div>

                            <!-- Gönderim Tipi -->
                            <div class="col-6">
                                <label class="form-label">Gönderim Tipi</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:send"></iconify-icon>
                                    </span>
                                    <select class="form-select select2" name="send_type" id="sendType">
                                        <option value="">Seçiniz</option>
                                        <option value="all" {{ old('send_type') == 'all' ? 'selected' : '' }}>Tüm Kullanıcılar</option>
                                        <option value="role" {{ old('send_type') == 'role' ? 'selected' : '' }}>Rol Bazlı</option>
                                        <option value="branch" {{ old('send_type') == 'branch' ? 'selected' : '' }}>Şube Bazlı</option>
                                        <option value="department" {{ old('send_type') == 'department' ? 'selected' : '' }}>Departman Bazlı</option>
                                    </select>
                                    <x-form-error field="send_type" />
                                </div>
                            </div>

                            <!-- Rol Seçimi (Rol bazlı seçildiğinde görünür) -->
                            <div class="col-12 send-type-section" id="roleSection" style="display: none;">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label">Roller</label>
                                        <div class="icon-field">
                                            <span class="icon">
                                                <iconify-icon icon="carbon:user-role"></iconify-icon>
                                            </span>
                                            <select class="form-select select2" name="roles[]" id="roles" multiple>
                                                @foreach ($roleValues as $role)
                                                    <option value="{{ $role->id }}"
                                                        {{ isset($item) && in_array($role->id, $item->roles ?? []) ? 'selected' : '' }}>
                                                        {{ $role->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <x-form-error field="roles" />
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Kullanıcı Seçimi</label>
                                        <div class="icon-field">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="role_user_type"
                                                    value="all" checked>
                                                <label class="form-check-label">Seçili rollerdeki tüm kullanıcılar</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="role_user_type"
                                                    value="specific">
                                                <label class="form-check-label">Seçili kullanıcılar</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12" id="roleUsersSection" style="display: none;">
                                        <label class="form-label">Kullanıcılar</label>
                                        <div class="icon-field">
                                            <span class="icon">
                                                <iconify-icon icon="carbon:user"></iconify-icon>
                                            </span>
                                            <select class="form-select select2" name="role_users[]" id="roleUsers" multiple>
                                            </select>
                                            <x-form-error field="role_users" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Şube Seçimi (Şube bazlı seçildiğinde görünür) -->
                            <div class="col-12 send-type-section" id="branchSection" style="display: none;">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label">Şubeler</label>
                                        <div class="icon-field">
                                            <span class="icon">
                                                <iconify-icon icon="carbon:building"></iconify-icon>
                                            </span>
                                            <select class="form-select select2" name="branches[]" id="branches" multiple>
                                                @foreach ($branches ?? [] as $branch)
                                                    <option value="{{ $branch->id }}">{{ $branch->title }}</option>
                                                @endforeach
                                            </select>
                                            <x-form-error field="branches" />
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Kullanıcı Seçimi</label>
                                        <div class="icon-field">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio" name="branch_user_type"
                                                    value="all" checked>
                                                <label class="form-check-label">Seçili şubelerdeki tüm kullanıcılar</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="branch_user_type"
                                                    value="specific">
                                                <label class="form-check-label">Seçili kullanıcılar</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12" id="branchUsersSection" style="display: none;">
                                        <label class="form-label">Kullanıcılar</label>
                                        <div class="icon-field">
                                            <span class="icon">
                                                <iconify-icon icon="carbon:user"></iconify-icon>
                                            </span>
                                            <select class="form-select select2" name="branch_users[]" id="branchUsers"
                                                multiple>
                                            </select>
                                            <x-form-error field="branch_users" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Departman Seçimi (Departman bazlı seçildiğinde görünür) -->
                            <div class="col-12 send-type-section" id="departmentSection" style="display: none;">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label">Departmanlar</label>
                                        <div class="icon-field">
                                            <span class="icon">
                                                <iconify-icon icon="carbon:building"></iconify-icon>
                                            </span>
                                            <select class="form-select select2" name="departments[]" id="departments"
                                                multiple>
                                                @foreach ($departments ?? [] as $department)
                                                    <option value="{{ $department->id }}">{{ $department->title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <x-form-error field="departments" />
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Kullanıcı Seçimi</label>
                                        <div class="icon-field">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio"
                                                    name="department_user_type" value="all" checked>
                                                <label class="form-check-label">Seçili departmanlardaki tüm
                                                    kullanıcılar</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="department_user_type" value="specific">
                                                <label class="form-check-label">Seçili kullanıcılar</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12" id="departmentUsersSection" style="display: none;">
                                        <label class="form-label">Kullanıcılar</label>
                                        <div class="icon-field">
                                            <span class="icon">
                                                <iconify-icon icon="carbon:user"></iconify-icon>
                                            </span>
                                            <select class="form-select select2" name="department_users[]"
                                                id="departmentUsers" multiple>
                                            </select>
                                            <x-form-error field="department_users" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- İçerik -->
                            <div class="col-12">
                                <label class="form-label">İçerik</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:text-annotation"></iconify-icon>
                                    </span>
                                    <textarea class="form-control" name="content" id="content"
                                        placeholder="Lütfen duyuru içeriğini giriniz">{{ old('content') ?? ($item->content ?? '') }}</textarea>
                                    <x-form-error field="content" />
                                </div>
                            </div>

                            <!-- Başlangıç Tarihi -->
                            <div class="col-6">
                                <label class="form-label">Başlangıç Tarihi</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:calendar"></iconify-icon>
                                    </span>
                                    <input type="datetime-local" class="form-control" name="start_date"
                                        value="{{ old('start_date') ?? (isset($item) ? $item->start_date?->format('Y-m-d\TH:i') : '') }}">
                                    <x-form-error field="start_date" />
                                </div>
                            </div>

                            <!-- Bitiş Tarihi -->
                            <div class="col-6">
                                <label class="form-label">Bitiş Tarihi</label>
                                <div class="icon-field">
                                    <span class="icon">
                                        <iconify-icon icon="carbon:calendar"></iconify-icon>
                                    </span>
                                    <input type="datetime-local" class="form-control" name="end_date"
                                        value="{{ old('end_date') ?? (isset($item) ? $item->end_date?->format('Y-m-d\TH:i') : '') }}">
                                    <x-form-error field="end_date" />
                                </div>
                            </div>

                            <!-- Durum -->
                            <div class="col-12">
                                <label class="form-label">Durumu</label>
                                <div class="icon-field">
                                    <select class="form-select select2" name="status">
                                        <option value="1"
                                            {{ old('status') ?? ($item->status ?? '') == 1 ? 'selected' : '' }}>Aktif
                                        </option>
                                        <option value="0"
                                            {{ old('status') ?? ($item->status ?? '') == 0 ? 'selected' : '' }}>Pasif
                                        </option>
                                    </select>
                                    <x-form-error field="status" />
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
        // Gönderim tipi değiştiğinde
        $('#sendType').change(function() {
            $('.send-type-section').hide();
            let selectedType = $(this).val();
            if (selectedType && selectedType !== 'all') {
                $(`#${selectedType}Section`).show();
            }
        });

        // Rol değiştiğinde kullanıcıları getir
        $('#roles').change(function() {
            if ($('input[name="role_user_type"]:checked').val() === 'specific') {
                loadUsers('role');
            }
        });

        // Şube değiştiğinde kullanıcıları getir
        $('#branches').change(function() {
            if ($('input[name="branch_user_type"]:checked').val() === 'specific') {
                loadUsers('branch');
            }
        });

        // Departman değiştiğinde kullanıcıları getir
        $('#departments').change(function() {
            if ($('input[name="department_user_type"]:checked').val() === 'specific') {
                loadUsers('department');
            }
        });

        // Kullanıcı seçim tipi değiştiğinde
        $('input[name="role_user_type"]').change(function() {
            toggleUserSection('role');
        });

        $('input[name="branch_user_type"]').change(function() {
            toggleUserSection('branch');
        });

        $('input[name="department_user_type"]').change(function() {
            toggleUserSection('department');
        });

        // Kullanıcı seçim bölümünü göster/gizle
        function toggleUserSection(type) {
            let value = $(`input[name="${type}_user_type"]:checked`).val();
            if (value === 'specific') {
                $(`#${type}UsersSection`).show();
                loadUsers(type);
            } else {
                $(`#${type}UsersSection`).hide();
            }
        }

        // AJAX ile kullanıcıları getir
        function loadUsers(type) {
            let ids = $(`#${type}s`).val();
            if (!ids || ids.length === 0) return;

            $.ajax({
                url: '{{ route('backend.announcements_get_users') }}',
                type: 'POST',
                data: {
                    type: type,
                    ids: ids,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    let select = $(`#${type}Users`);
                    select.empty();

                    response.forEach(function(user) {
                        select.append(new Option(user.name, user.id));
                    });
                }
            });
        }

        // Sayfa yüklendiğinde seçili gönderim tipini göster
        let initialSendType = $('#sendType').val();
        if (initialSendType && initialSendType !== 'all') {
            $(`#${initialSendType}Section`).show();
        }
    </script>
@endsection
