<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$row = App\Models\DayLevel::query()->where('id', 19)->first();
$payload = $row->structured_payload;
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
