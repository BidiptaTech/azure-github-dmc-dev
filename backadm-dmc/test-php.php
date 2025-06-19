<?php
echo "<h1>✅ PHP Test - DMC Laravel</h1>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Server:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Current Directory:</strong> " . getcwd() . "</p>";
echo "<p><strong>Request URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";

echo "<hr>";
echo "<h2>Directory Contents:</h2>";
echo "<pre>";
$files = scandir('.');
foreach($files as $file) {
    if ($file != '.' && $file != '..') {
        echo $file . "\n";
    }
}
echo "</pre>";

echo "<hr>";
echo "<h2>Laravel Check:</h2>";
if (file_exists('backadm-dmc/public/index.php')) {
    echo "<p style='color: green;'>✅ Laravel public/index.php found</p>";
} else {
    echo "<p style='color: red;'>❌ Laravel public/index.php NOT found</p>";
}

if (file_exists('backadm-dmc/app/Http/Kernel.php')) {
    echo "<p style='color: green;'>✅ Laravel app structure found</p>";
} else {
    echo "<p style='color: red;'>❌ Laravel app structure NOT found</p>";
}

echo "<hr>";
echo "<h2>Environment Variables:</h2>";
echo "<pre>";
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'LARAVEL') !== false || strpos($key, 'APP_') !== false || strpos($key, 'DB_') !== false) {
        echo $key . " = " . $value . "\n";
    }
}
echo "</pre>";
?> 