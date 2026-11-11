<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Exception\ValidationException;
use App\Model\BookingMode;
use App\Model\CalcMethod;
use App\Model\CalendarStatus;
use App\Model\CancellationPolicy;
use App\Model\PricingRuleType;
use App\Model\PropertyStatus;
use App\Model\PropertyType;
use App\Model\ReservationStatus;
use App\Model\RoomType;
use App\Model\UnitStatus;
use App\Model\ValueType;
use App\Service\AvailabilityService;
use App\Service\Dto\PropertyInput;
use App\Service\PhotoService;
use App\Service\PriceRuleService;
use App\Service\PricingService;
use App\Service\PropertyService;
use App\Service\ReportService;
use App\Service\ReservationService;
use App\Support\ClockInterface;
use App\Support\DateRange;
use App\Support\Decimal;
use DateTimeImmutable;
use InvalidArgumentException;

/** İlan sahibi paneli (UC-03, UC-05; taslak 06). Her işlemde sahiplik serviste denetlenir (BR-08). */
final class HostController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        Auth $auth,
        private readonly PropertyService $properties,
        private readonly AvailabilityService $availability,
        private readonly PriceRuleService $priceRules,
        private readonly PricingService $pricing,
        private readonly PhotoService $photos,
        private readonly ReservationService $reservations,
        private readonly ReportService $reports,
        private readonly ClockInterface $clock,
    ) {
        parent::__construct($view, $session, $auth);
    }

    public function dashboard(Request $request): Response
    {
        $hostId = $this->userId();
        $today = $this->clock->today();
        $all = $this->reservations->listForHost($hostId);

        return $this->render('host/dashboard', [
            'title' => 'İlan sahibi paneli',
            'propertyRows' => $this->properties->listForOwner($hostId),
            'pending' => array_values(array_filter($all, fn ($v): bool => $v->isHold)),
            'upcoming' => array_values(array_filter($all, fn ($v): bool => $v->reservation->status === ReservationStatus::Onaylandi && $v->reservation->checkOut > $today)),
            'report' => $this->reports->build($hostId, $today->modify('first day of this month'), $today->modify('last day of this month')),
        ]);
    }

    public function properties(Request $request): Response
    {
        return $this->render('host/properties', ['title' => 'İlanlarım', 'rows' => $this->properties->listForOwner($this->userId())]);
    }

    public function createProperty(Request $request): Response
    {
        return $this->propertyForm(null, [], $this->defaultInput());
    }

    public function storeProperty(Request $request): Response
    {
        [$input, $errors] = $this->propertyInput($request);
        try {
            if ($input === null) {
                throw new ValidationException($errors);
            }
            $property = $this->properties->create($this->userId(), $input);
        } catch (ValidationException $e) {
            return $this->propertyForm(null, $e->errors(), $request->post, 422);
        }
        $this->flash('success', 'İlan oluşturuldu. Şimdi oda/birim ekleyin.');

        return $this->redirect('/panel/ilan/' . $property->id . '/odalar');
    }

    public function editProperty(Request $request, string $id): Response
    {
        $property = $this->properties->getOwned($this->userId(), (int) $id);
        $detail = $this->properties->detail((int) $id, $this->user());

        return $this->propertyForm((int) $id, [], [
            'ad' => $property->name,
            'tur' => $property->type->value,
            'aciklama' => $property->description,
            'adres' => $property->address,
            'sehir' => $property->city,
            'mod' => $property->bookingMode->value,
            'politika' => $property->cancellationPolicy->value,
            'durum' => $property->status->value,
            'olanak' => array_map(fn ($a): string => (string) $a->id, $detail->amenities),
        ]);
    }

    public function updateProperty(Request $request, string $id): Response
    {
        [$input, $errors] = $this->propertyInput($request);
        try {
            if ($input === null) {
                throw new ValidationException($errors);
            }
            $this->properties->update($this->userId(), (int) $id, $input);
        } catch (ValidationException $e) {
            return $this->propertyForm((int) $id, $e->errors(), $request->post, 422);
        }
        $this->flash('success', 'İlan güncellendi. İptal politikası değişikliği yalnızca yeni rezervasyonlara uygulanır.');

        return $this->redirect('/panel/ilanlar');
    }

    public function deleteProperty(Request $request, string $id): Response
    {
        try {
            $this->properties->delete($this->userId(), (int) $id);
            $this->flash('success', 'İlan silindi (kayıtlar korunarak kaldırıldı olarak işaretlendi).');
        } catch (ValidationException $e) {
            $this->flash('danger', $e->getMessage());
        }

        return $this->redirect('/panel/ilanlar');
    }

    public function units(Request $request, string $id): Response
    {
        return $this->unitsPage((int) $id, $request->nullableInt('duzenle'), [], []);
    }

    public function saveUnit(Request $request, string $id): Response
    {
        $unitId = $request->nullableInt('birim_id');
        $roomType = RoomType::tryFrom($request->input('oda_tipi'));
        $status = UnitStatus::tryFrom($request->input('durum')) ?? UnitStatus::Aktif;
        $price = $request->input('fiyat');
        try {
            if ($roomType === null) {
                throw ValidationException::single('oda_tipi', 'Oda tipi seçin.');
            }
            if (!Decimal::isValid($price)) {
                throw ValidationException::single('fiyat', 'Geçerli bir fiyat girin (ör. 1250 veya 1250.50).');
            }
            $this->properties->saveUnit($this->userId(), (int) $id, $unitId, $request->input('ad'), $roomType, $request->int('kapasite'), Decimal::toInt($price), $status);
        } catch (ValidationException $e) {
            return $this->unitsPage((int) $id, $unitId, $e->errors(), $request->post, 422);
        }
        $this->flash('success', $unitId === null ? 'Oda eklendi.' : 'Oda güncellendi.');

        return $this->redirect('/panel/ilan/' . $id . '/odalar');
    }

    public function pricing(Request $request, string $id): Response
    {
        return $this->pricingPage($request, (int) $id, [], []);
    }

    /** Fiyat kuralları (FR-43) ve vergi/ek ücretler: tek formdan "islem" alanına göre. */
    public function savePricing(Request $request, string $id): Response
    {
        $hostId = $this->userId();
        $propertyId = (int) $id;
        $unitId = $request->nullableInt('birim') ?: null;
        try {
            switch ($request->input('islem')) {
                case 'sezon':
                    $this->priceRules->addSeasonRule(
                        $hostId,
                        $propertyId,
                        $unitId,
                        $request->input('ad'),
                        $this->date($request->input('baslangic'), 'baslangic'),
                        $this->date($request->input('bitis'), 'bitis'),
                        $this->decimal($request->input('yuzde'), 'deger'),
                        $request->int('oncelik', 1),
                    );
                    break;
                case 'hafta_sonu':
                    $this->priceRules->setWeekendRule($hostId, $propertyId, $unitId, $this->decimal($request->input('yuzde'), 'deger'));
                    break;
                case 'uzun':
                    $this->priceRules->addLongStayRule(
                        $hostId,
                        $propertyId,
                        $unitId,
                        $request->int('en_az_gece'),
                        $this->decimal($request->input('deger'), 'deger'),
                        ValueType::tryFrom($request->input('deger_turu')) ?? ValueType::Yuzde,
                    );
                    break;
                case 'vergi':
                    $this->priceRules->addPricingRule(
                        $hostId,
                        $propertyId,
                        $request->input('ad'),
                        PricingRuleType::tryFrom($request->input('tur')) ?? PricingRuleType::Vergi,
                        CalcMethod::tryFrom($request->input('hesap')) ?? CalcMethod::Yuzde,
                        $this->decimal($request->input('deger'), 'deger'),
                    );
                    break;
                case 'sil_kural':
                    $this->priceRules->deletePriceRule($hostId, $request->int('kural'));
                    break;
                case 'sil_vergi':
                    $this->priceRules->deletePricingRule($hostId, $request->int('kural'));
                    break;
                default:
                    throw ValidationException::single('islem', 'Geçersiz işlem.');
            }
        } catch (ValidationException $e) {
            return $this->pricingPage($request, $propertyId, $e->errors(), $request->post, 422);
        }
        $this->flash('success', 'Fiyat kuralları güncellendi.');

        return $this->redirect('/panel/ilan/' . $id . '/fiyat');
    }

    public function photos(Request $request, string $id): Response
    {
        $property = $this->properties->getOwned($this->userId(), (int) $id);

        return $this->render('host/photos', [
            'title' => 'Fotoğraflar',
            'property' => $property,
            'units' => $this->properties->unitsOf($this->userId(), (int) $id),
            'photos' => $this->photos->forProperty((int) $id),
            'errors' => [],
        ]);
    }

    /** FR-46: yükleme, sıralama ve silme. */
    public function savePhoto(Request $request, string $id): Response
    {
        $hostId = $this->userId();
        try {
            match ($request->input('islem')) {
                'yukle' => $this->photos->upload($hostId, (int) $id, $request->file('foto'), $request->nullableInt('birim') ?: null),
                'yukari' => $this->photos->move($hostId, $request->int('foto_id'), -1),
                'asagi' => $this->photos->move($hostId, $request->int('foto_id'), 1),
                'sil' => $this->photos->delete($hostId, $request->int('foto_id')),
                default => throw ValidationException::single('foto', 'Geçersiz işlem.'),
            };
        } catch (ValidationException $e) {
            return $this->render('host/photos', [
                'title' => 'Fotoğraflar',
                'property' => $this->properties->getOwned($hostId, (int) $id),
                'units' => $this->properties->unitsOf($hostId, (int) $id),
                'photos' => $this->photos->forProperty((int) $id),
                'errors' => $e->errors(),
            ], 422);
        }
        $this->flash('success', $request->input('islem') === 'yukle' ? 'Fotoğraf yüklendi.' : 'Fotoğraflar güncellendi.');

        return $this->redirect('/panel/ilan/' . $id . '/fotograflar');
    }

    /** Taslak 06: takvim ve özel fiyat/kapalı gece yönetimi (FR-22). */
    public function calendar(Request $request): Response
    {
        return $this->calendarPage($request, [], $request->query);
    }

    public function saveCalendar(Request $request): Response
    {
        $unitId = $request->int('birim');
        $price = trim($request->input('fiyat'));
        try {
            if ($price !== '' && !Decimal::isValid($price)) {
                throw ValidationException::single('fiyat', 'Geçerli bir gecelik fiyat girin.');
            }
            $count = $this->availability->setCalendarRange(
                $this->userId(),
                $unitId,
                $this->date($request->input('baslangic'), 'baslangic'),
                $this->date($request->input('bitis'), 'bitis'),
                CalendarStatus::tryFrom($request->input('durum')) ?? CalendarStatus::Acik,
                $price === '' ? null : Decimal::toInt($price),
            );
        } catch (ValidationException $e) {
            return $this->calendarPage($request, $e->errors(), $request->post, 422);
        }
        $this->flash('success', $count . ' gece için takvim güncellendi.');

        return $this->redirect('/panel/takvim', ['ilan' => $request->input('ilan'), 'birim' => $unitId, 'ay' => substr($request->input('baslangic'), 0, 7)]);
    }

    public function reservations(Request $request): Response
    {
        $status = ReservationStatus::tryFrom($request->query('durum'));

        return $this->render('host/reservations', [
            'title' => 'Gelen rezervasyonlar',
            'list' => $this->reservations->listForHost($this->userId(), $status),
            'status' => $status->value ?? '',
        ]);
    }

    /** Kısmi yenileme: gelen rezervasyonlar tablosu birkaç saniyede bir güncellenir. */
    public function reservationsFragment(Request $request): Response
    {
        $hostId = $this->userId();
        $this->session->close();
        $status = ReservationStatus::tryFrom($request->query('durum'));

        return $this->fragment('host/_reservations_table', [
            'list' => $this->reservations->listForHost($hostId, $status),
            'compact' => $request->query('kisa') === '1',
        ]);
    }

    public function approve(Request $request, string $no): Response
    {
        try {
            $this->reservations->approve($no, $this->userId());
            $this->flash('success', $no . ' onaylandı.');
        } catch (ValidationException $e) {
            $this->flash('danger', $e->getMessage());
        }

        return $this->redirect($this->safePath($request->input('donus'), '/panel/rezervasyonlar'));
    }

    public function reject(Request $request, string $no): Response
    {
        try {
            $this->reservations->reject($no, $this->userId(), $request->input('neden'));
            $this->flash('success', $no . ' reddedildi; geceler serbest bırakıldı.');
        } catch (ValidationException $e) {
            $this->flash('danger', $e->getMessage());
        }

        return $this->redirect($this->safePath($request->input('donus'), '/panel/rezervasyonlar'));
    }

    /** FR-30: yalnızca kendi ilanlarının doluluk ve gelir raporu; bonus: CSV. */
    public function report(Request $request): Response
    {
        [$from, $to, $errors] = $this->period($request);
        try {
            $report = $this->reports->build($this->userId(), $from, $to);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $report = $this->reports->build($this->userId(), $this->clock->today()->modify('first day of this month'), $this->clock->today()->modify('last day of this month'));
        }
        if ($request->query('csv') === '1' && $errors === []) {
            return Response::csv('doluluk-gelir-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.csv', $this->reports->toCsvRows($report));
        }

        return $this->render('reports/report', [
            'title' => 'Doluluk ve gelir raporu',
            'report' => $report,
            'errors' => $errors,
            'action' => '/panel/rapor',
            'scope' => 'Yalnızca kendi ilanlarınız',
        ]);
    }

    // ------------------------------------------------------------------ yardımcılar

    /**
     * @param array<string, string> $errors
     * @param array<string, mixed> $old
     */
    private function propertyForm(?int $id, array $errors, array $old, int $status = 200): Response
    {
        return $this->render('host/property_form', [
            'title' => $id === null ? 'Yeni ilan' : 'İlanı düzenle',
            'propertyId' => $id,
            'errors' => $errors,
            'old' => $old,
            'amenities' => $this->properties->amenities(),
            'types' => PropertyType::options(),
            'modes' => BookingMode::options(),
            'policies' => CancellationPolicy::options(),
            'statuses' => array_diff_key(PropertyStatus::options(), [PropertyStatus::Kaldirildi->value => true]),
        ], $status);
    }

    /** @return array<string, mixed> */
    private function defaultInput(): array
    {
        return ['tur' => 'otel', 'mod' => 'aninda', 'politika' => 'orta', 'durum' => 'aktif', 'olanak' => []];
    }

    /** @return array{0: ?PropertyInput, 1: array<string, string>} */
    private function propertyInput(Request $request): array
    {
        $type = PropertyType::tryFrom($request->input('tur'));
        $mode = BookingMode::tryFrom($request->input('mod'));
        $policy = CancellationPolicy::tryFrom($request->input('politika'));
        $status = PropertyStatus::tryFrom($request->input('durum'));
        $errors = [];
        if ($type === null) {
            $errors['tur'] = 'Konaklama türü seçin.';
        }
        if ($mode === null) {
            $errors['mod'] = 'Rezervasyon modu seçin.';
        }
        if ($policy === null) {
            $errors['politika'] = 'İptal politikası seçin.';
        }
        if ($status === null || $status === PropertyStatus::Kaldirildi) {
            $errors['durum'] = 'Durum seçin.';
        }
        if ($type === null || $mode === null || $policy === null || $status === null || $errors !== []) {
            return [null, $errors];
        }

        return [new PropertyInput(
            $request->input('ad'),
            $type,
            $request->input('aciklama'),
            $request->input('adres'),
            $request->input('sehir'),
            $mode,
            $policy,
            $status,
            array_values(array_map('intval', array_filter($request->list('olanak'), 'ctype_digit'))),
        ), []];
    }

    /**
     * @param array<string, string> $errors
     * @param array<string, mixed> $old
     */
    private function unitsPage(int $propertyId, ?int $editId, array $errors, array $old, int $status = 200): Response
    {
        $hostId = $this->userId();
        $units = $this->properties->unitsOf($hostId, $propertyId);
        $editing = null;
        foreach ($units as $unit) {
            if ($unit->id === $editId) {
                $editing = $unit;
            }
        }

        return $this->render('host/units', [
            'title' => 'Odalar / birimler',
            'property' => $this->properties->getOwned($hostId, $propertyId),
            'units' => $units,
            'editing' => $editing,
            'errors' => $errors,
            'old' => $old,
            'roomTypes' => RoomType::options(),
        ], $status);
    }

    /**
     * @param array<string, string> $errors
     * @param array<string, mixed> $old
     */
    private function pricingPage(Request $request, int $propertyId, array $errors, array $old, int $status = 200): Response
    {
        $hostId = $this->userId();
        $property = $this->properties->getOwned($hostId, $propertyId);
        $units = $this->properties->unitsOf($hostId, $propertyId);
        $rules = $this->priceRules->rulesFor($hostId, $propertyId);

        // Fiyat önizleme: kuralların etkisini kayıt oluşturmadan görmek için (AC-23, AC-24)
        $quote = null;
        $quoteError = null;
        $simUnitId = $request->int('sim_birim', (int) ($units[0]->id ?? 0));
        if ($request->query('sim_giris') !== '' && $request->query('sim_cikis') !== '') {
            try {
                $range = DateRange::fromStrings($request->query('sim_giris'), $request->query('sim_cikis'));
                foreach ($units as $unit) {
                    if ($unit->id === $simUnitId) {
                        $quote = $this->pricing->quote($unit, $range);
                    }
                }
            } catch (InvalidArgumentException) {
                $quoteError = 'Önizleme için geçerli tarihler girin.';
            } catch (ValidationException $e) {
                $quoteError = $e->getMessage();
            }
        }

        return $this->render('host/pricing', [
            'title' => 'Fiyat kuralları',
            'property' => $property,
            'units' => $units,
            'priceRules' => $rules['price'],
            'pricingRules' => $rules['pricing'],
            'errors' => $errors,
            'old' => $old,
            'quote' => $quote,
            'quoteError' => $quoteError,
            'sim' => ['birim' => $simUnitId, 'giris' => $request->query('sim_giris'), 'cikis' => $request->query('sim_cikis')],
            'valueTypes' => ValueType::options(),
            'pricingTypes' => PricingRuleType::options(),
            'calcMethods' => CalcMethod::options(),
        ], $status);
    }

    /**
     * @param array<string, string> $errors
     * @param array<string, mixed> $values
     */
    private function calendarPage(Request $request, array $errors, array $values, int $status = 200): Response
    {
        $hostId = $this->userId();
        $rows = $this->properties->listForOwner($hostId);
        $propertyId = (int) ($values['ilan'] ?? 0) ?: (int) ($rows[0]['property']->id ?? 0);
        $units = $propertyId > 0 ? $this->properties->unitsOf($hostId, $propertyId) : [];
        $unitId = (int) ($values['birim'] ?? 0);
        $unitIds = array_map(fn ($u): int => (int) $u->id, $units);
        if (!in_array($unitId, $unitIds, true)) {
            $unitId = $unitIds[0] ?? 0;
        }
        $ay = is_string($values['ay'] ?? null) ? $values['ay'] : '';
        $today = $this->clock->today();
        [$year, $month] = preg_match('/^(\d{4})-(\d{2})$/', $ay, $m)
            ? [(int) $m[1], (int) $m[2]]
            : [(int) $today->format('Y'), (int) $today->format('n')];

        return $this->render('host/calendar', [
            'title' => 'Takvim ve fiyat yönetimi',
            'rows' => $rows,
            'propertyId' => $propertyId,
            'units' => $units,
            'unitId' => $unitId,
            'month' => sprintf('%04d-%02d', $year, $month),
            'calendar' => $unitId > 0 ? $this->availability->monthCalendar($unitId, $year, $month) : null,
            'errors' => $errors,
            'old' => $values,
        ], $status);
    }

    /** @return array{0: DateTimeImmutable, 1: DateTimeImmutable, 2: array<string, string>} */
    private function period(Request $request): array
    {
        $today = $this->clock->today();
        $errors = [];
        try {
            $from = $request->query('baslangic') !== '' ? DateRange::parseDate($request->query('baslangic')) : $today->modify('first day of this month');
            $to = $request->query('bitis') !== '' ? DateRange::parseDate($request->query('bitis')) : $today->modify('last day of this month');
        } catch (InvalidArgumentException) {
            $errors['baslangic'] = 'Geçerli tarihler girin.';
            $from = $today->modify('first day of this month');
            $to = $today->modify('last day of this month');
        }

        return [$from, $to, $errors];
    }

    private function date(string $value, string $field): DateTimeImmutable
    {
        try {
            return DateRange::parseDate($value);
        } catch (InvalidArgumentException) {
            throw ValidationException::single($field, 'Geçerli bir tarih seçin.');
        }
    }

    private function decimal(string $value, string $field): int
    {
        if (!Decimal::isValid($value)) {
            throw ValidationException::single($field, 'Geçerli bir sayı girin (ör. 20 veya 12.5).');
        }

        return Decimal::toInt($value);
    }
}
