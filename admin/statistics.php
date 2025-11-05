<?php
require_once '../config.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Helper function to safely get count
function getCount($conn, $query) {
    try {
        $stmt = $conn->query($query);
        return $stmt->fetch()['count'] ?? 0;
    } catch (PDOException $e) {
        return 0;
    }
}

// 🧭 Visitor & Traffic Stats
$total_visitors = getCount($conn, "SELECT COUNT(*) as count FROM visitor_stats");
$unique_visitors_monthly = getCount($conn, "SELECT COUNT(DISTINCT session_id) as count FROM visitor_stats WHERE visited_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$daily_visitors = getCount($conn, "SELECT COUNT(DISTINCT session_id) as count FROM visitor_stats WHERE DATE(visited_at) = CURDATE()");
$total_page_views = getCount($conn, "SELECT SUM(view_count) as count FROM page_views");

// Average visit duration
$stmt = $conn->query("SELECT AVG(visit_duration) as avg_duration FROM visitor_stats WHERE visit_duration > 0");
$avg_duration = round($stmt->fetch()['avg_duration'] ?? 0);

// Top 5 countries
$stmt = $conn->query("
    SELECT country, COUNT(*) as visits 
    FROM visitor_stats 
    WHERE country != 'Unknown' 
    GROUP BY country 
    ORDER BY visits DESC 
    LIMIT 5
");
$top_countries = $stmt->fetchAll();

// Traffic sources
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
$traffic_sources = $stmt->fetchAll();

// 💼 Portfolio Content Stats
$total_projects = getCount($conn, "SELECT COUNT(*) as count FROM projects");
$published_projects = getCount($conn, "SELECT COUNT(*) as count FROM projects WHERE status = 'published'");
$draft_projects = getCount($conn, "SELECT COUNT(*) as count FROM projects WHERE status = 'draft'");

// Projects by category
$stmt = $conn->query("SELECT category, COUNT(*) as count FROM projects GROUP BY category");
$projects_by_category = $stmt->fetchAll();

// Top 3 viewed projects
$stmt = $conn->query("
    SELECT p.id, p.title, COALESCE(pv.view_count, 0) as views
    FROM projects p
    LEFT JOIN project_views pv ON p.id = pv.project_id
    ORDER BY views DESC
    LIMIT 3
");
$top_projects = $stmt->fetchAll();

// 💬 User Interaction Stats
$total_messages = getCount($conn, "SELECT COUNT(*) as count FROM contact_messages");
$unread_messages = getCount($conn, "SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0");
$replied_messages = getCount($conn, "SELECT COUNT(*) as count FROM contact_messages WHERE replied = 1");

// Conversion rate
$conversion_rate = $unique_visitors_monthly > 0 ? round(($total_messages / $unique_visitors_monthly) * 100, 2) : 0;

// ⚙️ System/Performance Stats
$stmt = $conn->query("SELECT stat_value FROM system_stats WHERE stat_key = 'site_launch_date'");
$launch_date = $stmt->fetch()['stat_value'] ?? '2025-10-30';
$days_since_launch = round((time() - strtotime($launch_date)) / 86400);

// Database size
$stmt = $conn->query("
    SELECT 
        SUM(data_length + index_length) / 1024 / 1024 AS size_mb
    FROM information_schema.TABLES
    WHERE table_schema = DATABASE()
");
$db_size = round($stmt->fetch()['size_mb'] ?? 0, 2);

// 🧠 Bonus Stats
$returning_visitors = getCount($conn, "
    SELECT COUNT(DISTINCT session_id) as count 
    FROM visitor_stats 
    WHERE session_id IN (
        SELECT session_id 
        FROM visitor_stats 
        GROUP BY session_id 
        HAVING COUNT(*) > 1
    )
");
$returning_ratio = $total_visitors > 0 ? round(($returning_visitors / $total_visitors) * 100, 2) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin-styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            background-color: #1E1E1E;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            border: 1px solid #2A2A2A;
            margin-bottom: 30px;
        }
        .chart-container h3 {
            color: #E0E0E0;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .chart-wrapper {
            position: relative;
            height: 300px;
        }
        .stats-section {
            margin-bottom: 40px;
        }
        .stats-section h2 {
            color: #BB86FC;
            margin-bottom: 20px;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .list-card {
            background-color: #1E1E1E;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #2A2A2A;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .list-card span {
            color: #E0E0E0;
        }
        .list-card .badge {
            background-color: #BB86FC;
            color: #121212;
            padding: 5px 15px;
            border-radius: 15px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php
        $current_page = basename(__FILE__);
        $show_welcome = !isset($_GET['no_sidebar']);
        include 'sidebar.php';
        ?>

        <main class="main-content" <?php if(isset($_GET['no_sidebar'])) echo 'style="margin-left: 0;"'; ?>>
            <div class="page-header">
                <h1><i class="fas fa-chart-line"></i> Statistics Dashboard</h1>
                <p>Comprehensive analytics and insights</p>
            </div>

            <!-- 🧭 Visitor & Traffic Stats -->
            <div class="stats-section">
                <h2><i class="fas fa-users"></i> Visitor & Traffic Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($total_visitors); ?></h3>
                            <p>Total Visitors</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($unique_visitors_monthly); ?></h3>
                            <p>Unique Visitors (30 days)</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($daily_visitors); ?></h3>
                            <p>Today's Visitors</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($total_page_views); ?></h3>
                            <p>Total Page Views</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo gmdate("i:s", $avg_duration); ?></h3>
                            <p>Avg Visit Duration</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                            <i class="fas fa-redo"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $returning_ratio; ?>%</h3>
                            <p>Returning Visitors</p>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px; margin-top: 30px;">
                    <div class="chart-container">
                        <h3>Top 5 Countries</h3>
                        <?php if(empty($top_countries)): ?>
                            <p style="color: #999;">No visitor data available yet</p>
                        <?php else: ?>
                            <?php foreach($top_countries as $country): ?>
                                <div class="list-card">
                                    <span><i class="fas fa-globe"></i> <?php echo htmlspecialchars($country['country']); ?></span>
                                    <span class="badge"><?php echo number_format($country['visits']); ?> visits</span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="chart-container">
                        <h3>Traffic Sources</h3>
                        <div class="chart-wrapper">
                            <canvas id="trafficSourceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 💼 Portfolio Content Stats -->
            <div class="stats-section">
                <h2><i class="fas fa-briefcase"></i> Portfolio Content Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_projects; ?></h3>
                            <p>Total Projects</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $published_projects; ?></h3>
                            <p>Published Projects</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $draft_projects; ?></h3>
                            <p>Draft Projects</p>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px; margin-top: 30px;">
                    <div class="chart-container">
                        <h3>Top 3 Most Viewed Projects</h3>
                        <?php if(empty($top_projects)): ?>
                            <p style="color: #999;">No project data available yet</p>
                        <?php else: ?>
                            <?php foreach($top_projects as $project): ?>
                                <div class="list-card">
                                    <span><i class="fas fa-star"></i> <?php echo htmlspecialchars($project['title']); ?></span>
                                    <span class="badge"><?php echo number_format($project['views']); ?> views</span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="chart-container">
                        <h3>Projects by Category</h3>
                        <div class="chart-wrapper">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 💬 User Interaction Stats -->
            <div class="stats-section">
                <h2><i class="fas fa-comments"></i> User Interaction Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_messages; ?></h3>
                            <p>Total Messages</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                            <i class="fas fa-envelope-open"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $unread_messages; ?></h3>
                            <p>Unread Messages</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                            <i class="fas fa-reply"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $replied_messages; ?></h3>
                            <p>Replies Sent</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $conversion_rate; ?>%</h3>
                            <p>Conversion Rate</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ⚙️ System Stats -->
            <div class="stats-section">
                <h2><i class="fas fa-server"></i> System Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $days_since_launch; ?> days</h3>
                            <p>Since Launch</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $db_size; ?> MB</h3>
                            <p>Database Size</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Traffic Sources Chart
        const trafficData = <?php echo json_encode(array_column($traffic_sources, 'count')); ?>;
        const trafficLabels = <?php echo json_encode(array_column($traffic_sources, 'source')); ?>;
        
        if (trafficData.length > 0) {
            new Chart(document.getElementById('trafficSourceChart'), {
                type: 'doughnut',
                data: {
                    labels: trafficLabels,
                    datasets: [{
                        data: trafficData,
                        backgroundColor: [
                            'rgba(187, 134, 252, 0.8)',
                            'rgba(100, 181, 246, 0.8)',
                            'rgba(129, 199, 132, 0.8)',
                            'rgba(255, 167, 38, 0.8)'
                        ],
                        borderColor: '#1E1E1E',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: '#E0E0E0', font: { size: 12 } }
                        }
                    }
                }
            });
        }

        // Category Chart
        const categoryData = <?php echo json_encode(array_column($projects_by_category, 'count')); ?>;
        const categoryLabels = <?php echo json_encode(array_column($projects_by_category, 'category')); ?>;
        
        if (categoryData.length > 0) {
            new Chart(document.getElementById('categoryChart'), {
                type: 'bar',
                data: {
                    labels: categoryLabels,
                    datasets: [{
                        label: 'Projects',
                        data: categoryData,
                        backgroundColor: 'rgba(187, 134, 252, 0.8)',
                        borderColor: '#BB86FC',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: '#E0E0E0', stepSize: 1 },
                            grid: { color: 'rgba(255, 255, 255, 0.1)' }
                        },
                        x: {
                            ticks: { color: '#E0E0E0' },
                            grid: { display: false }
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>