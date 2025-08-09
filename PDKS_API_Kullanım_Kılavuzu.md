# PDKS API Kullanım Kılavuzu

Bu doküman, PDKS (Personel Devam Kontrol Sistemi) API'sinin kullanımı hakkında bilgi vermektedir.

## İçindekiler

1. [Genel Bilgiler](#genel-bilgiler)
2. [Kimlik Doğrulama (Auth)](#kimlik-doğrulama-auth)
3. [Vardiya Takibi (Shift Follow)](#vardiya-takibi-shift-follow)
4. [Vardiya Takip Raporları (Shift Follow Report)](#vardiya-takip-raporları-shift-follow-report)
5. [Postman Koleksiyonu Kullanımı](#postman-koleksiyonu-kullanımı)

## Genel Bilgiler

- API Base URL: `http://localhost/api`
- Tüm istekler JSON formatında yapılmalıdır.
- Başlıklar (Headers):
  - `Content-Type: application/json`
  - `Accept: application/json`
  - Kimlik doğrulama gerektiren istekler için: `Authorization: Bearer {token}`

- Yanıt formatı:
  ```json
  {
    "status": true,
    "message": "İşlem mesajı",
    "data": { ... }
  }
  ```

## Kimlik Doğrulama (Auth)

### Login

- **URL:** `/api/auth/login`
- **Metod:** `POST`
- **Açıklama:** Kullanıcı girişi yapar ve JWT token döndürür.
- **İstek Parametreleri:**
  ```json
  {
    "email": "kullanici@ornek.com",
    "password": "sifre123",
    "device_id": "device_123456"
  }
  ```
- **Yanıt:**
  ```json
  {
    "status": true,
    "message": "Giriş başarılı",
    "data": {
      "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
      "token_type": "bearer",
      "expires_in": 3600
    }
  }
  ```

### Refresh Token

- **URL:** `/api/auth/refresh`
- **Metod:** `POST`
- **Açıklama:** JWT token'ı yeniler.
- **Yanıt:**
  ```json
  {
    "status": true,
    "message": "Token yenilendi",
    "data": {
      "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
      "token_type": "bearer",
      "expires_in": 3600
    }
  }
  ```

### Logout

- **URL:** `/api/auth/logout`
- **Metod:** `POST`
- **Açıklama:** Kullanıcı çıkışı yapar ve token'ı geçersiz kılar.
- **Yanıt:**
  ```json
  {
    "status": true,
    "message": "Çıkış başarılı",
    "data": null
  }
  ```

### Me

- **URL:** `/api/auth/me`
- **Metod:** `GET`
- **Açıklama:** Giriş yapmış kullanıcının bilgilerini döndürür.
- **Yanıt:**
  ```json
  {
    "status": true,
    "message": "Kullanıcı bilgileri",
    "data": {
      "id": 1,
      "name": "Örnek Kullanıcı",
      "email": "kullanici@ornek.com",
      ...
    }
  }
  ```

## Vardiya Takibi (Shift Follow)

### Check

- **URL:** `/api/shift-follow/check`
- **Metod:** `POST`
- **Açıklama:** Vardiya giriş/çıkış kontrolü yapar.
- **İstek Parametreleri:**
  ```json
  {
    "type": "check_in",
    "branch_id": 1,
    "zone_id": 1,
    "shift_id": 1,
    "positions": {
      "latitude": 41.0082,
      "longitude": 28.9784
    },
    "is_offline": false,
    "device_id": "device_123456",
    "device_model": "iPhone 12",
    "note": "Giriş yapıldı"
  }
  ```
- **Yanıt:**
  ```json
  {
    "status": true,
    "message": "İşleminiz Başarılı",
    "data": { ... }
  }
  ```

### Store

- **URL:** `/api/shift-follow/store`
- **Metod:** `POST`
- **Açıklama:** Yeni bir vardiya takip kaydı oluşturur.
- **İstek Parametreleri:**
  ```json
  {
    "branch_id": 1,
    "zone_id": 1,
    "user_id": 1,
    "shift_id": 1,
    "transaction_date": "2023-05-20 08:00:00",
    "shift_follow_type_id": 1,
    "positions": {
      "latitude": 41.0082,
      "longitude": 28.9784
    },
    "is_offline": false,
    "device_id": "device_123456",
    "device_model": "iPhone 12",
    "note": "Manuel kayıt"
  }
  ```
- **Yanıt:**
  ```json
  {
    "status": true,
    "message": "Vardiya Takibi başarıyla oluşturuldu",
    "data": { ... }
  }
  ```

### Status

- **URL:** `/api/shift-follow/status`
- **Metod:** `GET`
- **Açıklama:** Kullanıcının mevcut vardiya durumunu döndürür.
- **Yanıt:**
  ```json
  {
    "status": true,
    "message": "İşleminiz Başarılı",
    "data": { ... }
  }
  ```

### List

- **URL:** `/api/shift-follow/list`
- **Metod:** `GET`
- **Açıklama:** Kullanıcının vardiya listesini döndürür.
- **Yanıt:**
  ```json
  {
    "status": true,
    "message": "İşleminiz Başarılı",
    "data": [ ... ]
  }
  ```

### Clear Pings

- **URL:** `/api/shift-follow/clear-pings`
- **Metod:** `POST`
- **Açıklama:** Kullanıcının ping kayıtlarını temizler.
- **Yanıt:**
  ```json
  {
    "status": true,
    "message": "İşleminiz Başarılı",
    "data": null
  }
  ```

### Ping

- **URL:** `/api/shift-follow/ping`
- **Metod:** `POST`
- **Açıklama:** Kullanıcının konum bilgisini kaydeder (ping).
- **İstek Parametreleri:**
  ```json
  {
    "type": 1,
    "positions": {
      "latitude": 41.0082,
      "longitude": 28.9784
    },
    "offline": false,
    "device_id": "device_123456",
    "device_model": "iPhone 12",
    "note": "Ping kaydı"
  }
  ```
- **Yanıt:**
  ```json
  {
    "status": true,
    "message": "İşleminiz Başarılı",
    "data": null
  }
  ```

### Zone

- **URL:** `/api/shift-follow/zone`
- **Metod:** `POST`
- **Açıklama:** Kullanıcının bölge bilgisini kaydeder.
- **İstek Parametreleri:**
  ```json
  {
    "positions": {
      "latitude": 41.0082,
      "longitude": 28.9784
    },
    "offline": false,
    "device_id": "device_123456",
    "device_model": "iPhone 12",
    "note": "Bölge kaydı"
  }
  ```
- **Yanıt:**
  ```json
  {
    "status": true,
    "message": "İşleminiz Başarılı",
    "data": null
  }
  ```

## Vardiya Takip Raporları (Shift Follow Report)

### Daily

- **URL:** `/api/shift-follow-report/daily`
- **Metod:** `POST`
- **Açıklama:** Günlük vardiya takip raporunu döndürür.
- **İstek Parametreleri:**
  ```json
  {
    "date": "2023-05-20",
    "user_id": 1
  }
  ```
- **Yanıt:**
  ```json
  {
    "status": true,
    "message": "Günlük vardiya raporu başarıyla oluşturuldu",
    "data": { ... }
  }
  ```

### Weekly Report

- **URL:** `/api/shift-follow-report/weekly-report`
- **Metod:** `POST`
- **Açıklama:** Haftalık vardiya takip raporunu döndürür.
- **İstek Parametreleri:**
  ```json
  {
    "start_date": "2023-05-15",
    "end_date": "2023-05-21",
    "user_id": 1
  }
  ```
- **Yanıt:**
  ```json
  {
    "status": true,
    "message": "Haftalık vardiya raporu başarıyla oluşturuldu",
    "data": {
      "start_date": "2023-05-15",
      "end_date": "2023-05-21",
      "user_id": 1,
      "daily_summaries": [ ... ],
      "weekly_totals": {
        "total_work_minutes": 2400,
        "total_work_hours": 40.0,
        "total_break_minutes": 300,
        "total_break_hours": 5.0,
        "total_net_work_minutes": 2100,
        "total_net_work_hours": 35.0
      }
    }
  }
  ```

## Postman Koleksiyonu Kullanımı

1. Postman uygulamasını açın.
2. "Import" butonuna tıklayın.
3. "PDKS_API_Collection.postman_collection.json" dosyasını seçin.
4. Koleksiyon içindeki değişkenleri düzenleyin:
   - `base_url`: API'nin çalıştığı sunucu adresi (örn. `http://localhost`)
   - `token`: Giriş yaptıktan sonra alınan JWT token

5. İlk olarak "Login" isteğini gönderin ve dönen token'ı kopyalayın.
6. Koleksiyon değişkenlerinden `token` değişkenini güncelleyin.
7. Artık diğer API isteklerini kullanabilirsiniz.

**Not:** Postman koleksiyonunda yer alan örnekler, gerçek veritabanınızdaki ID'ler ile uyumlu olmayabilir. Kendi sisteminize uygun ID'leri kullanmayı unutmayın.
