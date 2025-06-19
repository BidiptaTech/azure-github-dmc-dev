<?php
echo "<h1>Laravel Debug - Azure App Service</h1>";
echo "<p><strong>Current Directory:</strong> " . getcwd() . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>Request URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>Script Name:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";

// Check if we're in the right directory
echo "<h3>Directory Structure Check:</h3>";
$currentDir = getcwd();
echo "<p><strong>Current working directory:</strong> " . $currentDir . "</p>";

// List files in current directory
echo "<h4>Files in current directory:</h4>";
$files = scandir('.');
echo "<ul>";
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        $fullPath = $currentDir . '/' . $file;
        $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
        echo "<li><strong>" . $file . "</strong>";
        if (is_dir($fullPath)) {
            echo " (directory) - " . $perms;
        } else {
            echo " (" . filesize($fullPath) . " bytes) - " . $perms;
        }
        echo "</li>";
    }
}
echo "</ul>";

// Check if public directory exists and its contents
echo "<h3>Public Directory Check:</h3>";
if (is_dir('public')) {
    echo "<p>✅ <strong>public/</strong> directory exists</p>";
    
    $publicPerms = substr(sprintf('%o', fileperms('public')), -4);
    echo "<p><strong>Public directory permissions:</strong> " . $publicPerms . "</p>";
    
    if (file_exists('public/index.php')) {
        echo "<p>✅ <strong>public/index.php</strong> exists</p>";
        $indexPerms = substr(sprintf('%o', fileperms('public/index.php')), -4);
        echo "<p><strong>index.php permissions:</strong> " . $indexPerms . "</p>";
        
        if (is_readable('public/index.php')) {
            echo "<p>✅ <strong>public/index.php is readable</strong></p>";
        } else {
            echo "<p>❌ <strong>public/index.php is NOT readable</strong></p>";
        }
    } else {
        echo "<p>❌ <strong>public/index.php</strong> NOT found</p>";
    }
    
    // Check .htaccess in public
    if (file_exists('public/.htaccess')) {
        echo "<p>✅ <strong>public/.htaccess</strong> exists</p>";
        $htaccessPerms = substr(sprintf('%o', fileperms('public/.htaccess')), -4);
        echo "<p><strong>.htaccess permissions:</strong> " . $htaccessPerms . "</p>";
        
        // Show .htaccess content
        $htaccessContent = file_get_contents('public/.htaccess');
        echo "<h4>public/.htaccess content:</h4>";
        echo "<pre>" . htmlspecialchars($htaccessContent) . "</pre>";
    } else {
        echo "<p>❌ <strong>public/.htaccess</strong> NOT found</p>";
    }
    
    // List files in public directory
    echo "<h4>Files in public/ directory:</h4>";
    $publicFiles = scandir('public');
    echo "<ul>";
    foreach ($publicFiles as $file) {
        if ($file != '.' && $file != '..') {
            $fullPath = 'public/' . $file;
            $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
            echo "<li><strong>" . $file . "</strong>";
            if (is_dir($fullPath)) {
                echo " (directory) - " . $perms;
            } else {
                echo " (" . filesize($fullPath) . " bytes) - " . $perms;
            }
            echo "</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p>❌ <strong>public/</strong> directory NOT found</p>";
}

// Check root .htaccess
echo "<h3>Root .htaccess Check:</h3>";
$rootHtaccess = '../.htaccess';
if (file_exists($rootHtaccess)) {
    echo "<p>✅ <strong>Root .htaccess</strong> exists</p>";
    $rootHtaccessContent = file_get_contents($rootHtaccess);
    echo "<h4>Root .htaccess content:</h4>";
    echo "<pre>" . htmlspecialchars($rootHtaccessContent) . "</pre>";
} else {
    echo "<p>❌ <strong>Root .htaccess</strong> NOT found</p>";
    
    // Check in document root
    $docRootHtaccess = $_SERVER['DOCUMENT_ROOT'] . '/.htaccess';
    if (file_exists($docRootHtaccess)) {
        echo "<p>✅ <strong>Found at document root</strong></p>";
        $content = file_get_contents($docRootHtaccess);
        echo "<pre>" . htmlspecialchars($content) . "</pre>";
    } else {
        echo "<p>❌ <strong>Not found at document root either</strong></p>";
    }
}

// Check Laravel requirements
echo "<h3>Laravel Environment Check:</h3>";
if (file_exists('vendor/autoload.php')) {
    echo "<p>✅ <strong>Composer autoload</strong> exists</p>";
} else {
    echo "<p>❌ <strong>Composer autoload</strong> NOT found</p>";
}

if (file_exists('.env')) {
    echo "<p>✅ <strong>.env file</strong> exists</p>";
} else {
    echo "<p>❌ <strong>.env file</strong> NOT found</p>";
}

// Check storage permissions
if (is_dir('storage')) {
    echo "<p>✅ <strong>storage/</strong> directory exists</p>";
    $storagePerms = substr(sprintf('%o', fileperms('storage')), -4);
    echo "<p><strong>Storage permissions:</strong> " . $storagePerms . "</p>";
    
    if (is_writable('storage')) {
        echo "<p>✅ <strong>storage/ is writable</strong></p>";
    } else {
        echo "<p>❌ <strong>storage/ is NOT writable</strong></p>";
    }
} else {
    echo "<p>❌ <strong>storage/</strong> directory NOT found</p>";
}

// Test direct PHP execution
echo "<h3>PHP Test:</h3>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>PHP Extensions:</strong> " . implode(', ', get_loaded_extensions()) . "</p>";

// Try to include Laravel's index.php and see what happens
echo "<h3>Laravel Bootstrap Test:</h3>";
if (file_exists('public/index.php')) {
    echo "<p>Testing Laravel bootstrap...</p>";
    ob_start();
    try {
        // Don't actually include, just test if it would work
        $indexContent = file_get_contents('public/index.php', false, null, 0, 500);
        echo "<p>✅ <strong>Can read index.php content</strong></p>";
        echo "<h4>First 500 chars of index.php:</h4>";
        echo "<pre>" . htmlspecialchars($indexContent) . "</pre>";
    } catch (Exception $e) {
        echo "<p>❌ <strong>Error reading index.php:</strong> " . $e->getMessage() . "</p>";
    }
    ob_end_clean();
}

echo "<h3>Recommended Actions:</h3>";
echo "<ol>";
echo "<li>Check if mod_rewrite is enabled on the server</li>";
echo "<li>Verify file permissions (should be 644 for files, 755 for directories)</li>";
echo "<li>Ensure .htaccess files are being processed</li>";
echo "<li>Check if the document root is correctly set</li>";
echo "</ol>";

?> 