<aside class="sidebar">
    <button type="button" class="sidebar-close-btn">
        <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
    </button>
    <div>
        <a href="{{ route('backend.index') }}" class="sidebar-logo">
            <img src="{{ asset('assets/images/logo/logo.png') }}" alt="site logo" class="light-logo">
            <img src="{{ asset('assets/images/logo/logo.png') }}" alt="site logo" class="dark-logo">
            <img src="{{ asset('assets/images/logo/logo.png') }}" alt="site logo" class="logo-icon">
        </a>
    </div>
    <div class="sidebar-menu-area">
        <ul class="sidebar-menu" id="sidebar-menu">
            <li>
                <a href="{{ route('backend.index') }}">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Anasayfa</span>
                </a>
            </li>

            @if (Helpers::hasPermission(['company', 'branch', 'department']))
                <li class="dropdown">
                    <a href="javascript:void(0)">
                        <iconify-icon icon="carbon:location-company" class="menu-icon"></iconify-icon>
                        <span>Şirket Yönetimi</span>
                    </a>
                    <ul class="sidebar-submenu">
                        @if (Helpers::hasPermission(['company']))
                            <li>
                                <a href="{{ route('backend.company_list') }}">
                                    <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>
                                    Şirketler
                                </a>
                            </li>
                        @endif
                        @if (Helpers::hasPermission(['branch']))
                            <li>
                                <a href="{{ route('backend.branch_list') }}">
                                    <i class="ri-circle-fill circle-icon text-warning-main w-auto"></i>
                                    Şubeler
                                </a>
                            </li>
                        @endif
                        @if (Helpers::hasPermission(['department']))
                            <li>
                                <a href="{{ route('backend.department_list') }}">
                                    <i class="ri-circle-fill circle-icon text-info-main w-auto"></i>
                                    Departmanlar
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if (Helpers::hasPermission(['shift_definition', 'shift_assignment', 'user_shift_custom', 'shift_follow', 'weekly_holiday']))
                <li class="dropdown">
                    <a href="javascript:void(0)">
                        <iconify-icon icon="fluent:shifts-30-minutes-24-regular" class="menu-icon"></iconify-icon>
                        <span>Vardiya Yönetimi</span>
                    </a>
                    <ul class="sidebar-submenu">
                        @if (Helpers::hasPermission(['shift_definition']))
                            <li>
                                <a href="{{ route('backend.shift_definition_list') }}">
                                    <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>
                                    Vardiya Tanımlamaları
                                </a>
                            </li>
                        @endif
                        @if (Helpers::hasPermission(['shift_assignment']))
                            <li>
                                <a href="{{ route('backend.shift_assignment_list') }}">
                                    <i class="ri-circle-fill circle-icon text-success-main w-auto"></i>
                                    Vardiya Atamaları
                                </a>
                            </li>
                        @endif
                        @if (Helpers::hasPermission(['weekly_holiday']))
                            <li>
                                <a href="{{ route('backend.weekly_holiday_list') }}">
                                    <i class="ri-circle-fill circle-icon text-warning-main w-auto"></i>
                                    Haftalık Tatil Günleri
                                </a>
                            </li>
                        @endif
                        @if (Helpers::hasPermission(['user_shift_custom']))
                            <li>
                                <a href="{{ route('backend.user_shift_custom_list') }}">
                                    <i class="ri-circle-fill circle-icon text-secondary w-auto"></i>
                                    Özel Vardiya Tanımlamaları
                                </a>
                            </li>
                        @endif
                        @if (Helpers::hasPermission(['shift_follow']))
                            <li>
                                <a href="{{ route('backend.shift_follow_list') }}">
                                    <i class="ri-circle-fill circle-icon text-info-main w-auto"></i>
                                    Giriş-Çıkış Kayıtları
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if (Helpers::hasPermission(['holiday', 'official_holiday', 'hourly_leave']))
                <li class="dropdown">
                    <a href="javascript:void(0)">
                        <iconify-icon icon="fluent:calendar-20-regular" class="menu-icon"></iconify-icon>
                        <span>İzin Yönetimi</span>
                    </a>
                    <ul class="sidebar-submenu">
                        @if (Helpers::hasPermission(['holiday']))
                            <li>
                                <a href="{{ route('backend.holiday_list') }}">
                                    <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>
                                    İzin Talepleri
                                </a>
                            </li>
                        @endif
                        @if (Helpers::hasPermission(['hourly_leave']))
                            <li>
                                <a href="{{ route('backend.hourly_leave_list') }}">
                                    <i class="ri-circle-fill circle-icon text-info-main w-auto"></i>
                                    Saatlik İzin Talepleri
                                </a>
                            </li>
                        @endif
                        @if (Helpers::hasPermission(['official_holiday']))
                            <li>
                                <a href="{{ route('backend.official_holiday_list') }}">
                                    <i class="ri-circle-fill circle-icon text-warning-main w-auto"></i>
                                    Resmi Tatil Günleri
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if (Helpers::hasPermission(['debit_device']))
                <li class="dropdown">
                    <a href="javascript:void(0)">
                        <iconify-icon icon="tabler:devices" class="menu-icon"></iconify-icon>
                        <span>Zimmet Yönetimi</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a href="{{ route('backend.debit_device_list') }}">
                                <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>
                                Zimmetler
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('backend.user_debit_device_list') }}">
                                <i class="ri-circle-fill circle-icon text-warning-main w-auto"></i>
                                Zimmet Atamaları
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            @if (Helpers::hasPermission(['file_type', 'user_file']))
                <li class="dropdown">
                    <a href="javascript:void(0)">
                        <iconify-icon icon="carbon:document" class="menu-icon"></iconify-icon>
                        <span>Dosya Yönetimi</span>
                    </a>
                    <ul class="sidebar-submenu">
                        @if (Helpers::hasPermission(['file_type']))
                            <li>
                                <a href="{{ route('backend.file_type_list') }}">
                                    <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>
                                    Dosya Tipleri
                                </a>
                            </li>
                        @endif
                        @if (Helpers::hasPermission(['user_file']))
                            <li>
                                <a href="{{ route('backend.user_file_list') }}">
                                    <i class="ri-circle-fill circle-icon text-warning-main w-auto"></i>
                                    Personel Dosyaları
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if (Helpers::hasPermission(['announcement', 'event']))
                <li class="dropdown">
                    <a href="javascript:void(0)">
                        <iconify-icon icon="carbon:notification" class="menu-icon"></iconify-icon>
                        <span>Duyuru & Etkinlik Yönetimi</span>
                    </a>
                    <ul class="sidebar-submenu">
                        @if (Helpers::hasPermission(['announcement']))
                            <li>
                                <a href="{{ route('backend.announcements_list') }}">
                                    <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>
                                    Duyurular
                                </a>
                            </li>
                        @endif
                        @if (Helpers::hasPermission(['event']))
                            <li>
                                <a href="{{ route('backend.event_list') }}">
                                    <i class="ri-circle-fill circle-icon text-warning-main w-auto"></i>
                                    Etkinlikler
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if (Helpers::hasPermission(['user', 'role']))
                <li class="dropdown">
                    <a href="javascript:void(0)">
                        <iconify-icon icon="flowbite:users-group-outline" class="menu-icon"></iconify-icon>
                        <span>Kullanıcı Yönetimi</span>
                    </a>
                    <ul class="sidebar-submenu">
                        @if (Helpers::hasPermission(['user']))
                            <li>
                                <a href="{{ route('backend.user_list') }}">
                                    <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i>
                                    Kullanıcılar
                                </a>
                            </li>
                        @endif
                        @if (Helpers::hasPermission(['role']))
                            <li>
                                <a href="{{ route('backend.role_list') }}">
                                    <i class="ri-circle-fill circle-icon text-warning-main w-auto"></i>
                                    Roller
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
        </ul>
    </div>
</aside>

<style>
.sidebar-submenu {
    display: none;
    padding-left: 2.5rem;
    margin-top: 0.5rem;
    transition: all 0.2s ease;
}

.dropdown.active .sidebar-submenu {
    display: block;
}

.sidebar-submenu li a {
    padding: 0.5rem 1rem;
    display: flex;
    align-items: center;
    font-size: 0.875rem;
    color: var(--body-color);
    transition: all 0.2s ease;
}

.sidebar-submenu li a:hover {
    color: var(--primary-600);
    background: var(--primary-50);
    border-radius: 0.375rem;
}

.sidebar-submenu li a i {
    margin-right: 0.5rem;
    font-size: 0.5rem;
}

.dropdown > a::after {
    content: "\ea4e";
    font-family: "remixicon";
    margin-left: auto;
    transition: transform 0.2s ease;
}

.dropdown.active > a::after {
    transform: rotate(90deg);
}

.dropdown > a:hover {
    background: var(--primary-50);
    border-radius: 0.375rem;
}

.dropdown > a {
    cursor: pointer;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sayfa yüklendiğinde aktif menüyü bul ve aç
    const currentPath = window.location.pathname;

    // Önce tam URL eşleşmesi dene
    let activeLink = document.querySelector(`.sidebar-submenu a[href="${currentPath}"]`);

    // Tam eşleşme yoksa, modül bazlı eşleştirme yap
    if (!activeLink) {
        const pathParts = currentPath.split('/').filter(part => part !== '');

                if (pathParts.length >= 2 && pathParts[0] === 'admin') {
            let moduleName = pathParts[1];

            // Özel URL yapıları için modül adını düzelt
            if (pathParts.length >= 3) {
                // /admin/user/file -> user_file
                // /admin/user/file/form -> user_file
                // /admin/user/shift/custom -> user_shift_custom
                // /admin/user/debit/device -> user_debit_device
                if (moduleName === 'user') {
                    if (pathParts[2] === 'file') {
                        moduleName = 'user_file';
                    } else if (pathParts[2] === 'shift') {
                        moduleName = 'user_shift_custom';
                    } else if (pathParts[2] === 'debit') {
                        moduleName = 'user_debit_device';
                    }
                }
            }



                                    // Sidebar'daki tüm linkleri kontrol et
            const allLinks = document.querySelectorAll('.sidebar-submenu a');

            for (let link of allLinks) {
                const linkHref = link.getAttribute('href');
                if (linkHref) {

                    // Tam URL eşleştirmesi (en hassas)
                    if (linkHref === currentPath) {
                        activeLink = link;
                        break;
                    }

                    // Özel modül eşleştirmeleri
                    if (moduleName === 'user_file' && (linkHref.includes('/admin/user/file') || linkHref.includes('/admin/user_file'))) {
                        activeLink = link;
                        break;
                    }
                    else if (moduleName === 'user_shift_custom' && (linkHref.includes('/admin/user/shift/custom') || linkHref.includes('/admin/user_shift_custom'))) {
                        activeLink = link;
                        break;
                    }
                    else if (moduleName === 'user_debit_device' && (linkHref.includes('/admin/user/debit/device') || linkHref.includes('/admin/user_debit_device'))) {
                        activeLink = link;
                        break;
                    }
                    // Normal kullanıcı sayfaları için hassas eşleştirme
                    else if (moduleName === 'user' && linkHref.includes('/admin/user') && !linkHref.includes('/admin/user/')) {
                        activeLink = link;
                        break;
                    }
                    // Normal tam eşleşme kontrolü (user hariç)
                    else if (moduleName !== 'user' && linkHref.includes(`/admin/${moduleName}`)) {
                        activeLink = link;
                        break;
                    }
                    // Özel durum: announcements linki announcements modülü için
                    else if (linkHref.includes('/admin/announcements') && moduleName === 'announcements') {
                        activeLink = link;
                        break;
                    }
                }
            }
        }
    }

    // Aktif link bulunduysa dropdown'ı aç ve linki vurgula
    if (activeLink) {
        const parentDropdown = activeLink.closest('.dropdown');
        if (parentDropdown) {
            parentDropdown.classList.add('active');
        }

        // Aktif linki vurgula
        activeLink.style.color = 'var(--primary-600)';
        activeLink.style.background = 'var(--primary-50)';
        activeLink.style.borderRadius = '0.375rem';
    }

    // Ana menü öğelerine tıklama olayı
    document.querySelectorAll('.dropdown').forEach(dropdown => {
        const dropdownTitle = dropdown.querySelector('a:first-child');

        dropdownTitle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Diğer tüm dropdown'ları kapat
            document.querySelectorAll('.dropdown').forEach(d => {
                if (d !== dropdown) {
                    d.classList.remove('active');
                }
            });

            // Tıklanan dropdown'ı aç
            dropdown.classList.add('active');
        });
    });

    // Alt menü linklerine tıklama olayı
    document.querySelectorAll('.sidebar-submenu a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
});
</script>
