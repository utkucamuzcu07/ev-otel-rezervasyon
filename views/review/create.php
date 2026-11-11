<?php
/**
 * FR-24: puan (1-5) ve yorum.
 *
 * @var App\Core\View $view
 * @var App\Service\Dto\ReservationView $r
 * @var array<string, string> $errors
 * @var array<string, string> $old
 */
$res = $r->reservation;
?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4">Konaklamanızı değerlendirin</h1>
                <p class="text-body-secondary"><?= e($r->property->name) ?> · <?= e((string) $res->range()) ?></p>
                <form method="post" action="<?= e($view->url('/rezervasyon/' . $res->number . '/yorum')) ?>" novalidate>
                    <?= $view->csrfField() ?>
                    <div class="mb-3">
                        <label for="puan" class="form-label fw-semibold">Puan</label>
                        <select class="form-select<?= $view->invalid($errors, 'puan') ?>" id="puan" name="puan" required>
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <option value="<?= $i ?>"<?= $view->selected($old['puan'] ?? '5', $i) ?>><?= str_repeat('★', $i) . str_repeat('☆', 5 - $i) ?> (<?= $i ?>)</option>
                            <?php endfor; ?>
                        </select>
                        <?= $view->error($errors, 'puan') ?>
                    </div>
                    <div class="mb-3">
                        <label for="yorum" class="form-label fw-semibold">Yorumunuz</label>
                        <textarea class="form-control<?= $view->invalid($errors, 'yorum') ?>" id="yorum" name="yorum" rows="4" minlength="10" maxlength="1000" required><?= e($old['yorum'] ?? '') ?></textarea>
                        <?= $view->error($errors, 'yorum') ?>
                    </div>
                    <button type="submit" class="btn btn-success">Gönder</button>
                </form>
            </div>
        </div>
    </div>
</div>
