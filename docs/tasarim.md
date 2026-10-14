01.ana-sayfa.png
02.arama-sonuclari.png
03.ilan-detay-rezervasyon.png
04.rezervasyon-özeti.png
05.giris-kayit.png

use case kodu mermaid ile
## 2. Sınıf Diyagramı

Diyagram, çekirdek akışı gösterir: rezervasyon oluşturma, çakışma kontrolü, Strategy ile gecelik fiyat hesabı, iptal ve iade. Controller yalnızca Service'i, Service yalnızca Repository arayüzünü çağırır.

```mermaid
classDiagram
    class ReservationController {
        +store() void
        +cancel() void
    }

    class ReservationService {
        +create(int userId, int propertyId, DateTimeImmutable checkIn, DateTimeImmutable checkOut, int guests) Reservation
    }
    class AvailabilityService {
        +isAvailable(int propertyId, DateTimeImmutable checkIn, DateTimeImmutable checkOut) bool
        +assertCapacity(Property property, int guests) void
    }
    class PricingService {
        +quote(Property property, DateTimeImmutable checkIn, DateTimeImmutable checkOut) PriceQuote
    }
    class CancellationService {
        +cancel(int reservationId, int userId) Cancellation
        +refundAmount(Reservation reservation) float
    }

    class PricingStrategyInterface {
        <<interface>>
        +apply(float price, DateTimeImmutable night, int totalNights) float
    }
    class WeekendStrategy
    class SeasonStrategy
    class LongStayDiscountStrategy

    class ReservationRepositoryInterface {
        <<interface>>
        +save(Reservation reservation) Reservation
        +findOverlapping(int propertyId, DateTimeImmutable checkIn, DateTimeImmutable checkOut) array
        +findById(int id) Reservation
    }
    class JsonReservationRepository
    class PdoReservationRepository
    class PropertyRepositoryInterface {
        <<interface>>
        +findById(int id) Property
    }
    class PriceRuleRepositoryInterface {
        <<interface>>
        +findByProperty(int propertyId) array
    }
    class CancellationRepositoryInterface {
        <<interface>>
        +save(Cancellation cancellation) Cancellation
    }

    class Reservation {
        +int id
        +string number
        +DateTimeImmutable checkIn
        +DateTimeImmutable checkOut
        +string status
    }
    class Property {
        +int id
        +string name
        +int capacity
        +float basePrice
    }
    class RoomType {
        +int id
        +string name
    }
    class User {
        +int id
        +string role
    }
    class PriceRule {
        +int id
        +string type
    }
    class Cancellation {
        +int id
        +float refundAmount
    }
    class PriceQuote {
        +float total
    }
    class NightlyCharge {
        +DateTimeImmutable night
        +float price
    }

    ReservationController --> ReservationService
    ReservationController --> CancellationService

    ReservationService --> AvailabilityService
    ReservationService --> PricingService
    ReservationService --> ReservationRepositoryInterface
    AvailabilityService --> ReservationRepositoryInterface
    AvailabilityService --> PropertyRepositoryInterface
    PricingService --> PriceRuleRepositoryInterface
    PricingService --> PricingStrategyInterface
    CancellationService --> ReservationRepositoryInterface
    CancellationService --> CancellationRepositoryInterface

    WeekendStrategy ..|> PricingStrategyInterface
    SeasonStrategy ..|> PricingStrategyInterface
    LongStayDiscountStrategy ..|> PricingStrategyInterface
    JsonReservationRepository ..|> ReservationRepositoryInterface
    PdoReservationRepository ..|> ReservationRepositoryInterface

    PricingService ..> PriceQuote
    PriceQuote *-- NightlyCharge
    ReservationRepositoryInterface ..> Reservation
    Reservation --> Property
    Reservation --> User
    Property --> RoomType
    PriceRule --> Property
    Cancellation --> Reservation
```

### Diyagramın okunuşu
- **Strategy:** `PricingService`, fiyat kurallarını `PricingStrategyInterface` üzerinden çalıştırır. Yeni kural eklemek yeni bir sınıf eklemektir.
- **Repository:** Servisler yalnızca `...RepositoryInterface` bilir. JSON (Aşama 2) ve PDO (Aşama 3) uygulamaları aynı arayüzü uygular.
- **Çekirdek problem:** `AvailabilityService::isAvailable`, `findOverlapping` ile çakışmaları arar. Aynı gün çıkış-giriş çakışma sayılmaz.
3. Sıralama Diyagramı – "Rezervasyon Oluştur" (çekirdek akış, hata dallarıyla)

Akış, UC-02 ve FR-11/12/13/14/18/26'ya dayanır. Üç hata dalı vardır: (1) geçersiz tarih/kapasite, (2) tarih aralığı müsait değil, (3) eş zamanlı başka istek müsaitliği tüketmiş (yarış durumu).

NotificationService
AvailabilityRepository
ReservationRepository
PricingService
AvailabilityService
ReservationService
ReservationController
Tarayıcı
NotificationService
AvailabilityRepository
ReservationRepository
PricingService
AvailabilityService
ReservationService
ReservationController
Tarayıcı
alt
[başka istek geceleri bu arada tüketmiş]
[hâlâ müsait]
alt
[aralıkta dolu/kapalı gece var]
[aralık müsait]
alt
[tarih veya kapasite geçersiz]
[geçerli]
POST /rezervasyon (ilanId, girisTarihi, cikisTarihi, kisiSayisi)
create(userId, data)
giriş < çıkış mı? (BR-01, BR-02)
assertBookable(ilanId, giris, cikis, kisiSayisi)
kişi sayısı <= kapasite mi? (BR-03)
ValidationException
ValidationException
formu alan bazlı hata mesajıyla tekrar göster
findRange(ilanId, giris, cikis)
gecelik müsaitlik kayıtları
NotAvailableException
NotAvailableException
"Tarih müsait değil, alternatif tarih seçin"
tamam
quote(ilan, giris, cikis)
kalemler (konaklama, vergi, ek ücret) ve toplam
beginTransaction()
block(ilanId, giris, cikis)
lockRange(ilanId, giris, cikis)
kilitli kayıtlar (güncel durum)
ConflictException
rollback()
ConflictException
"Bu tarihler az önce rezerve edildi"
markRange(ilanId, giris, cikis, false)
save(rezervasyon + ücret kalemleri)
rezervasyon no (benzersiz, FR-13)
commit()
notify(userId, "REZERVASYON", mesaj)
Reservation (durum: Beklemede)
302 /rezervasyonlarim + "Rezervasyon oluşturuldu"
Sıralama diyagramı gerekçeleri
Doğrulama iki aşamalıdır: hızlı kontroller (tarih, kapasite) işlem başlamadan yapılır; müsaitlik kontrolü ise kilit altında tekrarlanır. Yalnızca ilk kontrole güvenilirse iki kullanıcı aynı anda "müsait" görüp ikisi de kayıt oluşturabilir (AC-05).
lockRange ve transaction: aynı kaynak ve gece için ikinci istek, ilkinin işlemi bitene kadar bekler; ilk işlem geceleri kapatınca ikinci istek ConflictException alır. Böylece NFR-05 sağlanır.
Controller yalnızca Service'i çağırır; SQL yok, hesap yok. Fiyat hesabı PricingService'te, çakışma kontrolü AvailabilityService'te yapılır.
Diyagramda SQL yoktur; yalnızca metot çağrıları gösterilmiştir (kılavuzdaki "sık hata" uyarısı).
Ödeme simülasyonu ilk sürümde kapsam dışıdır (SRS 10.3); kayıt Beklemede durumunda oluşur, ilan sahibi onay/ret verir (FR-23).
