<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="tr" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Bakım Modu | {{ config('app.name') }}</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.min.css') }}">
    
    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <style>
        .custom-bg {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        
        .maintenance-container {
            padding: 40px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            margin-top: 40px;
        }
        
        .countdown-item {
            background-color: #5D87FF !important;
        }
        
        .btn-primary-custom {
            background-color: #5D87FF;
            border-color: #5D87FF;
            color: white;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom:hover {
            background-color: #4A6FE6;
            border-color: #4A6FE6;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(93, 135, 255, 0.3);
        }
    </style>
</head>

<body>
    <div class="custom-bg">
        <div class="container container--xl">
            <div class="d-flex align-items-center justify-content-between py-24">
                <a href="{{ route('backend.index') }}" class="">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" height="40">
                </a>
            </div>

            <div class="maintenance-container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <h3 class="mb-32 max-w-1000-px">Sitemiz şu anda bakım modunda</h3>
                        <p class="text-neutral-500 max-w-700-px text-lg">Sistemimizde bakım ve güncelleme çalışmaları yapılmaktadır. Lütfen daha sonra tekrar ziyaret edin.</p>

                        <div class="countdown my-56 d-flex align-items-center flex-wrap gap-md-4 gap-3" id="maintenance-timer">
                            <div class="d-flex flex-column align-items-center">
                                <h4 class="days countdown-item mb-0 w-110-px fw-medium h-110-px w-100 h-100 rounded-circle text-white aspect-ratio-1 d-flex justify-content-center align-items-center">0</h4>
                                <span class="text-neutral-500 text-md text-uppercase fw-medium mt-8">Gün</span>
                            </div>
                            <div class="d-flex flex-column align-items-center">
                                <h4 class="hours countdown-item mb-0 w-110-px fw-medium h-110-px w-100 h-100 rounded-circle text-white aspect-ratio-1 d-flex justify-content-center align-items-center">0</h4>
                                <span class="text-neutral-500 text-md text-uppercase fw-medium mt-8">Saat</span>
                            </div>
                            <div class="d-flex flex-column align-items-center">
                                <h4 class="minutes countdown-item mb-0 w-110-px fw-medium h-110-px w-100 h-100 rounded-circle text-white aspect-ratio-1 d-flex justify-content-center align-items-center">0</h4>
                                <span class="text-neutral-500 text-md text-uppercase fw-medium mt-8">Dakika</span>
                            </div>
                            <div class="d-flex flex-column align-items-center">
                                <h4 class="seconds countdown-item mb-0 w-110-px fw-medium h-110-px w-100 h-100 rounded-circle text-white aspect-ratio-1 d-flex justify-content-center align-items-center">0</h4>
                                <span class="text-neutral-500 text-md text-uppercase fw-medium mt-8">Saniye</span>
                            </div>
                        </div>

                        <div class="mt-24 max-w-500-px text-start">
                            <span class="fw-semibold text-neutral-600 text-lg text-hover-neutral-600">Bakım süreci hakkında bilgi almak için bize ulaşın</span>
                            <div class="mt-16 d-flex gap-16 flex-sm-row flex-column">
                                <a href="mailto:info@example.com" class="btn btn-primary-custom px-24 flex-shrink-0 d-flex align-items-center justify-content-center gap-8">
                                    <i class="ri-mail-line"></i> E-posta Gönder
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 d-lg-block d-none">
                        <img src="{{ asset('assets/images/error-img.png') }}" alt="Bakım Modu" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function() {
        /***** CALCULATE THE TIME REMAINING *****/
        function getTimeRemaining(endtime) {
            var t = Date.parse(endtime) - Date.parse(new Date());

            /***** CONVERT THE TIME TO A USEABLE FORMAT *****/
            var seconds = Math.floor((t / 1000) % 60);
            var minutes = Math.floor((t / 1000 / 60) % 60);
            var hours = Math.floor((t / (1000 * 60 * 60)) % 24);
            var days = Math.floor(t / (1000 * 60 * 60 * 24));

            /***** OUTPUT THE CLOCK DATA AS A REUSABLE OBJECT *****/
            return {
                total: t,
                days: days,
                hours: hours,
                minutes: minutes,
                seconds: seconds,
            };
        }

        /***** DISPLAY THE CLOCK AND STOP IT WHEN IT REACHES ZERO *****/
        function initializeClock(id, endtime) {
            var clock = document.getElementById(id);
            var daysSpan = clock.querySelector(".days");
            var hoursSpan = clock.querySelector(".hours");
            var minutesSpan = clock.querySelector(".minutes");
            var secondsSpan = clock.querySelector(".seconds");

            function updateClock() {
                var t = getTimeRemaining(endtime);

                daysSpan.innerHTML = t.days;
                hoursSpan.innerHTML = ("0" + t.hours).slice(-2);
                minutesSpan.innerHTML = ("0" + t.minutes).slice(-2);
                secondsSpan.innerHTML = ("0" + t.seconds).slice(-2);

                if (t.total <= 0) {
                    clearInterval(timeinterval);
                }
            }

            updateClock(); // run function once at first to avoid delay
            var timeinterval = setInterval(updateClock, 1000);
        }

        /***** SET A VALID END DATE *****/
        var deadline = new Date(Date.parse(new Date()) + 2 * 24 * 60 * 60 * 1000); // 2 gün
        initializeClock("maintenance-timer", deadline);
    })();
    </script>

    <!-- Bootstrap JS -->
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
</body>

</html>
