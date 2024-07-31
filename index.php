<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gumnaam Sahayata</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        form {
            margin-bottom: 20px;
        }

        input[type="text"],
        textarea {
            width: calc(100% - 22px);
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        input[type="file"] {
            margin: 10px 0;
        }

        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .feeling {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .reply {
            border-top: 1px solid #eee;
            margin-top: 10px;
            padding-top: 10px;
        }

        .replyForm {
            margin-top: 10px;
        }

        @media (max-width: 600px) {
            .container {
                padding: 10px;
            }

            input[type="text"],
            textarea {
                width: 100%;
            }

            button {
                width: 100%;
            }
        }

        .time-ago {
            color: #888;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gumnaam Sahayata</h1>
        <form id="feelingForm" enctype="multipart/form-data">
            <input type="text" id="name" name="name" placeholder="Your Name (Optional)" autocomplete="off">
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

            feelingForm.addEventListener('submit', submitFeeling);

            function submitFeeling(event) {
                event.preventDefault();
                const formData = new FormData(feelingForm);

                fetch('submit_feeling.php', {
                    method: 'POST',
                    body: formData
                }).then(response => response.text()).then(text => {
                    try {
                        const data = JSON.parse(text);
                        if (data.success) {
                            fetchFeelings();
                            feelingForm.reset();
                        } else {
                            console.error(data.error);
                        }
                    } catch (error) {
                        console.error('JSON Parsing Error:', error, text);
                    }
                }).catch(error => console.error('Error:', error));
            }

            function fetchFeelings() {
                fetch('get_feelings.php')
                    .then(response => response.text())
                    .then(text => {
                        try {
                            const data = JSON.parse(text);
                            feelingsContainer.innerHTML = '';
                            data.forEach(feeling => {
                                const feelingDiv = document.createElement('div');
                                feelingDiv.classList.add('feeling');
                                feelingDiv.innerHTML = `
                                    <p><strong>${feeling.name || 'Anonymous'}</strong> <span class="time-ago" data-timestamp="${feeling.timestamp}"></span></p>
                                    <p>${feeling.content}</p>
                                    ${feeling.image_path ? `<img src="${feeling.image_path}" alt="Feeling Image" style="max-width:100%; height: auto; margin-top: 10px;">` : ''}
                                    <p>Views: <span id="viewCount_${feeling.id}">${feeling.views}</span></p>
                                    <p>Likes: <span id="likeCount_${feeling.id}">${feeling.likes}</span></p>
                                    <button onclick="likeFeeling(${feeling.id})">Like</button>
                                    <div class="replies">
                                        ${(feeling.replies || []).map(reply => `
                                            <div class="reply">
                                                <p><strong>${reply.name || 'Anonymous'}</strong> <span class="time-ago" data-timestamp="${reply.timestamp}"></span></p>
                                                <p>${reply.content}</p>
                                            </div>
                                        `).join('')}
                                        <form class="replyForm" data-feeling-id="${feeling.id}" onsubmit="submitReply(event, ${feeling.id})">
                                            <input type="text" id="replyName_${feeling.id}" name="replyName" placeholder="Your Name (Optional)" autocomplete="off">
                                            <textarea id="replyContent_${feeling.id}" name="replyContent" placeholder="Write a reply..." required></textarea>
                                            <button type="submit">Reply</button>
                                        </form>
                                    </div>
                                `;
                                feelingsContainer.appendChild(feelingDiv);
                                recordView(feeling.id);
                            });
                            updateTimestamps();
                        } catch (error) {
                            console.error('JSON Parsing Error:', error, text);
                        }
                    }).catch(error => console.error('Error:', error));
            }

            window.submitReply = function(event, feelingId) {
                event.preventDefault();
                const form = event.target;
                const name = form.querySelector(`#replyName_${feelingId}`).value || 'Anonymous';
                const content = form.querySelector(`#replyContent_${feelingId}`).value;

                fetch('submit_reply.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ feelingId, name, content })
                }).then(response => response.text()).then(text => {
                    try {
                        const data = JSON.parse(text);
                        if (data.success) {
                            fetchFeelings();
                        } else {
                            console.error(data.error);
                        }
                    } catch (error) {
                        console.error('JSON Parsing Error:', error, text);
                    }
                }).catch(error => console.error('Error:', error));
            };

            window.likeFeeling = function(feelingId) {
                fetch('like_feeling.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ feelingId })
                }).then(response => response.text()).then(text => {
                    try {
                        const data = JSON.parse(text);
                        if (data.success) {
                            const likeCount = document.getElementById(`likeCount_${feelingId}`);
                            likeCount.textContent = data.newLikes;
                        } else {
                            console.error(data.error);
                        }
                    } catch (error) {
                        console.error('JSON Parsing Error:', error, text);
                    }
                }).catch(error => console.error('Error:', error));
            };

            function recordView(feelingId) {
                fetch('record_view.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ feelingId })
                }).then(response => response.text()).then(text => {
                    try {
                        const data = JSON.parse(text);
                        if (data.success) {
                            const viewCount = document.getElementById(`viewCount_${feelingId}`);
                            viewCount.textContent = data.newViews;
                        } else {
                            console.error(data.error);
                        }
                    } catch (error) {
                        console.error('JSON Parsing Error:', error, text);
                    }
                }).catch(error => console.error('Error:', error));
            }

            function updateTimestamps() {
                const timeElements = document.querySelectorAll('.time-ago');
                timeElements.forEach(element => {
                    const timestamp = new Date(element.dataset.timestamp);
                    const now = new Date();
                    const diffInSeconds = Math.floor((now - timestamp) / 1000);

                    let timeAgo = '';
                    if (diffInSeconds < 60) {
                        timeAgo = `${diffInSeconds} seconds ago`;
                    } else if (diffInSeconds < 3600) {
                        timeAgo = `${Math.floor(diffInSeconds / 60)} minutes ago`;
                    } else if (diffInSeconds < 86400) {
                        timeAgo = `${Math.floor(diffInSeconds / 60)} minutes ago`;
                    } else if (diffInSeconds < 86400) {
                        timeAgo = `${Math.floor(diffInSeconds / 3600)} hours ago`;
                    } else {
                        timeAgo = `${Math.floor(diffInSeconds / 86400)} days ago`;
                    }
                    element.textContent = timeAgo;
                
                });
            }
            

            fetchFeelings();
        });
    </script>
</body>
</html>