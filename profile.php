<?php
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'db.php'; // Include your database connection file

$user_id = $_SESSION['user_id'];
$message = '';

// Fetch user data from the database
$sql = "SELECT username, email, profile_picture FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    // Handle error: user not found
    header('Location: login.php');
    exit;
}

// Handle profile update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];

    // Handle profile picture upload
    if ($_FILES['profile_picture']['name']) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES['profile_picture']['name']);
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file);
    } else {
        $target_file = $user['profile_picture'];
    }

    $sql = "UPDATE users SET username = ?, email = ?, profile_picture = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssi', $username, $email, $target_file, $user_id);

    if ($stmt->execute()) {
        $message = "Profile updated successfully.";
        $user['username'] = $username;
        $user['email'] = $email;
        $user['profile_picture'] = $target_file;
    } else {
        $message = "Error updating profile.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Gumnaam Sahayata</title>
    <style>
        /* Add your CSS styles here */
        body {
    margin: 0;
    font-family: Arial, sans-serif;
    padding-top: 70px; /* Adjust according to header height */
    color: #333;
    background-color: #f4f4f4;
}



header {
    background-color: #4267B2;
    color: white;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 1000;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.header-logo {
    font-size: 28px;
    font-weight: bold;
    letter-spacing: 1px;
}

.header-search {
    flex: 1;
    margin: 0 10px;
    display: flex;
    justify-content: center;
}

.header-search input[type="text"] {
    padding: 10px;
    width: 70%;
    border: none;
    border-radius: 20px;
    outline: none;
    font-size: 16px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.header-nav {
    display: flex;
    align-items: center;
}

.header-nav a {
    color: white;
    text-decoration: none;
    margin: 0 10px;
    font-size: 16px;
    transition: color 0.3s;
}

.header-nav a:hover {
    text-decoration: underline;
    color: #ffeb3b;
}

.header-nav a.active {
    border-bottom: 2px solid #ffeb3b;
}

.header-profile {
    display: flex;
    align-items: center;
}

.header-profile img {
    border-radius: 50%;
    width: 32px;
    height: 32px;
    margin-right: 10px;
}

.header-profile span {
    font-size: 16px;
}

/* Footer Styles */
footer {
    background-color: #24292e;
    color: #fff;
    padding: 40px 0;
    text-align: center;
    position: relative;
    width: 100%;
    box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.1);
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.footer-links {
    margin-bottom: 20px;
}

.footer-links a {
    color: #fff;
    margin: 0 15px;
    text-decoration: none;
    font-size: 14px;
    transition: color 0.3s;
}

.footer-links a:hover {
    text-decoration: underline;
    color: #ffeb3b;
}

.footer-social {
    margin-bottom: 20px;
}

.footer-social a {
    margin: 0 10px;
    display: inline-block;
    transition: transform 0.3s;
}

.footer-social a:hover {
    transform: scale(1.1);
}

.footer-social img {
    width: 32px;
    height: 32px;
}

footer p {
    font-size: 14px;
    margin: 0;
}

.footer-credits {
    margin-top: 20px;
    font-size: 12px;
    color: #ccc;
}

/* Responsive Design */
@media (max-width: 768px) {
    .header-logo {
        font-size: 24px;
    }

    .header-search input[type="text"] {
        width: 100%;
    }

    .header-nav a {
        font-size: 14px;
        margin: 0 5px;
    }

    .footer-container {
        padding: 0 20px;
    }

    .footer-links a {
        margin: 0 5px;
        font-size: 12px;
        margin-right:100px;
    }

    .footer-social img {
        width: 24px;
        height: 24px;
    }

    footer p {
        font-size: 12px;
    }
}

/* Header Dropdown Styles */
.header-dropdown {
    position: relative;
    display: inline-block;
}

.header-dropdown-content {
    display: none;
    position: absolute;
    right: 0;
    background-color: #f9f9f9;
    min-width: 160px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    z-index: 1;
}

.header-dropdown-content a {
    color: black;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
    transition: background-color 0.3s;
}

.header-dropdown-content a:hover {
    background-color: #f1f1f1;
}

.header-dropdown:hover .header-dropdown-content {
    display: block;
}

/* Footer Form Styles */
.footer-form {
    margin-top: 20px;
}

.footer-form input[type="email"] {
    padding: 10px;
    border: none;
    border-radius: 5px;
    outline: none;
    font-size: 14px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin-right: 10px;
}

.footer-form button {
    padding: 10px 20px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.3s;
}

.footer-form button:hover {
    background-color: #0056b3;
}

/* Additional Footer Styles */
.footer-about {
    margin-bottom: 20px;
    font-size: 14px;
    color: #bbb;
}

.footer-about p {
    margin: 0;
}

.footer-newsletter {
    margin-bottom: 20px;
}

.footer-newsletter h3 {
    font-size: 18px;
    margin-bottom: 10px;
}

.footer-newsletter p {
    font-size: 14px;
    color: #bbb;
}

.footer-bottom {
    border-top: 1px solid #444;
    padding-top: 20px;
    margin-top: 20px;
}

.footer-bottom p {
    font-size: 12px;
    color: #ccc;
}

.footer-bottom a {
    color: #fff;
    text-decoration: none;
}

.footer-bottom a:hover {
    text-decoration: underline;
    color: #ffeb3b;
}

/* Optional: Animation for "like" count change */
.like-button .like-count-change {
    animation: likeCountChange 0.5s ease-in-out;
}

@keyframes likeCountChange {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.5);
        opacity: 0;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}


/* Form styles */
form {
    background-color: #fff;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;

}
.container{
    margin-top:50px;
}
form label {
    display: block;
    margin-bottom: 10px;
    font-weight: bold;
    color: #333;
}

form input, form textarea {
    width: calc(100% - 22px);
    padding: 12px;
    margin-bottom: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
    transition: border-color 0.3s ease;
}

form input:focus, form textarea:focus {
    border-color: #4267B2;
    outline: none;
}

form button {
    background-color: #4267B2;
    color: #fff;
    border: none;
    padding: 12px 20px;
    cursor: pointer;
    border-radius: 5px;
    transition: background-color 0.3s ease;
    font-size: 16px;
}

form button:hover {
    background-color: #365899;
}

/* Comment section */
.comment-section {
    margin-top: 15px;
    border-top: 1px solid #ddd;
    padding-top: 15px;
}

.comment {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.comment img {
    border-radius: 50%;
    width: 30px;
    height: 30px;
    margin-right: 10px;
    border: 2px solid #4267B2;
}

.comment p {
    font-size: 14px;
    color: #666;
    background-color: #f0f2f5;
    padding: 10px;
    border-radius: 8px;
}


/* Responsive design */
@media (max-width: 768px) {
    header {
        flex-direction: column;
        padding: 10px;
    }

    .header-logo {
        margin: 0 0 10px 0;
    }

    .header-search input[type="text"] {
        width: 100%;
    }

    .header-nav {
        margin: 10px 0 0 0;
    }

    .header-nav a {
        font-size: 0.9em;
        margin: 0 5px;
    }

    header h1 {
        font-size: 1.8em;
    }

    .feeling h2 {
        font-size: 18px;
    }

    .feeling p {
        font-size: 14px;
    }

    form input, form textarea {
        width: calc(100% - 20px);
        font-size: 14px;
    }

    form button {
        font-size: 14px;
    }

    footer p {
        font-size: 12px;
    }
}



        header, footer {
            background-color: #4267B2;
            color: white;
            padding: 10px 20px;
            position: fixed;
            width: 100%;
            z-index: 1000;
        }

        header {
            top: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        footer {
            bottom: 0;
            text-align: center;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-top: 90px; /* Adjust based on your header height */
        }








        .profile-pic {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        form input[type="text"],
        form input[type="email"],
        form input[type="file"] {
            margin-bottom: 15px;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        form button {
            padding: 10px;
            font-size: 16px;
            color: white;
            background-color: #4267B2;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        form button:hover {
            background-color: #365899;
        }

        .message {
            font-size: 14px;
            color: green;
            margin-bottom: 15px;
        }
        .profile-pic{
    border-radius: 50%;
    width: 50px;
    height: 50px;
    margin-right: 15px;
    border: 2px solid #4267B2;
}

    </style>
</head>
<body>
<header>
    <div class="header-logo">Gumnaam Sahayata</div>
    <div class="header-nav">
        <a href="index.php">Home</a>
        <a href="profile.php">Profile</a>
        <a href="message.php">Messages</a>
        <a href="logout.php">Logout</a>
        <img src="<?php echo $user['profile_picture']; ?>" alt="Profile Picture" class="profile-pic">



    </div>
    </div>
</header>

<div class="container">
    <h2><?php echo $user['username']; ?></h2>
    <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <img src="<?php echo $user['profile_picture']; ?>" alt="Profile Picture" class="profile-pic">
        <label for="username">Username</label>
        <input type="text" id="username" name="username"  value="<?php echo $user['username']; ?>" required>
        
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>
        
        <label for="profile_picture">Profile Picture</label>
        <input type="file" id="profile_picture" name="profile_picture">

        <button type="submit">Update Profile</button>
    </form>
</div>

<footer>
    <p>&copy; 2024 Gumnaam Sahayata. All rights reserved.</p>
</footer>
</body>
</html>
