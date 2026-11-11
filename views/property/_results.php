<?php
/**
 * Arama sonuç listesi (sayfada ve canlı aramada aynı parça kullanılır). Yatay ilan kartları.
 *
 * @var App\Core\View $view
 * @var App\Service\Dto\SearchResult $result
 * @var array<string, mixed> $q
 * @var App\Model\User|null $currentUser
 */
$c = $result->criteria;
?>
<?php if ($result->errors !== []): ?>
    <div class="alert alert-danger" role="alert">
        <?php foreach ($result->errors as $message): ?>
            <div><?= e($message) ?></div>
        <?php endforeach; ?>
    </div>
<?php elseif ($result->items->total === 0): ?>
    <div class="alert alert-info">Bu ölçütlere uygun müsait konaklama bulunamadı. Tarihleri veya filtreleri değiştirmeyi deneyin.</div>
<?php else: ?>
    <p class="small text-body-secondary mb-3"><?= (int) $result->items->total ?> konaklama bulundu<?= $result->range !== null ? ' · tüm geceleri müsait olanlar listeleniyor' : '' ?>.</p>
<?php endif; ?>

<?php foreach ($result->items->items as $item): ?>
    <?php
    $p = $item->property;
    $detailQuery = ['birim' => $item->cheapestUnit?->id, 'giris' => $c->checkIn, 'cikis' => $c->checkOut, 'kisi' => $c->guests];
    ?>
    <article class="ilan-karti yatay mb-3">
        <div class="ilan-karti-foto"><?= $view->partial('partials/photo', ['photoId' => $item->photoId, 'type' => $p->type, 'alt' => $p->name]) ?></div>
        <div class="ilan-karti-govde">
            <div class="d-flex flex-wrap gap-2">
                <span class="rozet"><?= e($p->type->label()) ?></span>
                <?php if ($p->type === App\Model\PropertyType::Otel): ?>
                    <span class="rozet rozet-basari"><?= (int) $item->availableUnits ?> uygun oda</span>
                <?php endif; ?>
            </div>
            <h2 class="ilan-karti-baslik"><a class="stretched-link" href="<?= e($view->url('/ilan/' . $p->id, $detailQuery)) ?>"><?= e($p->name) ?></a></h2>
            <p class="ilan-karti-sehir"><?= e($p->address) ?>, <?= e($p->city) ?> · en çok <?= (int) $item->maxCapacity ?> kişi</p>
            <p class="ilan-karti-puan"><?= $view->stars($item->rating, $item->reviewCount) ?></p>
            <?php if ($item->amenityNames !== []): ?>
                <p class="small text-body-secondary mb-0"><?= e(implode(' · ', $item->amenityNames)) ?></p>
            <?php endif; ?>
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mt-auto pt-2">
                <div class="ilan-karti-fiyat">
                    <strong><?= $view->money($item->nightly) ?></strong> <span class="text-body-secondary">/ gece<?= $item->quote === null ? "'den" : ' (ort.)' ?></span>
                    <?php if ($item->quote !== null): ?>
                        <div class="small text-body-secondary">Toplam <?= $view->money($item->quote->total) ?> · <?= (int) $item->quote->nightCount() ?> gece, vergi dahil</div>
                    <?php endif; ?>
                </div>
                <span class="small text-body-secondary" aria-hidden="true">Detayı gör →</span>
            </div>
        </div>
        <?php if (($currentUser ?? null) !== null): ?>
            <div class="ilan-karti-favori"><?= $view->partial('partials/favorite_button', ['propertyId' => (int) $p->id, 'isFavorite' => $item->isFavorite]) ?></div>
        <?php endif; ?>
    </article>
<?php endforeach; ?>

<?= $view->partial('partials/pagination', ['paginator' => $result->items, 'path' => '/ara', 'query' => array_diff_key($q, ['sayfa' => true])]) ?>
