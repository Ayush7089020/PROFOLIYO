<?php
// Stream.php - Optimized for Byte-Range Streaming

// Include the database connection file
require_once 'db_connect.php';

// Log file path
$log_file = __DIR__ . '/logs.log';

// Function to write logs
function writeLog($message) {
    global $log_file;
    file_put_contents($log_file, "[" . date('Y-m-d H:i:s') . "] " . $message . PHP_EOL, FILE_APPEND);
}

// Check if a project ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    writeLog("Error: Invalid project ID provided.");
    die("Invalid project ID.");
}

$project_id = $_GET['id'];
writeLog("Request received for project ID: $project_id");

$source_url = ""; // The URL or file path of the video source

try {
    $sql = "SELECT video_link FROM projects WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $project_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && !empty($result['video_link'])) {
        $db_file_url = $result['video_link'];
        writeLog("Database 'video_link' for ID $project_id: $db_file_url");

        // Check if Google Drive link
        if (preg_match('/\/d\/([a-zA-Z0-9_-]+)\//', $db_file_url, $matches)) {
            $file_id = $matches[1];
            $source_url = "https://drive.google.com/uc?export=download&id=" . $file_id;
            writeLog("Detected Google Drive link. Direct link: $source_url");
        } else {
            // Normal server file
            $file_name = basename($db_file_url);
            $file_path = __DIR__ . '/uploads/' . $file_name;
            if (file_exists($file_path)) {
                $source_url = $file_path;
                writeLog("Normal server file found: $file_path");
            } else {
                writeLog("Error: File not found on server: $file_path");
                die("Video file not found on the server.");
            }
        }
    } else {
        writeLog("Error: Project ID $project_id not found in DB.");
        die("Project not found.");
    }
} catch (PDOException $e) {
    writeLog("Database error: " . $e->getMessage());
    die("Database error: " . $e->getMessage());
}

if (empty($source_url)) {
    writeLog("Error: No video URL to stream.");
    die("No video available.");
}

// --- Unified Streaming Logic ---
writeLog("Starting unified stream for: $source_url");

$is_local_file = strpos($source_url, __DIR__) === 0;

if ($is_local_file) {
    $filesize = filesize($source_url);
} else {
    // For Google Drive, get file size from the HTTP headers
    $headers = get_headers($source_url, 1);
    if (!isset($headers['Content-Length'])) {
        http_response_code(500);
        writeLog("Cannot determine file size from source.");
        die("Error: Cannot get video size.");
    }
    $filesize = intval($headers['Content-Length']);
}

$mime_type = 'video/mp4'; // Assuming all are MP4s for simplicity

$start = 0;
$end = $filesize - 1;
$length = $filesize;
$status_code = 200;

if (isset($_SERVER['HTTP_RANGE'])) {
    $range = $_SERVER['HTTP_RANGE'];
    list(, $range_value) = explode('=', $range, 2);
    list($start, $end_str) = explode('-', $range_value);

    $start = intval($start);
    if ($end_str !== '') {
        $end = intval($end_str);
    }
    
    // Validate range
    if ($start > $end || $start >= $filesize || ($end !== '' && $end >= $filesize)) {
        header("Content-Range: bytes */$filesize");
        http_response_code(416); // Range Not Satisfiable
        exit;
    }

    $length = $end - $start + 1;
    $status_code = 206; // Partial Content
    writeLog("Partial content request: $start-$end");
}

header("Content-Type: " . $mime_type);
header("Content-Length: " . $length);
header("Content-Range: bytes $start-$end/$filesize");
header("Accept-Ranges: bytes");
http_response_code($status_code);

if ($is_local_file) {
    $handle = fopen($source_url, 'rb');
    fseek($handle, $start);
    $buffer = 1024 * 256; // 256KB buffer for efficiency
    while (!feof($handle) && ($p = ftell($handle)) <= $end) {
        if ($p + $buffer > $end) {
            $buffer = $end - $p + 1;
        }
        echo fread($handle, $buffer);
        flush();
    }
    fclose($handle);
} else {
    // Stream Google Drive link with range
    $headers = ["Range: bytes=$start-$end"];
    $context = stream_context_create([
        "http" => ["method" => "GET", "header" => implode("\r\n", $headers)]
    ]);
    $stream = fopen($source_url, 'rb', false, $context);
    if (!$stream) {
        http_response_code(500);
        writeLog("Cannot open Google Drive link for streaming.");
        die();
    }
    fpassthru($stream);
    fclose($stream);
}

writeLog("Streaming finished for: $source_url");
exit;
?>
