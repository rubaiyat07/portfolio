<?php
require_once '../config.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Get statistics
require_once 'stats-helper.php';

$skills_count = getProjectStats($conn)['total'];
$projects_count = getProjectStats($conn)['total'];
$messages_count = getMessageStats($conn)['total'];
$unread_messages_count = getMessageStats($conn)['unread'];

// Get visitor statistics
$visitor_stats = getVisitorStats($conn);
$total_visitors = $visitor_stats['total'];
$unique_visitors_monthly = $visitor_stats['unique_monthly'];
$daily_visitors = $visitor_stats['daily'];
$avg_duration = formatDuration(getAverageVisitDuration($conn));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Portfolio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin-styles.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php
        $current_page = basename(__FILE__);
        include 'sidebar.php';
        ?>

        <main class="main-content">
            <div class="page-header">
                <h1>Dashboard</h1>
                <p>Overview of your portfolio</p>
            </div>

            <div class="stats-grid">
                <!-- Visitor Statistics -->
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
                    <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $avg_duration; ?></h3>
                        <p>Avg Visit Duration</p>
                    </div>
                </div>

                <!-- Portfolio Statistics -->
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <i class="fas fa-code"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $skills_count; ?></h3>
                        <p>Total Skills</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $projects_count; ?></h3>
                        <p>Total Projects</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $messages_count; ?> (<?php echo $unread_messages_count; ?> unread)</h3>
                        <p>Contact Messages</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Live</h3>
                        <p>Site Status</p>
                    </div>
                </div>
            </div>

            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <a href="manage-skills.php?action=add" class="action-btn">
                        <i class="fas fa-plus-circle"></i> Add New Skill
                    </a>
                    <a href="manage-projects.php?action=add" class="action-btn">
                        <i class="fas fa-plus-circle"></i> Add New Project
                    </a>
                    <a href="manage-messages.php" class="action-btn">
                        <i class="fas fa-envelope"></i> View Messages
                    </a>
                    <a href="../index.php" target="_blank" class="action-btn">
                        <i class="fas fa-external-link-alt"></i> Preview Site
                    </a>
                </div>
            </div>

            <div class="recent-section">
                <h2>Recent Updates</h2>
                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <p>Welcome to your portfolio admin panel. From here you can manage your skills, projects, and view your site statistics.</p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
