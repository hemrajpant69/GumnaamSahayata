<?php
session_start();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }

    $feelingId = $input['feelingId'] ?? null;
    $userId = $_SESSION['user_id'] ?? null;

    if (empty($feelingId) || empty($userId)) {
        throw new Exception('Feeling ID and User ID are required');
    }

    require 'db.php';

    // Check if the user has already viewed the post
    $checkStmt = $conn->prepare("SELECT * FROM views WHERE feeling_id = ? AND user_id = ?");
    $checkStmt->bind_param('ii', $feelingId, $userId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        throw new Exception('You have already viewed this post');
    }

    // Insert the view
    $stmt = $conn->prepare("INSERT INTO views (feeling_id, user_id) VALUES (?, ?)");
    $stmt->bind_param('ii', $feelingId, $userId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $views = $conn->query("SELECT COUNT(*) AS views FROM views WHERE feeling_id = $feelingId")->fetch_assoc()['views'];
        echo json_encode(['success' => true, 'newViews' => $views]);
    } else {
        throw new Exception('Unable to record view');
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
