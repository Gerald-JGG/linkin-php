<?php
require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Models/User.php';

class AuthController {
    private $db;
    private $userModel;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->userModel = new User($this->db);
    }
    
    public function register($data) {
        // Validar que las contraseñas coincidan
        if ($data['password'] !== $data['password_confirm']) {
            return ['success' => false, 'message' => 'Las contraseñas no coinciden'];
        }
        
        // Hash de la contraseña
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Registrar usuario
        $userId = $this->userModel->create($data);
        
        if ($userId) {
            // Asignar rol de pasajero por defecto
            $this->userModel->assignRole($userId, 2); // 2 = Pasajero
            return ['success' => true, 'message' => 'Usuario registrado exitosamente', 'user_id' => $userId];
        }
        
        return ['success' => false, 'message' => 'Error al registrar usuario'];
    }
    
    public function login($username, $password) {
        $user = $this->userModel->findByUsername($username);
        
        if ($user && password_verify($password, $user['password'])) {
            // Iniciar sesión
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['first_name'] = $user['first_name'];
            
            // Obtener roles del usuario
            $roles = $this->userModel->getUserRoles($user['id']);
            $_SESSION['roles'] = $roles;
            
            return ['success' => true, 'message' => 'Inicio de sesión exitoso', 'roles' => $roles];
        }
        
        return ['success' => false, 'message' => 'Usuario o contraseña incorrectos'];
    }
    
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        return ['success' => true, 'message' => 'Sesión cerrada'];
    }
    
    public function checkAuth() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }
    
    public function hasRole($roleId) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['roles'])) {
            return false;
        }
        return in_array($roleId, array_column($_SESSION['roles'], 'role_id'));
    }
}
?>