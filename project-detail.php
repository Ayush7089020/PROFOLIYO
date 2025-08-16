<?php
// Include the database connection file
require_once 'db_connect.php';

$project_title = 'Project not found';
$project_category = '';
$video_link = 'about:blank';

// Check if the 'id' parameter is set in the URL
if (!isset($_GET['id'])) {
    die("Something went wrong. Please provide a project ID.");
}

$project_id = $_GET['id'];

// Check if the ID is a valid number
if (!is_numeric($project_id)) {
    die("Something went wrong. The project ID is invalid.");
}

try {
    // Prepare a safe SQL query
    $sql = "SELECT title, category, video_link FROM projects WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $project_id, PDO::PARAM_INT);
    $stmt->execute();
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        die("Project not found.");
    }

    // Extract and sanitize data
    $project_title = htmlspecialchars($project['title']);
    $project_category = htmlspecialchars($project['category']);
    // Construct the URL to your streaming script
    $video_link = "stream.php?id=" . urlencode($project_id);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $project_title; ?> - Project Video</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">


    <style>
        :root {
            --bg-light-1: #f0f2f5;
            --bg-card: #ffffff;
            --card-shadow: rgba(0, 0, 0, 0.08);
            --primary-color: #007bff;
            --text-dark: #333;
            --text-medium: #666;
            --border-light: #e0e0e0;
        }

        body {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
            background: var(--bg-light-1);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            flex-direction: column;
            padding: 2rem 1rem;
            color: var(--text-dark);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .container {
            display: none; /* Hidden by default */
            width: 100%;
            max-width: 900px;
        }

        h1 {
            font-size: 2.2rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            text-align: center;
        }

        p {
            font-size: 1rem;
            color: var(--text-medium);
            text-align: center;
            margin-top: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .video-card {
            width: 100%;
            background: var(--bg-card);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 12px 24px var(--card-shadow);
            margin-bottom: 2rem;
            border: 1px solid var(--border-light);
        }

        .plyr {
            width: 100%;
            border-radius: 16px;
        }

        /* Ensure the Plyr container is responsive within the card */
        .plyr--video {
            padding-top: 56.25%; /* 16:9 Aspect Ratio */
            position: relative;
            background: #000; /* Black background for video player */
        }
        
        .plyr--video .plyr__video-wrapper {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        /* Loading spinner */
        .loading-spinner {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 5px solid rgba(255, 255, 255, 0.3);
            border-top: 5px solid var(--primary-color);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        .download-btn-container {
            text-align: center;
            margin-top: 1rem;
        }

        .download-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            font-size: 1rem;
            font-weight: 500;
            color: white;
            background-color: var(--primary-color);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2);
        }

        .download-btn:hover {
            background-color: #0056b3;
            box-shadow: 0 6px 16px rgba(0, 123, 255, 0.3);
            transform: translateY(-2px);
        }

        .download-btn i {
            margin-right: 8px;
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 1.8rem;
            }

            .download-btn {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</head>

<body>
    <div class="loading-spinner" id="loading-spinner"></div>

    <div class="container" id="main-content">
        <h1><?php echo $project_title; ?></h1>
        <p>Category: <?php echo $project_category; ?></p>

        <div class="video-card">
            <video id="player" playsinline controls preload="auto">
                <source src="<?php echo $video_link; ?>" type="video/mp4" />
                Your browser does not support the video tag.
            </video>
        </div>

        <p>Here you can add more description about the video content.</p>

        <div class="download-btn-container">
            <a href="<?php echo $video_link; ?>" download class="download-btn">
                <i class="fas fa-download"></i> Download Video
            </a>
        </div>
    </div>
    
    <script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const player = new Plyr('#player', {
                controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'fullscreen'],
                ratio: '16:9'
            });

            const loadingSpinner = document.getElementById('loading-spinner');
            const mainContent = document.getElementById('main-content');
            const videoElement = document.getElementById('player');
            
            // Listen for 'loadeddata' event
            // This event fires when the first frame of the video has finished loading
            player.on('loadeddata', () => {
                loadingSpinner.style.display = 'none'; // Hide the spinner
                mainContent.style.display = 'block'; // Show the content
                console.log('Video loaded and content is now visible.');
            });

            // Fallback for errors or if the event doesn't fire as expected
            // This will ensure the content is shown even if there's an issue with the video
            setTimeout(() => {
                if (mainContent.style.display === 'none') {
                    loadingSpinner.style.display = 'none';
                    mainContent.style.display = 'block';
                    console.log('Timeout triggered, showing content.');
                }
            }, 5000); // 5-second timeout
        });
    </script>
</body>

</html>
