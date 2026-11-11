<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Core\Kernel;
use App\Core\Request;
use App\Core\Response;
use Tests\Support\AppTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Ön denetleyici üzerinden: CSRF (NFR-14), rol bazlı yetki (FR-05, NFR-03), XSS kaçışı,
 * oturum zaman aşımı (bonus), kısmi yenileme parçası, arka plan isteklerinin (sayfa bölümü,
 * JSON uç noktası, fetch ile gönderilen form) oturum ve hata yanıtları, CSV dışa aktarma.
 * Veri klasörü boş olduğundan ilk istekte örnek veri yüklenir.
 */
final class WebTest extends AppTestCase
{
    private Kernel $kernel;

    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
        $this->kernel = new Kernel($this->c);
        $this->get('/'); // örnek veriyi yükle
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        parent::tearDown();
    }

    /** @param array<string, mixed> $query */
    private function get(string $path, array $query = []): Response
    {
        return $this->kernel->handle(new Request('GET', $path, $query));
    }

    /** @param array<string, mixed> $post */
    private function post(string $path, array $post, bool $withCsrf = true): Response
    {
        if ($withCsrf) {
            $post['_csrf'] = $_SESSION['_csrf'] ?? '';
        }

        return $this->kernel->handle(new Request('POST', $path, [], $post));
    }

    /**
     * Sayfa yenilenmeden, fetch ile gönderilen form (favori düğmesi): yanıtı tarayıcı değil betik okur.
     *
     * @param array<string, mixed> $post
     */
    private function fetchPost(string $path, array $post): Response
    {
        $post['_csrf'] = $_SESSION['_csrf'] ?? '';

        return $this->kernel->handle(new Request('POST', $path, [], $post, [], ['HTTP_X_REQUESTED_WITH' => 'fetch']));
    }

    private function loginAs(string $email): void
    {
        $user = $this->c->users()->findByEmail($email);
        self::assertNotNull($user);
        $_SESSION['user_id'] = $user->id;
        $_SESSION['_last_activity'] = $this->clock->now()->getTimestamp();
        $this->c = new \App\Core\Container($this->settings(), $this->clock);
        $this->kernel = new Kernel($this->c);
    }

    #[Group('AC-29')]
    public function testHomePageListsSeededProperties(): void
    {
        $response = $this->get('/');

        self::assertSame(200, $response->status);
        self::assertStringContainsString('Konaklamanızı bulun', $response->body);
        self::assertStringContainsString('Öne çıkan konaklamalar', $response->body);
        self::assertGreaterThanOrEqual(10, count($this->c->units()->all()), 'AC-29: en az 10 birim');
    }

    #[Group('NFR-14')]
    public function testPostWithoutCsrfTokenIsRejected(): void
    {
        $response = $this->post('/giris', ['eposta' => 'ali@evotel.test', 'parola' => 'x'], false);

        self::assertSame(419, $response->status);
    }

    #[Group('FR-05')]
    #[Group('NFR-03')]
    public function testGuestIsRedirectedToLoginKeepingTheTarget(): void
    {
        $response = $this->get('/rezervasyon/onizleme', ['birim' => '1', 'giris' => '2026-11-20', 'cikis' => '2026-11-22', 'kisi' => '1']);

        self::assertSame(303, $response->status);
        self::assertStringStartsWith('/giris?donus=', $response->headers['Location']);
        self::assertStringContainsString('2026-11-20', urldecode($response->headers['Location']));
    }

    #[Group('FR-03')]
    public function testLogoutEndsTheSession(): void
    {
        $this->loginAs('ali@evotel.test');
        self::assertSame(200, $this->get('/rezervasyonlarim')->status);

        self::assertSame(405, $this->get('/cikis')->status, 'Çıkış yalnızca POST + CSRF ile yapılır');
        $response = $this->post('/cikis', []);
        self::assertSame(303, $response->status);
        self::assertArrayNotHasKey('user_id', $_SESSION);
        self::assertSame(303, $this->get('/rezervasyonlarim')->status, 'Çıkıştan sonra korumalı sayfa girişe yönlendirir');
    }

    #[Group('FR-09')]
    #[Group('FR-10')]
    #[Group('BR-06')]
    public function testPropertyDetailShowsUnitsPolicyAndBookingMode(): void
    {
        $property = $this->c->properties()->findById(1);
        self::assertNotNull($property);

        $html = $this->get('/ilan/1')->body;

        self::assertStringContainsString(e($property->name), $html);
        self::assertStringContainsString(e($property->city), $html);
        self::assertStringContainsString($property->cancellationPolicy->label(), $html, 'BR-06: iptal koşulu rezervasyondan önce görünür');
        foreach ($this->c->units()->findByProperty(1) as $unit) {
            self::assertStringContainsString(e($unit->name), $html, 'Birimler listelenir');
        }
        self::assertStringContainsString('/api/musaitlik', $html, 'Fiyat ve müsaitlik sayfa yenilenmeden JSON uç noktasından gelir');
    }

    #[Group('FR-09')]
    #[Group('FR-24')]
    public function testPropertyPageShowsRatingDistributionAndEveryReview(): void
    {
        $detail = $this->c->propertyService()->detail(1, null);
        self::assertGreaterThanOrEqual(4, $detail->reviewCount, 'Örnek veride ilan başına birden çok yorum');

        $html = $this->get('/ilan/1')->body;

        self::assertStringContainsString($detail->reviewCount . ' değerlendirme', $html);
        self::assertSame(5, substr_count($html, 'class="puan-satiri"'), "5'ten 1'e puan dağılımı");
        self::assertSame($detail->reviewCount, substr_count($html, 'class="yorum"'), 'Yayındaki yorumların hepsi listelenir');
        self::assertSame($detail->reviewCount, array_sum($detail->ratingDistribution));
    }

    /** NFR-12: form alanlarının her biri bir <label for> veya aria-label ile etiketlidir (ekran okuyucu). */
    #[Group('NFR-12')]
    public function testFormFieldsAreLabelled(): void
    {
        $pages = ['/', '/giris', '/ara?sehir=Trabzon', '/ilan/1', '/parola-sifirla'];
        $this->loginAs('ayse@evotel.test');
        $pages[] = '/panel/ilan/yeni';
        $unlabelled = [];
        foreach ($pages as $page) {
            [$path, $query] = array_pad(explode('?', $page, 2), 2, '');
            parse_str($query, $params);
            $html = $this->get($path, $params)->body;
            $dom = new \DOMDocument();
            @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
            $xpath = new \DOMXPath($dom);
            $labelled = [];
            foreach ($xpath->query('//label[@for]') ?: [] as $label) {
                if ($label instanceof \DOMElement) {
                    $labelled[$label->getAttribute('for')] = true;
                }
            }
            foreach ($xpath->query('//input[not(@type="hidden") and not(@type="submit") and not(@type="button")] | //select | //textarea') ?: [] as $field) {
                if (!$field instanceof \DOMElement) {
                    continue;
                }
                $id = $field->getAttribute('id');
                $wrapped = $field->parentNode instanceof \DOMElement && $field->parentNode->nodeName === 'label';
                if (!($id !== '' && isset($labelled[$id])) && $field->getAttribute('aria-label') === '' && !$wrapped) {
                    $unlabelled[] = $page . ' -> ' . $field->nodeName . '[name=' . $field->getAttribute('name') . ']';
                }
            }
        }

        self::assertSame([], $unlabelled, 'Etiketsiz form alanları');
    }

    /**
     * NFR-12 (WCAG 2.1 AA, 3.2.2): seçim listesinde değer değişince form kendiliğinden gönderilmez.
     * Klavyede ok tuşu her basışta değeri değiştirir; otomatik gönderim sayfayı her seferinde yeniden yükler.
     * Seçim, yanındaki düğmeyle gönderilir.
     */
    #[Group('NFR-12')]
    public function testSelectsDoNotSubmitOnChange(): void
    {
        $pages = [
            '/ara' => ['giris' => '2026-12-10', 'cikis' => '2026-12-13', 'kisi' => '2'],
            '/ilan/1' => [],
        ];
        $found = [];
        foreach ($pages as $path => $params) {
            $html = $this->get($path, $params)->body;
            if (preg_match('/<select[^>]*\bonchange=/i', $html) === 1) {
                $found[] = $path;
            }
        }
        $this->loginAs('ayse@evotel.test');
        $html = $this->get('/panel/takvim')->body;
        self::assertStringContainsString('<select class="form-select" id="birim"', $html);
        if (preg_match('/<select[^>]*\bonchange=/i', $html) === 1) {
            $found[] = '/panel/takvim';
        }

        self::assertSame([], $found, 'Kendiliğinden gönderilen seçim listesi');
    }

    #[Group('FR-05')]
    #[Group('NFR-03')]
    #[Group('AC-07')]
    public function testRolesAreEnforcedOnTheServer(): void
    {
        $this->loginAs('ali@evotel.test');
        self::assertSame(403, $this->get('/panel')->status, 'Müşteri ilan sahibi paneline giremez');
        self::assertSame(403, $this->get('/yonetim')->status, 'Müşteri yönetici paneline giremez');

        $this->loginAs('ayse@evotel.test');
        self::assertSame(200, $this->get('/panel')->status);
        self::assertSame(403, $this->get('/yonetim')->status, 'İlan sahibi yönetici paneline giremez');

        $this->loginAs('yonetici@evotel.test');
        self::assertSame(200, $this->get('/yonetim')->status);
    }

    #[Group('NFR-14')]
    public function testUserInputIsEscapedInOutput(): void
    {
        $this->get('/giris');
        $response = $this->post('/kayit', [
            'ad' => '<script>alert(1)</script>',
            'soyad' => 'Test',
            'eposta' => 'xss@example.com',
            'parola' => 'Parola1234',
            'rol' => 'musteri',
        ]);
        self::assertSame(303, $response->status);

        $profile = $this->get('/profil');
        self::assertStringNotContainsString('<script>alert(1)</script>', $profile->body);
        self::assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $profile->body);
    }

    #[Group('FR-10')]
    #[Group('AC-32')]
    public function testCalendarFragmentIsPartialHtml(): void
    {
        $response = $this->get('/parca/takvim', ['birim' => '1', 'ay' => '2026-11']);

        self::assertSame(200, $response->status);
        self::assertStringContainsString('class="takvim"', $response->body);
        self::assertStringNotContainsString('<html', $response->body, 'Parça tam sayfa değildir');
    }

    #[Group('FR-06')]
    #[Group('AC-02')]
    #[Group('AC-03')]
    #[Group('UC-01')]
    public function testSearchListsAvailablePropertiesAndValidatesDates(): void
    {
        $ok = $this->get('/ara', ['konum' => 'Trabzon', 'giris' => '2026-11-20', 'cikis' => '2026-11-22', 'kisi' => '2']);
        self::assertStringContainsString('Karadeniz Otel', $ok->body);

        $bad = $this->get('/ara', ['konum' => 'Trabzon', 'giris' => '2026-11-22', 'cikis' => '2026-11-20']);
        self::assertStringContainsString('Çıkış tarihi giriş tarihinden sonra olmalıdır', $bad->body);
    }

    #[Group('FR-30')]
    public function testAdminCanExportReportAsCsv(): void
    {
        $this->loginAs('yonetici@evotel.test');
        $response = $this->get('/yonetim/rapor', ['csv' => '1']);

        self::assertSame(200, $response->status);
        self::assertStringStartsWith('text/csv', $response->headers['Content-Type']);
        self::assertStringContainsString('Doluluk', $response->body);
    }

    #[Group('NFR-15')]
    public function testIdleSessionTimesOut(): void
    {
        $this->loginAs('ali@evotel.test');
        $_SESSION['_last_activity'] = $this->clock->now()->modify('-31 minutes')->getTimestamp();

        $response = $this->get('/rezervasyonlarim');

        self::assertSame(303, $response->status);
        self::assertSame('/giris', $response->headers['Location']);
    }

    /**
     * Bonus (oturum zaman aşımı): kendiliğinden yenilenen sayfa bölümü kullanıcı işlemi sayılmaz.
     * Sayılsaydı açık bırakılan sekme birkaç saniyede bir istek gönderdiği için oturum hiç kapanmazdı.
     */
    #[Group('NFR-15')]
    public function testPolledFragmentDoesNotKeepTheSessionAlive(): void
    {
        $this->loginAs('ali@evotel.test');
        $lastAction = $this->clock->now()->getTimestamp();

        // Sekme açık kalır: bildirim sayacı kendiliğinden yenilenir, kullanıcı hiçbir şey yapmaz.
        foreach ([10, 20, 30] as $minute) {
            $this->clock->advance('+10 minutes');
            self::assertSame(200, $this->get('/parca/bildirim-sayaci')->status, $minute . '. dakikada oturum henüz açık');
            self::assertSame($lastAction, $_SESSION['_last_activity'], 'Sayfa bölümü isteği süreyi uzatmaz');
        }
        $this->clock->advance('+1 minute');
        self::assertSame(401, $this->get('/parca/bildirim-sayaci')->status, '31. dakikada oturum kapanır');
        self::assertArrayNotHasKey('user_id', $_SESSION);

        // Kullanıcının kendi açtığı sayfa ise süreyi yeniler.
        $this->loginAs('ali@evotel.test');
        $this->clock->advance('+20 minutes');
        self::assertSame(200, $this->get('/rezervasyonlarim')->status);
        self::assertSame($this->clock->now()->getTimestamp(), $_SESSION['_last_activity']);
    }

    /**
     * Zaman aşımına uğrayan oturumda arka plan isteği giriş sayfasına yönlendirilmez (yanıt bölümün içine
     * basılacağı için); 401 alır. Uyarı, kullanıcının açacağı ilk sayfada gösterilir.
     */
    #[Group('NFR-15')]
    public function testTimedOutBackgroundRequestGets401InsteadOfTheLoginPage(): void
    {
        $this->loginAs('ali@evotel.test');
        $_SESSION['_last_activity'] = $this->clock->now()->modify('-31 minutes')->getTimestamp();
        $fragment = $this->get('/parca/bildirim-sayaci');
        self::assertSame(401, $fragment->status);
        self::assertSame('Oturum gerekli.', $fragment->body, 'Bölümün içine giriş sayfası basılmaz');
        self::assertArrayNotHasKey('Location', $fragment->headers);
        self::assertArrayNotHasKey('user_id', $_SESSION, 'Oturum yine de kapatılır');
        self::assertStringContainsString('Oturumunuz uzun süre işlem yapılmadığı için kapatıldı', $this->get('/giris')->body);

        $this->loginAs('ali@evotel.test');
        $_SESSION['_last_activity'] = $this->clock->now()->modify('-31 minutes')->getTimestamp();
        $api = $this->get('/api/musaitlik', ['birim' => '1', 'giris' => '2027-05-19', 'cikis' => '2027-05-22', 'kisi' => '1']);
        self::assertSame(401, $api->status);
        self::assertSame('Oturum gerekli.', $this->json($api)['hata'], 'JSON uç noktası hatayı JSON olarak verir');

        $this->loginAs('ali@evotel.test');
        $_SESSION['_last_activity'] = $this->clock->now()->modify('-31 minutes')->getTimestamp();
        $fetch = $this->fetchPost('/favori/1', ['donus' => '/ilan/1']);
        self::assertSame(401, $fetch->status);
        self::assertArrayNotHasKey('Location', $fetch->headers);
    }

    /** Oturumu olmayan arka plan isteği de 401 alır; normal sayfa ve form istekleri girişe yönlendirilmeye devam eder. */
    #[Group('FR-05')]
    #[Group('NFR-03')]
    public function testGuestBackgroundRequestGets401InsteadOfARedirect(): void
    {
        $fragment = $this->get('/parca/bildirim-sayaci');
        self::assertSame(401, $fragment->status);
        self::assertSame('Oturum gerekli.', $fragment->body);

        $viaFetch = $this->fetchPost('/favori/1', ['donus' => '/ilan/1']);
        self::assertSame(401, $viaFetch->status);
        self::assertArrayNotHasKey('Location', $viaFetch->headers);

        $viaForm = $this->post('/favori/1', ['donus' => '/ilan/1']);
        self::assertSame(303, $viaForm->status);
        self::assertStringStartsWith('/giris', $viaForm->headers['Location']);
    }

    /** Bonus: favori formu sayfa yenilenmeden gönderilir; yanıt yalnızca düğmenin yeni HTML'idir. */
    #[Group('FR-19')]
    public function testFavoriteIsToggledWithoutPageReload(): void
    {
        $this->loginAs('ali@evotel.test');
        $ali = $this->c->users()->findByEmail('ali@evotel.test');
        self::assertNotNull($ali);
        $wasFavorite = in_array(1, $this->c->favoriteService()->propertyIds((int) $ali->id), true);

        $response = $this->fetchPost('/favori/1', ['donus' => '/ilan/1']);

        self::assertSame(200, $response->status);
        self::assertStringNotContainsString('<html', $response->body, 'Yanıt tam sayfa değil, yalnızca düğmedir');
        self::assertStringContainsString('data-favori', $response->body);
        self::assertStringContainsString($wasFavorite ? '♡ Favori' : '♥ Favoride', $response->body);
        self::assertStringContainsString('name="donus" value="/ilan/1"', $response->body, 'Yeni düğme de aynı sayfaya döner');
        self::assertSame(!$wasFavorite, in_array(1, $this->c->favoriteService()->propertyIds((int) $ali->id), true));
    }

    /** Sayfa bölümü hatayı da bölüm olarak döndürür; tam hata sayfası (menü, alt bilgi) bölümün içine basılmaz. */
    #[Group('NFR-06')]
    public function testFragmentErrorsAreShortFragmentsNotFullPages(): void
    {
        $missing = $this->get('/parca/takvim', ['birim' => '999999', 'ay' => '2026-11']);
        self::assertSame(404, $missing->status);
        self::assertStringContainsString('Oda bulunamadı.', $missing->body);
        self::assertStringNotContainsString('<html', $missing->body);

        $this->loginAs('ali@evotel.test');
        $forbidden = $this->get('/parca/panel-rezervasyonlar');
        self::assertSame(403, $forbidden->status, 'Parça da yetki denetiminden geçer');
        self::assertStringContainsString('Bu sayfaya erişim yetkiniz yok.', $forbidden->body);
        self::assertStringNotContainsString('<html', $forbidden->body);
    }

    /** Bozuk kodlanmış (UTF-8 olmayan) girdi JSON dosyasına yazılamaz; "beklenmeyen hata" yerine anlaşılır mesajla reddedilir. */
    #[Group('NFR-06')]
    public function testInputWithInvalidEncodingIsRejectedWithAClearMessage(): void
    {
        $this->get('/giris');
        $userCount = count($this->c->users()->all());

        $response = $this->post('/kayit', [
            'ad' => "Ay\xFFe",
            'soyad' => 'Test',
            'eposta' => 'bozuk@example.com',
            'parola' => 'Parola1234',
            'rol' => 'musteri',
        ]);

        self::assertSame(400, $response->status);
        self::assertStringContainsString('okunamayan karakterler', $response->body);
        self::assertCount($userCount, $this->c->users()->all(), 'Kayıt oluşturulmaz');
        self::assertSame(400, $this->get('/ara', ['konum' => "\xC3\x28"])->status);
    }

    /** @return array<string, mixed> */
    private function json(Response $response): array
    {
        self::assertStringStartsWith('application/json', $response->headers['Content-Type'] ?? '');
        $data = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($data);

        return $data;
    }

    /** Etkileşim standardı: JSON döndüren AJAX uç noktası (ilan sayfası sayfa yenilenmeden çağırır). */
    #[Group('FR-10')]
    #[Group('FR-44')]
    #[Group('AC-08')]
    #[Group('BR-07')]
    public function testAvailabilityApiReturnsNightlyBreakdownAsJson(): void
    {
        $unit = $this->c->units()->findById(1);
        self::assertNotNull($unit);
        $query = ['birim' => '1', 'giris' => '2027-05-19', 'cikis' => '2027-05-22', 'kisi' => '1'];

        $data = $this->json($response = $this->get('/api/musaitlik', $query));

        self::assertSame(200, $response->status);
        self::assertTrue($data['basarili']);
        self::assertTrue($data['musait']);
        self::assertSame(3, $data['gece']);
        self::assertCount(3, $data['geceler']);
        $quote = $this->c->pricingService()->quote($unit, $this->range('2027-05-19', '2027-05-22'));
        self::assertSame(\App\Support\Decimal::fromInt($quote->total), $data['toplam']['tutar'], 'API ile rezervasyon aynı fiyat hesabını kullanır');
        self::assertNotSame('', $data['iptal_kosulu']);
    }

    #[Group('NFR-06')]
    #[Group('AC-03')]
    public function testAvailabilityApiReportsErrorsAsJson(): void
    {
        $invalid = $this->get('/api/musaitlik', ['birim' => '1', 'giris' => '2027-05-22', 'cikis' => '2027-05-19', 'kisi' => '1']);
        $data = $this->json($invalid);
        self::assertSame(422, $invalid->status);
        self::assertFalse($data['basarili']);
        self::assertSame('Çıkış tarihi giriş tarihinden sonra olmalıdır.', $data['hatalar']['cikis']);

        $missing = $this->get('/api/musaitlik', ['birim' => '999999', 'giris' => '2027-05-19', 'cikis' => '2027-05-22', 'kisi' => '1']);
        self::assertSame(404, $missing->status);
        self::assertFalse($this->json($missing)['basarili'], 'API hataları da JSON döner, HTML sayfa değil');
    }

    #[Group('FR-36')]
    #[Group('AC-12')]
    public function testAvailabilityApiOffersAlternativesForBookedDates(): void
    {
        $booked = null;
        foreach ($this->c->reservations()->all() as $reservation) {
            if ($reservation->status === \App\Model\ReservationStatus::Onaylandi && $reservation->checkIn >= $this->clock->today()) {
                $booked = $reservation;
                break;
            }
        }
        self::assertNotNull($booked, 'Örnek veride gelecekte onaylı rezervasyon var');

        $data = $this->json($this->get('/api/musaitlik', [
            'birim' => (string) $booked->unitId,
            'giris' => $booked->checkIn->format('Y-m-d'),
            'cikis' => $booked->checkOut->format('Y-m-d'),
            'kisi' => '1',
        ]));

        self::assertTrue($data['basarili']);
        self::assertFalse($data['musait']);
        self::assertNotEmpty($data['alternatifler']);
        self::assertLessThanOrEqual(3, count($data['alternatifler']), 'FR-36: en çok 3 alternatif');
        foreach ($data['alternatifler'] as $alternative) {
            self::assertSame($booked->nights(), $this->range($alternative['giris'], $alternative['cikis'])->nights(), 'Aynı uzunlukta');
        }
    }
}
