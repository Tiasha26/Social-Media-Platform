<?php
/**
 * Private Messaging System
 * Allows users to send and receive private messages
 */

require_once 'includes/config.php';
requireLogin();

$pdo = getDBConnection();
$errors = [];
$success = '';
$currentUserId = getCurrentUserId();

// Mark received messages as read
$stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ?");
$stmt->execute([$currentUserId]);

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $recipientUsername = trim($_POST['recipient'] ?? '');
    $messageContent = trim($_POST['message'] ?? '');
    
    if (empty($recipientUsername)) {
        $errors[] = "Please enter a recipient username";
    }
    
    if (empty($messageContent)) {
        $errors[] = "Message cannot be empty";
    }
    
    if (empty($errors)) {
        // Find recipient by username
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$recipientUsername]);
        
        if ($stmt->rowCount() === 0) {
            $errors[] = "User not found";
        } else {
            $recipient = $stmt->fetch();
            $recipientId = $recipient['id'];
            
            if ($recipientId == $currentUserId) {
                $errors[] = "You cannot send a message to yourself";
            } else {
                $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
                if ($stmt->execute([$currentUserId, $recipientId, $messageContent])) {
                    $success = "Message sent successfully!";
                    $_POST = []; // Clear form
                } else {
                    $errors[] = "Failed to send message";
                }
            }
        }
    }
}

// Fetch all messages (sent and received)
$stmt = $pdo->prepare("
    SELECT m.*, 
           sender.username as sender_username, 
           receiver.username as receiver_username
    FROM messages m
    JOIN users sender ON m.sender_id = sender.id
    JOIN users receiver ON m.receiver_id = receiver.id
    WHERE m.sender_id = ? OR m.receiver_id = ?
    ORDER BY m.created_at DESC
");
$stmt->execute([$currentUserId, $currentUserId]);
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Social Media Platform</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <div class="messages-container">
            <h1 style="text-align: center; color: var(--primary-color); margin-bottom: 30px;">
                ✉️ Send a Message
            </h1>
            
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo sanitizeOutput($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message"><?php echo sanitizeOutput($success); ?></div>
            <?php endif; ?>
            
            <div class="message-form">
                <form method="POST" action="" id="messageForm">
                    <div class="form-group">
                        <input type="text" name="recipient" id="recipient" 
                               class="form-control" 
                               placeholder="Username of the recipient"
                               value="<?php echo sanitizeOutput($_POST['recipient'] ?? ''); ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <textarea name="message" id="messageContent" 
                                  class="form-control" 
                                  placeholder="Type your message..."
                                  rows="4" 
                                  required><?php echo sanitizeOutput($_POST['message'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" name="send_message" class="btn btn-primary" 
                            style="width: 100%;">Send</button>
                </form>
            </div>
            
            <h2 style="margin-top: 40px; margin-bottom: 20px; color: var(--primary-color);">
                Your Messages:
            </h2>
            
            <?php if (empty($messages)): ?>
                <p style="text-align: center; color: var(--text-light); padding: 20px;">
                    No messages yet. Start a conversation!
                </p>
            <?php else: ?>
                <div class="message-list">
                    <?php foreach ($messages as $msg): ?>
                        <div class="message-card <?php echo ($msg['sender_id'] == $currentUserId) ? 'sent' : ''; ?>">
                            <div class="message-header">
                                <div>
                                    <?php if ($msg['sender_id'] == $currentUserId): ?>
                                        <span class="message-from"><?php echo sanitizeOutput($msg['sender_username']); ?></span>
                                        <br>
                                        <span class="message-to">To: <?php echo sanitizeOutput($msg['receiver_username']); ?></span>
                                    <?php else: ?>
                                        <span class="message-from">
                                            From: <?php echo sanitizeOutput($msg['sender_username']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="message-body">
                                <?php echo nl2br(sanitizeOutput($msg['message'])); ?>
                            </div>
                            <div class="message-time">
                                <?php echo $msg['created_at']; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="js/messages.js"></script>
    <script src="js/navbar.js"></script>
</body>
</html>