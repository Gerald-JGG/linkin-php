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
    
    // Verificar que el usuario existe
    $existingUser = $userModel->findById($userId);
    
    if (!$existingUser) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }
    
    // Actualizar datos básicos
    $data = [
        'first_name' => $_POST['first_name'] ?? '',
        'last_name' => $_POST['last_name'] ?? '',
        'cedula' => $_POST['cedula'] ?? '',
        'birth_date' => $_POST['birth_date'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? ''
    ];
    
    $updateSuccess = $userModel->update($userId, $data);
    
    if ($updateSuccess) {
        // Actualizar username si es necesario
        if (!empty($_POST['username']) && $_POST['username'] !== $existingUser['username']) {
            $query = "UPDATE users SET username = :username WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $_POST['username']);
            $stmt->bindParam(':id', $userId);
            $stmt->execute();
        }
        
        // Actualizar contraseña si se proporciona
        if (!empty($_POST['password'])) {
            $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $query = "UPDATE users SET password = :password WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':id', $userId);
            $stmt->execute();
        }
        
        // Actualizar estado activo/inactivo
        $isActive = $_POST['is_active'] ?? 1;
        $query = "UPDATE users SET is_active = :is_active WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':is_active', $isActive);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        
        // Actualizar roles
        // Primero eliminar roles actuales
        $query = "DELETE FROM user_roles WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        // Asignar nuevos roles
        $roles = $_POST['roles'] ?? [];
        foreach ($roles as $roleId) {
            $userModel->assignRole($userId, $roleId);
        }
        
        echo json_encode(['success' => true, 'message' => 'Usuario actualizado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar usuario']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>