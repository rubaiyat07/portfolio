<?php
require_once '../config.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$message = '';
$action = $_GET['action'] ?? 'list';
$project_id = $_GET['id'] ?? null;

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $image_url = $_POST['image_url'] ?? '';
    $code_url = $_POST['code_url'] ?? '';
    $demo_url = $_POST['demo_url'] ?? '';
    $display_order = $_POST['display_order'] ?? 0;
    $technologies = $_POST['technologies'] ?? '';
    
    if($action == 'add') {
        $stmt = $conn->prepare("INSERT INTO projects (title, description, image_url, code_url, demo_url, display_order) VALUES (?, ?, ?, ?, ?, ?)");
        if($stmt->execute([$title, $description, $image_url, $code_url, $demo_url, $display_order])) {
            $project_id = $conn->lastInsertId();
            
            // Add technologies
            $tech_array = array_filter(array_map('trim', explode(',', $technologies)));
            $stmt = $conn->prepare("INSERT INTO project_technologies (project_id, technology) VALUES (?, ?)");
            foreach($tech_array as $tech) {
                $stmt->execute([$project_id, $tech]);
            }
            
            $message = 'Project added successfully!';
            $action = 'list';
        }
    } elseif($action == 'edit' && $project_id) {
        $stmt = $conn->prepare("UPDATE projects SET title = ?, description = ?, image_url = ?, code_url = ?, demo_url = ?, display_order = ? WHERE id = ?");
        if($stmt->execute([$title, $description, $image_url, $code_url, $demo_url, $display_order, $project_id])) {
            // Delete old technologies
            $stmt = $conn->prepare("DELETE FROM project_technologies WHERE project_id = ?");
            $stmt->execute([$project_id]);
            
            // Add new technologies
            $tech_array = array_filter(array_map('trim', explode(',', $technologies)));
            $stmt = $conn->prepare("INSERT INTO project_technologies (project_id, technology) VALUES (?, ?)");
            foreach($tech_array as $tech) {
                $stmt->execute([$project_id, $tech]);
            }
            
            $message = 'Project updated successfully!';
            $action = 'list';
        }
    }
}

// Handle delete
if(isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
    if($stmt->execute([$_GET['delete']])) {
        $message = 'Project deleted successfully!';
    }
}

// Fetch all projects
$stmt = $conn->query("SELECT * FROM projects ORDER BY display_order ASC");
$projects = $stmt->fetchAll();

// Fetch single project for editing
$edit_project = null;
$edit_technologies = '';
if($action == 'edit' && $project_id) {
    $stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);
    $edit_project = $stmt->fetch();
    
    // Get technologies
    $stmt = $conn->prepare("SELECT technology FROM project_technologies WHERE project_id = ?");
    $stmt->execute([$project_id]);
    $techs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $edit_technologies = implode(', ', $techs);
}

// Function to get project technologies
function getProjectTech($pid) {
    global $conn;
    $stmt = $conn->prepare("SELECT technology FROM project_technologies WHERE project_id = ?");
    $stmt->execute([$pid]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Projects - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin-styles.css">
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
                <h1>Manage Projects</h1>
                <p>Add, edit, or delete your projects</p>
            </div>
            
            <?php if($message): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <?php if($action == 'add' || $action == 'edit'): ?>
            <div class="form-container">
                <h2><?php echo $action == 'add' ? 'Add New Project' : 'Edit Project'; ?></h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="title">Project Title</label>
                        <input type="text" id="title" name="title" 
                               value="<?php echo htmlspecialchars($edit_project['title'] ?? ''); ?>" 
                               placeholder="e.g., E-Commerce Website" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($edit_project['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="image_url">Image URL</label>
                        <input type="text" id="image_url" name="image_url" 
                               value="<?php echo htmlspecialchars($edit_project['image_url'] ?? ''); ?>" 
                               placeholder="images/project1.jpg">
                    </div>
                    
                    <div class="form-group">
                        <label for="technologies">Technologies (comma-separated)</label>
                        <input type="text" id="technologies" name="technologies" 
                               value="<?php echo htmlspecialchars($edit_technologies); ?>" 
                               placeholder="React, Node.js, MongoDB">
                    </div>
                    
                    <div class="form-group">
                        <label for="code_url">GitHub/Code URL</label>
                        <input type="url" id="code_url" name="code_url" 
                               value="<?php echo htmlspecialchars($edit_project['code_url'] ?? ''); ?>" 
                               placeholder="https://github.com/username/project">
                    </div>
                    
                    <div class="form-group">
                        <label for="demo_url">Live Demo URL</label>
                        <input type="url" id="demo_url" name="demo_url" 
                               value="<?php echo htmlspecialchars($edit_project['demo_url'] ?? ''); ?>" 
                               placeholder="https://demo.example.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="display_order">Display Order</label>
                        <input type="number" id="display_order" name="display_order" 
                               value="<?php echo htmlspecialchars($edit_project['display_order'] ?? '0'); ?>" 
                               placeholder="0">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Project
                        </button>
                        <a href="manage-projects.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
            <?php else: ?>
            <div style="margin-bottom: 20px;">
                <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Add New Project
                </a>
            </div>
            
            <div class="data-table">
                <h2>All Projects (<?php echo count($projects); ?>)</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Technologies</th>
                            <th>Order</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($projects as $project): 
                            $technologies = getProjectTech($project['id']);
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($project['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars(substr($project['description'], 0, 100)); ?>...</td>
                            <td><?php echo htmlspecialchars(implode(', ', $technologies)); ?></td>
                            <td><?php echo htmlspecialchars($project['display_order']); ?></td>
                            <td style="white-space: nowrap;">
                                <a href="?action=edit&id=<?php echo $project['id']; ?>" class="btn btn-edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete=<?php echo $project['id']; ?>" 
                                   class="btn btn-delete" 
                                   onclick="return confirm('Are you sure you want to delete this project?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>