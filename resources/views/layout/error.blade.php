<!DOCTYPE html>
<html lang="tr">
<x-head />
<style>
body {
    background-color: #f8f9fa;
    font-family: 'Inter', sans-serif;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0;
    padding: 0;
}

.error-container {
    max-width: 600px;
    padding: 40px;
    text-align: center;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.error-code {
    font-size: 52px;
    font-weight: 800;
    color: #5D87FF;
    margin-bottom: 20px;
    line-height: 1;
}

.error-title {
    font-size: 24px !important;
    font-weight: 700;
    color: #2A3547;
    margin-bottom: 16px;
}

.error-message {
    font-size: 16px;
    color: #6C757D;
}

.error-image {
    max-width: 200px;
    margin-bottom: 30px;
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

.logo {
    margin-bottom: 30px;
}
</style>
<body>
    <div class="error-container">
        <div class="logo">
            <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo" height="80">
        </div>
        @yield('content')
    </div>
</body>
</html>