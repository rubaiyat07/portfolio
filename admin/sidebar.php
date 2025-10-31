<?php
// Parameters: $current_page, $show_welcome, $iframe_mode
$current_page = $current_page ?? '';
$show_welcome = $show_welcome ?? true;
$iframe_mode = $iframe_mode ?? false;

// Determine active link
$active_dashboard = (!$iframe_mode && $current_page == 'dashboard.php') ? 'active' : '';
$active_skills = (!$iframe_mode && $current_page == 'manage-skills.php') ? 'active' : '';
$active_projects = (!$iframe_mode && $current_page == 'manage-projects.php') ? 'active' : '';
$active_messages = (!$iframe_mode && $current_page == 'manage-messages.php') ? 'active' : '';

// Link modifiers for iframe mode
$link_suffix = $iframe_mode ? '?no_sidebar=1' : '';
$target_attr = $iframe_mode ? ' target="admin-content"' : '';
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <h2><i class="fas fa-cog"></i> Admin Panel</h2>
        <?php if ($show_welcome): ?>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></p>
        <?php endif; ?>
    </div>
    <nav class="sidebar-nav">
        <a href="dashboard.php<?php echo $link_suffix; ?>"<?php echo $target_attr; ?> class="<?php echo $active_dashboard; ?>"><i class="fas fa-home"></i> Dashboard</a>
        <a href="manage-skills.php<?php echo $link_suffix; ?>"<?php echo $target_attr; ?> class="<?php echo $active_skills; ?>"><i class="fas fa-code"></i> Manage Skills</a>
        <a href="manage-projects.php<?php echo $link_suffix; ?>"<?php echo $target_attr; ?> class="<?php echo $active_projects; ?>"><i class="fas fa-project-diagram"></i> Manage Projects</a>
        <a href="manage-messages.php<?php echo $link_suffix; ?>"<?php echo $target_attr; ?> class="<?php echo $active_messages; ?>"><i class="fas fa-envelope"></i> Manage Messages</a>
        <a href="../index.php" target="_blank"><i class="fas fa-eye"></i> View Site</a>
        <?php if (!$iframe_mode): ?>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        <?php endif; ?>
    </nav>
</aside>
