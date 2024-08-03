<?php
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'db.php'; // Include your database connection file

$user_id = $_SESSION['user_id'];

// Fetch user data from the database
$sql = "SELECT username, email, profile_picture FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    header('Location: register.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gumnaam Sahayata</title>
    <link rel="stylesheet" href="assests/style.css">

</head>
<body>
<header>
        <div class="header-logo">Gumnaam Sahayata</div>
        <div class="header-search">
        <input type="text" id="searchInput" placeholder="Search...">
    </div>
    <div class="header-nav">
            <a href="index.php" class="active">Home</a>
            <a href="profile.php">Profile</a>
            <a href="message.php">Message</a>
            <a href="logout.php">Logout</a>
            <img src="<?php echo $user['profile_picture']; ?>" alt="Profile Picture" class="profile-pic">

        </div>
    </header>
    <div class="container">
        <form id="feelingForm" enctype="multipart/form-data">
        <!--  <input type="text" id="name" name="name" placeholder="Your Name (Optional)" autocomplete="off" value="<?php echo $user['username']; ?>">!-->
            <textarea id="content" name="content" placeholder="Share your feelings..." required></textarea>
            <input type="file" id="image" name="image" accept="image/*">
            
            <button type="submit">Submit</button>
        </form>
        <div id="feelingsContainer"></div>
    </div>
    <script>
     
    
     document.addEventListener('DOMContentLoaded', function () {
        const feelingForm = document.getElementById('feelingForm');
        const feelingsContainer = document.getElementById('feelingsContainer');
        const searchInput = document.getElementById('searchInput');

        feelingForm.addEventListener('submit', submitFeeling);
        searchInput.addEventListener('input', performSearch);

        async function submitFeeling(event) {
            event.preventDefault();
            const formData = new FormData(feelingForm);

            try {
                const response = await fetch('submit_feeling.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    fetchFeelings();
                    feelingForm.reset();
                } else {
                    console.error(data.error);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function fetchFeelings(search = '') {
            try {
                const response = await fetch(`get_feelings.php?search=${encodeURIComponent(search)}`);
                const data = await response.json();
                if (Array.isArray(data)) {
                    feelingsContainer.innerHTML = '';
                    data.forEach(feeling => {
                        const feelingDiv = document.createElement('div');
                        feelingDiv.classList.add('feeling');
                        feelingDiv.innerHTML = `
                            <div class="feeling-header">
                                <img class="profile_picture" src="${feeling.profile || 'uploads/defaultpic.png'}" alt="Profile Pic">
                                <div class="username">${feeling.name || 'Anonymous'}</div>
                                <div class="timestamp">${calculateTimeAgo(feeling.timestamp)}</div>
                            </div>
                            <p>${feeling.content}</p>
                            ${feeling.image_path ? `<img src="${feeling.image_path}" alt="Feeling Image" style="max-width:100%; height: auto; margin-top: 10px;">` : ''}
                            <p>Views: <span id="viewCount_${feeling.id}">${feeling.views}</span></p>
                            <p>Likes: <span id="likeCount_${feeling.id}">${feeling.likes}</span></p>
                            <button class="like-button" onclick="likeFeeling(${feeling.id})">
                                <span class="like-icon">👍</span> Like 
                            </button>
                            <div class="replies">
                                ${(feeling.replies || []).map(reply => `
                                    <div class="reply">
                                        <p><strong>${reply.name || 'Anonymous'}</strong> <span class="time-ago" data-timestamp="${reply.timestamp}"></span></p>
                                        <p>${reply.content}</p>
                                    </div>
                                `).join('')}
                                <form class="replyForm" data-feeling-id="${feeling.id}" onsubmit="submitReply(event, ${feeling.id})">
                                  <input type="text" id="replyName_${feeling.id}" name="replyName" placeholder="Your Name (Optional)" autocomplete="off" value="<?php echo $user['username']; ?>"> 
                                    <textarea id="replyContent_${feeling.id}" name="replyContent" placeholder="Write a Comment..." required></textarea>
                                    <button type="submit">Comment</button>
                                </form>
                            </div>
                        `;
                        feelingsContainer.appendChild(feelingDiv);
                        recordView(feeling.id);
                    });
                } else {
                    console.error(data.error);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function likeFeeling(feelingId) {
            try {
                const response = await fetch('like_feeling.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ feelingId: feelingId })
                });
                const data = await response.json();
                if (data.success) {
                    document.getElementById(`likeCount_${feelingId}`).textContent = data.newLikes;
                } else {
                    console.error(data.error);
                    alert("You already Liked on This Post");
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function submitReply(event, feelingId) {
            event.preventDefault();
            const replyForm = document.querySelector(`form[data-feeling-id="${feelingId}"]`);
            const name = replyForm.querySelector(`#replyName_${feelingId}`).value;
            const content = replyForm.querySelector(`#replyContent_${feelingId}`).value;

            try {
                const response = await fetch('submit_reply.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        feelingId: feelingId,
                        replyName: name,
                        replyContent: content
                    })
                });
                const data = await response.json();
                if (data.success) {
                    fetchFeelings();
                    replyForm.reset();
                } else {
                    console.error(data.error);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function recordView(feelingId) {
            try {
                const response = await fetch('record_view.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ feelingId: feelingId })
                });
                const data = await response.json();
                if (data.success) {
                    document.getElementById(`viewCount_${feelingId}`).textContent = data.newViews;
                } else {
                    console.error(data.error);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function performSearch() {
            fetchFeelings(searchInput.value);
        }

        function calculateTimeAgo(timestamp) {
            const now = new Date();
            const timeDiff = now - new Date(timestamp);
            const seconds = Math.floor(timeDiff / 1000);
            const minutes = Math.floor(seconds / 60);
            const hours = Math.floor(minutes / 60);
            const days = Math.floor(hours / 24);

            if (days > 0) {
                return `${days} day(s) ago`;
            } else if (hours > 0) {
                return `${hours} hour(s) ago`;
            } else if (minutes > 0) {
                return `${minutes} minute(s) ago`;
            } else {
                return `${seconds} second(s) ago`;
            }
        }

        function updateTimestamps() {
            const elements = document.querySelectorAll('.time-ago');
            elements.forEach(element => {
                const timestamp = element.getAttribute('data-timestamp');
                element.textContent = calculateTimeAgo(timestamp);
            });
        }

        fetchFeelings();
        setInterval(updateTimestamps, 200); // Update timestamps every minute

        // Expose functions to the global scope
        window.likeFeeling = likeFeeling;
        window.submitReply = submitReply;
    });
</script>
</body>
<footer>
    <p>&copy; 2024 Gumnaam Sahayata. All rights reserved.</p>
</footer>
</html>