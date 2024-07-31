<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

try {
    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'gumnaam_sahayata');
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    // Fetch feelings with associated replies
    $sql = "SELECT f.id, f.name, f.timestamp, f.content, f.image_path, f.likes, 
                   COUNT(v.id) AS views, 
                   r.id AS reply_id, r.name AS reply_name, r.timestamp AS reply_timestamp, r.content AS reply_content
            FROM feelings f
            LEFT JOIN replies r ON f.id = r.feeling_id
            LEFT JOIN views v ON f.id = v.feeling_id
            GROUP BY f.id, r.id
            ORDER BY f.timestamp DESC";
    $result = $conn->query($sql);

    if ($result === false) {
        throw new Exception('Error executing query: ' . $conn->error);
    }

    $feelings = [];
    while ($row = $result->fetch_assoc()) {
        $feelingId = $row['id'];
        if (!isset($feelings[$feelingId])) {
            $feelings[$feelingId] = [
                'id' => $feelingId,
                'name' => $row['name'],
                'timestamp' => $row['timestamp'],
                'content' => $row['content'],
                'image_path' => $row['image_path'],
                'likes' => $row['likes'],
                'views' => $row['views'],
                'replies' => []
            ];
        }

        // Add reply to the feeling
        if (!empty($row['reply_id'])) {
            $feelings[$feelingId]['replies'][] = [
                'id' => $row['reply_id'],
                'name' => $row['reply_name'],
                'timestamp' => $row['reply_timestamp'],
                'content' => $row['reply_content']
            ];
        }
    }

    // Convert associative array to indexed array
    $feelings = array_values($feelings);

    echo json_encode($feelings);
} catch (Exception $e) {
    echo json_encode([]);
}

$conn->close();
?>
