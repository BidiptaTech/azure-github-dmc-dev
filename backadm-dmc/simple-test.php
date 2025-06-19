<?php
echo "<h1>Simple Laravel Test</h1>";
echo "<p><strong>Current Directory:</strong> " . getcwd() . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

// Check if public directory exists
if (is_dir('public')) {
    echo "<p>✅ <strong>public/</strong> directory exists</p>";
    
    // Check if index.php exists in public
    if (file_exists('public/index.php')) {
        echo "<p>✅ <strong>public/index.php</strong> file exists</p>";
        echo "<p><strong>File size:</strong> " . filesize('public/index.php') . " bytes</p>";
        echo "<p><strong>File permissions:</strong> " . substr(sprintf('%o', fileperms('public/index.php')), -4) . "</p>";
        
        // Try to read the first few lines
        $content = file_get_contents('public/index.php', false, null, 0, 200);
        echo "<p><strong>First 200 characters of index.php:</strong></p>";
        echo "<pre>" . htmlspecialchars($content) . "</pre>";
        
    } else {
        echo "<p>❌ <strong>public/index.php</strong> file NOT found</p>";
    }
} else {
    echo "<p>❌ <strong>public/</strong> directory NOT found</p>";
}

// List all files in public directory
if (is_dir('public')) {
    echo "<h3>Contents of public/ directory:</h3>";
    $files = scandir('public');
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "<li>" . $file;
            if (is_file('public/' . $file)) {
                echo " (" . filesize('public/' . $file) . " bytes)";
            }
            echo "</li>";
        }
    }
    echo "</ul>";
}

// Test Laravel autoloader
echo "<h3>Testing Laravel Bootstrap:</h3>";
if (file_exists('public/index.php')) {
    echo "<p>Attempting to include Laravel's public/index.php...</p>";
    try {
        // Don't actually include it, just test if it's readable
        if (is_readable('public/index.php')) {
            echo "<p>✅ public/index.php is readable</p>";
        } else {
            echo "<p>❌ public/index.php is NOT readable</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    }
}
?> 