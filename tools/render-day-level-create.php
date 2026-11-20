<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::whereIn('role_id', [11, 33, 34, 128, 129, 130, 131, 132, 134, 135, 136, 137, 138, 37, 38])
    ->whereNull('deleted_at')
    ->first();
if (!$user) {
    echo "no user\n";
    exit(1);
}
auth()->login($user);
echo 'user ' . $user->userId . ' role ' . $user->role_id . PHP_EOL;

$resp = app()->call('App\\Http\\Controllers\\DayLevelController@create');
if ($resp instanceof Illuminate\Http\RedirectResponse) {
    echo "redirected\n";
    exit(1);
}
$html = $resp->render();
$lines = explode("\n", $html);
file_put_contents(__DIR__ . '/../storage/app/_daylevel_snip.txt', implode("\n", array_slice($lines, 3720, 50)));
echo "wrote snip\n";
echo 'line 3743: ' . json_encode($lines[3742] ?? '') . PHP_EOL;

if (preg_match_all('/<script\b([^>]*)>([\s\S]*?)<\/script>/i', $html, $matches, PREG_SET_ORDER)) {
    $n = 0;
    foreach ($matches as $m) {
        $attrs = $m[1];
        $js = $m[2];
        if (stripos($attrs, 'src=') !== false) {
            continue;
        }
        if (stripos($attrs, 'application/json') !== false) {
            continue;
        }
        $n++;
        $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "dl_script_$n.js";
        file_put_contents($tmp, $js);
        $out = [];
        $code = 0;
        exec('node --check ' . escapeshellarg($tmp) . ' 2>&1', $out, $code);
        $hasCtp = strpos($js, 'ctpSyncTourTypeFromPax') !== false;
        $hasCache = strpos($js, 'cacheAllCities') !== false;
        if ($code !== 0) {
            echo "SCRIPT $n FAIL\n";
            echo implode("\n", $out) . "\n";
            echo substr($js, 0, 200) . "\n";
        } else {
            echo "SCRIPT $n OK len=" . strlen($js)
                . ($hasCtp ? ' CTP' : '')
                . ($hasCache ? ' DAYLEVEL' : '')
                . "\n";
        }
    }
}
