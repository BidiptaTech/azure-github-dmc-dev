<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = App\Models\DayLevel::query()
    ->with('dmc')
    ->whereNull('deleted_at')
    ->latest()
    ->get();

$payload = App\Models\DayLevel::collectFlatPackageExportsFromRows($rows);
$json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

$dir = public_path('day-level-json');
if (! is_dir($dir)) {
    mkdir($dir, 0755, true);
}

file_put_contents($dir . '/day-level-combined.json', $json);
file_put_contents(public_path('day-level-combined.json'), $json);

$masterIds = [];
foreach ($payload as $entry) {
    if (! is_array($entry)) {
        continue;
    }
    $masterId = (int) ($entry['Master_DMC_id'] ?? 0);
    if ($masterId > 0) {
        $masterIds[$masterId] = true;
    }
}

foreach (array_keys($masterIds) as $masterId) {
    $masterPackages = array_values(array_filter(
        $payload,
        fn (array $entry) => (int) ($entry['Master_DMC_id'] ?? 0) === $masterId
    ));
    file_put_contents(
        $dir . '/master-dmc-' . $masterId . '.json',
        json_encode($masterPackages, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );
}

echo 'Regenerated combined: ' . public_path('day-level-combined.json') . PHP_EOL;
echo 'Regenerated folder: ' . $dir . PHP_EOL;
