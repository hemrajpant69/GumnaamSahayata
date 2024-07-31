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

    if (empty($feelingId)) {
        throw new Exception('Feeling ID is required');
    }

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'gumnaam_sahayata');
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    $stmt = $conn->prepare("UPDATE feelings SET likes = likes + 1 WHERE id = ?");
    $stmt->bind_param('i', $feelingId);
    if (!$stmt->execute()) {
        throw new Exception('Error executing query: ' . $stmt->error);
    }

    // Get the updated number of likes
    $result = $conn->query("SELECT likes FROM feelings WHERE id = $feelingId");
    if (!$result) {
        throw new Exception('Error fetching updated likes: ' . $conn->error);
    }
    $likes = $result->fetch_assoc()['likes'];

    echo json_encode(['success' => true, 'newLikes' => $likes]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
