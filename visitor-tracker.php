<?php
require_once 'config.php';

/**
 * Visitor Tracking System
 * Tracks visitor statistics including IP, geolocation, referrer, and session data
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to get client IP address
function getClientIP() {
    $ip_headers = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];

    foreach ($ip_headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = $_SERVER[$header];
            // Handle comma-separated IPs (like X-Forwarded-For)
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            // Validate IP
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }

    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

// Function to get country from IP using ipapi.co
function getCountryFromIP($ip) {
    // Skip for localhost/private IPs
    if ($ip === '127.0.0.1' || $ip === '::1' || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) === false) {
        return 'Local/Unknown';
    }

    // Optional: Get geo data from free API
    /*
    // Use ipapi.co API (free tier allows 1000 requests/day)
    $api_url = "http://ipapi.co/{$ip}/country_name/";

    $context = stream_context_create([
        'http' => [
            'timeout' => 2, // 2 second timeout
            'user_agent' => 'Portfolio-Tracker/1.0'
        ]
    ]);

    $country = @file_get_contents($api_url, false, $context);

    if ($country === false || empty($country)) {
        return 'Unknown';
    }

    return trim($country);
    */

    // Geo-location disabled - return Unknown for all IPs
    return 'Unknown';
}

// Function to get referrer
function getReferrer() {
    $referrer = $_SERVER['HTTP_REFERER'] ?? '';

    // Clean up referrer
    if (empty($referrer)) {
        return 'direct';
    }

    // Remove protocol and www
    $referrer = preg_replace('/^https?:\/\//', '', $referrer);
    $referrer = preg_replace('/^www\./', '', $referrer);

    return $referrer;
}

// Function to track visitor
function trackVisitor($conn) {

    try {
        $session_id = session_id();
        $ip_address = hash('sha256', getClientIP()); // Anonymize IP address for privacy
        $country = getCountryFromIP($ip_address);
        $referrer = getReferrer();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $page_url = $_SERVER['REQUEST_URI'] ?? '/';
        $current_time = time();

        // Check if this session was already tracked in the last 30 minutes
        $stmt = $conn->prepare("
            SELECT id, visited_at
            FROM visitor_stats
            WHERE session_id = ? AND page_url = ?
            ORDER BY visited_at DESC
            LIMIT 1
        ");
        $stmt->execute([$session_id, $page_url]);
        $last_visit = $stmt->fetch();

        $should_track = true;
        $visit_duration = 0;

        if ($last_visit) {
            $last_visit_time = strtotime($last_visit['visited_at']);
            $time_diff = $current_time - $last_visit_time;

            // If visited same page within 30 minutes, don't track again
            if ($time_diff < 1800) {
                $should_track = false;
            } else {
                // Calculate visit duration for previous visit
                $visit_duration = min($time_diff, 3600); // Max 1 hour
                $conn->prepare("UPDATE visitor_stats SET visit_duration = ? WHERE id = ?")
                      ->execute([$visit_duration, $last_visit['id']]);
            }
        }

        if ($should_track) {
            // Insert new visitor record
            $stmt = $conn->prepare("
                INSERT INTO visitor_stats
                (session_id, ip_address, country, referrer, user_agent, page_url, visit_duration)
                VALUES (?, ?, ?, ?, ?, ?, 0)
            ");
            $stmt->execute([$session_id, $ip_address, $country, $referrer, $user_agent, $page_url]);

            // Update page views
            $stmt = $conn->prepare("
                INSERT INTO page_views (page_url, view_count)
                VALUES (?, 1)
                ON DUPLICATE KEY UPDATE view_count = view_count + 1, last_viewed = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$page_url]);
        }

        // Store session start time for duration calculation
        if (!isset($_SESSION['visit_start'])) {
            $_SESSION['visit_start'] = $current_time;
        }

    } catch (PDOException $e) {
        // Log error but don't break the page
        error_log("Visitor tracking error: " . $e->getMessage());
    }
}

// Function to update visit duration on page unload (called via JavaScript)
function updateVisitDuration() {
    if (isset($_SESSION['visit_start'])) {
        global $conn;

        try {
            $duration = time() - $_SESSION['visit_start'];
            $session_id = session_id();

            // Update the most recent visit for this session
            $stmt = $conn->prepare("
                UPDATE visitor_stats
                SET visit_duration = ?
                WHERE session_id = ?
                ORDER BY visited_at DESC
                LIMIT 1
            ");
            $stmt->execute([$duration, $session_id]);

        } catch (PDOException $e) {
            error_log("Duration update error: " . $e->getMessage());
        }
    }
}

// Handle AJAX duration update
if (isset($_POST['action']) && $_POST['action'] === 'update_duration') {
    updateVisitDuration();
    exit;
}

// Function to track project views
function trackProjectView($conn, $project_id) {
    try {
        $session_id = session_id();
        $current_time = time();

        // Check if this project was viewed by this session in the last 30 minutes
        $stmt = $conn->prepare("
            SELECT id, last_viewed
            FROM project_views
            WHERE project_id = ?
            LIMIT 1
        ");
        $stmt->execute([$project_id]);
        $project_view = $stmt->fetch();

        $should_track = true;

        if ($project_view) {
            $last_view_time = strtotime($project_view['last_viewed']);
            $time_diff = $current_time - $last_view_time;

            // If viewed same project within 30 minutes, don't track again
            if ($time_diff < 1800) {
                $should_track = false;
            }
        }

        if ($should_track) {
            if ($project_view) {
                // Update existing record
                $stmt = $conn->prepare("
                    UPDATE project_views
                    SET view_count = view_count + 1, last_viewed = CURRENT_TIMESTAMP
                    WHERE project_id = ?
                ");
                $stmt->execute([$project_id]);
            } else {
                // Insert new record
                $stmt = $conn->prepare("
                    INSERT INTO project_views
                    (project_id, view_count, unique_views, last_viewed)
                    VALUES (?, 1, 1, CURRENT_TIMESTAMP)
                ");
                $stmt->execute([$project_id]);
            }

            // Update project view count in projects table
            $stmt = $conn->prepare("
                UPDATE projects
                SET view_count = view_count + 1, last_viewed = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmt->execute([$project_id]);
        }

    } catch (PDOException $e) {
        // Log error but don't break the page
        error_log("Project view tracking error: " . $e->getMessage());
    }
}
?>

<script>
// Update visit duration when user leaves the page
window.addEventListener('beforeunload', function() {
    // Send AJAX request to update duration
    navigator.sendBeacon('visitor-tracker.php', new URLSearchParams({
        'action': 'update_duration'
    }));
});

// Also update duration periodically (every 30 seconds)
setInterval(function() {
    fetch('visitor-tracker.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=update_duration',
        keepalive: true
    });
}, 30000);
</script>
