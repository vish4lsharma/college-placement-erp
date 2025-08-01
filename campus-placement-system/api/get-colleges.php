<?php
header('Content-Type: application/json');
require_once '../includes/auth.php';

Auth::requireRole('Developer');

try {
    require_once '../config/database.php';
    
    $stmt = $pdo->query("
        SELECT 
            c.*,
            u.full_name as superadmin_name
        FROM colleges c
        LEFT JOIN users u ON c.superadmin_id = u.user_id
        ORDER BY c.created_at DESC
    ");
    
    $colleges = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $colleges
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch colleges: ' . $e->getMessage()
    ]);
}
?>