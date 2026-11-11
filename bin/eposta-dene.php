<?php

declare(strict_types=1);

/*
 * Gerçek e-posta (SMTP) ayarını dener. Ayarlar: config/yerel.php (örnek: config/yerel.ornek.php).
 *   php bin/eposta-dene.php --kontrol          bağlanır, TLS'e geçer, kimlik doğrular; ileti göndermez
 *   php bin/eposta-dene.php alici@ornek.com    alıcıya kısa bir deneme iletisi gönderir
 */

require dirname(__DIR__) . '/vendor/autoload.php';

$config = require dirname(__DIR__) . '/config/app.php';
$container = App\Core\Container::fromConfig($config);
$settings = $container->settings;

if (!$settings->usesSmtp()) {
    fwrite(STDERR, "E-posta simüle modunda: iletiler storage/mail klasörüne yazılır ve /posta-kutusu sayfasında görünür.\n"
        . "Gerçek gönderim için config/yerel.ornek.php dosyasını config/yerel.php adıyla kopyalayıp doldurun.\n");
    exit(1);
}

$hedef = $argv[1] ?? '';
try {
    if ($hedef === '--kontrol') {
        $yetenekler = $container->smtpMailer()->probe();
        echo "Bağlantı başarılı ({$settings->smtpHost}:{$settings->smtpPort}, {$settings->smtpSecure})"
            . ($settings->smtpUser !== '' ? ', kimlik doğrulandı' : '') . ".\n";
        echo 'Sunucu yetenekleri: ' . implode(', ', $yetenekler) . "\n";
        exit(0);
    }
    if (filter_var($hedef, FILTER_VALIDATE_EMAIL) === false) {
        fwrite(STDERR, "Kullanım: php bin/eposta-dene.php alici@ornek.com   (yalnızca bağlantı denemesi: --kontrol)\n");
        exit(1);
    }
    $container->mailer()->send(
        $hedef,
        'EvOtel e-posta denemesi',
        "Merhaba,\n\nBu ileti EvOtel'in SMTP ayarlarını denemek için gönderildi.\nSunucu: {$settings->smtpHost}:{$settings->smtpPort}\n"
    );
    echo "Gönderildi: {$hedef}. Gelen kutusunu (ve istenmeyen klasörünü) kontrol edin.\n";
} catch (App\Exception\MailDeliveryException $e) {
    fwrite(STDERR, 'Gönderilemedi: ' . $e->getMessage() . "\n");
    exit(1);
}
