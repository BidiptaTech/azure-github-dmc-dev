<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\DayLevelController;
use App\Models\DayLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

$masterDmcId = 16;
$dmcId = 18;
$packageId = 'pkg_' . (int) (microtime(true) * 1000) . '_jkt4d';

$days = [];
for ($d = 1; $d <= 4; $d++) {
    $days[(string) ($d - 1)] = [
        'day'          => $d,
        'hotels'       => $d === 1 ? [
            'Hotel 1' => [
                'hotel_id'                 => '296',
                'hotel_name'               => 'Beverly Hotel',
                'city'                     => 'Jakarta',
                'meal_plan'                => 'Breakfast',
                'price'                    => 0,
                'night'                    => 3,
                'meal_type'                => 'Buffet',
                'guide_required'           => 'No',
                'arrival_departure'        => 'No',
                'arrival_departure_type'   => '',
                'priority'                 => 1,
                'transfer_city'            => '',
                'transfer_pickup'          => '',
                'transfer_drop'            => '',
            ],
        ] : [],
        'attractions'  => [],
        'restaurants'  => [],
        'services'     => [],
    ];
}

$payload = [
    'Master_DMC' => [
        [
            'Master_DMC_id' => $masterDmcId,
            'destinations'  => [
                [
                    'DMC_id'  => $dmcId,
                    'country' => 'Indonesia',
                    'cities'  => [
                        [
                            'city'         => 'Jakarta',
                            'checkin_day'  => 1,
                            'checkout_day' => 4,
                            'packages'     => [
                                [
                                    'package_id' => $packageId,
                                    'total_days' => 4,
                                    'days'       => $days,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];

/** @var DayLevelController $controller */
$controller = app(DayLevelController::class);
$request = Request::create('/day-level', 'POST', [
    'structured_mode' => '1',
    'payload_json'    => json_encode($payload),
]);

try {
    DB::beginTransaction();
    $ref = new ReflectionClass($controller);
    $method = $ref->getMethod('storeStructuredPayload');
    $method->setAccessible(true);
    $saved = $method->invoke($controller, $payload);
    DB::commit();

    $refresh = $ref->getMethod('refreshCombinedJsonFile');
    $refresh->setAccessible(true);
    $refresh->invoke($controller);

    $row = DayLevel::query()
        ->where('master_dmc_id', $masterDmcId)
        ->where('dmc_id', $dmcId)
        ->whereNull('deleted_at')
        ->first();

    echo "Added package {$packageId} for Master_DMC {$masterDmcId}, DMC {$dmcId}." . PHP_EOL;
    echo "Saved rows: {$saved}" . PHP_EOL;
    if ($row) {
        echo 'Packages now: ' . json_encode($row->collectPackageSummaries(), JSON_PRETTY_PRINT) . PHP_EOL;
    }
} catch (Throwable $e) {
    DB::rollBack();
    fwrite(STDERR, 'Failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
