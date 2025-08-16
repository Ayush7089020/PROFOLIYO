<?php
// full-htdocs-backup.php
// Creates a zip of the ENTIRE folder where this script is placed (e.g., htdocs) and downloads it.
// ⚠ For LOCAL/DEV USE ONLY – Do not put this on a public server without authentication.

set_time_limit(0);

// Current folder (htdocs)
$rootPath = realpath(__DIR__);

// Temp zip file path
$zipFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'htdocs_backup_' . date('Y-m-d_H-i-s') . '.zip';

// Create Zip
$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    exit("❌ Cannot create zip file");
}

// Recursively add all files/folders to zip
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootPath, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($files as $file) {
    $filePath = $file->getRealPath();
    $relativePath = substr($filePath, strlen($rootPath) + 1);

    if ($file->isDir()) {
        $zip->addEmptyDir($relativePath);
    } else {
        $zip->addFile($filePath, $relativePath);
    }
}

$zip->close();

// Send to browser
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="htdocs_backup.zip"');
header('Content-Length: ' . filesize($zipFile));
readfile($zipFile);

// Remove temp file
unlink($zipFile);
exit;
?>