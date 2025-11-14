<?php
require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Models/User.php';

class AuthController
{
    private $db;
    private $userModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $database = new Database();
        $this->db = $database->getConnection();
        $this->userModel = new User($this->db);
    }

    public function login(string $username, string $password): array
    {
        if (empty($username) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Credenciales incompletas'
            ];
        }

        // Buscar usuario
        $user = $this->userModel->findByUsername($username);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Usuario no encontrado'
            ];
        }

        // Verificar contraseña
        if (!password_verify($password, $user['password'])) {
            return [
                'success' => false,
                'message' => 'Contraseña incorrecta'
            ];
        }

        // Cargar roles
        $roles = $this->userModel->getUserRoles($user['id']);

        // Guardar en sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['photo'] = $user['photo'] ?? null;
        $_SESSION['roles'] = $roles;

        return [
            'success' => true,
            'message' => 'Login correcto',
            'roles' => $roles
        ];
    }
}
