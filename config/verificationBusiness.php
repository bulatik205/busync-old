<?php
function verificationBusiness($pdo, $userId) : bool {
    try {
        if ($userId === null) {
            return false;
        }

        if (!is_numeric($userId)) {
            return false;
        }

        if (strlen($userId) > 72) {
            return false;
        }
        
        $stmt = $pdo->prepare("SELECT * FROM business_info WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $businessInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!empty($businessInfo)) {
            return true;
        } 

        return false;
    } catch (Exception $e) {
        error_log("Auth error: " . $e->getMessage());
        return false;
    }
} 