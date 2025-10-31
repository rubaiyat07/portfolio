<?php
require_once '../config.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$message = '';
$action = $_GET['action'] ?? 'list';
$message_id = $_GET['id'] ?? null;

// Handle individual reply
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply_message'])) {
    $reply_to = $_POST['reply_to'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $reply_message = $_POST['reply_message'] ?? '';

    if(!empty($reply_to) && !empty($subject) && !empty($reply_message)) {
        $headers = "From: hello@rubaiyatdev.com\r\n" .
                   "Reply-To: hello@rubaiyatdev.com\r\n" .
                   "X-Mailer: PHP/" . phpversion();

        if(@mail($reply_to, $subject, $reply_message, $headers)) {
            // Mark message as replied
            $stmt = $conn->prepare("UPDATE contact_messages SET replied = 1, replied_at = NOW() WHERE id = ?");
            $stmt->execute([$message_id]);
            $message = 'Reply sent successfully!';
            $action = 'list';
        } else {
            $message = 'Failed to send reply. Please try again.';
        }
    } else {
        $message = 'All fields are required.';
    }
}

// Handle bulk reply
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bulk_reply'])) {
    $selected_messages = $_POST['selected_messages'] ?? [];
    $subject = $_POST['bulk_subject'] ?? '';
    $reply_message = $_POST['bulk_reply_message'] ?? '';

    if(!empty($selected_messages) && !empty($subject) && !empty($reply_message)) {
        $success_count = 0;
        $headers = "From: hello@rubaiyatdev.com\r\n" .
                   "Reply-To: hello@rubaiyatdev.com\r\n" .
                   "X-Mailer: PHP/" . phpversion();

        foreach($selected_messages as $msg_id) {
            $stmt = $conn->prepare("SELECT email FROM contact_messages WHERE id = ?");
            $stmt->execute([$msg_id]);
            $msg = $stmt->fetch();

            if($msg && @mail($msg['email'], $subject, $reply_message, $headers)) {
                // Mark message as replied
                $stmt = $conn->prepare("UPDATE contact_messages SET replied = 1, replied_at = NOW() WHERE id = ?");
                $stmt->execute([$msg_id]);
                $success_count++;
            }
        }

        $message = "Bulk reply sent to $success_count message(s).";
        $action = 'list';
    } else {
        $message = 'Please select messages and fill all fields.';
    }
}

// Handle mark as read/unread
if(isset($_GET['mark_read'])) {
    $stmt = $conn->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
    $stmt->execute([$_GET['mark_read']]);
    header('Location: manage-messages.php');
    exit();
}

if(isset($_GET['mark_unread'])) {
    $stmt = $conn->prepare("UPDATE contact_messages SET is_read = 0 WHERE id = ?");
    $stmt->execute([$_GET['mark_unread']]);
    header('Location: manage-messages.php');
    exit();
}

// Add replied and is_read columns if they don't exist
try {
    $conn->exec("ALTER TABLE contact_messages ADD COLUMN IF NOT EXISTS replied TINYINT(1) DEFAULT 0");
    $conn->exec("ALTER TABLE contact_messages ADD COLUMN IF NOT EXISTS replied_at TIMESTAMP NULL");
    $conn->exec("ALTER TABLE contact_messages ADD COLUMN IF NOT EXISTS is_read TINYINT(1) DEFAULT 0");
} catch(PDOException $e) {
    // Columns might already exist
}

// Fetch all messages
$stmt = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll();

// Fetch single message for replying
$reply_message = null;
if($action == 'reply' && $message_id) {
    $stmt = $conn->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $stmt->execute([$message_id]);
    $reply_message = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Messages - Admin Panel</title>
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
                <h1>Manage Messages</h1>
                <p>View and reply to contact form messages</p>
            </div>

            <?php if($message): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <?php if($action == 'reply' && $reply_message): ?>
            <div class="form-container">
                <h2>Reply to Message</h2>
                <div class="info-box" style="margin-bottom: 20px;">
                    <div>
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <strong>From:</strong> <?php echo htmlspecialchars($reply_message['name']); ?> (<?php echo htmlspecialchars($reply_message['email']); ?>)<br>
                        <strong>Received:</strong> <?php echo date('M j, Y g:i A', strtotime($reply_message['created_at'])); ?><br>
                        <strong>Original Message:</strong><br>
                        <div style="background-color: #121212; padding: 15px; margin-top: 10px; border-radius: 8px; border: 1px solid #2A2A2A;">
                            <?php echo nl2br(htmlspecialchars($reply_message['message'])); ?>
                        </div>
                    </div>
                </div>

                <form method="POST">
                    <input type="hidden" name="reply_to" value="<?php echo htmlspecialchars($reply_message['email']); ?>">
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" value="Re: Your message to Rubaiyat Dev" required>
                    </div>

                    <div class="form-group">
                        <label for="reply_message">Reply Message</label>
                        <textarea id="reply_message" name="reply_message" rows="8" required>Dear <?php echo htmlspecialchars($reply_message['name']); ?>,

Thank you for your message. I appreciate you reaching out.

[Your reply here]

Best regards,
Rubaiyat Dev</textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Send Reply
                        </button>
                        <a href="manage-messages.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
            <?php elseif($action == 'bulk_reply'): ?>
            <div class="form-container">
                <h2>Bulk Reply</h2>
                <form method="POST">
                    <?php 
                    $selected_ids = explode(',', $_POST['selected_messages'][0] ?? '');
                    foreach($selected_ids as $id): 
                        if(!empty($id)):
                    ?>
                    <input type="hidden" name="selected_messages[]" value="<?php echo htmlspecialchars($id); ?>">
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                    <div class="form-group">
                        <label for="bulk_subject">Subject</label>
                        <input type="text" id="bulk_subject" name="bulk_subject" value="Re: Your message to Rubaiyat Dev" required>
                    </div>

                    <div class="form-group">
                        <label for="bulk_reply_message">Reply Message</label>
                        <textarea id="bulk_reply_message" name="bulk_reply_message" rows="8" required>Dear Visitor,

Thank you for your message. I appreciate you reaching out.

[Your reply here]

Best regards,
Rubaiyat Dev</textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="bulk_reply" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Send Bulk Reply
                        </button>
                        <a href="manage-messages.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
            <?php else: ?>
            <div style="margin-bottom: 20px;">
                <form method="POST" id="bulk-form" action="?action=bulk_reply">
                    <button type="submit" class="btn btn-primary" onclick="return checkSelected()">
                        <i class="fas fa-reply-all"></i> Reply to Selected
                    </button>
                    <input type="hidden" name="selected_messages[]" id="selected-messages">
                </form>
            </div>

            <div class="data-table">
                <h2>All Messages (<?php echo count($messages); ?>)</h2>
                <table>
                    <thead>
                        <tr>
                            <th class="checkbox-column"><input type="checkbox" id="select-all"></th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($messages as $msg): ?>
                        <tr class="message-row <?php echo $msg['is_read'] ? '' : 'unread'; ?> <?php echo $msg['replied'] ? 'replied' : ''; ?>">
                            <td><input type="checkbox" class="message-checkbox" value="<?php echo $msg['id']; ?>"></td>
                            <td><strong><?php echo htmlspecialchars($msg['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($msg['email']); ?></td>
                            <td><?php echo htmlspecialchars(substr($msg['message'], 0, 100)); ?>...</td>
                            <td><?php echo date('M j, Y', strtotime($msg['created_at'])); ?></td>
                            <td>
                                <?php if($msg['replied']): ?>
                                    <span style="color: #81C784;"><i class="fas fa-check"></i> Replied</span>
                                <?php elseif($msg['is_read']): ?>
                                    <span style="color: #64B5F6;"><i class="fas fa-eye"></i> Read</span>
                                <?php else: ?>
                                    <span style="color: #FFA726;"><i class="fas fa-envelope"></i> Unread</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?action=reply&id=<?php echo $msg['id']; ?>" class="btn btn-edit">
                                    <i class="fas fa-reply"></i>
                                </a>
                                <?php if($msg['is_read']): ?>
                                    <a href="?mark_unread=<?php echo $msg['id']; ?>" class="btn btn-secondary" style="padding: 8px 15px;">
                                        <i class="fas fa-eye-slash"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="?mark_read=<?php echo $msg['id']; ?>" class="btn btn-secondary" style="padding: 8px 15px;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // Only run scripts if elements exist (i.e., on the list page)
        const selectAll = document.getElementById('select-all');
        if (selectAll) {
            // Select all checkbox functionality
            selectAll.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.message-checkbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
        }

        // Update selected messages for bulk actions
        function updateSelectedMessages() {
            const selected = Array.from(document.querySelectorAll('.message-checkbox:checked')).map(cb => cb.value);
            const selectedMessagesInput = document.getElementById('selected-messages');
            if (selectedMessagesInput) {
                selectedMessagesInput.value = selected.join(',');
            }
        }

        document.querySelectorAll('.message-checkbox').forEach(cb => {
            cb.addEventListener('change', updateSelectedMessages);
        });

        function checkSelected() {
            const selected = document.querySelectorAll('.message-checkbox:checked');
            if(selected.length === 0) {
                alert('Please select at least one message to reply to.');
                return false;
            }
            updateSelectedMessages();
            return true;
        }
    </script>
</body>
</html>