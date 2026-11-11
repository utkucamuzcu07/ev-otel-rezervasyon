<?php
/**
 * FR-43: sezon / hafta sonu / uzun konaklama kuralları, vergi ve ek ücretler; fiyat önizlemesi.
 *
 * @var App\Core\View $view
 * @var App\Model\Property $property
 * @var list<App\Model\BookableUnit> $units
 * @var list<App\Model\PriceRule> $priceRules
 * @var list<App\Model\PricingRule> $pricingRules
 * @var array<string, string> $errors
 * @var array<string, mixed> $old
 * @var App\Service\Pricing\PriceQuote|null $quote
 * @var string|null $quoteError
 * @var array{birim: int, giris: string, cikis: string} $sim
 * @var array<string, string> $valueTypes
 * @var array<string, string> $pricingTypes
 * @var array<string, string> $calcMethods
 */
use App\Model\CalcMethod;
use App\Model\PriceRuleType;
use App\Model\ValueType;
use App\Support\Money;

$unitName = function (?int $unitId) use ($units): string {
    foreach ($units as $unit) {
        if ($unit->id === $unitId) {
            return $unit->name;
        }
    }

    return 'Tüm odalar';
};
$unitSelect = function () use ($units, $view): string {
    $html = '<select class="form-select form-select-sm" name="birim"><option value="">Tüm odalar</option>';
    foreach ($units as $unit) {
        $html .= '<option value="' . (int) $unit->id . '">' . e($unit->name) . '</option>';
    }

    return $html . '</select>';
};
$deleteForm = function (int $id, string $action) use ($view, $property): string {
    return '<form method="post" action="' . e($view->url('/panel/ilan/' . $property->id . '/fiyat')) . '" class="d-inline">'
        . $view->csrfField() . '<input type="hidden" name="islem" value="' . e($action) . '"><input type="hidden" name="kural" value="' . $id . '">'
        . '<button type="submit" class="btn btn-outline-danger btn-sm">Sil</button></form>';
};
?>
<div class="row g-3">
    <div class="col-12 col-lg-3"><?= $view->partial('partials/host_nav') ?></div>
    <div class="col-12 col-lg-9">
        <h1 class="h4">Fiyat kuralları · <?= e($property->name) ?></h1>
        <p class="small text-body-secondary">Hesap sırası: gece temel fiyatı (takvimde özel fiyat varsa o) × (1 + sezon) × (1 + hafta sonu), gece bazında kuruşa yuvarlama → uzun konaklama indirimi → vergi ve ek ücretler (indirimli tutar üzerinden).</p>
        <?php if ($errors !== []): ?>
            <div class="alert alert-danger"><?php foreach ($errors as $message): ?><div><?= e($message) ?></div><?php endforeach; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm mb-3"><div class="card-body">
            <h2 class="h6 fw-bold">Tanımlı kurallar</h2>
            <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Tür</th><th>Ad</th><th>Kapsam</th><th>Ayrıntı</th><th>Değer</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($priceRules as $rule): ?>
                    <tr>
                        <td><?= e($rule->type->label()) ?></td>
                        <td><?= e($rule->name) ?></td>
                        <td class="small"><?= e($unitName($rule->unitId)) ?></td>
                        <td class="small">
                            <?php if ($rule->type === PriceRuleType::Sezon): ?><?= $view->date($rule->startDate) ?> – <?= $view->date($rule->endDate) ?> · öncelik <?= (int) $rule->priority ?>
                            <?php elseif ($rule->type === PriceRuleType::UzunKonaklama): ?>en az <?= (int) $rule->minNights ?> gece
                            <?php else: ?>Cuma ve Cumartesi geceleri<?php endif; ?>
                        </td>
                        <td><?= e($rule->valueType === ValueType::Yuzde ? Money::formatPercent($rule->value) : Money::format($rule->value)) ?></td>
                        <td class="text-end"><?= $deleteForm((int) $rule->id, 'sil_kural') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php foreach ($pricingRules as $rule): ?>
                    <tr>
                        <td><?= e($rule->type->label()) ?></td>
                        <td><?= e($rule->name) ?></td>
                        <td class="small">Tüm rezervasyonlar</td>
                        <td class="small"><?= e($rule->calcMethod->label()) ?></td>
                        <td><?= e($rule->calcMethod === CalcMethod::Yuzde ? Money::formatPercent($rule->value) : Money::format($rule->value)) ?></td>
                        <td class="text-end"><?= $deleteForm((int) $rule->id, 'sil_vergi') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($priceRules === [] && $pricingRules === []): ?><tr><td colspan="6" class="text-body-secondary">Kural yok; temel fiyat uygulanır.</td></tr><?php endif; ?>
                </tbody>
            </table>
            </div>
        </div></div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-xl-6">
                <form method="post" action="<?= e($view->url('/panel/ilan/' . $property->id . '/fiyat')) ?>" class="card card-body shadow-sm h-100" novalidate>
                    <h2 class="h6 fw-bold">Sezon farkı</h2>
                    <?= $view->csrfField() ?><input type="hidden" name="islem" value="sezon">
                    <div class="row g-2">
                        <div class="col-12"><input type="text" class="form-control form-control-sm" name="ad" placeholder="Ad (ör. Yaz sezonu)" aria-label="Kural adı" required></div>
                        <div class="col-6"><label class="form-label small mb-0" for="s-bas">Başlangıç gecesi</label><input type="date" class="form-control form-control-sm" id="s-bas" name="baslangic" required></div>
                        <div class="col-6"><label class="form-label small mb-0" for="s-bit">Bitiş gecesi (dahil)</label><input type="date" class="form-control form-control-sm" id="s-bit" name="bitis" required></div>
                        <div class="col-4"><label class="form-label small mb-0" for="s-yuzde">Fark (%)</label><input type="number" step="0.01" class="form-control form-control-sm" id="s-yuzde" name="yuzde" placeholder="30" required></div>
                        <div class="col-4"><label class="form-label small mb-0" for="s-onc">Öncelik</label><input type="number" min="1" max="100" class="form-control form-control-sm" id="s-onc" name="oncelik" value="1"></div>
                        <div class="col-4"><label class="form-label small mb-0">Oda</label><?= $unitSelect() ?></div>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm mt-2">Sezon ekle</button>
                    <div class="form-text">Aynı geceyi kapsayan kurallardan önceliği yüksek olan uygulanır; eşit öncelik kabul edilmez.</div>
                </form>
            </div>
            <div class="col-12 col-xl-6">
                <form method="post" action="<?= e($view->url('/panel/ilan/' . $property->id . '/fiyat')) ?>" class="card card-body shadow-sm mb-3" novalidate>
                    <h2 class="h6 fw-bold">Hafta sonu farkı (Cuma, Cumartesi geceleri)</h2>
                    <?= $view->csrfField() ?><input type="hidden" name="islem" value="hafta_sonu">
                    <div class="row g-2 align-items-end">
                        <div class="col-5"><label class="form-label small mb-0" for="h-yuzde">Fark (%)</label><input type="number" step="0.01" class="form-control form-control-sm" id="h-yuzde" name="yuzde" placeholder="20" required></div>
                        <div class="col-4"><label class="form-label small mb-0">Oda</label><?= $unitSelect() ?></div>
                        <div class="col-3"><button type="submit" class="btn btn-success btn-sm w-100">Kaydet</button></div>
                    </div>
                </form>
                <form method="post" action="<?= e($view->url('/panel/ilan/' . $property->id . '/fiyat')) ?>" class="card card-body shadow-sm" novalidate>
                    <h2 class="h6 fw-bold">Uzun konaklama indirimi</h2>
                    <?= $view->csrfField() ?><input type="hidden" name="islem" value="uzun">
                    <div class="row g-2 align-items-end">
                        <div class="col-4"><label class="form-label small mb-0" for="u-gece">En az gece</label><input type="number" min="2" max="30" class="form-control form-control-sm" id="u-gece" name="en_az_gece" placeholder="7" required></div>
                        <div class="col-4"><label class="form-label small mb-0" for="u-deger">Değer</label><input type="number" step="0.01" class="form-control form-control-sm" id="u-deger" name="deger" placeholder="10" required></div>
                        <div class="col-4"><label class="form-label small mb-0" for="u-tur">Tür</label>
                            <select class="form-select form-select-sm" id="u-tur" name="deger_turu"><?php foreach ($valueTypes as $value => $label): ?><option value="<?= e($value) ?>"><?= e($label) ?></option><?php endforeach; ?></select></div>
                        <div class="col-8"><label class="form-label small mb-0">Oda</label><?= $unitSelect() ?></div>
                        <div class="col-4"><button type="submit" class="btn btn-success btn-sm w-100">Ekle</button></div>
                    </div>
                </form>
            </div>
            <div class="col-12">
                <form method="post" action="<?= e($view->url('/panel/ilan/' . $property->id . '/fiyat')) ?>" class="card card-body shadow-sm" novalidate>
                    <h2 class="h6 fw-bold">Vergi / ek ücret</h2>
                    <?= $view->csrfField() ?><input type="hidden" name="islem" value="vergi">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-md-3"><input type="text" class="form-control form-control-sm" name="ad" placeholder="Ad (ör. KDV, Temizlik ücreti)" aria-label="Kalem adı" required></div>
                        <div class="col-6 col-md-2"><select class="form-select form-select-sm" name="tur" aria-label="Tür"><?php foreach ($pricingTypes as $value => $label): ?><option value="<?= e($value) ?>"><?= e($label) ?></option><?php endforeach; ?></select></div>
                        <div class="col-6 col-md-4"><select class="form-select form-select-sm" name="hesap" aria-label="Hesaplama"><?php foreach ($calcMethods as $value => $label): ?><option value="<?= e($value) ?>"><?= e($label) ?></option><?php endforeach; ?></select></div>
                        <div class="col-6 col-md-2"><input type="number" step="0.01" class="form-control form-control-sm" name="deger" placeholder="10" aria-label="Değer" required></div>
                        <div class="col-6 col-md-1"><button type="submit" class="btn btn-success btn-sm w-100">Ekle</button></div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm"><div class="card-body">
            <h2 class="h6 fw-bold">Fiyat önizleme (kayıt oluşturmaz)</h2>
            <form method="get" action="<?= e($view->url('/panel/ilan/' . $property->id . '/fiyat')) ?>" class="row g-2 align-items-end mb-2">
                <div class="col-12 col-md-4"><label class="form-label small mb-0" for="sim-birim">Oda</label>
                    <select class="form-select form-select-sm" id="sim-birim" name="sim_birim"><?php foreach ($units as $unit): ?><option value="<?= (int) $unit->id ?>"<?= $view->selected($unit->id, $sim['birim']) ?>><?= e($unit->name) ?></option><?php endforeach; ?></select></div>
                <div class="col-6 col-md-3"><label class="form-label small mb-0" for="sim-giris">Giriş</label><input type="date" class="form-control form-control-sm" id="sim-giris" name="sim_giris" value="<?= e($sim['giris']) ?>"></div>
                <div class="col-6 col-md-3"><label class="form-label small mb-0" for="sim-cikis">Çıkış</label><input type="date" class="form-control form-control-sm" id="sim-cikis" name="sim_cikis" value="<?= e($sim['cikis']) ?>"></div>
                <div class="col-12 col-md-2"><button type="submit" class="btn btn-outline-success btn-sm w-100">Hesapla</button></div>
            </form>
            <?php if ($quoteError !== null): ?><div class="alert alert-warning py-2"><?= e($quoteError) ?></div><?php endif; ?>
            <?php if ($quote !== null): ?>
                <table class="table table-sm dokum mb-0">
                    <?php foreach ($quote->nights as $night): ?><tr><td class="small"><?= e($night->description()) ?></td><td><?= $view->money($night->price) ?></td></tr><?php endforeach; ?>
                    <tr class="fw-semibold"><td>Konaklama toplamı</td><td><?= $view->money($quote->stayTotal) ?></td></tr>
                    <?php if ($quote->discount !== null): ?><tr class="text-success"><td><?= e($quote->discount->description) ?></td><td><?= $view->money($quote->discount->amount) ?></td></tr><?php endif; ?>
                    <?php foreach ($quote->extras as $line): ?><tr><td><?= e($line->description) ?></td><td><?= $view->money($line->amount) ?></td></tr><?php endforeach; ?>
                    <tr class="table-light fw-bold"><td>TOPLAM</td><td><?= $view->money($quote->total) ?></td></tr>
                </table>
            <?php endif; ?>
        </div></div>
    </div>
</div>
