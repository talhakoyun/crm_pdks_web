
<script>
    document.addEventListener("DOMContentLoaded", function() {
        function updatePersonnelFields() {
            var selectedRoleId = document.querySelector('select[name="role_id"]').value;
            var isPersonnelRole = ["5", "6", "7"].includes(selectedRoleId);
            var isDepartmentManager = selectedRoleId === "6";

            // Tüm personel alanlarını bul
            var requiredFields = document.querySelectorAll('.js-personel-required');
            requiredFields.forEach(function(field) {
                if (isPersonnelRole) {
                    field.setAttribute('required', 'required');
                    field.classList.add('wizard-required');
                } else {
                    field.removeAttribute('required');
                    field.classList.remove('wizard-required');
                }
            });

            // Personel section'ı göster/gizle
            var personnelSections = document.querySelectorAll('.js-personel-section');
            personnelSections.forEach(function(section) {
                section.style.display = isPersonnelRole ? '' : 'none';
            });

            // Departman yetkilisi için departman alanını gizle
            var departmentField = document.querySelector('select[name="department_id"]');
            var departmentRow = document.querySelector('.js-department-field');

            if (departmentField && departmentRow && isDepartmentManager) {
                // Departman alanını gizle
                departmentRow.style.display = 'none';

                // Departman alanını zorunlu olmaktan çıkar
                departmentField.removeAttribute('required');
                departmentField.classList.remove('wizard-required', 'js-personel-required');
            } else if (departmentField && departmentRow && isPersonnelRole && !isDepartmentManager) {
                // Diğer personel rolleri için departman alanını göster
                departmentRow.style.display = '';

                // Departman alanını zorunlu yap
                departmentField.setAttribute('required', 'required');
                departmentField.classList.add('wizard-required', 'js-personel-required');
            }
        }

        // Sayfa ilk açıldığında çalıştır
        updatePersonnelFields();

        // Role değişince tekrar kontrol et
        document.querySelector('select[name="role_id"]').addEventListener('change', updatePersonnelFields);
    });
</script>
    <div class="row gy-3">
        @php
            // Yetki kontrolü
            $authUser = Auth::user();
            $authUserRoleId = $authUser->role_id;

            // Rol bazlı yetki kontrolleri
            $isSuperAdmin = $authUserRoleId == 1;
            $isAdmin = $authUserRoleId == 2;
            $isCompanyOwner = $authUserRoleId == 3;
            $isCompanyAdmin = $authUserRoleId == 4;
            $isBranchAdmin = $authUserRoleId == 5;
            $isDepartmentAdmin = $authUserRoleId == 6;

            $canEditBranch = $isSuperAdmin || $isAdmin || $isCompanyOwner || $isCompanyAdmin;

            $canEditShift = $isSuperAdmin || $isAdmin || $isCompanyOwner || $isCompanyAdmin || $isBranchAdmin || $isDepartmentAdmin;

            $canEditDepartment = $isSuperAdmin || $isAdmin || $isCompanyOwner || $isCompanyAdmin || $isBranchAdmin;

            $canEditPosition = $isSuperAdmin || $isAdmin || $isCompanyOwner || $isCompanyAdmin || $isBranchAdmin;

            $canEditPermits = $isSuperAdmin || $isAdmin || $isCompanyOwner || $isCompanyAdmin;
        @endphp

        <div class="row gy-3 js-personel-section">
        <!-- Şube -->
        <div class="col-sm-3">
            <label class="form-label">İzin Verilen Şubeler <span class="text-danger">*</span></label>
        </div>
        <div class="col-sm-9">
            <div class="position-relative">
                <div class="icon-field">
                    <span class="icon">
                        <iconify-icon icon="carbon:building"></iconify-icon>
                    </span>
                    <select class="form-select select2 js-personel-required" name="branch_ids[]" multiple>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" {{ in_array($branch->id, old('branch_ids', $item->branches->pluck('id')->toArray() ?? [])) ? 'selected' : '' }}>
                                {{ $branch->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="wizard-form-error"></div>
                <x-form-error field="branch_ids" />
            </div>
        </div>

        <!-- Departman -->
        <div class="row gy-3 js-department-field">
            <div class="col-sm-3">
                <label class="form-label">Departman <span class="text-danger">*</span></label>
            </div>
            <div class="col-sm-9">
                <div class="position-relative">
                    <div class="icon-field">
                        <span class="icon">
                            <iconify-icon icon="carbon:location"></iconify-icon>
                        </span>
                        <select class="form-select select2 js-personel-required" name="department_id">
                            <option value="">Lütfen departman seçiniz</option>
                            @if (isset($departments) && count($departments) > 0)
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}" {{ (old('department_id') ?? $item->department_id) == $department->id ? 'selected' : '' }}>
                                        {{ $department->title }}
                                    </option>
                                @endforeach
                            @else
                                <option value="" disabled>Departman bulunamadı</option>
                            @endif
                        </select>
                    </div>
                    <div class="wizard-form-error"></div>
                    <x-form-error field="department_id" />
                </div>
            </div>
        </div>

        <!-- Vardiya -->
        <div class="col-sm-3">
            <label class="form-label">Vardiya <span class="text-danger">*</span></label>
        </div>
        <div class="col-sm-9">
            <div class="position-relative">
                <div class="icon-field">
                    <span class="icon">
                        <iconify-icon icon="carbon:time"></iconify-icon>
                    </span>
                    <select class="form-select select2 js-personel-required" name="shift_definition_id">
                        <option value="">Lütfen vardiya seçiniz</option>
                        @foreach ($shiftDefinitions as $shiftDefinition)
                            <option value="{{ $shiftDefinition->id }}" {{ (old('shift_definition_id') ?? $item->shift_definition_id) == $shiftDefinition->id ? 'selected' : '' }}>
                                {{ $shiftDefinition->title . ' - ' . $shiftDefinition->start_time . ' - ' . $shiftDefinition->end_time }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="wizard-form-error"></div>
                <x-form-error field="shift_definition_id" />
            </div>
        </div>

        <!-- Kurum Dışı Bildirim -->
        <div class="col-sm-3">
            <label class="form-label">Kurum Dışı Bildirim <span class="text-danger">*</span></label>
        </div>
        <div class="col-sm-9">
            <div class="position-relative">
                <div class="icon-field">
                    <span class="icon">
                        <iconify-icon icon="carbon:location-company"></iconify-icon>
                    </span>
                    <select class="form-select select2" name="allow_outside" {{ !$canEditPermits ? 'disabled' : '' }}>
                        <option value="1" {{ $item->allow_outside == 1 || is_null($item->id) ? 'selected' : '' }}>
                            Aktif</option>
                        <option value="0"
                            {{ $item->allow_outside == 0 && !is_null($item->id) ? 'selected' : '' }}>
                            Pasif</option>
                    </select>
                </div>
                <div class="wizard-form-error"></div>
                <x-form-error field="allow_outside" />
            </div>
        </div>
        <div class="form-group d-flex align-items-center justify-content-end gap-8">
            <button type="button"
                class="form-wizard-previous-btn btn btn-neutral-500 border-neutral-100 px-32">Geri</button>
            <button type="submit" class="form-wizard-submit btn btn-primary-600 px-32">Kaydet</button>
        </div>
    </div>
