<?php
session_start();
header('Content-Type: application/json');

require 'db.php'; // Include your database connection file

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }

    $user_id = $_SESSION['user_id'];
    $feeling_id = $input['feelingId'] ?? null;
    $content = $input['replyContent'] ?? '';
    $anonymous = $input['anonymous'] ?? false;

    if (empty($user_id) || empty($feeling_id) || empty($content)) {
        throw new Exception('User ID, Feeling ID, and Content are required');
    }

    if (!$anonymous) {
        $sql = "SELECT username FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $name = $user['username'];
        } else {
            throw new Exception('User not found');
        }
    } else {
        $name = 'Anonymous';
    }

    $sql = "INSERT INTO replies (feeling_id, user_id, name, content) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiss', $feeling_id, $user_id, $name, $content);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
