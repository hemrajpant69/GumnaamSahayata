<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? null; // Ensure user_id is set
    if ($user_id === null) {
        echo json_encode(['success' => false, 'error' => 'User not logged in']);
        exit;
    }

    $content = $_POST['content'];
    $name = $_POST['name'] ?? 'Anonymous';

    // Handle file upload
    $filePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $uploadDir = 'uploads/';
        $filePath = $uploadDir . basename($fileName);

        if (!move_uploaded_file($fileTmpPath, $filePath)) {
            echo json_encode(['success' => false, 'error' => 'Failed to upload image']);
            exit;
        }
    }

    $sql = "INSERT INTO feelings (user_id, content, image_path) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iss', $user_id, $content, $filePath);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
}
?>
