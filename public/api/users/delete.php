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
    
    $userId = $_POST['user_id'] ?? '';
    
    if (empty($userId)) {
        echo json_encode(['success' => false, 'message' => 'ID de usuario no proporcionado']);
        exit;
    }
    
    // Verificar que no sea el usuario actual
    if ($userId == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'No puedes eliminar tu propia cuenta']);
        exit;
    }
    
    // Verificar que el usuario existe
    $existingUser = $userModel->findById($userId);
    
    if (!$existingUser) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }
    
    // Eliminar usuario (los CASCADE eliminarán vehículos, viajes, reservas y roles)
    if ($userModel->delete($userId)) {
        echo json_encode(['success' => true, 'message' => 'Usuario eliminado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar usuario']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>