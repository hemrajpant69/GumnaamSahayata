<?php
require 'db.php'; // Include your database connection file

$search = $_GET['search'] ?? '';

$sql = "SELECT feelings.id, feelings.content, feelings.image_path, feelings.timestamp, users.username AS name,users.profile_picture AS profile,
        (SELECT COUNT(*) FROM views WHERE views.feeling_id = feelings.id) AS views,
                (SELECT COUNT(*) FROM likes WHERE likes.feeling_id = feelings.id) AS likes,
        (SELECT COUNT(*) FROM replies WHERE replies.feeling_id = feelings.id) AS reply_count
        FROM feelings
        JOIN users ON feelings.user_id = users.id
        WHERE feelings.content LIKE ?
        ORDER BY feelings.timestamp DESC";

$stmt = $conn->prepare($sql);
$searchTerm = '%' . $search . '%';
$stmt->bind_param('s', $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$feelings = [];
while ($row = $result->fetch_assoc()) {
    $feelingId = $row['id'];
    $row['replies'] = [];
    $replySql = "SELECT replies.content, replies.timestamp, users.username AS name
                 FROM replies 
                 JOIN users ON replies.user_id = users.id 
                 WHERE replies.feeling_id = ?";
    $replyStmt = $conn->prepare($replySql);
    $replyStmt->bind_param('i', $feelingId);
    $replyStmt->execute();
    $replyResult = $replyStmt->get_result();

    while ($replyRow = $replyResult->fetch_assoc()) {
        $row['replies'][] = $replyRow;
    }

    $feelings[] = $row;
}

echo json_encode($feelings);
?>
