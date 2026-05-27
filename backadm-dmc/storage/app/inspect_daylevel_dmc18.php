<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$row = App\Models\DayLevel::query()
    ->where('master_dmc_id', 16)
    ->where('dmc_id', 18)
    ->whereNull('deleted_at')
    ->first();

if (! $row) {
    echo "No row\n";
    exit(1);
}

echo 'ID: ' . $row->id . PHP_EOL;
echo 'Packages: ' . json_encode($row->collectPackageSummaries(), JSON_PRETTY_PRINT) . PHP_EOL;
$ic = $row->inter_city;
echo 'inter_city keys: ' . implode(', ', array_keys(is_array($ic) ? $ic : [])) . PHP_EOL;
if (isset($ic['destinations'][0]['cities'])) {
    echo 'cities count: ' . count($ic['destinations'][0]['cities']) . PHP_EOL;
    foreach ($ic['destinations'][0]['cities'] as $c) {
        echo ' - ' . ($c['city'] ?? '?') . ' packages: ' . count($c['packages'] ?? []) . PHP_EOL;
        foreach (($c['packages'] ?? []) as $p) {
            echo '   package_id: ' . ($p['package_id'] ?? 'none') . PHP_EOL;
        }
    }
}
