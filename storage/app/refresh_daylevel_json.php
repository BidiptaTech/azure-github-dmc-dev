<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = App\Models\DayLevel::query()->whereNull('deleted_at')->latest()->get();
$masters = [];
foreach ($rows as $row) {
    $payload = $row->structured_payload;
    $masterNodes = is_array($payload['Master_DMC'] ?? null) ? $payload['Master_DMC'] : [];
    foreach ($masterNodes as $masterNode) {
        if (!is_array($masterNode)) continue;
        $masterId = (int) ($masterNode['Master_DMC_id'] ?? 0);
        if ($masterId <= 0) continue;
        if (!isset($masters[$masterId])) {
            $masters[$masterId] = ['Master_DMC_id' => $masterId, 'destinations' => []];
        }
        foreach ((array)($masterNode['destinations'] ?? []) as $destination) {
            if (!is_array($destination)) continue;
            $dmcId = (int) ($destination['DMC_id'] ?? 0);
            $countryKey = mb_strtolower(trim((string)($destination['country'] ?? '')));
            $destKey = $dmcId . '|' . $countryKey;
            if (!isset($masters[$masterId]['destinations'][$destKey])) {
                $masters[$masterId]['destinations'][$destKey] = $destination;
                continue;
            }
            $existing = $masters[$masterId]['destinations'][$destKey];
            $cities = is_array($existing['cities'] ?? null) ? $existing['cities'] : [];
            $incomingCities = is_array($destination['cities'] ?? null) ? $destination['cities'] : [];
            foreach ($incomingCities as $cIn) {
                if (!is_array($cIn)) continue;
                $cityKey = mb_strtolower(trim((string)($cIn['city'] ?? '')));
                $matchIdx = null;
                foreach ($cities as $i => $c) {
                    if (!is_array($c)) continue;
                    if (mb_strtolower(trim((string)($c['city'] ?? ''))) === $cityKey) { $matchIdx = $i; break; }
                }
                if ($matchIdx === null) {
                    $cities[] = $cIn;
                } else {
                    $prevPk = is_array($cities[$matchIdx]['packages'] ?? null) ? $cities[$matchIdx]['packages'] : [];
                    $addPk  = is_array($cIn['packages'] ?? null) ? $cIn['packages'] : [];
                    $cities[$matchIdx]['packages'] = array_merge($prevPk, $addPk);
                }
            }
            $existing['cities'] = $cities;
            $masters[$masterId]['destinations'][$destKey] = $existing;
        }
    }
}
$masterList = array_values(array_map(function ($master) {
    $master['destinations'] = array_values($master['destinations']);
    return $master;
}, $masters));
usort($masterList, fn($a, $b) => ((int)$a['Master_DMC_id']) <=> ((int)$b['Master_DMC_id']));
$payload = ['Master_DMC' => $masterList];
$json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
$dir = public_path('day-level-json');
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}
file_put_contents($dir . '/day-level-combined.json', $json);
file_put_contents(public_path('day-level-combined.json'), $json);
foreach ($masterList as $master) {
    $masterId = (int) ($master['Master_DMC_id'] ?? 0);
    if ($masterId <= 0) {
        continue;
    }
    $masterPayload = ['Master_DMC' => [$master]];
    file_put_contents(
        $dir . '/master-dmc-' . $masterId . '.json',
        json_encode($masterPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );
}
echo "Regenerated combined: " . public_path('day-level-combined.json') . PHP_EOL;
echo "Regenerated folder: " . $dir . PHP_EOL;
