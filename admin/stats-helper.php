<?php
// stats-helper.php - Helper functions for statistics

/**
 * Safely get count from database with error handling
 */
function getStatCount($conn, $query) {
    try {
        $stmt = $conn->query($query);
        return $stmt->fetch()['count'] ?? 0;
    } catch (PDOException $e) {
        error_log("Stats query error: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get visitor statistics
 */
function getVisitorStats($conn) {
    return [
        'total' => getStatCount($conn, "SELECT COUNT(*) as count FROM visitor_stats"),
        'unique_monthly' => getStatCount($conn, "SELECT COUNT(DISTINCT session_id) as count FROM visitor_stats WHERE visited_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"),
        'daily' => getStatCount($conn, "SELECT COUNT(DISTINCT session_id) as count FROM visitor_stats WHERE DATE(visited_at) = CURDATE()"),
        'weekly' => getStatCount($conn, "SELECT COUNT(DISTINCT session_id) as count FROM visitor_stats WHERE visited_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")
    ];
}

/**
 * Get project statistics
 */
function getProjectStats($conn) {
    return [
        'total' => getStatCount($conn, "SELECT COUNT(*) as count FROM projects"),
        'published' => getStatCount($conn, "SELECT COUNT(*) as count FROM projects WHERE status = 'published'"),
        'draft' => getStatCount($conn, "SELECT COUNT(*) as count FROM projects WHERE status = 'draft'")
    ];
}

/**
 * Get message statistics
 */
function getMessageStats($conn) {
    return [
        'total' => getStatCount($conn, "SELECT COUNT(*) as count FROM contact_messages"),
        'unread' => getStatCount($conn, "SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0"),
        'replied' => getStatCount($conn, "SELECT COUNT(*) as count FROM contact_messages WHERE replied = 1")
    ];
}

/**
 * Get top countries by visitor count
 */
function getTopCountries($conn, $limit = 5) {
    try {
        $stmt = $conn->prepare("
            SELECT country, COUNT(*) as visits 
            FROM visitor_stats 
            WHERE country != 'Unknown' 
            GROUP BY country 
            ORDER BY visits DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get traffic source breakdown
 */
function getTrafficSources($conn) {
    try {
        $stmt = $conn->query("
            SELECT 
                CASE 
                    WHEN referrer = 'direct' THEN 'Direct'
                    WHEN referrer LIKE '%google%' OR referrer LIKE '%bing%' THEN 'Search'
                    WHEN referrer LIKE '%facebook%' OR referrer LIKE '%twitter%' OR referrer LIKE '%linkedin%' THEN 'Social'
                    ELSE 'Referral'
                END as source,
                COUNT(*) as count
            FROM visitor_stats
            GROUP BY source
        ");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get most viewed projects
 */
function getTopProjects($conn, $limit = 3) {
    try {
        $stmt = $conn->prepare("
            SELECT p.id, p.title, COALESCE(pv.view_count, 0) as views
            FROM projects p
            LEFT JOIN project_views pv ON p.id = pv.project_id
            ORDER BY views DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get visitor trends for chart (last N days)
 */
function getVisitorTrends($conn, $days = 7) {
    try {
        $stmt = $conn->prepare("
            SELECT DATE(visited_at) as date, COUNT(DISTINCT session_id) as visitors
            FROM visitor_stats
            WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(visited_at)
            ORDER BY date ASC
        ");
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get average visit duration in seconds
 */
function getAverageVisitDuration($conn) {
    try {
        $stmt = $conn->query("SELECT AVG(visit_duration) as avg_duration FROM visitor_stats WHERE visit_duration > 0");
        return round($stmt->fetch()['avg_duration'] ?? 0);
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Get conversion rate (percentage of visitors who sent a message)
 */
function getConversionRate($conn) {
    $visitors = getStatCount($conn, "SELECT COUNT(DISTINCT session_id) as count FROM visitor_stats WHERE visited_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $messages = getStatCount($conn, "SELECT COUNT(*) as count FROM contact_messages WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    
    return $visitors > 0 ? round(($messages / $visitors) * 100, 2) : 0;
}

/**
 * Get returning visitor ratio
 */
function getReturningVisitorRatio($conn) {
    $total = getStatCount($conn, "SELECT COUNT(*) as count FROM visitor_stats");
    $returning = getStatCount($conn, "
        SELECT COUNT(DISTINCT session_id) as count 
        FROM visitor_stats 
        WHERE session_id IN (
            SELECT session_id 
            FROM visitor_stats 
            GROUP BY session_id 
            HAVING COUNT(*) > 1
        )
    ");
    
    return $total > 0 ? round(($returning / $total) * 100, 2) : 0;
}

/**
 * Get database size in MB
 */
function getDatabaseSize($conn) {
    try {
        $stmt = $conn->query("
            SELECT 
                SUM(data_length + index_length) / 1024 / 1024 AS size_mb
            FROM information_schema.TABLES
            WHERE table_schema = DATABASE()
        ");
        return round($stmt->fetch()['size_mb'] ?? 0, 2);
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Get days since launch
 */
function getDaysSinceLaunch($conn) {
    try {
        $stmt = $conn->query("SELECT stat_value FROM system_stats WHERE stat_key = 'site_launch_date'");
        $launch_date = $stmt->fetch()['stat_value'] ?? '2025-10-30';
        return round((time() - strtotime($launch_date)) / 86400);
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Format duration from seconds to readable format
 */
function formatDuration($seconds) {
    if ($seconds < 60) {
        return $seconds . "s";
    } elseif ($seconds < 3600) {
        return gmdate("i:s", $seconds);
    } else {
        return gmdate("H:i:s", $seconds);
    }
}

/**
 * Get projects by category
 */
function getProjectsByCategory($conn) {
    try {
        $stmt = $conn->query("SELECT category, COUNT(*) as count FROM projects GROUP BY category");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Update system stat
 */
function updateSystemStat($conn, $key, $value) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO system_stats (stat_key, stat_value) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE stat_value = ?
        ");
        $stmt->execute([$key, $value, $value]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Calculate storage usage
 */
function calculateStorageUsage() {
    $upload_dir = '../uploads/';
    if (!is_dir($upload_dir)) {
        return 0;
    }
    
    $total_size = 0;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($upload_dir));
    
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $total_size += $file->getSize();
        }
    }
    
    return round($total_size / 1024 / 1024, 2); // Convert to MB
}
?>