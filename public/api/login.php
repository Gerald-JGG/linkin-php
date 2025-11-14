<?php
header('Content-Type: application/json');

session_start();
require_once __DIR__ . '/../../app/Controllers/AuthController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

try {
    $auth = new AuthController();
    $result = $auth->login($username, $password);

    // Asegurar estructura válida
    if (!is_array($result)) {
        echo json_encode([
            'success' => false,
            'message' => 'Respuesta inesperada del servidor'
        ]);
        exit;
    }

    // Si login OK → devolver success
    echo json_encode($result);
    exit;

} catch (Throwable $e) {
    error_log("LOGIN ERROR: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor'
    ]);
    exit;
}
