<?php
require_once 'config.php';
require_once 'visitor-tracker.php';

echo 'Testing visitor tracking functions...' . PHP_EOL;

// Test trackVisitor function
echo 'Testing trackVisitor function...' . PHP_EOL;
try {
    trackVisitor($conn);
    echo '✓ trackVisitor executed successfully' . PHP_EOL;
} catch (Exception $e) {
    echo '✗ trackVisitor failed: ' . $e->getMessage() . PHP_EOL;
}

// Test trackProjectView function
echo 'Testing trackProjectView function...' . PHP_EOL;
try {
    trackProjectView($conn, 1); // Assuming project ID 1 exists
    echo '✓ trackProjectView executed successfully' . PHP_EOL;
} catch (Exception $e) {
    echo '✗ trackProjectView failed: ' . $e->getMessage() . PHP_EOL;
}

echo 'Basic function tests completed.' . PHP_EOL;
?>
