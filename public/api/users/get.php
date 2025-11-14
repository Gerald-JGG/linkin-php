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

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de usuario no proporcionado']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);

$user = $userModel->findById($_GET['id']);

if ($user) {
    // Obtener roles del usuario
    $roles = $userModel->getUserRoles($_GET['id']);
    
    echo json_encode([
        'success' => true, 
        'user' => $user,
        'roles' => $roles
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
}
?>