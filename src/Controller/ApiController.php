<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Exception\ValidationException;
use App\Service\Pricing\PriceLine;
use App\Service\ReservationService;
use App\Support\ClockInterface;
use App\Support\DateRange;
use App\Support\Decimal;
use App\Support\Money;
use InvalidArgumentException;

/**
 * JSON döndüren AJAX uç noktaları (ders standardı "Etkileşim").
 * İlan sayfasındaki "Fiyatı gör" düğmesi bu adresi fetch ile çağırır; sayfa yenilenmez.
 */
final class ApiController extends Controller
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

    /**
     * GET /api/musaitlik?birim=2&giris=2026-11-20&cikis=2026-11-23&kisi=2
     * Müsaitlik ve gecelik fiyat dökümü (FR-06, FR-10, FR-44). Rezervasyonla aynı kural ve fiyat kodu kullanılır.
     */
    public function availability(Request $request): Response
    {
        $viewer = $this->auth->user();
        $this->session->close(); // oturum okundu; aynı oturumdaki diğer istekleri bekletme

        try {
            $range = DateRange::fromStrings($request->query('giris'), $request->query('cikis'), $this->clock->today()->getTimezone());
        } catch (InvalidArgumentException) {
            return Response::json(['basarili' => false, 'hatalar' => ['giris' => 'Giriş ve çıkış tarihlerini seçin.']], 422);
        }
        try {
            $preview = $this->reservations->preview($viewer?->id, $request->int('birim'), $range, $request->int('kisi', 1));
        } catch (ValidationException $e) {
            return Response::json(['basarili' => false, 'hatalar' => $e->errors()], 422);
        }

        $quote = $preview->quote;
        $line = static fn (PriceLine $l): array => ['aciklama' => $l->description] + self::money($l->amount);

        return Response::json([
            'basarili' => true,
            'musait' => $preview->available,
            'birim' => [
                'id' => $preview->unit->id,
                'ad' => $preview->unit->name,
                'oda_tipi' => $preview->unit->roomType->label(),
                'kapasite' => $preview->unit->capacity,
            ],
            'giris' => $range->checkIn->format('Y-m-d'),
            'cikis' => $range->checkOut->format('Y-m-d'),
            'gece' => $range->nights(),
            'kisi' => $preview->guests,
            'geceler' => array_map(
                fn ($n): array => ['tarih' => $n->date->format('Y-m-d'), 'aciklama' => $n->description()] + self::money($n->price),
                $quote->nights
            ),
            'konaklama_toplami' => self::money($quote->stayTotal),
            'indirim' => $quote->discount === null ? null : $line($quote->discount),
            'ek_kalemler' => array_map($line, $quote->extras),
            'toplam' => self::money($quote->total),
            'iptal_kosulu' => $preview->policyText,
            'alternatifler' => array_map(
                fn (DateRange $alt): array => ['giris' => $alt->checkIn->format('Y-m-d'), 'cikis' => $alt->checkOut->format('Y-m-d'), 'metin' => (string) $alt],
                $preview->alternatives
            ),
        ]);
    }

    /** @return array{tutar: string, metin: string} */
    private static function money(int $kurus): array
    {
        return ['tutar' => Decimal::fromInt($kurus), 'metin' => Money::format($kurus)];
    }
}
