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

    // Check if the user has already liked the post
    $checkStmt = $conn->prepare("SELECT * FROM likes WHERE feeling_id = ? AND user_id = ?");
    $checkStmt->bind_param('ii', $feelingId, $userId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        throw new Exception('You have already liked this post');
    }

    // Insert the like
    $stmt = $conn->prepare("INSERT INTO likes (feeling_id, user_id) VALUES (?, ?)");
    $stmt->bind_param('ii', $feelingId, $userId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $newLikes = $conn->query("SELECT COUNT(*) AS likes FROM likes WHERE feeling_id = $feelingId")->fetch_assoc()['likes'];
        echo json_encode(['success' => true, 'newLikes' => $newLikes]);
    } else {
        throw new Exception('Unable to like feeling');
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
