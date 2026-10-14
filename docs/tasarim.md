<<<<<<< HEAD
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
=======
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
## 4. ER Diyagramı
 
```mermaid
erDiagram
    USERS ||--o{ PROPERTIES : "sahip olur"
    USERS ||--o{ RESERVATIONS : "yapar"
    USERS ||--o{ REVIEWS : "yazar"
    USERS ||--o{ FAVORITES : "ekler"
    USERS ||--o{ NOTIFICATIONS : "alır"
    USERS ||--o{ COMPLAINTS : "gönderir"
    USERS ||--o{ PASSWORD_RESET_TOKENS : "talep eder"
    USERS |o--o{ AUDIT_LOGS : "işlem yapar"
 
    PROPERTIES ||--|{ BOOKABLE_UNITS : "içerir"
    PROPERTIES ||--o{ PHOTOS : "sahiptir"
    PROPERTIES ||--o{ PROPERTY_AMENITIES : "sunar"
    AMENITIES ||--o{ PROPERTY_AMENITIES : "ilan listesinde yer alır"
    PROPERTIES ||--o{ PRICING_RULES : "tanımlar"
    PROPERTIES ||--o{ FAVORITES : "favorilenir"
    PROPERTIES |o--o{ COMPLAINTS : "hakkında"
 
    BOOKABLE_UNITS ||--o{ CALENDAR_ENTRIES : "istisna tanımlanır"
    BOOKABLE_UNITS ||--o{ RESERVATIONS : "ayrılır"
    BOOKABLE_UNITS ||--o{ RESERVATION_NIGHTS : "geceleri tutulur"
 
    RESERVATIONS ||--o{ RESERVATION_NIGHTS : "engelleyen geceler"
    RESERVATIONS ||--|{ RESERVATION_CHARGES : "kalemlere ayrılır"
    RESERVATIONS ||--o{ PAYMENTS : "ödenir"
    RESERVATIONS ||--o| REVIEWS : "yorumlanır"
    RESERVATIONS |o--o{ COMPLAINTS : "hakkında"
 
    USERS {
        int id PK
        string first_name
        string last_name
        string email UK
        string password_hash
        enum role "musteri, ilan_sahibi, yonetici"
        enum status "aktif, kisitli"
        datetime created_at
    }
    PROPERTIES {
        int id PK
        int owner_id FK
        string name
        enum type "otel, kiralik_ev"
        text description
        string address
        string city
        enum booking_mode "aninda, onayli"
        enum cancellation_policy "esnek, orta, kati"
        enum status "taslak, aktif, pasif, kaldirildi"
    }
    BOOKABLE_UNITS {
        int id PK
        int property_id FK
        string name "UK (property_id, name)"
        int capacity
        decimal base_price "DECIMAL(10,2)"
        enum status "aktif, pasif"
    }
    PHOTOS {
        int id PK
        int property_id FK
        string url
        int sort_order
    }
    AMENITIES {
        int id PK
        string name UK
    }
    PROPERTY_AMENITIES {
        int property_id PK, FK
        int amenity_id PK, FK
    }
    CALENDAR_ENTRIES {
        int id PK
        int unit_id FK "UK (unit_id, date)"
        date date
        enum status "acik, kapali"
        decimal special_price "NULL olabilir"
    }
    RESERVATIONS {
        int id PK "UK (id, unit_id)"
        string reservation_no UK "RZ-YYYYMMDD-XXXXXX"
        int customer_id FK
        int unit_id FK
        date check_in "CHECK check_out > check_in"
        date check_out
        int guest_count
        enum status "Beklemede, Onaylandi, Iptal, Reddedildi, SuresiDoldu, Tamamlandi"
        datetime expires_at
        decimal total_price "DECIMAL(10,2)"
        string idempotency_key UK
        string cancel_reason
        datetime created_at
        datetime updated_at
    }
    RESERVATION_NIGHTS {
        int reservation_id PK, FK
        date night_date PK
        int unit_id FK "UK (unit_id, night_date)"
    }
    RESERVATION_CHARGES {
        int id PK
        int reservation_id FK
        enum type "konaklama, vergi, ek_ucret"
        string description
        decimal amount "DECIMAL(10,2)"
    }
    PRICING_RULES {
        int id PK
        int property_id FK
        string name
        enum type "vergi, ek_ucret"
        enum calc_method "yuzde, gecelik_sabit, rezervasyon_basina_sabit"
        decimal value "DECIMAL(10,2)"
    }
    PAYMENTS {
        int id PK
        int reservation_id FK
        string provider_ref
        decimal amount "DECIMAL(10,2)"
        string currency
        enum status "beklemede, basarili, basarisiz, iade_simule"
    }
    REVIEWS {
        int id PK
        int reservation_id FK, UK
        int customer_id FK
        tinyint rating "1-5"
        text comment
        enum moderation_status
        datetime created_at
    }
    FAVORITES {
        int user_id PK, FK
        int property_id PK, FK
        datetime added_at
    }
    NOTIFICATIONS {
        int id PK
        int user_id FK
        string type
        string message
        boolean is_read
        datetime created_at
    }
    COMPLAINTS {
        int id PK
        int sender_id FK
        int property_id FK "NULL olabilir"
        int reservation_id FK "NULL olabilir"
        text description
        enum status
        text admin_note
    }
    AUDIT_LOGS {
        int id PK
        int actor_id FK "sistem islemlerinde NULL"
        string entity_type
        int entity_id
        string action
        string old_value
        string new_value
        string reason
        datetime created_at
    }
    PASSWORD_RESET_TOKENS {
        int id PK
        int user_id FK
        string token_hash UK
        datetime expires_at
        datetime used_at "NULL olabilir"
    }
```
 
### Normalizasyon Gerekçesi
 
Her varlık kendi tablosunda tutulmuş, tekrarlayan veriler (fotoğraf, olanak, gece, fiyat kalemi, favori) ara veya alt tablolara ayrılarak **1NF** sağlanmıştır. Bileşik anahtarlı tablolarda (`PROPERTY_AMENITIES`, `FAVORITES`, `RESERVATION_NIGHTS`) yalnızca anahtarın tamamına bağlı sütunlar bırakıldığı için **2NF** sağlanır. Müşteri, ilan veya şehir gibi bilgiler yalnızca bir tabloda tutulur ve diğer tablolar bunlara FK ile bağlanır; böylece anahtar olmayan sütunlar arasında geçişli bağımlılık kalmaz ve **3NF** sağlanır.
 
**Bilinçli iki istisna:**
 
1. `RESERVATION_NIGHTS.unit_id`: Kesin 3NF'ye göre fazlalıktır; ancak veritabanının `UNIQUE (unit_id, night_date)` kısıtını uygulayabilmesi için gereklidir. Bu kısıt çift rezervasyona karşı son savunmadır. Tutarsızlığı `(reservation_id, unit_id)` bileşik FK'si önler.
2. `RESERVATIONS.total_price`: `RESERVATION_CHARGES` toplamından türetilebilir; ancak fiyat anlık görüntüsü olarak bilinçli saklanır (BR-16) ve testle kalemlerin toplamına eşitliği doğrulanır.


## 3. Sıralama Diyagramı: Rezervasyon Oluşturma (Çekirdek: Çift Rezervasyon Önleme)
 
```mermaid
sequenceDiagram
    autonumber
    participant T as Tarayıcı
    participant C as ReservationController
    participant S as ReservationService
    participant P as PricingService
    participant B as BookingConflictService
    participant R as ReservationRepository
 
    T->>C: POST /rezervasyon (birimId, giriş, çıkış, kişi, idempotencyKey)
    C->>S: create(userId, data)
    S->>R: findByIdempotencyKey(key)
 
    alt anahtar daha önce kullanılmış (çift gönderim, CS-06)
        R-->>S: mevcut Reservation
        S-->>C: mevcut Reservation
        C-->>T: 302 mevcut rezervasyon sayfası (yeni kayıt yok)
    else yeni istek
        S->>S: tarih, kişi sayısı, ilan ve birim durumu doğrula (BR-01, BR-02, BR-03, BR-14)
        alt doğrulama geçersiz
            S-->>C: ValidationException
            C-->>T: formu hata mesajıyla tekrar göster (kayıt yok)
        else doğrulama geçerli
            S->>P: calculate(birimId, giriş, çıkış)
            P-->>S: gece, vergi ve ek ücret kalemleri
            S->>R: beginTransaction()
            S->>B: assertAvailable(birimId, giriş, çıkış, kişi)
            B->>R: lockUnit(birimId)
            alt kilit alınamadı (2 yeniden denemeden sonra)
                R-->>B: LockTimeoutException
                B-->>S: LockTimeoutException
                S->>R: rollback()
                S-->>C: BusyException
                C-->>T: "Yoğunluk var, lütfen tekrar deneyin" (kayıt yok)
            else kilit alındı
                B->>R: expireStaleHolds(birimId)
                Note over B,R: Süresi dolmuş Beklemede kayıtlar işlem içinde<br/>Süresi Doldu yapılır ve geceleri serbest kalır (CS-05)
                B->>R: findOverlapping(birimId, giriş, çıkış)
                B->>R: findClosedNights(birimId, giriş, çıkış)
                R-->>B: çakışan rezervasyonlar ve kapalı geceler
                alt çakışma var veya gece kapalı (CS-01, CS-04, CS-08)
                    B->>B: en çok 3 yakın müsait aralık hesapla (FR-36)
                    B-->>S: ConflictException(alternatifler)
                    S->>R: rollback()
                    S-->>C: ConflictException(alternatifler)
                    C-->>T: 409 "Tarihler müsait değil" + alternatif aralıklar (kayıt yok)
                else birim müsait
                    B-->>S: uygun
                    S->>R: createWithNights(rezervasyon, kalemler, geceler)
                    alt UNIQUE(birim, gece) ihlali veya yazma hatası (CS-01 yarışı, CS-12)
                        R-->>S: PersistenceException
                        S->>R: rollback()
                        S-->>C: ConflictException veya SystemException
                        C-->>T: hata mesajı (yarım rezervasyon veya yarım gece kaydı kalmaz)
                    else kayıt başarılı
                        R-->>S: Reservation (Beklemede, expires_at)
                        S->>R: commit()
                        S-->>C: Reservation
                        C-->>T: 302 /rezervasyon/RZ-... "Beklemede" (anında 10 dk, onaylı 24 saat)
                    end
                end
            end
        end
    end
```
 
### Diyagramdaki durumlar
 
| Dal | Senaryo | Sonuç |
| --- | --- | --- |
| Başarılı | Birim müsait, kayıt ve geceler tek işlemde yazıldı | Rezervasyon Beklemede olur, geceler tutulur |
| Hata 1 | Çift gönderim (CS-06) | Yeni kayıt oluşmaz, mevcut rezervasyon gösterilir |
| Hata 2 | Geçersiz tarih, kapasite aşımı, pasif ilan | ValidationException, kayıt yok |
| Hata 3 | Kilit alınamadı | 2 deneme sonrası "yoğunluk" mesajı, kayıt yok |
| Hata 4 | Çakışma veya kapalı gece (CS-01, 04, 08) | ConflictException, en çok 3 alternatif önerilir |
| Hata 5 | UNIQUE(birim, gece) ihlali veya yazma hatası (CS-12) | Rollback, yarım kayıt kalmaz |


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

4. Neden: Yeni bir fiyat kuralı (örneğin "bayram dönemi zammı" veya "erken rezervasyon indirimi") eklemek için mevcut PricingService veya diğer strateji sınıflarına dokunmadan yalnızca yeni bir sınıf (HolidayPricingStrategy) yazıp arayüzü uygulamak yeterlidir. Bu, Açık/Kapalı Prensibi'ne (OCP) uygundur; ayrıca her strateji bağımsız olarak birim testine tabi tutulabildiği için SRS'teki AC-08 (toplam fiyatın onay öncesi açık gösterimi) ve kılavuzdaki "en az 10 PHPUnit testi" şartı daha güvenilir biçimde karşılanır. Sunumda "Hangi sınıflar bu deseni oluşturuyor?" sorusuna PricingStrategyInterface ile üç somut strateji sınıfı diyagr

6 risk tablosu
[risk-listesi.xlsx](https://github.com/user-attachments/files/33447133/risk-listesi.xlsx)


6. Arayüz
6.1 Sayfa haritası
```mermaid
 flowchart LR
subgraph HER["Herkes (ziyaretçi dahil)"]
  A["Ana sayfa ve arama"] --> B["Arama sonuçları"]
  B --> C["İlan detayı ve aylık takvim"]
  G["Giriş"]
  K["Kayıt ol"]
  PW["Parola sıfırlama"]
end
subgraph MUS["Kayıtlı müşteri"]
  D["Rezervasyon onayı ve gecelik fiyat dökümü"] --> E["Rezervasyonlarım"]
  E --> F["İptal önizleme ve iade"]
  E --> Y["Yorum yaz"]
  H["Favoriler"]
  I["Profil"]
  BL["Bildirimler"]
  SK["Şikâyet oluştur"]
end
subgraph ILN["İlan sahibi (yalnızca ilan sahibi)"]
  J["İlan sahibi paneli"] --> L1["İlanlarım ve odalar"]
  L1 --> RS["Resim yükleme"]
  J --> TK["Takvim ve fiyat kuralları"]
  J --> GR["Gelen rezervasyonlar: onayla / reddet"]
  J --> RP["Doluluk ve gelir raporu"]
end
subgraph YON["Yönetici (yalnızca yönetici)"]
  O["Yönetici paneli"] --> KU["Kullanıcılar"]
  O --> IL["İlanlar: inceleme ve kaldırma"]
  O --> SC["Şikâyetler"]
  O --> YD["Yorum denetimi"]
  O --> YR["Raporlar"]
end
G --> A
K --> G
G -.-> PW
C -->|"giriş gerekir"| D
A --> H
A --> I
A --> BL
C -.-> SK
G -->|"ilan sahibi rolü"| J
G -->|"yönetici rolü"| O
classDef her fill:#eef2f7,stroke:#5b6b82,color:#1b2430
classDef mus fill:#e3f1e8,stroke:#3d8b5a,color:#12301f
classDef iln fill:#fdf0d9,stroke:#b9852a,color:#3a2a08
classDef yon fill:#f9e1e1,stroke:#b04a4a,color:#3a1010
class A,B,C,G,K,PW her
class D,E,F,Y,H,I,BL,SK mus
class J,L1,RS,TK,GR,RP iln
class O,KU,IL,SC,YD,YR yon
style HER fill:#f7f9fc,stroke:#c3ccd8,color:#1b2430
style MUS fill:#f4faf6,stroke:#b9dcc6,color:#12301f
style ILN fill:#fffaf0,stroke:#ecd5a6,color:#3a2a08
style YON fill:#fdf6f6,stroke:#e6c0c0,color:#3a1010
```

Renkler rolü gösterir: gri = herkes, yeşil = kayıtlı müşteri, turuncu = yalnızca ilan sahibi, kırmızı = yalnızca yönetici. Yetki sunucu tarafında da denetlenir (NFR-03).

Yönetici (yalnızca yönetici)
İlan sahibi (yalnızca ilan sahibi)
Kayıtlı müşteri
Herkes (ziyaretçi dahil)
giriş gerekir
ilan sahibi rolü
yönetici rolü
Ana sayfa ve arama
Arama sonuçları
İlan detayı ve aylık takvim
Giriş
Kayıt ol
Parola sıfırlama
Rezervasyon onayı vegecelik fiyat dökümü
Rezervasyonlarım
İptal önizleme ve iade
Yorum yaz
Favoriler
Profil
Bildirimler
Şikâyet oluştur
İlan sahibi paneli
İlanlarım ve odalar
Resim yükleme
Takvim ve fiyat kuralları
Gelen rezervasyonlar:onayla / reddet
Doluluk ve gelir raporu
Yönetici paneli
Kullanıcılar
İlanlar: inceleme ve kaldırma
Şikâyetler
Yorum denetimi
Raporlar
6.2 Ekran taslakları

Aşağıdaki taslaklar metin tabanlıdır; docs/wireframes/ klasörüne draw.io veya excalidraw ile PNG olarak da çizilmesi önerilir (Onur).

Ekran 1 — Arama ve sonuçlar (FR-06, 07, 08)

text
+----------------------------------------------------------------+
| Otel / Kiralık Ev                          [Giriş]  [Kayıt ol] |
+----------------------------------------------------------------+
| Konum [__________]  Giriş [__/__/____]  Çıkış [__/__/____]     |
| Kişi [ 2 v ]  Tür [ Hepsi v ]  Oda tipi [ Hepsi v ]  [ ARA ]   |
| (hata mesajı: "Çıkış tarihi girişten sonra olmalı")            |
+----------------------------------------------------------------+
| Filtre: Fiyat [___]-[___]  Puan [ v ]  Olanaklar [x] Wi-Fi ... |
| Sırala: [ Fiyat v ]                                            |
+----------------------------------------------------------------+
| [foto]  Otel Karadeniz        4,5 ★   3 müsait oda             |
|         Trabzon                3 gece toplam: 4.862,00 TL      |
|                                                    [ Detay ]   |
+----------------------------------------------------------------+

Ekran 2 — İlan detayı ve aylık müsaitlik takvimi (FR-09, 10)

text
+----------------------------------------------------------------+
| Otel Karadeniz — Trabzon                          4,5 ★        |
| [foto1] [foto2] [foto3]                                        |
| Açıklama ... Olanaklar: Wi-Fi, Kahvaltı ...                     |
| İptal politikası: ORTA (5+ gün %100, 1–4 gün %50)              |
+----------------------------------------------------------------+
| Oda [ Deluxe 204 v ]      < Temmuz 2027 >                      |
|  Pt Sa Ça Pe Cu Ct Pa                                          |
|   .  .  .  1  2  3  4     . müsait  X dolu  - kapalı           |
|   5  6  X  X  X  -  -                                          |
| Giriş [__] Çıkış [__] Kişi [ 2 v ]        [ Fiyatı göster ]    |
+----------------------------------------------------------------+

Ekran 3 — Rezervasyon onayı, gecelik fiyat dökümü (çekirdek ekran) (FR-11, 14, 36)

text
+----------------------------------------------------------------+
| Rezervasyonu onayla                                            |
| Otel Karadeniz / Deluxe 204   9–12 Temmuz 2027 (3 gece, 2 kişi)|
+----------------------------------------------------------------+
| Gece          Açıklama                          Tutar          |
| 9 Tem (Cum)   1000 +%30 sezon +%20 hafta sonu   1.560,00 TL    |
| 10 Tem (Cmt)  1000 +%30 sezon +%20 hafta sonu   1.560,00 TL    |
| 11 Tem (Paz)  1000 +%30 sezon                   1.300,00 TL    |
| Konaklama toplamı                               4.420,00 TL    |
| Uzun konaklama indirimi                              0,00 TL   |
| Vergi (%10)                                        442,00 TL   |
| TOPLAM                                          4.862,00 TL    |
+----------------------------------------------------------------+
| İptal koşulu: ORTA — girişe 5+ gün kala %100 iade              |
| Bekletme süresi: 09:58 (anında rezervasyonda)                  |
| [ Onayla ve öde (simüle) ]   [ Vazgeç ]                        |
+----------------------------------------------------------------+
| HATA (çakışmada): "Seçtiğiniz tarihler artık müsait değil."    |
|   Alternatifler: [12–15 Tem]  [14–17 Tem]  [16–19 Tem]         |
+----------------------------------------------------------------+

Ekran 4 — Rezervasyonlarım ve iptal önizleme (FR-16, 17, 45)

text
+----------------------------------------------------------------+
| Rezervasyonlarım          [ Gelecek ] [ Geçmiş ]               |
| No             Tarih            Durum       Tutar     İşlem    |
| RZ-20261014-A7 9–12 Tem 2027    Onaylandı   4.862,00  [İptal]  |
+----------------------------------------------------------------+
| İptal önizleme                                                 |
| Kalan gün: 3   Politika: ORTA   İade oranı: %50                |
| İade: 2.431,00 TL   Kesinti: 2.431,00 TL                       |
| [ İptali onayla ]   [ Vazgeç ]                                 |
+----------------------------------------------------------------+

Ekran 5 — İlan sahibi: takvim ve fiyat kuralları (FR-22, 43)

text
+----------------------------------------------------------------+
| İlan sahibi paneli > Otel Karadeniz > Fiyat ve takvim          |
| Temel fiyat (Deluxe 204): [ 1000,00 ] TL                       |
| Fiyat kuralları                                [ + Kural ekle ]|
|  Tür             Parametre                 Değer      Sil      |
|  Sezon           1 Tem – 31 Ağu            +%30       [x]      |
|  Hafta sonu      Cuma, Cumartesi geceleri  +%20       [x]      |
|  Uzun konaklama  7+ gece                   -%10       [x]      |
| Takvim: gece seç -> [ Kapat ]  [ Aç ]  [ Özel fiyat: ____ ]    |
| (Rezervasyonlu geceler kilitli; kapatma denemesi hata verir)   |
+----------------------------------------------------------------+

Ekran 6 — Doluluk ve gelir raporu (FR-30)

text
+----------------------------------------------------------------+
| Rapor   Tarih [01.07.2027] – [31.07.2027]  Kırılım [ Oda tipi v]|
| Doluluk oranı: %72   Net gelir: 184.250,00 TL   Rezervasyon: 41 |
| Oda tipi    Doluluk   Net gelir                                 |
| Deluxe      %80       96.000,00 TL                              |
| Standart    %65       88.250,00 TL                              |
| Durum dağılımı: Onaylandı 30 · İptal 8 · Tamamlandı 3           |
+-----------------------------------------------------------
