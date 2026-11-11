<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Core\Container;
use App\Core\Kernel;
use App\Core\Request;
use App\Core\Response;
use App\Service\Dto\MailMessage;
use App\Service\Mail\FileMailer;
use App\Service\Mail\SmtpMailer;
use App\Support\Settings;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\AppTestCase;
use Tests\Support\TestSettings;

/**
 * FR-04 uçtan uca (ön denetleyici üzerinden): sıfırlama talebi, hesap varlığının sızdırılmaması,
 * simüle posta kutusu (/posta-kutusu, yalnızca yerelden), bağlantıyla yeni parola ve gönderim hatası.
 */
#[Group('FR-04')]
final class PasswordResetWebTest extends AppTestCase
{
    private const LOCAL = ['REMOTE_ADDR' => '127.0.0.1', 'HTTP_HOST' => 'localhost:8000'];
    private const REMOTE = ['REMOTE_ADDR' => '203.0.113.7', 'HTTP_HOST' => 'evotel.ornek.test'];

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

    protected function settings(): Settings
    {
        return TestSettings::create($this->dir, $this->dir . '/app.log', mailInbox: true);
    }

    /** @param array<string, mixed> $server */
    private function get(string $path, array $server = self::LOCAL): Response
    {
        return $this->kernel->handle(new Request('GET', $path, [], [], [], $server));
    }

    /** @param array<string, mixed> $post */
    private function post(string $path, array $post): Response
    {
        $post['_csrf'] = $_SESSION['_csrf'] ?? '';

        return $this->kernel->handle(new Request('POST', $path, [], $post, [], self::LOCAL));
    }

    /** @return list<string> posta kutusundaki iletilerin alıcıları */
    private function inbox(): array
    {
        return array_map(static fn (MailMessage $m): string => $m->to, $this->c->mailbox()->latest());
    }

    private static function flash(string $html): string
    {
        preg_match('~<div class="alert alert-info[^"]*" role="alert">\s*(.*?)\s*<button~s', $html, $m);

        return $m[1] ?? '';
    }

    public function testUnknownAndKnownAddressesGetTheSameAnswer(): void
    {
        $unknown = $this->post('/parola-sifirla', ['eposta' => 'kayitsiz@ornek.test']);
        $unknownFlash = self::flash($this->get('/giris')->body);
        self::assertSame([], $this->inbox(), 'Kayıtlı olmayan adrese ileti gönderilmez');

        $known = $this->post('/parola-sifirla', ['eposta' => 'ali@evotel.test']);
        $knownFlash = self::flash($this->get('/giris')->body);
        self::assertSame(['ali@evotel.test'], $this->inbox());

        self::assertSame([303, '/giris'], [$unknown->status, $unknown->headers['Location']]);
        self::assertSame([303, '/giris'], [$known->status, $known->headers['Location']]);
        self::assertSame($unknownFlash, $knownFlash, 'Hesap varlığı sızdırılmaz: iki durumda aynı mesaj');
        self::assertStringContainsString('kayıtlı bir hesap varsa', $knownFlash);
    }

    public function testResetLinkFromTheMailboxSetsANewPassword(): void
    {
        $this->post('/parola-sifirla', ['eposta' => 'ali@evotel.test']);

        $list = $this->get('/posta-kutusu');
        self::assertSame(200, $list->status);
        self::assertSame('no-store', $list->headers['Cache-Control'] ?? null, 'Jeton içeren sayfa önbelleğe alınmaz');
        self::assertStringContainsString('Parola sıfırlama', $list->body);

        $id = $this->c->mailbox()->latest()[0]->id;
        $message = $this->get('/posta-kutusu/' . $id);
        self::assertSame(200, $message->status);
        self::assertSame(1, preg_match('~<a href="http://localhost:8000/parola-sifirla/([0-9a-f]{64})"~', $message->body, $m), 'Bağlantı tıklanabilir');
        $token = $m[1];

        self::assertSame(200, $this->get('/parola-sifirla/' . $token)->status);
        self::assertSame(303, $this->post('/parola-sifirla/' . $token, ['parola' => 'YeniParola2026'])->status);
        self::assertSame('ali@evotel.test', $this->c->authService()->login('ali@evotel.test', 'YeniParola2026')->email, 'Yeni parolayla giriş yapılır');
        self::assertSame(422, $this->post('/parola-sifirla/' . $token, ['parola' => 'BaskaParola2026'])->status, 'Bağlantı ikinci kez kullanılamaz');
    }

    public function testMailboxIsOnlyServedLocallyAndWhenEnabled(): void
    {
        $this->post('/parola-sifirla', ['eposta' => 'ali@evotel.test']);
        $id = $this->c->mailbox()->latest()[0]->id;

        self::assertSame(404, $this->get('/posta-kutusu', self::REMOTE)->status, 'Başka bilgisayardan açılmaz');
        self::assertSame(404, $this->get('/posta-kutusu/' . $id, self::REMOTE)->status);
        self::assertSame(404, $this->get('/posta-kutusu/..')->status, 'Klasör dışına çıkılamaz');
        self::assertSame(404, $this->get('/posta-kutusu/20261101-100000-000000')->status);
        self::assertStringContainsString('/posta-kutusu', $this->get('/parola-sifirla')->body, 'Yerelde bağlantı görünür');
        self::assertStringNotContainsString('/posta-kutusu', $this->get('/parola-sifirla', self::REMOTE)->body, 'Uzaktan bağlantı görünmez');

        $closed = new Kernel(new Container(TestSettings::create($this->dir), $this->clock));
        self::assertSame(404, $closed->handle(new Request('GET', '/posta-kutusu', [], [], [], self::LOCAL))->status, 'Ayar kapalıyken sayfa yoktur');
    }

    public function testDeliveryFailureIsReportedAndLogged(): void
    {
        file_put_contents($this->dir . '/mail', 'klasör yerine dosya'); // ileti yazılamaz

        $response = $this->post('/parola-sifirla', ['eposta' => 'ali@evotel.test']);

        self::assertSame(503, $response->status, '"Gönderildi" denmez');
        self::assertStringContainsString('E-posta gönderilemedi', $response->body);
        self::assertStringContainsString('eposta_gonderilemedi', (string) file_get_contents($this->dir . '/app.log'));
    }

    public function testSmtpModeUsesTheRealMailerAndHidesTheMailbox(): void
    {
        $settings = new Settings(mailDir: $this->dir . '/mail', mailDriver: 'smtp', mailInbox: true, smtpHost: 'smtp.ornek.test', smtpUser: 'evotel@ornek.test');
        $smtp = new Container($settings, $this->clock);

        self::assertInstanceOf(SmtpMailer::class, $smtp->mailer());
        self::assertFalse($smtp->mailbox()->enabled(), 'SMTP seçiliyken ileti gerçek kutuya gider; posta kutusu sayfası kapanır');
        self::assertInstanceOf(FileMailer::class, $this->c->mailer(), 'Varsayılan: simüle posta kutusu');
    }
}
