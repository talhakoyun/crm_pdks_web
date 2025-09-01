@extends('backend.layout.layout')
<style>
    .wizard-form-error-text {
        position: absolute;
        color: red;
        font-size: 13px;
    }
</style>
@php
    $title = $container->title . (is_null($item->id) ? ' Ekle' : ' Düzenle');
    $subTitle = $container->title . (is_null($item->id) ? ' Ekle' : ' Düzenle');
    $isSuperAdmin = request()->attributes->get('is_admin', false);
    $script = ' <script>
        // =============================== Wizard Step Js Start ================================
        $(document).ready(function() {
                        function checkRoleAndToggleWizard() {
                var selectedRoleId = $(\'select[name="role_id"]\').val();
                var isPersonnel = ["5", "6", "7"].includes(selectedRoleId);

                var wizardSteps = $(\'[data-attr="step2"]\');
                var wizardFieldsets = $(\'[data-tab-content="step2"]\');
                var wizardLine = $(\'.form-wizard-list__item[data-attr="step1"] .form-wizard-list__line\');

                if (isPersonnel) {
                    wizardSteps.show();
                    wizardLine.show(); // Çizgiyi göster
                    wizardFieldsets.removeClass(\'wizard-fieldset-hidden\');
                    wizardFieldsets.find(\'.wizard-required\').attr(\'required\', true);
                    $(\'.form-wizard-next-btn\').show().css(\'display\', \'inline-block\');
                    $(\'button[type="submit"]\').not(\'.form-wizard-submit\').hide();
                    $(\'.form-wizard-submit\').show().css(\'display\', \'inline-block\');

                    // Edit modunda wizard fieldset gorunurligunu duzelt
                    wizardFieldsets.each(function() {
                        if (!$(this).hasClass(\'show\')) {
                            $(this).hide();
                        }
                    });
                } else {
                    wizardSteps.hide();
                    wizardLine.hide(); // Çizgiyi gizle
                    wizardFieldsets.addClass(\'wizard-fieldset-hidden\');
                    wizardFieldsets.hide(); // Tum wizard fieldsetleri gizle
                    wizardFieldsets.removeClass(\'show\'); // Show classini kaldir
                    wizardFieldsets.find(\'.wizard-required\').removeAttr(\'required\');
                    $(\'.form-wizard-next-btn\').hide();
                    $(\'button[type="submit"]\').not(\'.form-wizard-submit\').show().css(\'display\', \'inline-block\');
                    $(\'.form-wizard-submit\').hide();
                }
            }

            checkRoleAndToggleWizard();

            $(document).on(\'change\', \'select[name="role_id"]\', function() {
                checkRoleAndToggleWizard();
            });

                            $(".form-wizard-next-btn").on("click", function() {
                var parentFieldset = $(this).parents(".wizard-fieldset");
                var currentActiveStep = $(this).parents(".form-wizard").find(".form-wizard-list .active");
                var next = $(this);
                var nextWizardStep = true;
                parentFieldset.find(".wizard-required").each(function() {
                    var thisValue = $(this).val();

                    if (thisValue == "") {
                        $(this).parent().find(".wizard-form-error").show();
                        $(this).parent().find(".wizard-form-error").after("<span class=\'wizard-form-error-text\'>Lütfen zorunlu alanları doldurunuz.</span>");
                        nextWizardStep = false;
                    } else {
                        $(this).siblings(".wizard-form-error").hide();
                    }
                });
                if (nextWizardStep) {
                    // Mevcut fieldseti gizle
                    next.parents(".wizard-fieldset").removeClass("show").hide();
                    currentActiveStep.removeClass("active").addClass("activated").next().addClass("active",
                        "400");
                    // Sonraki fieldseti goster
                    next.parents(".wizard-fieldset").next(".wizard-fieldset").addClass("show").show();

                    $(document).find(".wizard-fieldset").each(function() {
                        if ($(this).hasClass("show")) {
                            var formAtrr = $(this).attr("data-tab-content");
                            $(document).find(".form-wizard-list .form-wizard-step-item").each(
                                function() {
                                    if ($(this).attr("data-attr") == formAtrr) {
                                        $(this).addClass("active");
                                        var innerWidth = $(this).innerWidth();
                                        var position = $(this).position();
                                        $(document).find(".form-wizard-step-move").css({
                                            "left": position.left,
                                            "width": innerWidth
                                        });
                                    } else {
                                        $(this).removeClass("active");
                                    }
                                });
                        }
                    });
                }
            });
            $(".form-wizard-previous-btn").on("click", function() {
                var counter = parseInt($(".wizard-counter").text());;
                var prev = $(this);
                var currentActiveStep = $(this).parents(".form-wizard").find(".form-wizard-list .active");
                // Mevcut fieldseti gizle
                prev.parents(".wizard-fieldset").removeClass("show").hide();
                // Onceki fieldseti goster
                prev.parents(".wizard-fieldset").prev(".wizard-fieldset").addClass("show").show();
                currentActiveStep.removeClass("active").prev().removeClass("activated").addClass("active",
                    "400");
                $(document).find(".wizard-fieldset").each(function() {
                    if ($(this).hasClass("show")) {
                        var formAtrr = $(this).attr("data-tab-content");
                        $(document).find(".form-wizard-list .form-wizard-step-item").each(
                            function() {
                                if ($(this).attr("data-attr") == formAtrr) {
                                    $(this).addClass("active");
                                    var innerWidth = $(this).innerWidth();
                                    var position = $(this).position();
                                    $(document).find(".form-wizard-step-move").css({
                                        "left": position.left,
                                        "width": innerWidth
                                    });
                                } else {
                                    $(this).removeClass("active");
                                }
                            });
                    }
                });
            });
            $(document).on("click", ".form-wizard .form-wizard-submit", function() {
                var parentFieldset = $(this).parents(".wizard-fieldset");
                var currentActiveStep = $(this).parents(".form-wizard").find(".form-wizard-list .active");
                parentFieldset.find(".wizard-required").each(function() {
                    var thisValue = $(this).val();
                    console.log($(this).attr("name"),thisValue);
                    if (thisValue == "") {
                        $(this).siblings(".wizard-form-error").show();
                    } else {
                        $(this).siblings(".wizard-form-error").hide();
                    }
                });
            });
            $(".form-control").on("focus", function() {
                var tmpThis = $(this).val();
                if (tmpThis == "") {
                    $(this).parent().addClass("focus-input");
                } else if (tmpThis != "") {
                    $(this).parent().addClass("focus-input");
                }
            }).on("blur", function() {
                var tmpThis = $(this).val();
                if (tmpThis == "") {
                    $(this).parent().removeClass("focus-input");
                    $(this).siblings(".wizard-form-error").show();
                } else if (tmpThis != "") {
                    $(this).parent().addClass("focus-input");
                    $(this).siblings(".wizard-form-error").hide();
                }
            });
        });
        // =============================== Wizard Step Js End ================================
    </script>';
@endphp

@section('content')
    <div class="row gy-4">
        <div class="col-md-12">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-4 text-xl">
                            <span>{{ $subTitle }}</span>
                        </h6>
                        <p class="text-neutral-500">Kullanıcı Ekle.</p>

                        <!-- Form Wizard Start -->
                        <div class="form-wizard">
                            <form action="{{ route('backend.' . $container->page . '_save', ['unique' => $item->id]) }}"
                                method="POST">
                                @csrf
                                <div class="form-wizard-header overflow-x-auto scroll-sm pb-8 my-32">
                                    <ul class="form-wizard-list d-flex align-items-center justify-content-center" style="gap: 40px;">
                                        <li class="form-wizard-list__item active" data-attr="step1" style="flex: none;">
                                            <div class="form-wizard-list__line">
                                                <span class="count">1</span>
                                            </div>
                                            <span class="text text-xs fw-semibold">Kişisel Bilgileri</span>
                                        </li>
                                                                @if(!$isSuperAdmin)
                        <li class="form-wizard-list__item" data-attr="step2" style="display: {{ in_array((old('role_id') ?? $item->role_id), [5, 6, 7]) ? 'block' : 'none' }}; flex: none;">
                            <div class="form-wizard-list__line">
                                <span class="count">2</span>
                            </div>
                            <span class="text text-xs fw-semibold">Vardiyası ve Departmanı</span>
                        </li>
                        @endif
                                    </ul>
                                </div>

                                <fieldset class="wizard-fieldset show" data-tab-content="step1">
                                    @include('backend.user.field.personal')
                                </fieldset>

                                @if(!$isSuperAdmin)
                                <fieldset class="wizard-fieldset {{ in_array((old('role_id') ?? $item->role_id), [5, 6, 7]) ? '' : 'wizard-fieldset-hidden' }}" data-tab-content="step2">
                                    @include('backend.user.field.shift')
                                </fieldset>
                                @endif
                            </form>
                        </div>
                        <!-- Form Wizard End -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    {{ $script }}
@endsection

@section('script')
<script>
console.log('Script başladı');
$(document).ready(function() {
    var selectedRoleId = $('select[name="role_id"]').val();
    console.log("İlk yüklemede role_id:", selectedRoleId);
    $(document).on('change', 'select[name="role_id"]', function() {
        var selectedRoleId = $(this).val();
        console.log("Seçilen role_id:", selectedRoleId);
        // Burada selectedRoleId'ye göre istediğin işlemleri yapabilirsin
    });
});
console.log('Script bitti');
</script>
<script>
    alert('Form script çalıştı!');
</script>
<script>
    alert('Formun en altındaki script çalıştı!');
    $(document).ready(function() {
        var selectedRoleId = $('select[name="role_id"]').val();
        console.log("Formun en altı - İlk yüklemede role_id:", selectedRoleId);
        $(document).on('change', 'select[name="role_id"]', function() {
            var selectedRoleId = $(this).val();
            console.log("Formun en altı - Seçilen role_id:", selectedRoleId);
        });
    });
</script>
@endsection
