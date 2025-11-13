<?php
require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Models/ride.php';

class RideController {
    private $db;
    private $rideModel;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->rideModel = new Ride($this->db);
    }
    
    public function create($data) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'message' => 'No autorizado'];
        }
        
        // Verificar que tiene rol de chofer
        if (!in_array(3, array_column($_SESSION['roles'] ?? [], 'role_id'))) {
            return ['success' => false, 'message' => 'No tiene permisos de chofer'];
        }
        
        $data['driver_id'] = $_SESSION['user_id'];
        $rideId = $this->rideModel->create($data);
        
        if ($rideId) {
            return ['success' => true, 'message' => 'Viaje creado exitosamente', 'ride_id' => $rideId];
        }
        
        return ['success' => false, 'message' => 'Error al crear viaje'];
    }
    
    public function getMyRides() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'message' => 'No autorizado'];
        }
        
        $rides = $this->rideModel->getByDriverId($_SESSION['user_id']);
        return ['success' => true, 'rides' => $rides];
    }
}
?>