<?php
require_once 'config.php';

try {
    $stmt = $conn->query('DESCRIBE project_views');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo 'project_views table structure:' . PHP_EOL;
    foreach ($columns as $col) {
        echo '- ' . $col['Field'] . ' (' . $col['Type'] . ')' . PHP_EOL;
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
?>
