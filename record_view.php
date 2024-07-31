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

    $stmt = $conn->prepare("INSERT INTO views (feeling_id) VALUES (?)");
    $stmt->bind_param('i', $feelingId);
    if (!$stmt->execute()) {
        throw new Exception('Error executing query: ' . $stmt->error);
    }

    // Get the updated number of views
    $result = $conn->query("SELECT COUNT(id) AS views FROM views WHERE feeling_id = $feelingId");
    if (!$result) {
        throw new Exception('Error fetching updated views: ' . $conn->error);
    }
    $views = $result->fetch_assoc()['views'];

    echo json_encode(['success' => true, 'newViews' => $views]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
