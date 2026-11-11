<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Exception\ConflictException;
use App\Exception\PriceChangedException;
use App\Exception\ValidationException;
use App\Service\Dto\CreateReservationRequest;
use App\Service\ReservationService;
use App\Support\ClockInterface;
use App\Support\DateRange;
use App\Support\Decimal;
use App\Support\Money;
use InvalidArgumentException;

/**
 * Rezervasyon oluşturma, ödeme, iptal ve listeleme (UC-02, UC-04; taslak 04).
 * SRS 4.7: formu ve idempotency anahtarını alır, yalnızca ReservationService'i çağırır.
 */
final class ReservationController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        Auth $auth,
        private readonly ReservationService $reservations,
        private readonly ClockInterface $clock,
    ) {
        parent::__construct($view, $session, $auth);
    }

    /** UC-02 adım 2: fiyat dökümü ve iptal koşulları; geceler henüz tutulmaz. */
    public function preview(Request $request): Response
    {
        $unitId = $request->int('birim');
        try {
            $range = DateRange::fromStrings($request->query('giris'), $request->query('cikis'), $this->clock->today()->getTimezone());
            $preview = $this->reservations->preview($this->userId(), $unitId, $range, $request->int('kisi', 1));
        } catch (InvalidArgumentException) {
            return $this->backToProperty($request, ['giris' => 'Giriş ve çıkış tarihlerini seçin.']);
        } catch (ValidationException $e) {
            return $this->backToProperty($request, $e->errors());
        }

        return $this->render('reservation/preview', [
            'title' => 'Rezervasyonu onayla',
            'preview' => $preview,
            'key' => $this->reservations->newIdempotencyKey(),
            'errors' => [],
            'old' => ['iletisim_ad' => $this->user()->fullName(), 'iletisim_telefon' => '', 'not' => ''],
            'conflict' => null,
            'notice' => null,
        ]);
    }

    /**
     * UC-02 adım 3-5: rezervasyon atomik olarak oluşturulur, ardından simüle ödeme denenir.
     * Çakışma (A1), fiyat değişikliği (A5) ve form hatası (A2) aynı onay ekranında gösterilir.
     */
    public function store(Request $request): Response
    {
        $unitId = $request->int('birim');
        $guests = $request->int('kisi', 1);
        $key = $request->input('anahtar');
        $old = [
            'iletisim_ad' => $request->input('iletisim_ad'),
            'iletisim_telefon' => $request->input('iletisim_telefon'),
            'not' => $request->input('not'),
        ];
        try {
            $range = DateRange::fromStrings($request->input('giris'), $request->input('cikis'), $this->clock->today()->getTimezone());
        } catch (InvalidArgumentException) {
            return $this->backToProperty($request, ['giris' => 'Tarihler geçersiz.']);
        }

        if ($request->input('kosullar') !== '1') {
            return $this->previewAgain($request, $unitId, $range, $guests, $key, $old, ['kosullar' => 'Devam etmek için iptal koşullarını okuduğunuzu onaylayın.']);
        }

        try {
            $result = $this->reservations->create(new CreateReservationRequest(
                $this->userId(),
                $unitId,
                $range,
                $guests,
                $key,
                Decimal::isValid($request->input('beklenen_toplam')) ? Decimal::toInt($request->input('beklenen_toplam')) : -1,
                $old['iletisim_ad'],
                $old['iletisim_telefon'],
                $old['not'],
            ));
        } catch (ConflictException $e) {
            return $this->previewAgain($request, $unitId, $range, $guests, $this->reservations->newIdempotencyKey(), $old, [], $e);
        } catch (PriceChangedException $e) {
            return $this->previewAgain($request, $unitId, $range, $guests, $key, $old, [], null, $e->getMessage());
        } catch (ValidationException $e) {
            return $this->previewAgain($request, $unitId, $range, $guests, $key, $old, $e->errors());
        }

        $number = $result->reservation->number;
        if ($result->duplicate) {
            $this->flash('info', 'Bu form zaten gönderilmişti; yeni kayıt açılmadı, mevcut rezervasyonunuz gösteriliyor.');

            return $this->redirect('/rezervasyon/' . $number);
        }

        $this->flash('success', $number . ' numaralı rezervasyonunuz oluşturuldu ve geceler sizin için tutuluyor.');
        try {
            $payment = $this->reservations->pay($number, $this->userId(), $request->input('odeme_sonucu', 'basarili') === 'basarili');
            $this->flash(
                $payment->isCaptured() ? 'success' : 'warning',
                $payment->isCaptured()
                    ? 'Simüle ödeme başarılı (' . Money::format($payment->amount) . ').'
                    : 'Simüle ödeme başarısız oldu. Bekletme süresi dolmadan tekrar deneyebilirsiniz.'
            );
        } catch (ValidationException $e) {
            $this->flash('warning', $e->getMessage());
        }

        return $this->redirect('/rezervasyon/' . $number);
    }

    public function show(Request $request, string $no): Response
    {
        $view = $this->reservations->viewFor($no, $this->user());

        return $this->render('reservation/show', ['title' => 'Rezervasyon ' . $no, 'r' => $view]);
    }

    /** Kısmi yenileme: rezervasyon durumu ve kalan bekletme süresi (ilan sahibi onaylayınca kendiliğinden güncellenir). */
    public function statusFragment(Request $request, string $no): Response
    {
        $user = $this->user();
        $this->session->close();

        return $this->fragment('reservation/_status', ['r' => $this->reservations->viewFor($no, $user), 'now' => $this->clock->now()]);
    }

    public function pay(Request $request, string $no): Response
    {
        try {
            $payment = $this->reservations->pay($no, $this->userId(), $request->input('odeme_sonucu') === 'basarili');
            $this->flash(
                $payment->isCaptured() ? 'success' : 'warning',
                $payment->isCaptured() ? 'Simüle ödeme başarılı.' : 'Simüle ödeme başarısız oldu; tekrar deneyebilirsiniz.'
            );
        } catch (ValidationException $e) {
            $this->flash('danger', $e->getMessage());
        }

        return $this->redirect('/rezervasyon/' . $no);
    }

    /** UC-04 adım 2: iade önizlemesi. */
    public function cancelForm(Request $request, string $no): Response
    {
        return $this->render('reservation/cancel', [
            'title' => 'Rezervasyon iptali',
            'r' => $this->reservations->viewFor($no, $this->user()),
            'quote' => $this->reservations->cancellationQuote($no, $this->userId()),
        ]);
    }

    public function cancel(Request $request, string $no): Response
    {
        try {
            $this->reservations->cancelByCustomer($no, $this->userId(), $request->input('neden'));
            $this->flash('success', 'Rezervasyonunuz iptal edildi; geceler yeniden müsait.');
        } catch (ValidationException $e) {
            $this->flash('danger', $e->getMessage());
        }

        return $this->redirect('/rezervasyon/' . $no);
    }

    /** FR-16: gelecek ve geçmiş rezervasyonlar. */
    public function index(Request $request): Response
    {
        return $this->render('reservation/index', [
            'title' => 'Rezervasyonlarım',
            'lists' => $this->reservations->listForCustomer($this->userId()),
        ]);
    }

    /**
     * @param array<string, string> $old
     * @param array<string, string> $errors
     */
    private function previewAgain(
        Request $request,
        int $unitId,
        DateRange $range,
        int $guests,
        string $key,
        array $old,
        array $errors,
        ?ConflictException $conflict = null,
        ?string $notice = null,
    ): Response {
        try {
            $preview = $this->reservations->preview($this->userId(), $unitId, $range, $guests);
        } catch (ValidationException $e) {
            return $this->backToProperty($request, $e->errors());
        }

        return $this->render('reservation/preview', [
            'title' => 'Rezervasyonu onayla',
            'preview' => $preview,
            'key' => $key,
            'errors' => $errors,
            'old' => $old,
            'conflict' => $conflict,
            'notice' => $notice,
        ], $conflict !== null ? 409 : ($errors !== [] ? 422 : 200));
    }

    /** @param array<string, string> $errors */
    private function backToProperty(Request $request, array $errors): Response
    {
        foreach ($errors as $message) {
            $this->flash('danger', $message);
        }
        $unitId = $request->int('birim');
        $propertyId = $this->reservations->propertyIdOfUnit($unitId);
        if ($propertyId === null) {
            return $this->redirect('/ara');
        }

        return $this->redirect('/ilan/' . $propertyId, [
            'birim' => $unitId,
            'giris' => $request->input('giris'),
            'cikis' => $request->input('cikis'),
            'kisi' => $request->input('kisi'),
        ]);
    }
}
