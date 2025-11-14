<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar si es administrador
$isAdmin = false;
if (isset($_SESSION['roles'])) {
    foreach ($_SESSION['roles'] as $role) {
        if ($role['role_id'] == 1) {
            $isAdmin = true;
            break;
        }
    }
}

if (!$isAdmin) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../../app/Config/database.php';
require_once __DIR__ . '/../../../app/Models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    $userModel = new User($db);
    
    $data = [
        'first_name' => $_POST['first_name'] ?? '',
        'last_name' => $_POST['last_name'] ?? '',
        'cedula' => $_POST['cedula'] ?? '',
        'birth_date' => $_POST['birth_date'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'username' => $_POST['username'] ?? '',
        'password' => password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT),
        'photo' => null
    ];
    
    $userId = $userModel->create($data);
    
    if ($userId) {
        // Asignar roles
        $roles = $_POST['roles'] ?? [];
        foreach ($roles as $roleId) {
            $userModel->assignRole($userId, $roleId);
        }
        
        echo json_encode(['success' => true, 'message' => 'Usuario creado exitosamente', 'user_id' => $userId]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear usuario. Verifique que el email, usuario o cédula no estén duplicados.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>