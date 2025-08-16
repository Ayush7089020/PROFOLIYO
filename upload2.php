<?php
// upload-extract.php
// Upload a ZIP and extract it in the htdocs folder (document root).
// ⚠ For local/dev use only. Add authentication if on live server.

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check file upload
    if (!isset($_FILES['zip_file']) || $_FILES['zip_file']['error'] !== 0) {
        die("❌ Error: File upload failed.");
    }

    $fileName = $_FILES['zip_file']['name'];
    $fileTmp  = $_FILES['zip_file']['tmp_name'];

    // Validate extension
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if ($ext !== 'zip') {
        die("❌ Error: Only .zip files are allowed.");
    }

    // Yahan apna custom path specify karo (like env ke liye karte ho)
    // Example: htdocs se ek folder upar ke liye:
    $uploadDir = __DIR__ . '/';  // yahan change kar sakte ho jahan upload karna hai

    // Final destination path
    $destination = $uploadDir . basename($fileName);

    // Move uploaded file
    if (!move_uploaded_file($fileTmp, $destination)) {
        die("❌ Error: Cannot move uploaded file.");
    }

    // Extract ZIP file to same directory
    $zip = new ZipArchive;
    if ($zip->open($destination) === TRUE) {
        $zip->extractTo($uploadDir);
        $zip->close();
        unlink($destination);
        echo "✅ Extraction completed successfully!";
    } else {
        unlink($destination);
        die("❌ Error: Cannot open ZIP file.");
    }

    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Upload & Extract ZIP</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
body { font-family: Arial, sans-serif; background: #f3f3f3; padding: 30px; }
.container { max-width: 400px; background: white; padding: 20px; margin: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
button, input[type=file] { width: 100%; padding: 10px; margin-top: 10px; font-size: 16px; }
h2 { text-align: center; }
</style>
</head>
<body>
<div class="container">
    <h2>Upload & Extract ZIP</h2>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="zip_file" accept=".zip" required />
        <button type="submit">Upload & Extract</button>
    </form>
</div>
</body>
</html>