<?php
// upload_video.php

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'file_url' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['video_file'])) {
    $uploadDir = 'uploads/';
    
    // Create uploads directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $file = $_FILES['video_file'];
    $fileName = basename($file['name']);
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'avi', 'mkv', 'mp3', 'wav', 'pdf', 'doc', 'docx', 'txt', 'zip', 'rar'];
    $maxFileSize = 500 * 1024 * 1024; // Increased to 500MB

    if (!in_array($fileExt, $allowedTypes)) {
        $response['message'] = 'Invalid file type. Only JPG, JPEG, PNG, GIF, MP4, MOV, AVI, MKV, MP3, WAV, PDF, DOC, DOCX, TXT, ZIP, RAR are allowed.';
    } elseif ($fileError !== 0) {
        $response['message'] = 'There was an error uploading your file.';
    } elseif ($fileSize > $maxFileSize) {
        $response['message'] = 'File is too large (max 500MB).';
    } else {
        $uniqueFileName = uniqid('', true) . '.' . $fileExt;
        $fileDestination = $uploadDir . $uniqueFileName;
        
        if (move_uploaded_file($fileTmpName, $fileDestination)) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $domainName = $_SERVER['HTTP_HOST'];
            $fileUrl = $protocol . $domainName . '/' . $fileDestination;
            
            $response['success'] = true;
            $response['message'] = 'File uploaded successfully.';
            $response['file_url'] = $fileUrl;
        } else {
            $response['message'] = 'Failed to move the uploaded file.';
        }
    }
} else {
    $response['message'] = 'No file was uploaded.';
}

echo json_encode($response, JSON_UNESCAPED_SLASHES);

?>
