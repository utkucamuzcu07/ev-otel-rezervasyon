<?php

declare(strict_types=1);

/*
 * Gereksinim–test izlenebilirlik tablosunu üretir (docs/test-izlenebilirlik.md).
 *
 *   composer izlenebilirlik          -> testleri koşar, sonuç ve kapsamla birlikte tabloyu üretir
 *   php bin/izlenebilirlik.php [--sonuc=json.xml] [--kapsam=clover.xml] [--liste]
 *
 * Kaynaklar:
 *   - docs/srs.md: FR, NFR, BR, AC, CS, T ve UC kodları ile açıklamaları
 *   - testler: #[Group('FR-18')] öznitelikleri ve test adlarındaki kodlar (testT05..., testCs10..., testBr01...)
 *   - --sonuc : phpunit --log-junit çıktısı (her testin geçti / kaldı / atlandı durumu, JSON deposu)
 *   - --kapsam: phpunit --coverage-clover çıktısı (NFR-16: çekirdek servislerin satır kapsamı)
 *   - --liste : yalnızca SRS kodlarını ve açıklamalarını yazdırır
 *   - --json  : eşlemeyi (kod => açıklama, testler) ayrıca JSON dosyasına yazar (ör. issue/pano üretimi için)
 */

require dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__);
$options = getopt('', ['sonuc::', 'kapsam::', 'liste', 'json::']);
$categories = [
    'FR' => 'Fonksiyonel gereksinimler',
    'NFR' => 'Fonksiyonel olmayan gereksinimler',
    'BR' => 'İş kuralları',
    'AC' => 'Kabul kriterleri',
    'UC' => 'Kullanım senaryoları',
    'CS' => 'Çekirdek problem senaryoları (Bölüm 4.6)',
    'T' => 'Çekirdek problem test gereksinimleri (Bölüm 4.8)',
];

// ------------------------------------------------------------------ 1) SRS kodları
$srs = (string) file_get_contents($root . '/docs/srs.md');
$requirements = [];
$short = static function (string $text): string {
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags(str_replace(['**', '`'], '', $text))) ?? '');

    return mb_strlen($text) > 120 ? rtrim(mb_substr($text, 0, 117)) . '…' : $text;
};
if (preg_match_all('/\|\s*\*\*Gereksinim No\*\*\s*\|\s*\*\*((?:FR|NFR)-\d+)\*\*\s*\|(.*?)\|\s*\*\*Açıklama\*\*\s*\|\s*(.*?)\s*\|\s*$/msu', $srs, $m, PREG_SET_ORDER)) {
    foreach ($m as [, $code, , $text]) {
        $requirements[$code] ??= $short($text);
    }
}
if (preg_match_all('/^\|\s*\*\*((?:BR|AC|CS|T)-\d+)\*\*\s*\|\s*(.*?)\s*\|/mu', $srs, $m, PREG_SET_ORDER)) {
    foreach ($m as [, $code, $text]) {
        $requirements[$code] ??= $short($text);
    }
}
if (preg_match_all('/^#{2,4}\s+[\d.]+\s+Senaryo\s+(UC-\d+):\s*(.+)$/mu', $srs, $m, PREG_SET_ORDER)) {
    foreach ($m as [, $code, $text]) {
        $requirements[$code] ??= $short($text);
    }
}
$categoryOf = static fn (string $code): string => explode('-', $code)[0];
uksort($requirements, static function (string $a, string $b) use ($categories, $categoryOf): int {
    $order = array_flip(array_keys($categories));

    return [$order[$categoryOf($a)] ?? 99, (int) explode('-', $a)[1]] <=> [$order[$categoryOf($b)] ?? 99, (int) explode('-', $b)[1]];
});

if (isset($options['liste'])) {
    foreach ($requirements as $code => $text) {
        echo str_pad($code, 7) . ' ' . $text . "\n";
    }
    exit(0);
}

// ------------------------------------------------------------------ 2) testler ve kapsadıkları kodlar
$codePattern = '/^(FR|NFR|BR|AC|CS|T|UC)-\d{2}$/';
$tests = []; // "Sınıf::metot" => list<kod>
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/tests', FilesystemIterator::SKIP_DOTS));
$classes = [];
foreach ($files as $file) {
    if (!str_ends_with($file->getFilename(), 'Test.php')) {
        continue;
    }
    $relative = substr(str_replace('\\', '/', $file->getPathname()), strlen(str_replace('\\', '/', $root . '/tests/')), -4);
    $classes[] = 'Tests\\' . str_replace('/', '\\', $relative);
}
sort($classes);
foreach ($classes as $class) {
    if (!class_exists($class)) {
        continue;
    }
    $reflection = new ReflectionClass($class);
    $classCodes = [];
    foreach ($reflection->getAttributes(PHPUnit\Framework\Attributes\Group::class) as $attribute) {
        $classCodes[] = (string) $attribute->getArguments()[0];
    }
    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if (!str_starts_with($method->getName(), 'test') || $method->getDeclaringClass()->getName() !== $class) {
            continue;
        }
        $codes = $classCodes;
        foreach ($method->getAttributes(PHPUnit\Framework\Attributes\Group::class) as $attribute) {
            $codes[] = (string) $attribute->getArguments()[0];
        }
        // Adın içindeki kodlar: testT05..., testCs10..., testAc23..., testFr40..., testBr01Br02...
        if (preg_match_all('/(Nfr|Fr|Br|Ac|Cs|Uc|T)(\d{2})(?!\d)/', $method->getName(), $m, PREG_SET_ORDER)) {
            foreach ($m as [, $prefix, $number]) {
                $codes[] = strtoupper($prefix) . '-' . $number;
            }
        }
        $codes = array_values(array_unique(array_filter($codes, static fn (string $c): bool => preg_match($codePattern, $c) === 1)));
        $tests[$reflection->getShortName() . '::' . $method->getName()] = $codes;
    }
}

// ------------------------------------------------------------------ 3) test sonuçları (JUnit) ve kapsam (Clover)
/** @return array<string, list<string>> "Sınıf::metot" => list<'ok'|'kaldi'|'atlandi'> */
$parseJunit = static function (string $path): array {
    $results = [];
    $xml = is_file($path) ? simplexml_load_file($path) : false;
    foreach ($xml !== false ? $xml->xpath('//testcase') ?: [] : [] as $case) {
        $classParts = explode('\\', (string) $case['class']);
        $name = preg_replace('/ with data set .*$/', '', (string) $case['name']) ?? '';
        $status = isset($case->failure) || isset($case->error) ? 'kaldi' : (isset($case->skipped) ? 'atlandi' : 'ok');
        $results[end($classParts) . '::' . $name][] = $status;
    }

    return $results;
};
$resultSets = array_filter([
    'JSON' => !empty($options['sonuc']) ? $parseJunit((string) $options['sonuc']) : [],
]);
$coverage = [];
if (!empty($options['kapsam']) && is_file((string) $options['kapsam'])) {
    $xml = simplexml_load_file((string) $options['kapsam']);
    foreach (['BookingConflictService', 'ReservationService', 'AvailabilityService', 'PricingService'] as $service) {
        foreach ($xml !== false ? $xml->xpath('//file') ?: [] : [] as $file) {
            if (!str_ends_with(str_replace('\\', '/', (string) $file['name']), '/Service/' . $service . '.php')) {
                continue;
            }
            $total = 0;
            $covered = 0;
            foreach ($file->line as $line) {
                if ((string) $line['type'] === 'stmt') {
                    $total++;
                    $covered += (int) $line['count'] > 0 ? 1 : 0;
                }
            }
            $coverage[$service] = [$covered, $total];
        }
    }
}

// ------------------------------------------------------------------ 4) tablo
$byCode = [];
foreach ($tests as $test => $codes) {
    foreach ($codes as $code) {
        $byCode[$code][] = $test;
    }
}
$statusOf = static function (array $testNames, array $results): string {
    $all = [];
    foreach ($testNames as $test) {
        foreach ($results[$test] ?? ['yok'] as $status) {
            $all[] = $status;
        }
    }
    $ok = count(array_filter($all, static fn (string $s): bool => $s === 'ok'));
    if (in_array('kaldi', $all, true)) {
        return '✗ ' . $ok . '/' . count($all);
    }
    if ($ok === 0) {
        return 'atlandı';
    }

    return '✓ ' . $ok . '/' . count($all);
};

$lines = [];
$lines[] = '# Gereksinim–Test İzlenebilirlik Tablosu';
$lines[] = '';
$lines[] = '> Bu dosya `php bin/izlenebilirlik.php` ile **otomatik üretilir**; elle düzenlemeyin. Yeniden üretmek için: `composer izlenebilirlik`.';
$lines[] = '> Kaynaklar: [SRS v2.3](srs.md) kodları, testlerdeki `#[Group(\'FR-18\')]` öznitelikleri ve test adlarındaki kodlar'
    . ' (ör. `testT05...`, `testCs10...`). Bir gereksinimin testlerini çalıştırmak için: `vendor/bin/phpunit --group FR-18`.';
$lines[] = '';
$generated = (new DateTimeImmutable('now', new DateTimeZone('Europe/Istanbul')))->format('d.m.Y H:i');
$totalTests = count($tests);
$lines[] = 'Üretilme: ' . $generated . '. Test metodu sayısı: ' . $totalTests . '.';
foreach ($resultSets as $driver => $results) {
    $failed = count(array_filter($results, static fn (array $r): bool => in_array('kaldi', $r, true)));
    $passed = count(array_filter($results, static fn (array $r): bool => !in_array('kaldi', $r, true) && in_array('ok', $r, true)));
    $lines[] = sprintf(
        '- %s deposunda son çalıştırma: %d test metodu, %d geçti, %d kaldı, %d atlandı.',
        $driver,
        count($results),
        $passed,
        $failed,
        count($results) - $passed - $failed
    );
}
$lines[] = '';
$lines[] = '## Özet';
$lines[] = '';
$lines[] = '| Tür | Toplam | Otomatik testi olan | Oran |';
$lines[] = '| --- | ---: | ---: | ---: |';
foreach ($categories as $prefix => $label) {
    $codes = array_filter(array_keys($requirements), static fn (string $c): bool => $categoryOf($c) === $prefix);
    $covered = array_filter($codes, static fn (string $c): bool => isset($byCode[$c]));
    $lines[] = sprintf('| %s (%s) | %d | %d | %%%d |', $label, $prefix, count($codes), count($covered), count($codes) > 0 ? (int) round(count($covered) * 100 / count($codes)) : 0);
}
$lines[] = '';
if ($coverage !== []) {
    $lines[] = '## NFR-16: Çekirdek servislerin satır kapsamı';
    $lines[] = '';
    $lines[] = '| Servis | Kapsanan satır | Toplam | Kapsam | Hedef (%90) |';
    $lines[] = '| --- | ---: | ---: | ---: | --- |';
    foreach ($coverage as $service => [$covered, $total]) {
        $percent = $total > 0 ? $covered * 100 / $total : 0.0;
        $lines[] = sprintf('| %s | %d | %d | %%%s | %s |', $service, $covered, $total, number_format($percent, 1, ',', ''), $percent >= 90 ? '✓' : '✗');
    }
    $lines[] = '';
}
foreach ($categories as $prefix => $label) {
    $lines[] = '## ' . $label . ' (' . $prefix . ')';
    $lines[] = '';
    $header = '| Kod | Gereksinim | Testler |';
    $rule = '| --- | --- | --- |';
    foreach (array_keys($resultSets) as $driver) {
        $header .= ' ' . $driver . ' |';
        $rule .= ' --- |';
    }
    $lines[] = $header;
    $lines[] = $rule;
    $missing = [];
    foreach ($requirements as $code => $text) {
        if ($categoryOf($code) !== $prefix) {
            continue;
        }
        $testNames = $byCode[$code] ?? [];
        sort($testNames);
        if ($testNames === []) {
            $missing[] = $code;
        }
        $cell = $testNames === [] ? '—' : implode('<br>', array_map(static fn (string $t): string => '`' . $t . '`', $testNames));
        $row = '| **' . $code . '** | ' . str_replace('|', '/', $text) . ' | ' . $cell . ' |';
        foreach ($resultSets as $results) {
            $row .= ' ' . ($testNames === [] ? '—' : $statusOf($testNames, $results)) . ' |';
        }
        $lines[] = $row;
    }
    $lines[] = '';
    if ($missing !== []) {
        $lines[] = 'Otomatik testi olmayanlar: ' . implode(', ', $missing) . '. Bunlar arayüz/süreç gereksinimleridir veya elle (sunum senaryosu, kod incelemesi) doğrulanır.';
        $lines[] = '';
    }
}
$untagged = array_keys(array_filter($tests, static fn (array $codes): bool => $codes === []));
if ($untagged !== []) {
    $lines[] = '## Gereksinim kodu taşımayan testler';
    $lines[] = '';
    $lines[] = 'Yardımcı/altyapı testleri: ' . implode(', ', array_map(static fn (string $t): string => '`' . $t . '`', $untagged)) . '.';
    $lines[] = '';
}

file_put_contents($root . '/docs/test-izlenebilirlik.md', implode("\n", $lines));
if (!empty($options['json'])) {
    $export = [];
    foreach ($requirements as $code => $text) {
        $names = $byCode[$code] ?? [];
        sort($names);
        $export[$code] = ['aciklama' => $text, 'testler' => $names];
    }
    file_put_contents((string) $options['json'], json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}
printf("docs/test-izlenebilirlik.md üretildi: %d gereksinim, %d test.\n", count($requirements), $totalTests);
