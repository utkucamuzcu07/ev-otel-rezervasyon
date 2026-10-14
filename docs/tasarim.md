# Tasarım Dokümanı: Otel / Kiralık Ev Rezervasyon Sistemi

## 1. Mimari

Her istek aynı yoldan geçer: `public/index.php` → Router → Controller → Service → Repository. Her katmanın tek bir görevi vardır ve bir katman yalnızca altındaki katmanı çağırır.

| Katman | Görevi | Asla yapmaz | Otel rezervasyon örneği |
|---|---|---|---|
| Router | Adrese göre hangi controller metodunun çalışacağını seçer | İş kuralı içermez | `POST /rezervasyon` → `ReservationController::store` |
| Controller | `$_POST/$_GET` verisini alır, servisi çağırır, görünümü seçer | SQL yazmaz, hesap yapmaz | `ReservationController` |
| Service | İş kuralları ve çekirdek algoritma | `$_POST` okumaz, HTML üretmez | `AvailabilityService::isAvailable` (çakışma), `PricingService::quote` (gecelik döküm), `CancellationService::refund` |
| Repository | Veriyi okur ve yazar | İş kuralı içermez | `ReservationRepositoryInterface` |
| Model | Veriyi taşıyan sade sınıf | Veritabanına erişmez | `Reservation`, `Property` |
| View | HTML gösterir, çıktıyı kaçışlar | Hesap yapmaz, veri çekmez | `views/reservations/form.php` |

**Çekirdek problem:** Tarih çakışması ve çift rezervasyonun önlenmesi (`AvailabilityService`), sezonluk dinamik fiyat (`PricingService`, Strategy deseni) ve iptal/iade hesabı (`CancellationService`).

### Klasör yapısı

```
ev-otel-rezervasyon/
├── public/
│   └── index.php
├── src/
│   ├── Router.php
│   ├── Controller/      (Auth, Property, Reservation, Profile, Host, Review,
│   │                     Complaint, Admin, Api)
│   ├── Service/         (Auth, Availability, Property, Reservation, Pricing,
│   │   │                 Cancellation, Favorite, Review, Notification, Complaint,
│   │   │                 User, Report, ImageUpload)
│   │   └── Pricing/     (Weekend, Season, LongStayDiscount stratejileri)
│   ├── Repository/
│   │   ├── *Interface.php
│   │   ├── Json/        (Aşama 2)
│   │   └── Pdo/         (Aşama 3)
│   └── Model/           (User, RoomType, Property, Photo, Amenity, PriceRule,
│                         Availability, Reservation, Cancellation, Review,
│                         Favorite, Notification, Complaint)
├── views/
├── database/
├── tests/
├── docs/
└── storage/
```

**Kontrol sorusu:** Veritabanını değiştirirsek yalnızca `src/Repository/Pdo/` altındaki sınıflar değişir.