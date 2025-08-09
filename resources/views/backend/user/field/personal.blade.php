  <div class="row gy-3">
      <div class="col-sm-4">
          <label class="form-label">Ad<span class="text-danger">*</span></label>
          <div class="position-relative">
              <input type="text" class="form-control wizard-required" placeholder="Ad" name="name"
                  value="{{ old('name') ?? ($item->name ?? '') }}" required>
              <div class="wizard-form-error"></div>
              <x-form-error field="name" />
          </div>
      </div>
      <div class="col-sm-4">
          <label class="form-label">Soyad<span class="text-danger">*</span></label>
          <div class="position-relative">
              <input type="text" class="form-control wizard-required" placeholder="Soyad" name="surname"
                  value="{{ old('surname') ?? ($item->surname ?? '') }}" required>
              <div class="wizard-form-error"></div>
              <x-form-error field="surname" />
          </div>
      </div>
      <div class="col-sm-4">
          <label class="form-label">TC Kimlik No<span class="text-danger">*</span></label>
          <div class="position-relative">
              <input type="text" class="form-control wizard-required" placeholder="TC Kimlik No" name="tc"
                  value="{{ old('tc') ?? ($item->tc ?? '') }}" required id="tc" maxlength="11">
              <div class="wizard-form-error"></div>
              <x-form-error field="tc" />
          </div>
      </div>
      <div class="col-sm-4">
          <label class="form-label">Cinsiyet<span class="text-danger">*</span></label>
          <div class="position-relative">
              <select class="form-control wizard-required" name="gender" required>
                  <option value="">Lütfen cinsiyet seçiniz</option>
                  <option value="1" {{ (old('gender') ?? $item->gender) == 1 ? 'selected' : '' }}>Erkek
                  </option>
                  <option value="2" {{ (old('gender') ?? $item->gender) == 2 ? 'selected' : '' }}>Kadın
                  </option>
              </select>
              <div class="wizard-form-error"></div>
              <x-form-error field="gender" />
          </div>
      </div>
      <div class="col-sm-4">
          <label class="form-label">Doğum Tarihi<span class="text-danger">*</span></label>
          <div class="position-relative">
              <input type="date" class="form-control wizard-required" placeholder="Doğum Tarihi" name="birth_date"
                  value="{{ old('birth_date') ?? ($item->birth_date ?? '') }}" required>
              <div class="wizard-form-error"></div>
              <x-form-error field="birth_date" />
          </div>
      </div>
      <div class="col-sm-4">
          <label class="form-label">Telefon<span class="text-danger">*</span></label>
          <div class="position-relative">
              <input type="text" class="form-control wizard-required" placeholder="Telefon" name="phone"
                  value="{{ old('phone') ?? ($item->phone ?? '') }}" required id="phone">
              <div class="wizard-form-error"></div>
              <x-form-error field="phone" />
          </div>
      </div>
      <div class="col-sm-6">
          <label class="form-label">Email<span class="text-danger">*</span></label>
          <div class="position-relative">
              <input type="email" class="form-control wizard-required" placeholder="Email" name="email"
                  value="{{ old('email') ?? ($item->email ?? '') }}" required>
              <div class="wizard-form-error"></div>
              <x-form-error field="email" />
          </div>
      </div>
      <div class="col-sm-6">
          <label class="form-label">Şifre<span class="text-danger">{{ $item->id ? '' : '*' }}</span></label>
          <div class="position-relative">
              <input type="password" class="form-control {{ $item->id ? '' : 'wizard-required' }}" placeholder="Şifre"
                  name="password" {{ $item->id ? '' : 'required' }}>
              <div class="wizard-form-error"></div>
              <x-form-error field="password" />
          </div>
      </div>
      <div class="col-sm-6">
          <label class="form-label">İşe Başlama Tarihi<span class="text-danger">*</span></label>
          <div class="position-relative">
              <input type="date" class="form-control wizard-required" placeholder="İşe Başlama Tarihi"
                  name="start_work_date" value="{{ old('start_work_date') ?? ($item->start_work_date ?? '') }}"
                  required>
              <div class="wizard-form-error"></div>
              <x-form-error field="start_work_date" />
          </div>
      </div>
      <div class="col-sm-6">
          <label class="form-label">İşten Çıkış Tarihi</label>
          <div class="position-relative">
              <input type="date" class="form-control" placeholder="İşten Çıkış Tarihi" name="leave_work_date"
                  value="{{ old('leave_work_date') ?? ($item->leave_work_date ?? '') }}">
          </div>
      </div>

      @php
          // Rol seçimi kontrolü
          $canViewRoles =
              request()->attributes->get('is_super_admin', false) ||
              request()->attributes->get('is_admin', false) ||
              request()->attributes->get('is_company_owner', false) ||
              request()->attributes->get('is_company_admin', false) ||
              request()->attributes->get('is_branch_admin', false) ||
              request()->attributes->get('is_department_admin', false);

          // Şirket seçimi kontrolü
          $canSelectCompany =
              request()->attributes->get('is_super_admin', false) || request()->attributes->get('is_admin', false);

          // Şube seçimi kontrolü - auth kullanıcının role_id'sine göre
$authUserRoleId = request()->attributes->get('role_id');
$showBranchSelection = in_array($authUserRoleId, [3, 4]); // Role 3 ve 4 için şube seçimi göster
$authUserBranchId = request()->attributes->get('branch_id'); // Role 5 ve 6 için kullanılacak

// Sonraki sayfa kontrolü - admin ve süper admin dahil tüm yetkili kullanıcılar için
$showMultiPageForm =
    request()->attributes->get('is_super_admin', false) ||
    request()->attributes->get('is_admin', false) ||
    request()->attributes->get('is_company_owner', false) ||
    request()->attributes->get('is_company_admin', false) ||
    request()->attributes->get('is_branch_admin', false) ||
    request()->attributes->get('is_department_admin', false);

// Seçili rol ID'si (personel rolü kontrolü için)
          $selectedRoleId = old('role_id') ?? $item->role_id;
          $isPersonnelRole = in_array($selectedRoleId, [5, 6, 7]);
      @endphp

      {{-- Rol seçimi sadece yetkili rollere gösterilir --}}
      @if ($canViewRoles)
          <div class="col-sm-4">
              <label class="form-label">Rol<span class="text-danger">*</span></label>
              <div class="position-relative">
                  <div class="icon-field">
                      <span class="icon">
                          <iconify-icon icon="carbon:user-role"></iconify-icon>
                      </span>
                      <select class="form-select wizard-required" name="role_id" required>
                          <option value="">Lütfen rol seçiniz</option>
                          @foreach ($userRoles as $role)
                              <option value="{{ $role->id }}"
                                  {{ (old('role_id') ?? $item->role_id) == $role->id ? 'selected' : '' }}>
                                  {{ $role->name }}</option>
                          @endforeach
                      </select>
                  </div>
                  <div class="wizard-form-error"></div>
                  <x-form-error field="role_id" />
              </div>
          </div>
      @endif

      {{-- Şube seçimi --}}
      <div class="col-sm-4">
          <label class="form-label">Şube<span class="text-danger">*</span></label>
          <div class="position-relative">
              @if ($showBranchSelection)
                  {{-- Role 3 ve 4 için şube seçimi göster --}}
                  <div class="icon-field">
                      <span class="icon">
                          <iconify-icon icon="carbon:building"></iconify-icon>
                      </span>
                      <select class="form-select select2 wizard-required" name="branch_id" required>
                          <option value="">Lütfen şube seçiniz</option>
                          @foreach ($branches as $branch)
                              @php
                                  // Şube seçiminde görülebilirlik kontrolü
                                  $canSeeThisBranch = true;
                                  $companyId = request()->attributes->get('company_id');

                                  // Şirket kontrolü - Kendi şirketine ait şubeleri görebilir
                                  if (
                                      !request()->attributes->get('is_super_admin', false) &&
                                      !request()->attributes->get('is_admin', false) &&
                                      $companyId &&
                                      $branch->company_id != $companyId
                                  ) {
                                      $canSeeThisBranch = false;
                                  }
                              @endphp

                              @if ($canSeeThisBranch)
                                  <option value="{{ $branch->id }}"
                                      {{ (old('branch_id') ?? $item->branch_id) == $branch->id ? 'selected' : '' }}>
                                      {{ $branch->title }}
                                  </option>
                              @endif
                          @endforeach
                      </select>
                      <div class="wizard-form-error"></div>
                      <x-form-error field="branch_id" />
                  </div>
              @else
                  {{-- Role 5 ve 6 için sadece auth kullanıcının şubesini göster --}}
                  @php
                      $authUserBranch = $branches->firstWhere('id', $authUserBranchId);
                  @endphp
                  <div class="icon-field">
                      <span class="icon">
                          <iconify-icon icon="carbon:building"></iconify-icon>
                      </span>
                      <input type="text" class="form-control"
                          value="{{ $authUserBranch->title ?? 'Şube bulunamadı' }}" readonly>
                      <input type="hidden" name="branch_id" value="{{ $authUserBranchId }}">
                  </div>
              @endif
              <div class="wizard-form-error"></div>
              <x-form-error field="branch_id" />
          </div>
      </div>

      <div class="col-sm-4">
          <label class="form-label">Durum<span class="text-danger">*</span></label>
          <div class="position-relative">
              <div class="icon-field">
                  <span class="icon">
                      <iconify-icon icon="carbon:checkmark-outline"></iconify-icon>
                  </span>
                  <select class="form-select select2 wizard-required" name="is_active" required>
                      <option value="1" {{ old('is_active', $item->is_active ?? 1) == 1 ? 'selected' : '' }}>
                          Aktif
                      </option>
                      <option value="0" {{ old('is_active', $item->is_active ?? 1) == 0 ? 'selected' : '' }}>
                          Pasif
                      </option>
                  </select>
              </div>
              <div class="wizard-form-error"></div>
              <x-form-error field="is_active" />
          </div>
      </div>

      <div class="form-group text-end">
          {{-- JavaScript ile dinamik kontrol yapılacak, her iki butonu da göster --}}
          @if ($showMultiPageForm)
              <button type="button" class="form-wizard-next-btn btn btn-primary-600 px-32"
                  style="display: {{ in_array(old('role_id') ?? $item->role_id, [5, 6, 7]) ? 'inline-block' : 'none' }};">İleri</button>
              <button type="submit" class="btn btn-primary-600 px-32"
                  style="display: {{ in_array(old('role_id') ?? $item->role_id, [5, 6, 7]) ? 'none' : 'inline-block' }};">Kaydet</button>
          @else
              {{-- Admin ve süper admin için sadece Kaydet butonu --}}
              <button type="submit" class="btn btn-primary-600 px-32">Kaydet</button>
          @endif
      </div>
  </div>

  <script>
      // TC Kimlik Numarası formatlaması
      document.addEventListener('DOMContentLoaded', function() {
          const tcInput = document.getElementById('tc');

          if (tcInput) {
              tcInput.addEventListener('input', function(e) {
                  // Sadece rakamları al
                  let value = e.target.value.replace(/\D/g, '');

                  // Maksimum 11 karakter
                  if (value.length > 11) {
                      value = value.substring(0, 11);
                  }

                  // Input değerini güncelle
                  e.target.value = value;
              });

              // Paste olayını da kontrol et
              tcInput.addEventListener('paste', function(e) {
                  e.preventDefault();
                  let pastedText = (e.clipboardData || window.clipboardData).getData('text');
                  let value = pastedText.replace(/\D/g, '');

                  if (value.length > 11) {
                      value = value.substring(0, 11);
                  }

                  this.value = value;
              });
          }
      });
  </script>
