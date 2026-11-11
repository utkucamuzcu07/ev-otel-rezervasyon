<?php

declare(strict_types=1);

namespace App\Seed;

use App\Core\Container;
use App\Model\Amenity;
use App\Model\AuditLog;
use App\Model\BookableUnit;
use App\Model\BookingMode;
use App\Model\CalcMethod;
use App\Model\CalendarEntry;
use App\Model\CalendarStatus;
use App\Model\CancellationPolicy;
use App\Model\Complaint;
use App\Model\ComplaintStatus;
use App\Model\ModerationStatus;
use App\Model\Notification;
use App\Model\Payment;
use App\Model\PaymentStatus;
use App\Model\PriceRule;
use App\Model\PriceRuleType;
use App\Model\PricingRule;
use App\Model\PricingRuleType;
use App\Model\Property;
use App\Model\PropertyStatus;
use App\Model\PropertyType;
use App\Model\Reservation;
use App\Model\ReservationStatus;
use App\Model\Review;
use App\Model\Role;
use App\Model\RoomType;
use App\Model\UnitStatus;
use App\Model\User;
use App\Model\UserStatus;
use App\Service\State\ReservationState;
use App\Support\DateRange;
use DateTimeImmutable;
use RuntimeException;

/**
 * Örnek veri (ders kuralı: sunumda boş veriyle demo yapılmaz; AC-29: en az 10 birim, 3 oda tipi).
 * 8 ilan, 16 birim, 6 oda tipi; geçmiş, devam eden, bekleyen, iptal ve reddedilen rezervasyonlar;
 * her ilana birden çok yorum. Fotoğraf üretilmez (ders kuralı: resim, ikon veya çizim dosyası hazırlanmaz);
 * ilanlar emoji yer tutucuyla görünür, fotoğrafı ilan sahibi panelinden yükler (FR-46). Veriler JSON
 * dosyalarına yazılır. Tarihler bugüne göre hesaplanır. Demo parolası README'de yazılıdır.
 */
final class DemoSeeder
{
    public const DEMO_PASSWORD = 'Demo1234!';

    /** @var array<string, User> */
    private array $users = [];

    /** @var array<string, BookableUnit> */
    private array $units = [];

    /** @var array<string, string> birim anahtarı => ilan anahtarı */
    private array $propertyKeyOfUnit = [];

    /** @var array<string, int> ilan anahtarı ve puan başına kullanılan yorum metni sayısı */
    private array $textCursor = [];

    private int $reviewCount = 0;

    private DateTimeImmutable $today;
    private DateTimeImmutable $now;

    public function __construct(private readonly Container $c)
    {
        $this->now = $c->clock->now();
        $this->today = $c->clock->today();
    }

    /** @return array<string, int> */
    public function run(): array
    {
        // Önce JSON tabloları boşaltılır; yükleme tek işlemde yapılır.
        $this->c->resetStorage();

        return $this->c->tx()->transactional(function (): array {
            $amenities = $this->amenities();
            $this->seedUsers();
            $properties = $this->seedProperties($amenities);
            $this->seedPricing($properties);
            $this->seedCalendar();
            $reservations = $this->seedReservations();
            $this->seedExtras();

            return [
                'kullanıcı' => count($this->users),
                'ilan' => count($properties),
                'oda/birim' => count($this->units),
                'oda tipi' => count(array_unique(array_map(fn (BookableUnit $u): string => $u->roomType->value, $this->units))),
                'rezervasyon' => $reservations,
                'yorum' => $this->reviewCount,
            ];
        });
    }

    /** @return array<string, int> olanak adı => id */
    private function amenities(): array
    {
        $ids = [];
        foreach (['Wi-Fi', 'Kahvaltı', 'Otopark', 'Havuz', 'Klima', 'Mutfak', 'Deniz manzarası', 'Şömine', 'Spa', 'Evcil hayvan kabul'] as $name) {
            $ids[$name] = (int) $this->c->amenities()->save(new Amenity(null, $name))->id;
        }

        return $ids;
    }

    private function seedUsers(): void
    {
        $hash = password_hash(self::DEMO_PASSWORD, PASSWORD_DEFAULT);
        $people = [
            'yonetici' => ['Sistem', 'Yöneticisi', 'yonetici@evotel.test', Role::Yonetici],
            'ayse' => ['Ayşe', 'Kaya', 'ayse@evotel.test', Role::IlanSahibi],
            'mehmet' => ['Mehmet', 'Demir', 'mehmet@evotel.test', Role::IlanSahibi],
            'zeynep' => ['Zeynep', 'Çelik', 'zeynep@evotel.test', Role::IlanSahibi],
            'ali' => ['Ali', 'Yılmaz', 'ali@evotel.test', Role::Musteri],
            'can' => ['Can', 'Öztürk', 'can@evotel.test', Role::Musteri],
            'deniz' => ['Deniz', 'Aydın', 'deniz@evotel.test', Role::Musteri],
            'burak' => ['Burak', 'Koç', 'burak@evotel.test', Role::Musteri],
            'selin' => ['Selin', 'Arslan', 'selin@evotel.test', Role::Musteri],
            'elif' => ['Elif', 'Şahin', 'elif@evotel.test', Role::Musteri],
            'emre' => ['Emre', 'Kılıç', 'emre@evotel.test', Role::Musteri],
            'merve' => ['Merve', 'Yıldız', 'merve@evotel.test', Role::Musteri],
            'okan' => ['Okan', 'Polat', 'okan@evotel.test', Role::Musteri],
            'gizem' => ['Gizem', 'Er', 'gizem@evotel.test', Role::Musteri],
            'kerem' => ['Kerem', 'Aksoy', 'kerem@evotel.test', Role::Musteri],
        ];
        foreach ($people as $key => [$first, $last, $email, $role]) {
            $this->users[$key] = $this->c->users()->save(new User(null, $first, $last, $email, $hash, $role, UserStatus::Aktif, $this->now->modify('-400 days')));
        }
    }

    /**
     * @param array<string, int> $a
     * @return array<string, Property>
     */
    private function seedProperties(array $a): array
    {
        $list = [
            'karadeniz' => ['ayse', 'Karadeniz Otel', PropertyType::Otel, 'Ortahisar, Atatürk Alanı yakını', 'Trabzon', BookingMode::Aninda, CancellationPolicy::Orta,
                'Şehir merkezinde, meydana yürüme mesafesinde aile işletmesi otel. Sabah kahvaltısı ve ücretsiz otopark sunulur.',
                ['Wi-Fi', 'Kahvaltı', 'Otopark', 'Klima'],
                [['101', RoomType::TekKisilik, 1, 900], ['102', RoomType::CiftKisilik, 2, 1300], ['103', RoomType::CiftKisilik, 2, 1350], ['201', RoomType::Aile, 4, 1900], ['301 Süit', RoomType::Suit, 3, 2600]]],
            'uzungol' => ['ayse', 'Uzungöl Dağ Evi', PropertyType::KiralikEv, 'Uzungöl Mahallesi, Çaykara', 'Trabzon', BookingMode::Onayli, CancellationPolicy::Kati,
                'Göl manzaralı, şömineli ahşap dağ evi. 6 kişiye kadar aileler ve arkadaş grupları için uygundur.',
                ['Wi-Fi', 'Mutfak', 'Şömine', 'Otopark'],
                [['Dağ evi', RoomType::MustakilEv, 6, 3200]]],
            'bogaz' => ['mehmet', 'Boğaz Manzaralı Daire', PropertyType::KiralikEv, 'Bebek Mahallesi, Beşiktaş', 'İstanbul', BookingMode::Aninda, CancellationPolicy::Esnek,
                'Boğaz manzaralı, metroya yakın, tam donanımlı 2+1 daire. Kısa ve uzun konaklamalar için idealdir.',
                ['Wi-Fi', 'Mutfak', 'Klima', 'Deniz manzarası'],
                [['Daire 2+1', RoomType::Daire, 4, 2800]]],
            'sultanahmet' => ['mehmet', 'Sultanahmet Butik Otel', PropertyType::Otel, 'Cankurtaran, Fatih', 'İstanbul', BookingMode::Onayli, CancellationPolicy::Orta,
                'Tarihi yarımadanın kalbinde, Ayasofya ve Topkapı Sarayı\'na yürüme mesafesinde butik otel.',
                ['Wi-Fi', 'Kahvaltı', 'Klima'],
                [['Tek kişilik oda', RoomType::TekKisilik, 1, 1100], ['Çift kişilik oda', RoomType::CiftKisilik, 2, 1600], ['Süit', RoomType::Suit, 3, 3000]]],
            'kaleici' => ['zeynep', 'Kaleiçi Konak Otel', PropertyType::Otel, 'Kaleiçi, Muratpaşa', 'Antalya', BookingMode::Aninda, CancellationPolicy::Esnek,
                'Tarihi Kaleiçi\'nde restore edilmiş konak otel; avlu havuzu ve denize yürüme mesafesi.',
                ['Wi-Fi', 'Kahvaltı', 'Havuz', 'Klima'],
                [['Çift kişilik oda', RoomType::CiftKisilik, 2, 1400], ['Aile odası', RoomType::Aile, 4, 2100]]],
            'kas' => ['zeynep', 'Kaş Deniz Evi', PropertyType::KiralikEv, 'Çukurbağ Yarımadası, Kaş', 'Antalya', BookingMode::Aninda, CancellationPolicy::Orta,
                'Özel havuzlu, deniz manzaralı müstakil villa. Haftalık konaklamalarda indirim uygulanır.',
                ['Wi-Fi', 'Havuz', 'Mutfak', 'Deniz manzarası', 'Otopark', 'Evcil hayvan kabul'],
                [['Villa', RoomType::MustakilEv, 5, 3500]]],
            'kapadokya' => ['zeynep', 'Kapadokya Taş Ev', PropertyType::KiralikEv, 'Ürgüp merkez', 'Nevşehir', BookingMode::Onayli, CancellationPolicy::Esnek,
                'Peri bacalarına bakan, kayaya oyulmuş taş ev. Balon turlarına kolay ulaşım.',
                ['Wi-Fi', 'Kahvaltı', 'Şömine'],
                [['Taş ev', RoomType::MustakilEv, 4, 2400]]],
            'gumushane' => ['ayse', 'Gümüşhane Şehir Oteli', PropertyType::Otel, 'Merkez, Üniversite Caddesi', 'Gümüşhane', BookingMode::Aninda, CancellationPolicy::Orta,
                'Üniversiteye yakın, ekonomik ve temiz şehir oteli. Uzun süreli konaklamalara uygundur.',
                ['Wi-Fi', 'Kahvaltı', 'Otopark'],
                [['Tek kişilik', RoomType::TekKisilik, 1, 750], ['Çift kişilik', RoomType::CiftKisilik, 2, 1050]]],
        ];

        $properties = [];
        foreach ($list as $key => [$owner, $name, $type, $address, $city, $mode, $policy, $description, $amenityNames, $units]) {
            $property = $this->c->properties()->save(new Property(null, (int) $this->users[$owner]->id, $name, $type, $description, $address, $city, $mode, $policy, PropertyStatus::Aktif));
            $this->c->properties()->setAmenities((int) $property->id, array_map(fn (string $n): int => $a[$n], $amenityNames));
            foreach ($units as [$unitName, $roomType, $capacity, $price]) {
                $this->units[$key . ':' . $unitName] = $this->c->units()->save(new BookableUnit(null, (int) $property->id, $unitName, $roomType, $capacity, $price * 100, UnitStatus::Aktif));
                $this->propertyKeyOfUnit[$key . ':' . $unitName] = $key;
            }
            $properties[$key] = $property;
        }

        return $properties;
    }

    /** @param array<string, Property> $properties */
    private function seedPricing(array $properties): void
    {
        $year = (int) $this->today->format('Y');
        foreach ($properties as $key => $property) {
            $id = (int) $property->id;
            // Hafta sonu (Cuma, Cumartesi geceleri) +%20
            $this->c->priceRules()->save(new PriceRule(null, $id, null, PriceRuleType::HaftaSonu, 'Hafta sonu', null, null, null, 2000, \App\Model\ValueType::Yuzde, 1));
            // Yaz sezonu 1 Temmuz - 31 Ağustos +%30 (bu yıl ve gelecek yıl)
            foreach ([$year, $year + 1] as $y) {
                $this->c->priceRules()->save(new PriceRule(null, $id, null, PriceRuleType::Sezon, 'Yaz sezonu ' . $y, new DateTimeImmutable("$y-07-01"), new DateTimeImmutable("$y-08-31"), null, 3000, \App\Model\ValueType::Yuzde, 1));
            }
            // Uzun konaklama: 7+ gece %10, 28+ gece %20
            $this->c->priceRules()->save(new PriceRule(null, $id, null, PriceRuleType::UzunKonaklama, 'Uzun konaklama', null, null, 7, 1000, \App\Model\ValueType::Yuzde, 1));
            $this->c->priceRules()->save(new PriceRule(null, $id, null, PriceRuleType::UzunKonaklama, 'Uzun konaklama', null, null, 28, 2000, \App\Model\ValueType::Yuzde, 1));
            // Vergi %10 (indirimli konaklama tutarı üzerinden)
            $this->c->pricingRules()->save(new PricingRule(null, $id, 'KDV', PricingRuleType::Vergi, CalcMethod::Yuzde, 1000));
            if ($property->type === PropertyType::KiralikEv) {
                $this->c->pricingRules()->save(new PricingRule(null, $id, 'Temizlik ücreti', PricingRuleType::EkUcret, CalcMethod::RezervasyonBasinaSabit, 25000));
            }
            if ($key === 'uzungol') {
                // Kış sezonu ve yılbaşı: öncelik farkıyla aynı gecelerde yüksek öncelikli kural uygulanır (BR-26)
                $this->c->priceRules()->save(new PriceRule(null, $id, null, PriceRuleType::Sezon, 'Kış sezonu', new DateTimeImmutable("$year-12-15"), new DateTimeImmutable(($year + 1) . '-01-15'), null, 2500, \App\Model\ValueType::Yuzde, 1));
                $this->c->priceRules()->save(new PriceRule(null, $id, null, PriceRuleType::Sezon, 'Yılbaşı', new DateTimeImmutable("$year-12-28"), new DateTimeImmutable(($year + 1) . '-01-02'), null, 5000, \App\Model\ValueType::Yuzde, 2));
            }
        }
    }

    private function seedCalendar(): void
    {
        // Karadeniz Otel 103 numaralı oda bakım nedeniyle kapalı (5-7 gün sonrası)
        $unit = $this->units['karadeniz:103'];
        for ($d = 5; $d <= 7; $d++) {
            $this->c->calendar()->upsert(new CalendarEntry(null, (int) $unit->id, $this->today->modify("+$d days"), CalendarStatus::Kapali, null));
        }
        // Uzungöl yılbaşı gecesi özel fiyat (takvim istisnası: sezon/hafta sonu uygulanmaz)
        $newYear = new DateTimeImmutable($this->today->format('Y') . '-12-31');
        if ($newYear >= $this->today) {
            $this->c->calendar()->upsert(new CalendarEntry(null, (int) $this->units['uzungol:Dağ evi']->id, $newYear, CalendarStatus::Acik, 500000));
        }
        // Gümüşhane tek kişilik odaya 9. gün için indirimli özel fiyat
        $this->c->calendar()->upsert(new CalendarEntry(null, (int) $this->units['gumushane:Tek kişilik']->id, $this->today->modify('+9 days'), CalendarStatus::Acik, 60000));
    }

    private function seedReservations(): int
    {
        $done = ReservationStatus::Tamamlandi;
        /** @var list<array{string, string, int, int, int, ReservationStatus, bool, int|null}> $plan */
        $plan = [
            // [müşteri, birim, giriş (bugüne göre gün), gece, kişi, son durum, ödendi mi, puan]
            ['ali', 'karadeniz:102', -20, 3, 2, $done, true, 5],
            ['can', 'karadeniz:201', -12, 3, 4, $done, true, 4],
            ['deniz', 'uzungol:Dağ evi', -30, 4, 5, $done, true, 5],
            ['burak', 'bogaz:Daire 2+1', -15, 2, 3, $done, true, 4],
            ['selin', 'kas:Villa', -40, 7, 4, $done, true, 5],
            ['ali', 'sultanahmet:Çift kişilik oda', -8, 2, 2, $done, true, 3],
            ['can', 'kapadokya:Taş ev', -25, 3, 2, $done, true, 5],
            ['deniz', 'gumushane:Çift kişilik', -5, 2, 2, $done, true, null],
            ['burak', 'karadeniz:102', 3, 3, 2, ReservationStatus::Onaylandi, true, null],
            ['selin', 'karadeniz:101', 1, 3, 1, ReservationStatus::Onaylandi, true, null],
            ['ali', 'bogaz:Daire 2+1', 10, 4, 2, ReservationStatus::Onaylandi, true, null],
            ['can', 'kas:Villa', 20, 7, 4, ReservationStatus::Onaylandi, true, null],
            ['deniz', 'karadeniz:301 Süit', -1, 3, 2, ReservationStatus::Onaylandi, true, null],
            ['burak', 'uzungol:Dağ evi', 7, 3, 4, ReservationStatus::Beklemede, true, null],
            ['selin', 'sultanahmet:Süit', 5, 2, 2, ReservationStatus::Beklemede, true, null],
            ['ali', 'karadeniz:201', 15, 3, 3, ReservationStatus::IptalEdildi, true, null],
            ['can', 'kapadokya:Taş ev', 12, 2, 2, ReservationStatus::Reddedildi, true, null],
            ['deniz', 'gumushane:Tek kişilik', 2, 1, 1, ReservationStatus::SuresiDoldu, false, null],
            // Geçmiş konaklamalar ve yorumları (her ilana birden çok değerlendirme; geceler çakışmaz)
            ['elif', 'karadeniz:101', -48, 2, 1, $done, true, 5],
            ['emre', 'karadeniz:103', -63, 3, 2, $done, true, 4],
            ['merve', 'karadeniz:201', -90, 4, 4, $done, true, 5],
            ['okan', 'karadeniz:301 Süit', -120, 2, 2, $done, true, 3],
            ['gizem', 'karadeniz:102', -150, 3, 2, $done, true, 4],
            ['kerem', 'uzungol:Dağ evi', -70, 4, 5, $done, true, 5],
            ['selin', 'uzungol:Dağ evi', -110, 3, 4, $done, true, 5],
            ['elif', 'uzungol:Dağ evi', -160, 5, 6, $done, true, 4],
            ['can', 'uzungol:Dağ evi', -220, 3, 3, $done, true, 5],
            ['merve', 'bogaz:Daire 2+1', -55, 4, 2, $done, true, 5],
            ['okan', 'bogaz:Daire 2+1', -95, 3, 3, $done, true, 4],
            ['deniz', 'bogaz:Daire 2+1', -140, 2, 2, $done, true, 4],
            ['kerem', 'bogaz:Daire 2+1', -200, 5, 4, $done, true, 3],
            ['gizem', 'sultanahmet:Tek kişilik oda', -45, 2, 1, $done, true, 4],
            ['emre', 'sultanahmet:Çift kişilik oda', -75, 3, 2, $done, true, 5],
            ['burak', 'sultanahmet:Süit', -100, 2, 3, $done, true, 2],
            ['elif', 'sultanahmet:Çift kişilik oda', -130, 2, 2, $done, true, 4],
            ['merve', 'sultanahmet:Tek kişilik oda', -180, 3, 1, $done, true, 4],
            ['ali', 'kaleici:Çift kişilik oda', -50, 4, 2, $done, true, 5],
            ['okan', 'kaleici:Aile odası', -85, 5, 4, $done, true, 4],
            ['gizem', 'kaleici:Çift kişilik oda', -125, 3, 2, $done, true, 5],
            ['kerem', 'kaleici:Aile odası', -170, 4, 3, $done, true, 4],
            ['deniz', 'kaleici:Çift kişilik oda', -240, 3, 2, $done, true, 5],
            ['emre', 'kas:Villa', -80, 7, 4, $done, true, 5],
            ['ali', 'kas:Villa', -135, 5, 3, $done, true, 5],
            ['merve', 'kas:Villa', -190, 7, 5, $done, true, 4],
            ['okan', 'kas:Villa', -260, 4, 2, $done, true, 5],
            ['selin', 'kapadokya:Taş ev', -60, 3, 2, $done, true, 5],
            ['gizem', 'kapadokya:Taş ev', -105, 2, 2, $done, true, 5],
            ['emre', 'kapadokya:Taş ev', -145, 3, 3, $done, true, 4],
            ['kerem', 'kapadokya:Taş ev', -185, 2, 2, $done, true, 5],
            ['burak', 'kapadokya:Taş ev', -230, 4, 4, $done, true, 4],
            ['emre', 'gumushane:Tek kişilik', -35, 3, 1, $done, true, 4],
            ['elif', 'gumushane:Çift kişilik', -65, 2, 2, $done, true, 4],
            ['okan', 'gumushane:Tek kişilik', -115, 5, 1, $done, true, 3],
            ['merve', 'gumushane:Çift kişilik', -155, 2, 2, $done, true, 5],
        ];
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $count = 0;
        foreach ($plan as $i => [$customerKey, $unitKey, $offset, $nights, $guests, $status, $paid, $rating]) {
            $unit = $this->units[$unitKey];
            $property = $this->c->properties()->findById($unit->propertyId) ?? throw new RuntimeException('İlan bulunamadı');
            $range = new DateRange($this->today->modify(($offset >= 0 ? '+' : '') . $offset . ' days'), $this->today->modify(($offset + $nights >= 0 ? '+' : '') . ($offset + $nights) . ' days'));
            $createdAt = ($offset > 0 ? $this->now->modify('-2 days') : $range->checkIn->modify('-10 days')->setTime(10, 15))->modify('+' . $i . ' minutes');
            $holdHours = $property->bookingMode === BookingMode::Onayli ? 24 : 0;
            $expires = $status === ReservationStatus::Beklemede
                ? $this->now->modify('+' . (20 + $i % 3) . ' hours')
                : $createdAt->modify($holdHours > 0 ? '+24 hours' : '+10 minutes');
            $quote = $this->c->pricingService()->quote($unit, $range);
            $number = 'RZ-' . $createdAt->format('Ymd') . '-' . substr(str_shuffle(str_repeat($alphabet, 2)), 0, 6);

            $reservation = $this->c->reservations()->createWithNights(new Reservation(
                null,
                $number,
                (int) $this->users[$customerKey]->id,
                (int) $unit->id,
                $range->checkIn,
                $range->checkOut,
                $guests,
                ReservationStatus::Beklemede,
                $expires,
                $quote->total,
                $property->cancellationPolicy,
                bin2hex(random_bytes(16)),
                $this->users[$customerKey]->fullName(),
                '0532 555 ' . str_pad((string) (10 + $i), 2, '0', STR_PAD_LEFT) . ' ' . str_pad((string) (20 + $i), 2, '0', STR_PAD_LEFT),
                null,
                null,
                null,
                0,
                $createdAt,
                $createdAt,
            ), $quote->toCharges(), $range->nightDates());
            $id = (int) $reservation->id;
            $this->audit(null, $id, null, 'beklemede', 'Rezervasyon oluşturuldu (örnek veri)', $createdAt);

            if ($paid) {
                $this->c->payments()->save(new Payment(null, $id, 'SIM-SEED' . str_pad((string) $id, 4, '0', STR_PAD_LEFT), $quote->total, 0, 'TRY', PaymentStatus::Basarili, $createdAt->modify('+2 minutes')));
            }

            if ($status !== ReservationStatus::Beklemede) {
                $changes = [];
                $refund = 0;
                if ($status === ReservationStatus::IptalEdildi) {
                    $quoteRefund = $this->c->refundService()->quoteCustomerCancellation($reservation, $quote->total, $this->today);
                    $refund = $quoteRefund->refund;
                    $changes = ['cancel_reason' => 'Planlarım değişti', 'cancelled_at' => $this->now->modify('-1 day'), 'refund_amount' => $refund];
                } elseif ($status === ReservationStatus::Reddedildi) {
                    $refund = $quote->total;
                    $changes = ['cancel_reason' => 'Bu tarihlerde ev bakımda olacak', 'refund_amount' => $refund];
                }
                if ($status === ReservationStatus::Tamamlandi) {
                    $this->c->reservations()->updateStatusIf($id, ReservationStatus::Beklemede, ReservationStatus::Onaylandi, ['updated_at' => $createdAt->modify('+3 minutes')]);
                    $this->c->reservations()->updateStatusIf($id, ReservationStatus::Onaylandi, ReservationStatus::Tamamlandi, ['updated_at' => $range->checkOut->setTime(12, 5)]);
                } else {
                    $this->c->reservations()->updateStatusIf($id, ReservationStatus::Beklemede, $status, $changes + ['updated_at' => $createdAt->modify('+3 minutes')]);
                }
                if (!ReservationState::of($status)->holdsNights()) {
                    $this->c->reservations()->releaseNights($id);
                }
                if ($refund > 0) {
                    $this->c->paymentService()->refund($id, $refund);
                }
                $this->audit(null, $id, 'beklemede', $status->value, 'Örnek veri durumu', $createdAt->modify('+3 minutes'));
            }

            if ($rating !== null) {
                $comment = $this->reviewText($this->propertyKeyOfUnit[$unitKey], $rating);
                $this->c->reviews()->save(new Review(null, $id, (int) $this->users[$customerKey]->id, $rating, $comment, ModerationStatus::Yayinda, $range->checkOut->setTime(15, 0)));
                $this->reviewCount++;
            }
            $count++;
        }

        return $count;
    }

    /** İlana ve puana uygun, her seferinde farklı bir yorum metni. */
    private function reviewText(string $propertyKey, int $rating): string
    {
        $texts = [
            'karadeniz' => [
                5 => [
                    'Meydana yürüme mesafesinde, odalar tertemiz. Resepsiyon çok yardımcı oldu.',
                    'Kahvaltıda Karadeniz lezzetleri vardı, ücretsiz otopark da büyük kolaylık. Tekrar geleceğiz.',
                    'İş seyahati için ideal: sessiz oda, hızlı internet ve ilgili personel.',
                ],
                4 => [
                    'Konum harika, odalar rahat. Kahvaltı biraz daha zengin olabilirdi.',
                    'Ailecek kaldık; aile odası geniş ve kullanışlıydı. Asansör biraz yavaştı.',
                    'Temiz ve düzenli bir otel; oda biraz küçüktü ama fiyatına göre iyi.',
                ],
                3 => ['Konum çok iyi ama sokak tarafındaki oda gece gürültülüydü.'],
            ],
            'uzungol' => [
                5 => [
                    'Sabah göl manzarasıyla uyanmak paha biçilemez. Şömine akşamları çok keyifliydi.',
                    'Ev sahibi her soruya hızla döndü; ev tertemiz ve eksiksizdi.',
                    'Kalabalık bir aile olarak rahat ettik, mutfakta ihtiyacımız olan her şey vardı.',
                    'Kar manzarası eşliğinde şömine başında harika bir hafta sonu geçirdik.',
                ],
                4 => ['Ev ve manzara çok güzel; yalnızca yokuş yol araçla biraz zorlayıcı.'],
            ],
            'bogaz' => [
                5 => ['Balkondan Boğaz manzarası nefes kesici. Metroya da yakın, her yere kolayca ulaştık.'],
                4 => [
                    'Daire temiz ve donanımlı; yatak odası biraz sıcaktı ama klima işini gördü.',
                    'Bebek sahiline yürüme mesafesinde, kahvaltıyı sahilde yapmak harikaydı.',
                    'Giriş talimatları çok netti, anahtar teslimi sorunsuz geçti.',
                ],
                3 => ['Manzara güzel ama binada asansör yok; bavullarla üçüncü kata çıkmak yorucuydu.'],
            ],
            'sultanahmet' => [
                5 => ['Ayasofya\'ya beş dakika yürüme mesafesinde; terastaki kahvaltı unutulmazdı.'],
                4 => [
                    'Tarihi yarımadayı gezmek için mükemmel konum; odalar küçük ama çok şık.',
                    'Personel güler yüzlü, oda temizdi. Kahvaltı çeşitliydi.',
                    'Tek kişilik oda dar ama kısa bir şehir gezisi için yeterli ve temiz.',
                ],
                3 => ['Fiyatına göre idare eder; oda beklediğimizden küçüktü.'],
                2 => ['Süitin kliması ilk gece çalışmadı, ancak ertesi gün düzeltildi. Konum iyi ama hizmet yavaştı.'],
            ],
            'kaleici' => [
                5 => [
                    'Avludaki havuz ve restore edilmiş konak çok etkileyici. Kaleiçi sokakları kapının önünde.',
                    'Denize yürüyerek gittik; akşamları avluda oturmak çok huzurluydu.',
                    'Oda tarihi dokusunu korumuş ama konforundan bir şey kaybetmemiş.',
                ],
                4 => [
                    'Aile odası ferah, çocuklar havuzdan çıkmak istemedi. Yakında park yeri bulmak biraz zor.',
                    'Güzel bir konak; kahvaltı lezzetli ama saat 10\'da bitiyor.',
                ],
            ],
            'kas' => [
                5 => [
                    'Özel havuz ve deniz manzarası tam bir tatil hayali; villa fotoğraflardakinden de güzel.',
                    'Bir hafta kaldık, haftalık indirim de güzel bir sürpriz oldu. Çok sakin bir koy.',
                    'Mutfak eksiksiz; akşamları terasta gün batımını izlemek harikaydı.',
                    'Köpeğimizle kabul edildiğimiz için çok mutlu olduk; bahçe geniş ve güvenli.',
                ],
                4 => ['Villa ve havuz harika; Kaş merkezine arabasız ulaşım biraz zor.'],
            ],
            'kapadokya' => [
                5 => [
                    'Terastan balonları izlemek için erkenden kalktık, kesinlikle değdi.',
                    'Taş evin serinliği ve şömine birlikte çok özel bir atmosfer yarattı.',
                    'Ev sahibi balon turu ayarlamamıza yardım etti; kahvaltı da çok güzeldi.',
                    'Ürgüp merkezine yürüme mesafesinde, sakin ve otantik bir ev.',
                ],
                4 => [
                    'Ev çok güzel ama banyo biraz küçük. Konum ve manzara her şeyi telafi ediyor.',
                    'Kayaya oyulmuş oda ilginç bir deneyimdi; internet zaman zaman yavaşladı.',
                ],
            ],
            'gumushane' => [
                5 => ['Üniversiteye yürüme mesafesinde, temiz ve sessiz. Uzun süre kaldık, her şey yolundaydı.'],
                4 => [
                    'Ekonomik ve temiz bir otel; kahvaltı sade ama doyurucu.',
                    'Şehir merkezine yakın, personel ilgili. Odada çalışma masası olması iyi oldu.',
                ],
                3 => ['Fiyatı uygun ama oda biraz eski; ısıtma geç açıldı.'],
            ],
        ];
        $pool = $texts[$propertyKey][$rating] ?? ['Güzel bir konaklamaydı.'];
        $cursor = $propertyKey . ':' . $rating;
        $index = $this->textCursor[$cursor] ?? 0;
        $this->textCursor[$cursor] = $index + 1;

        return $pool[$index % count($pool)];
    }

    private function seedExtras(): void
    {
        $notify = function (string $userKey, string $type, string $message, int $minutesAgo): void {
            $this->c->notifications()->save(new Notification(null, (int) $this->users[$userKey]->id, $type, $message, false, $this->now->modify("-$minutesAgo minutes")));
        };
        $notify('ayse', 'yeni_talep', 'Uzungöl Dağ Evi için yeni rezervasyon talebi var; 24 saat içinde yanıtlayın.', 30);
        $notify('mehmet', 'yeni_talep', 'Sultanahmet Butik Otel (Süit) için yeni rezervasyon talebi var.', 45);
        $notify('deniz', 'tamamlandi', 'Gümüşhane Şehir Oteli konaklamanız tamamlandı. Deneyiminizi puanlayabilirsiniz.', 120);
        $notify('ali', 'iptal', 'Karadeniz Otel rezervasyonunuz iptal edildi; iade simüle olarak işlendi.', 60 * 24);

        // Şikâyet yönetim ekibinin Şikâyetler kutusuna düşer; yöneticiye bildirim gider (FR-27).
        $complaint = $this->c->complaints()->save(new Complaint(
            null,
            (int) $this->users['ali']->id,
            $this->units['sultanahmet:Süit']->propertyId,
            null,
            'Sultanahmet Butik Otel\'de kaldığım odanın kliması çalışmıyordu; resepsiyon ilgilenmedi.',
            ComplaintStatus::Yeni,
            null,
            $this->now->modify('-3 days'),
        ));
        $notify('yonetici', 'sikayet', 'Yeni şikâyet #' . $complaint->id . ': Sultanahmet Butik Otel. Yönetim → Şikâyetler sayfasından inceleyin.', 60 * 24 * 3);
    }

    private function audit(?int $actorId, int $reservationId, ?string $old, string $new, string $reason, DateTimeImmutable $at): void
    {
        $this->c->auditLogs()->add(new AuditLog(null, $actorId, 'reservation', $reservationId, 'durum_gecisi', $old, $new, $reason, $at));
    }
}
