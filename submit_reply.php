<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get the JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }

    // Retrieve form data
    $feelingId = $input['feelingId'] ?? null;
    $name = $input['name'] ?? 'Anonymous';
    $content = $input['content'] ?? null;

    if (empty($feelingId) || empty($content)) {
        throw new Exception('Feeling ID and content are required');
    }

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'gumnaam_sahayata');
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    $stmt = $conn->prepare("INSERT INTO replies (feeling_id, name, content) VALUES (?, ?, ?)");
    $stmt->bind_param('iss', $feelingId, $name, $content);
    if (!$stmt->execute()) {
        throw new Exception('Error executing query: ' . $stmt->error);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
