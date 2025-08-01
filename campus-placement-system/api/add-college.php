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
    
    $college_name = trim($input['college_name'] ?? '');
    $address = trim($input['address'] ?? '');
    $contact_email = trim($input['contact_email'] ?? '');
    $contact_phone = trim($input['contact_phone'] ?? '');
    $website = trim($input['website'] ?? '');
    
    // Validation
    if (empty($college_name) || empty($address) || empty($contact_email) || empty($contact_phone)) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
        exit;
    }
    
    if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }
    
    // Check if college already exists
    $stmt = $pdo->prepare("SELECT college_id FROM colleges WHERE college_name = ? OR contact_email = ?");
    $stmt->execute([$college_name, $contact_email]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'College with this name or email already exists']);
        exit;
    }
    
    // Insert college
    $stmt = $pdo->prepare("
        INSERT INTO colleges (college_name, address, contact_email, contact_phone, website) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([$college_name, $address, $contact_email, $contact_phone, $website]);
    
    echo json_encode([
        'success' => true,
        'message' => 'College added successfully',
        'college_id' => $pdo->lastInsertId()
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to add college: ' . $e->getMessage()
    ]);
}
?>