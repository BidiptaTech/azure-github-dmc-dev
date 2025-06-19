<?php
echo "<h2>Laravel Debug Information</h2>";
echo "<h3>PHP Information</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current Working Directory: " . getcwd() . "<br>";
echo "Script Path: " . __FILE__ . "<br>";

echo "<h3>Directory Structure</h3>";
$basePath = '/home/site/wwwroot/backadm-dmc';
echo "Base Path: $basePath<br>";

$dirs = [
    'storage',
    'storage/framework',
    'storage/framework/views',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/logs',
    'bootstrap',
    'bootstrap/cache'
];

foreach ($dirs as $dir) {
    $fullPath = $basePath . '/' . $dir;
    $exists = is_dir($fullPath) ? 'EXISTS' : 'MISSING';
    $writable = is_writable($fullPath) ? 'WRITABLE' : 'NOT WRITABLE';
    $perms = file_exists($fullPath) ? substr(sprintf('%o', fileperms($fullPath)), -4) : 'N/A';
    echo "$dir: $exists, $writable, Permissions: $perms<br>";
}

echo "<h3>Environment Variables</h3>";
$envVars = ['APP_ENV', 'APP_DEBUG', 'APP_KEY', 'CACHE_DRIVER', 'SESSION_DRIVER'];
foreach ($envVars as $var) {
    echo "$var: " . (getenv($var) ?: 'NOT SET') . "<br>";
}

echo "<h3>Laravel Bootstrap Test</h3>";
try {
    if (file_exists($basePath . '/bootstrap/app.php')) {
        echo "bootstrap/app.php exists<br>";
        // Try to load Laravel
        require_once $basePath . '/bootstrap/app.php';
        echo "Laravel bootstrap loaded successfully<br>";
    } else {
        echo "bootstrap/app.php NOT FOUND<br>";
    }
} catch (Exception $e) {
    echo "Laravel bootstrap error: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}
?>
