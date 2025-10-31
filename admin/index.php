<?php
require_once '../config.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Portfolio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin-styles.css">
    <style>
        .iframe-container {
            margin-left: 280px;
            height: 100vh;
        }
        .iframe-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        @media (max-width: 768px) {
            .iframe-container {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <?php
    $current_page = 'dashboard.php'; // Default for iframe mode
    $iframe_mode = true;
    include 'sidebar.php';
    ?>

    <div class="iframe-container">
        <iframe name="admin-content" src="dashboard.php?no_sidebar=1"></iframe>
    </div>

    <script>
        // Handle sidebar navigation clicks to update active state
        document.querySelectorAll('.sidebar-nav a[target="admin-content"]').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelectorAll('.sidebar-nav a').forEach(a => a.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>
