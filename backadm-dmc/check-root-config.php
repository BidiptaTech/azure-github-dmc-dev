<?php
echo "<h1>Root Web.config Check</h1>";
echo "<p><strong>Current Directory:</strong> " . getcwd() . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

// Check if root web.config exists (one level up from backadm-dmc)
$rootWebConfig = '../web.config';
echo "<h3>Checking Root web.config:</h3>";

if (file_exists($rootWebConfig)) {
    echo "<p>✅ <strong>Root web.config</strong> exists</p>";
    echo "<p><strong>File size:</strong> " . filesize($rootWebConfig) . " bytes</p>";
    echo "<p><strong>File permissions:</strong> " . substr(sprintf('%o', fileperms($rootWebConfig)), -4) . "</p>";
    
    // Read the content to verify it has the correct routing rules
    $content = file_get_contents($rootWebConfig);
    if (strpos($content, 'backadm-dmc') !== false) {
        echo "<p>✅ <strong>Contains 'backadm-dmc' routing rules</strong></p>";
    } else {
        echo "<p>❌ <strong>Does NOT contain 'backadm-dmc' routing rules</strong></p>";
    }
    
    if (strpos($content, 'Laravel App Route') !== false) {
        echo "<p>✅ <strong>Contains Laravel App Route rule</strong></p>";
    } else {
        echo "<p>❌ <strong>Missing Laravel App Route rule</strong></p>";
    }
    
    // Show relevant parts of the config
    echo "<h4>Root web.config content (first 1000 chars):</h4>";
    echo "<pre>" . htmlspecialchars(substr($content, 0, 1000)) . "</pre>";
    
} else {
    echo "<p>❌ <strong>Root web.config</strong> NOT found at ../web.config</p>";
    
    // Check if it exists in the document root directly
    $docRootConfig = $_SERVER['DOCUMENT_ROOT'] . '/web.config';
    if (file_exists($docRootConfig)) {
        echo "<p>✅ <strong>Found web.config at document root:</strong> " . $docRootConfig . "</p>";
    } else {
        echo "<p>❌ <strong>No web.config found at document root either</strong></p>";
    }
}

// Check current backadm-dmc web.config
echo "<h3>Checking backadm-dmc web.config:</h3>";
if (file_exists('web.config')) {
    echo "<p>✅ <strong>backadm-dmc/web.config</strong> exists</p>";
    echo "<p><strong>File size:</strong> " . filesize('web.config') . " bytes</p>";
} else {
    echo "<p>❌ <strong>backadm-dmc/web.config</strong> NOT found</p>";
}

// Check public web.config
echo "<h3>Checking public web.config:</h3>";
if (file_exists('public/web.config')) {
    echo "<p>✅ <strong>public/web.config</strong> exists</p>";
    echo "<p><strong>File size:</strong> " . filesize('public/web.config') . " bytes</p>";
} else {
    echo "<p>❌ <strong>public/web.config</strong> NOT found</p>";
}

// Test direct Laravel access
echo "<h3>Testing Laravel Access:</h3>";
echo "<p>Try accessing: <a href='/backadm-dmc/' target='_blank'>https://dev.travclicks.com/backadm-dmc/</a></p>";
echo "<p>If you get a 404, the root web.config routing is not working.</p>";

?> 