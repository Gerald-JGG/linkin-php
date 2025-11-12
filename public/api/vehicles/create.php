<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../../app/Controllers/VehicleController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'brand' => $_POST['brand'] ?? '',
        'model' => $_POST['model'] ?? '',
        'year' => $_POST['year'] ?? '',
        'color' => $_POST['color'] ?? '',
        'plate' => $_POST['plate'] ?? '',
        'photo' => null
    ];
    
    // Manejo de foto 
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $uploadDir = __DIR__ . '/../../uploads/vehicles/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $uploadPath = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
            $data['photo'] = 'uploads/vehicles/' . $filename;
        }
    }
    
    $vehicleController = new VehicleController();
    $result = $vehicleController->create($data);
    
    echo json_encode($result);
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>