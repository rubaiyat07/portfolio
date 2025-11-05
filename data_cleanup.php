<?php
/**
 * Data Cleanup Script
 * Removes old visitor tracking data to comply with privacy regulations
 * Run this script periodically (e.g., via cron job) to clean up old data
 */

require_once 'config.php';

try {
    // Delete visitor stats older than 90 days
    $stmt = $conn->prepare("DELETE FROM visitor_stats WHERE visited_at < DATE_SUB(NOW(), INTERVAL 90 DAY)");
    $deleted_visitors = $stmt->execute();

    // Delete page views older than 90 days
    $stmt = $conn->prepare("DELETE FROM page_views WHERE last_viewed < DATE_SUB(NOW(), INTERVAL 90 DAY)");
    $deleted_pages = $stmt->execute();

    // Delete project views older than 90 days
    $stmt = $conn->prepare("DELETE FROM project_views WHERE last_viewed < DATE_SUB(NOW(), INTERVAL 90 DAY)");
    $deleted_projects = $stmt->execute();

    // Reset view_count in projects table for projects that haven't been viewed recently
    $stmt = $conn->prepare("
        UPDATE projects
        SET view_count = 0, last_viewed = NULL
        WHERE last_viewed < DATE_SUB(NOW(), INTERVAL 90 DAY) OR last_viewed IS NULL
    ");
    $reset_projects = $stmt->execute();

    echo "Data cleanup completed successfully!\n";
    echo "Records deleted:\n";
    echo "- Visitor stats: $deleted_visitors\n";
    echo "- Page views: $deleted_pages\n";
    echo "- Project views: $deleted_projects\n";
    echo "- Projects reset: $reset_projects\n";

} catch (PDOException $e) {
    echo "Error during data cleanup: " . $e->getMessage() . "\n";
}
?>
