<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/auth.php';
Auth::requireRole('Developer');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    require_once '../config/database.php';
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $full_name = trim($input['full_name'] ?? '');
    $email = trim($input['email'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $password = $input['password'] ?? '';
    $college_id = $input['college_id'] ?? '';
    
    // Validation
    if (empty($full_name) || empty($email) || empty($phone) || empty($password) || empty($college_id)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }
    
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
        exit;
    }
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }
    
    // Check if college exists
    $stmt = $pdo->prepare("SELECT college_id FROM colleges WHERE college_id = ?");
    $stmt->execute([$college_id]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Invalid college selected']);
        exit;
    }
    
    // Get SuperAdmin role ID
    $stmt = $pdo->prepare("SELECT role_id FROM user_roles WHERE role_name = 'SuperAdmin'");
    $stmt->execute();
    $role = $stmt->fetch();
    
    if (!$role) {
        echo json_encode(['success' => false, 'message' => 'SuperAdmin role not found']);
        exit;
    }
    
    $role_id = $role['role_id'];
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Begin transaction
    $pdo->beginTransaction();
    
    try {
        // Insert user
        $stmt = $pdo->prepare("
            INSERT INTO users (email, password, role_id, full_name, phone) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$email, $hashed_password, $role_id, $full_name, $phone]);
        $user_id = $pdo->lastInsertId();
        
        // Update college with superadmin
        $stmt = $pdo->prepare("UPDATE colleges SET superadmin_id = ? WHERE college_id = ?");
        $stmt->execute([$user_id, $college_id]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Super Admin created successfully',
            'user_id' => $user_id
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create super admin: ' . $e->getMessage()
    ]);
}
?>