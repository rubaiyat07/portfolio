<?php
require_once '../config.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$message = '';
$action = $_GET['action'] ?? 'list';
$skill_id = $_GET['id'] ?? null;

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $icon_class = $_POST['icon_class'] ?? '';
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $display_order = $_POST['display_order'] ?? 0;
    
    if($action == 'add') {
        $stmt = $conn->prepare("INSERT INTO skills (icon_class, title, description, display_order) VALUES (?, ?, ?, ?)");
        if($stmt->execute([$icon_class, $title, $description, $display_order])) {
            $message = 'Skill added successfully!';
            $action = 'list';
        }
    } elseif($action == 'edit' && $skill_id) {
        $stmt = $conn->prepare("UPDATE skills SET icon_class = ?, title = ?, description = ?, display_order = ? WHERE id = ?");
        if($stmt->execute([$icon_class, $title, $description, $display_order, $skill_id])) {
            $message = 'Skill updated successfully!';
            $action = 'list';
        }
    }
}

// Handle delete
if(isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM skills WHERE id = ?");
    if($stmt->execute([$_GET['delete']])) {
        $message = 'Skill deleted successfully!';
    }
}

// Fetch all skills
$stmt = $conn->query("SELECT * FROM skills ORDER BY display_order ASC");
$skills = $stmt->fetchAll();

// Fetch single skill for editing
$edit_skill = null;
if($action == 'edit' && $skill_id) {
    $stmt = $conn->prepare("SELECT * FROM skills WHERE id = ?");
    $stmt->execute([$skill_id]);
    $edit_skill = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Skills - Admin Panel</title>
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
                <h1>Manage Skills</h1>
                <p>Add, edit, or delete your skills</p>
            </div>
            
            <?php if($message): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <?php if($action == 'add' || $action == 'edit'): ?>
            <div class="form-container">
                <h2><?php echo $action == 'add' ? 'Add New Skill' : 'Edit Skill'; ?></h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="icon_class">Icon Class (FontAwesome)</label>
                        <input type="text" id="icon_class" name="icon_class" 
                               value="<?php echo htmlspecialchars($edit_skill['icon_class'] ?? ''); ?>" 
                               placeholder="e.g., fab fa-html5" required>
                        <small>Visit <a href="https://fontawesome.com/icons" target="_blank">FontAwesome</a> to find icons</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="title">Skill Title</label>
                        <input type="text" id="title" name="title" 
                               value="<?php echo htmlspecialchars($edit_skill['title'] ?? ''); ?>" 
                               placeholder="e.g., HTML5" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="3" required><?php echo htmlspecialchars($edit_skill['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="display_order">Display Order</label>
                        <input type="number" id="display_order" name="display_order" 
                               value="<?php echo htmlspecialchars($edit_skill['display_order'] ?? '0'); ?>" 
                               placeholder="0">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Skill
                        </button>
                        <a href="manage-skills.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
            <?php else: ?>
            <div style="margin-bottom: 20px;">
                <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Add New Skill
                </a>
            </div>
            
            <div class="data-table">
                <h2>All Skills (<?php echo count($skills); ?>)</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Icon</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Order</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($skills as $skill): ?>
                        <tr>
                            <td><i class="<?php echo htmlspecialchars($skill['icon_class']); ?>"></i></td>
                            <td><strong><?php echo htmlspecialchars($skill['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($skill['description']); ?></td>
                            <td><?php echo htmlspecialchars($skill['display_order']); ?></td>
                            <td style="white-space: nowrap;">
                                <a href="?action=edit&id=<?php echo $skill['id']; ?>" class="btn btn-edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete=<?php echo $skill['id']; ?>" 
                                   class="btn btn-delete" 
                                   onclick="return confirm('Are you sure you want to delete this skill?')">
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