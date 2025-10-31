<?php
require_once 'config.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';
    
    // Validate inputs
    if(empty($name) || empty($email) || empty($message)) {
        header('Location: index.php#contact?error=empty');
        exit();
    }
    
    // Create contacts table if needed
    try {
        $conn->exec("CREATE TABLE IF NOT EXISTS contact_messages (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    } catch(PDOException $e) {
        // Table might already exist
    }
    
    // Save to database
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
    
    if($stmt->execute([$name, $email, $message])) {
        // Optional: Send email notification
        $to = "hello@rubaiyatdev.com";
        $subject = "New Contact Form Submission from " . $name;
        $email_message = "Name: $name\nEmail: $email\n\nMessage:\n$message";
        $headers = "From: $email\r\n" .
                   "Reply-To: $email\r\n" .
                   "X-Mailer: PHP/" . phpversion();
        
        @mail($to, $subject, $email_message, $headers);
        
        header('Location: index.php#contact?success=1');
    } else {
        header('Location: index.php#contact?error=failed');
    }
} else {
    header('Location: index.php');
}
exit();
?>