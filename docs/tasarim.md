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

5. Tasarım Desenleri
5.1 Repository Deseni (Zorunlu)
1. Problem: Projede kullanıcı, ilan, müsaitlik, rezervasyon, ödeme, yorum, favori, bildirim ve şikâyet olmak üzere çok sayıda kalıcı veri varlığı vardır. Veriye erişim kodu (SQL, dosya okuma) doğrudan servis sınıflarının içine yazılırsa, Aşama 2'de JSON dosyalarıyla Aşama 3'te MySQL ile çalışmak istendiğinde tüm iş mantığı kodunun değişmesi gerekir.

2. Desen: Repository deseni; UserRepositoryInterface, PropertyRepositoryInterface, ReservationRepositoryInterface, AvailabilityRepositoryInterface, ReviewRepositoryInterface gibi arayüzler ve bunların MySql*Repository (Aşama 3) ile Json*Repository (Aşama 2) somut uygulamalarıyla kurulur. Servis sınıfları (ReservationService, AvailabilityService, PricingService) yalnızca arayüzü tanır; gerçek veritabanı sınıfını bilmez.

3. Alternatif: Servis sınıflarının içinde doğrudan PDO veya file_get_contents() çağrıları yapmak; yani katmansız, veri erişimi ile iş kuralını aynı sınıfta toplamak.

4. Neden: Depolama katmanı değiştiğinde (JSON → MySQL) yalnızca yeni bir MySql*Repository sınıfı yazılır; ReservationService ve AvailabilityService içindeki çekirdek iş kuralı (tarih çakışma kontrolü) hiç değişmez. Bu, SRS'teki NFR-08 (Bakım yapılabilirlik) ve FR-18 (çift rezervasyon engeli) gereksinimlerinin sürdürülebilir biçimde karşılanmasını sağlar.

5.2 Strategy Deseni (Fiyatlandırma)
1. Problem: SRS'teki FR-22, ilan sahibinin tarih aralığına göre fiyat tanımlamasını; FR-14 ise gecelik fiyat ile vergi/ek ücretlerin ayrı ayrı hesaplanmasını zorunlu kılar. Aynı ilan için hafta sonu farklı fiyat, yüksek sezon farklı fiyat, uzun konaklamada indirim uygulanabilir. Bu kuralların tümünü PricingService içinde if ($tarih hafta sonu mu) ... elseif ($sezon mu) ... elseif ($gece > 7) ... zinciriyle yazmak hem okunması zor hem de yeni bir kural (örneğin "son dakika indirimi") eklendiğinde mevcut çalışan kodu bozar.

2. Desen: Strategy deseni; PricingStrategyInterface arayüzü ile WeekendPricingStrategy, SeasonalPricingStrategy ve LongStayDiscountStrategy somut strateji sınıfları üzerinden uygulanır. PricingService::calculate(int roomTypeId, string checkIn, string checkOut) metodu, PriceRuleRepositoryInterface'ten gelen kurallara bakarak çalışma anında uygun stratejiyi (setStrategy()) seçer ve fiyat dökümünü dizi olarak döner. Sınıf diyagramında bu ilişki PricingService --> PricingStrategyInterface ve WeekendPricingStrategy ..|> PricingStrategyInterface olarak gösterilmiştir.

3. Alternatif: Tek bir PricingService sınıfı içinde iç içe if/elseif bloklarıyla tüm fiyat kurallarını yönetmek; yani desensiz, tek sorumluluk ilkesini bozan çözüm.

4. Neden: Yeni bir fiyat kuralı (örneğin "bayram dönemi zammı" veya "erken rezervasyon indirimi") eklemek için mevcut PricingService veya diğer strateji sınıflarına dokunmadan yalnızca yeni bir sınıf (HolidayPricingStrategy) yazıp arayüzü uygulamak yeterlidir. Bu, Açık/Kapalı Prensibi'ne (OCP) uygundur; ayrıca her strateji bağımsız olarak birim testine tabi tutulabildiği için SRS'teki AC-08 (toplam fiyatın onay öncesi açık gösterimi) ve kılavuzdaki "en az 10 PHPUnit testi" şartı daha güvenilir biçimde karşılanır. Sunumda "Hangi sınıflar bu deseni oluşturuyor?" sorusuna PricingStrategyInterface ile üç somut strateji sınıfı diyagramdan gösterilerek cevap verilebilir.
