<?php
require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Models/Vehicle.php';
require_once __DIR__ . '/../Models/User.php';

class VehicleController {
    private $db;
    private $vehicleModel;
    private $userModel;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->vehicleModel = new Vehicle($this->db);
        $this->userModel = new User($this->db);
    }
    
    public function create($data) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'message' => 'No autorizado'];
        }
        
        $data['user_id'] = $_SESSION['user_id'];
        $vehicleId = $this->vehicleModel->create($data);
        
        if ($vehicleId) {
            return ['success' => true, 'message' => 'Vehículo registrado. Pendiente de aprobación', 'vehicle_id' => $vehicleId];
        }
        
        return ['success' => false, 'message' => 'Error al registrar vehículo'];
    }
    
    public function getMyVehicles() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'message' => 'No autorizado'];
        }
        
        $vehicles = $this->vehicleModel->getByUserId($_SESSION['user_id']);
        return ['success' => true, 'vehicles' => $vehicles];
    }
    
    public function getPending() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificar si es administrador (rol_id = 1)
        if (!in_array(1, array_column($_SESSION['roles'] ?? [], 'role_id'))) {
            return ['success' => false, 'message' => 'No autorizado'];
        }
        
        $vehicles = $this->vehicleModel->getPending();
        return ['success' => true, 'vehicles' => $vehicles];
    }
    
    public function approve($vehicleId) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificar si es administrador
        if (!in_array(1, array_column($_SESSION['roles'] ?? [], 'role_id'))) {
            return ['success' => false, 'message' => 'No autorizado'];
        }
        
        $vehicle = $this->vehicleModel->findById($vehicleId);
        
        if ($this->vehicleModel->approve($vehicleId, $_SESSION['user_id'])) {
            // Verificar si el usuario ya tiene rol de chofer
            $userRoles = $this->userModel->getUserRoles($vehicle['user_id']);
            $hasDriverRole = false;
            
            foreach ($userRoles as $role) {
                if ($role['role_id'] == 3) { // 3 = Chofer
                    $hasDriverRole = true;
                    break;
                }
            }
            
            // Si no tiene rol de chofer, asignarlo
            if (!$hasDriverRole) {
                $this->userModel->assignRole($vehicle['user_id'], 3);
            }
            
            return ['success' => true, 'message' => 'Vehículo aprobado'];
        }
        
        return ['success' => false, 'message' => 'Error al aprobar vehículo'];
    }
    
    public function reject($vehicleId, $reason) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!in_array(1, array_column($_SESSION['roles'] ?? [], 'role_id'))) {
            return ['success' => false, 'message' => 'No autorizado'];
        }
        
        $vehicle = $this->vehicleModel->findById($vehicleId);
        
        if ($this->vehicleModel->reject($vehicleId, $_SESSION['user_id'], $reason)) {
            return ['success' => true, 'message' => 'Vehículo rechazado'];
        }
        
        return ['success' => false, 'message' => 'Error al rechazar vehículo'];
    }
    
    public function update($id, $data) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $vehicle = $this->vehicleModel->findById($id);
        
        if ($vehicle['user_id'] != $_SESSION['user_id']) {
            return ['success' => false, 'message' => 'No autorizado'];
        }
        
        if ($this->vehicleModel->update($id, $data)) {
            return ['success' => true, 'message' => 'Vehículo actualizado'];
        }
        
        return ['success' => false, 'message' => 'Error al actualizar vehículo'];
    }
    
    public function delete($id) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $vehicle = $this->vehicleModel->findById($id);
        
        if ($vehicle['user_id'] != $_SESSION['user_id']) {
            return ['success' => false, 'message' => 'No autorizado'];
        }
        
        if ($this->vehicleModel->delete($id)) {
            return ['success' => true, 'message' => 'Vehículo eliminado'];
        }
        
        return ['success' => false, 'message' => 'Error al eliminar vehículo'];
    }
}
?>