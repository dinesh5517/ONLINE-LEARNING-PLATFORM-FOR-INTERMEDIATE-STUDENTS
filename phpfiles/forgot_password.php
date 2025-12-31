<?php
// Include database connection
require_once 'db_connection.php';

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        // Generate a unique token
        $token = bin2hex(random_bytes(32));
        $user_id = $result->fetch_assoc()['id'];
        
        // Set token expiration (24 hours from now)
        $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Check if there's already a reset token for this user
        $check_token = $conn->prepare("SELECT id FROM password_resets WHERE user_id = ?");
        $check_token->bind_param("i", $user_id);
        $check_token->execute();
        $token_result = $check_token->get_result();
        
        if ($token_result->num_rows > 0) {
            // Update existing token
            $update = $conn->prepare("UPDATE password_resets SET token = ?, expiry = ? WHERE user_id = ?");
            $update->bind_param("ssi", $token, $expiry, $user_id);
            $update->execute();
            $update->close();
        } else {
            // Create password_resets table if it doesn't exist yet
            $conn->query("CREATE TABLE IF NOT EXISTS password_resets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                token VARCHAR(64) NOT NULL,
                expiry DATETIME NOT NULL,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )");
            
            // Insert new token
            $insert = $conn->prepare("INSERT INTO password_resets (user_id, token, expiry) VALUES (?, ?, ?)");
            $insert->bind_param("iss", $user_id, $token, $expiry);
            $insert->execute();
            $insert->close();
        }
        
        $check_token->close();
        
        // In a real application, you would send an email with the reset link
        // For demonstration purposes, we'll just return success
        echo json_encode([
            "success" => true, 
            "message" => "If your email exists in our database, you will receive a password reset link."
        ]);
    } else {
        // Don't reveal if email exists or not for security
        echo json_encode([
            "success" => true, 
            "message" => "If your email exists in our database, you will receive a password reset link."
        ]);
    }
    
    $stmt->close();
}

$conn->close();
?>