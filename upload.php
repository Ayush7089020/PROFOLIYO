<?php
// Include DB connection
require_once 'db_connect.php';

$uploads_folder = 'uploads/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $thumbnail = '';
    $video_link = '';
    $description = !empty($_POST['description']) ? trim($_POST['description']) : '';

    // Handle thumbnail upload
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === 0) {
        $thumb_name = time() . '_thumb_' . basename($_FILES['thumbnail']['name']);
        move_uploaded_file($_FILES['thumbnail']['tmp_name'], $uploads_folder . $thumb_name);
        $thumbnail = $uploads_folder . $thumb_name;
    }

    // Handle video/file upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
        $uploaded_file_name = time() . '_' . basename($_FILES['file']['name']);
        move_uploaded_file($_FILES['file']['tmp_name'], $uploads_folder . $uploaded_file_name);
        $video_link = $uploads_folder . $uploaded_file_name;
    } elseif (!empty($_POST['file_link'])) {
        $video_link = trim($_POST['file_link']);
    }

    // Insert into DB
    $stmt = $pdo->prepare("INSERT INTO projects (title, category, thumbnail, video_link, description) VALUES (:title, :category, :thumbnail, :video_link, :description)");
    $stmt->execute([
        ':title' => $title,
        ':category' => $category,
        ':thumbnail' => $thumbnail,
        ':video_link' => $video_link,
        ':description' => $description
    ]);

    echo "<p style='color:green;'>Project added successfully!</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Add & View Projects</title>
<style>
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { border: 1px solid #aaa; padding: 8px; text-align: center; }
th { background-color: #f0f0f0; }
form { margin-bottom: 30px; }
video, img { max-width: 150px; max-height: 100px; }
</style>
</head>
<body>

<h2>Add New Project</h2>
<form id="projectForm" method="POST" enctype="multipart/form-data">
    <label>Title:</label><br>
    <input type="text" name="title" required><br><br>

    <label>Category:</label><br>
    <input type="text" name="category" required><br><br>

    <!-- Thumbnail section -->
    <label>Thumbnail:</label><br>
    <input type="file" name="thumbnail"><br><br>

    <!-- Video/File section -->
    <label>Video / Any File:</label><br>
    <input type="file" name="file" id="fileInput"><br><br>

    <label>OR Video/File Link:</label><br>
    <input type="url" name="file_link" id="fileLinkInput" placeholder="https://example.com/file"><br><br>

    <!-- Description field -->
    <label>Description (optional):</label><br>
    <textarea name="description" id="descriptionField"></textarea><br><br>

    <button type="submit">Add Project</button>
</form>

<h2>All Projects</h2>
<table>
<thead>
<tr>
<th>ID</th>
<th>Title</th>
<th>Category</th>
<th>Thumbnail</th>
<th>Video/File</th>
<th>Description</th>
</tr>
</thead>
<tbody>
<?php
$stmt = $pdo->query("SELECT * FROM projects ORDER BY id DESC");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
    echo "<td>" . htmlspecialchars($row['title']) . "</td>";
    echo "<td>" . htmlspecialchars($row['category']) . "</td>";

    // Thumbnail display
    if (!empty($row['thumbnail'])) {
        echo "<td><img src='" . htmlspecialchars($row['thumbnail']) . "'></td>";
    } else {
        echo "<td>N/A</td>";
    }

    // Video/File display
    if (!empty($row['video_link'])) {
        $ext = strtolower(pathinfo($row['video_link'], PATHINFO_EXTENSION));
        if (in_array($ext, ['mp4', 'webm', 'ogg'])) {
            echo "<td><video controls><source src='" . htmlspecialchars($row['video_link']) . "' type='video/$ext'>Your browser does not support video.</video></td>";
        } elseif (in_array($ext, ['jpg','jpeg','png','gif','bmp','svg'])) {
            echo "<td><img src='" . htmlspecialchars($row['video_link']) . "'></td>";
        } else {
            echo "<td><a href='" . htmlspecialchars($row['video_link']) . "' target='_blank'>Open File</a></td>";
        }
    } else {
        echo "<td>N/A</td>";
    }

    // Description display
    echo "<td>" . htmlspecialchars($row['description']) . "</td>";

    echo "</tr>";
}
?>
</tbody>
</table>

<script>
const fileInput = document.getElementById('fileInput');
const fileLinkInput = document.getElementById('fileLinkInput');

// Disable link if file selected
fileInput.addEventListener('change', function() {
    if(this.files.length > 0) {
        fileLinkInput.readOnly = true;
    } else {
        fileLinkInput.readOnly = false;
    }
});

// Disable file input if link entered
fileLinkInput.addEventListener('input', function() {
    if(this.value.trim() !== '') {
        fileInput.disabled = true;
    } else {
        fileInput.disabled = false;
    }
});
</script>

</body>
</html>