<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../app/Controllers/AuthController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $authController = new AuthController();
    $result = $authController->login($username, $password);
    
    echo json_encode($result);
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>