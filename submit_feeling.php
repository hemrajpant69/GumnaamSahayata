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

    // Retrieve form data
    $name = $_POST['name'] ?? 'Anonymous';
    $content = $_POST['content'] ?? null;
    $image_path = null; // Placeholder for image upload logic

    if (empty($content)) {
        throw new Exception('Content is required');
    }

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = basename($_FILES['image']['name']);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($file_tmp, $target_file)) {
            $image_path = $target_file;
        } else {
            throw new Exception('Failed to move uploaded file.');
        }
    }

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'gumnaam_sahayata');
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    $stmt = $conn->prepare("INSERT INTO feelings (name, content, image_path) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $name, $content, $image_path);
    if (!$stmt->execute()) {
        throw new Exception('Error executing query: ' . $stmt->error);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
