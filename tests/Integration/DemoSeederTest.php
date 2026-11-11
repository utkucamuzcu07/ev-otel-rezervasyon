<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Core\Kernel;
use App\Core\Request;
use App\Model\ReservationStatus;
use App\Seed\DemoSeeder;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\AppTestCase;

/**
 * Örnek veri (AC-29): her ilanda birden çok yorum (FR-24), tutarlı rezervasyonlar (T-11).
 * Ders kuralı gereği resim, ikon veya çizim dosyası hazırlanmaz: örnek veri fotoğraf üretmez.
 */
#[Group('AC-29')]
final class DemoSeederTest extends AppTestCase
{
    protected function tearDown(): void
    {
        $_SESSION = [];
        parent::tearDown();
    }

    #[Group('FR-24')]
    #[Group('T-11')]
    public function testEveryPropertyHasSeveralReviewsFromCompletedStays(): void
    {
        $summary = (new DemoSeeder($this->c))->run();

        self::assertGreaterThanOrEqual(40, $summary['yorum']);
        foreach ($this->c->properties()->all() as $property) {
            $reviews = $this->c->reviews()->findForProperty((int) $property->id);
            self::assertGreaterThanOrEqual(4, count($reviews), $property->name . ' en az 4 yorum');
            foreach ($reviews as $review) {
                $reservation = $this->c->reservations()->findById($review->reservationId);
                self::assertNotNull($reservation);
                self::assertSame(ReservationStatus::Tamamlandi, $reservation->status, 'FR-24: yorum yalnızca tamamlanmış konaklamadan');
                self::assertSame($review->customerId, $reservation->customerId, 'Yorumu konaklayan müşteri yazar');
            }
        }
        self::assertSame([], $this->c->consistencyAuditService()->violations(), 'T-11: örnek veri tutarlı');
    }

    /** Ders kuralı: "Resim, ikon veya çizim dosyası hazırlanmaz." Örnek veri fotoğraf üretmez; ilanlar emoji ile görünür. */
    #[Group('FR-09')]
    public function testDemoDataShipsNoPicturesAndListingsUseEmojiPlaceholders(): void
    {
        $summary = (new DemoSeeder($this->c))->run();

        self::assertArrayNotHasKey('fotoğraf', $summary);
        $properties = $this->c->properties()->all();
        self::assertNotSame([], $properties);
        foreach ($properties as $property) {
            self::assertSame([], $this->c->photos()->findByProperty((int) $property->id), $property->name);
        }
        self::assertSame([], glob($this->dir . '/uploads/*') ?: [], 'Yükleme klasörüne dosya yazılmaz');

        $_SESSION = [];
        $kernel = new Kernel($this->c);
        foreach (['/', '/ilan/' . $properties[0]->id] as $path) {
            $page = $kernel->handle(new Request('GET', $path));
            self::assertSame(200, $page->status, $path);
            self::assertStringNotContainsString('<img', $page->body, $path . ': fotoğrafı olmayan ilan resim etiketi basmaz');
            self::assertStringContainsString('class="emoji-foto" role="img"', $page->body, $path . ': yer tutucu emojidir');
        }
    }
}
