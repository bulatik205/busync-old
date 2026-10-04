<?php
/* 
*  I don't know how to make a 100 per cent secure system. 
*  I took a consultation with AI, but it didn't help. 
*  If anyone knows how to make it secure, please write to me: 
*  telegram (@bulatik205), 
*  email (bulatmullagal@gmail.com)
*/
function verifyAuth($pdo) : mixed {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty($_SESSION['username']) || empty($_SESSION['session_token'])) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT *
            FROM users 
            WHERE username = ? 
            AND session_token = ?
            LIMIT 1
        ");
        
        $stmt->execute([
            $_SESSION['username'],
            $_SESSION['session_token']
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Auth error: " . $e->getMessage());
        return false;
    }
}