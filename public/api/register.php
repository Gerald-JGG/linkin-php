<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../app/Controllers/AuthController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'first_name' => $_POST['first_name'] ?? '',
        'last_name' => $_POST['last_name'] ?? '',
        'cedula' => $_POST['cedula'] ?? '',
        'birth_date' => $_POST['birth_date'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'username' => $_POST['username'] ?? '',
        'password' => $_POST['password'] ?? '',
        'password_confirm' => $_POST['password_confirm'] ?? '',
        'photo' => null
    ];
    
    // Manejo de foto (opcional)
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $uploadDir = __DIR__ . '/../uploads/users/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $uploadPath = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
            $data['photo'] = 'uploads/users/' . $filename;
        }
    }
    
    $authController = new AuthController();
    $result = $authController->register($data);
    
    echo json_encode($result);
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>