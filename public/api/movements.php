<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/bootstrap.php';

header('Content-Type: application/json');

$clientId = $_GET['client_id'] ?? null;
if (!$clientId || !is_numeric($clientId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid client_id']);
    exit;
}

$movementModel = new \App\Models\Movement();
$movements = $movementModel->getClientMovements($clientId);

echo json_encode($movements); 