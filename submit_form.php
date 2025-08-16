<?php
// submit_form.php

header('Content-Type: application/json');
require_once 'db_connect.php';

$response = [
    'success' => false,
    'message' => ''
];

// Enable error reporting for debugging (you can disable this in production)
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'logs/php_error_log.txt'); // Path to your error log file
error_reporting(E_ALL);

// Check if the request method is POST and the content type is JSON
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || strpos($_SERVER['CONTENT_TYPE'], 'application/json') === false) {
    http_response_code(400);
    $response['message'] = 'Invalid request.';
    echo json_encode($response);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

// Basic validation for required fields
if (empty($data['fullName']) || empty($data['email']) || empty($data['country']) || empty($data['category']) || empty($data['payment']) || empty($data['message'])) {
    $response['message'] = 'Missing required fields.';
    echo json_encode($response);
    exit();
}

// Sanitize and validate data
$fullName = trim($data['fullName']);
$email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
$phone = trim($data['phone']);
$country = trim($data['country']);
$category = trim($data['category']);
$payment = trim($data['payment']);
$paymentDetails = isset($data['paymentDetails']) ? trim($data['paymentDetails']) : null;
$budget = isset($data['budget']) && is_numeric($data['budget']) ? (float)$data['budget'] : null;
$message = trim($data['message']);
$referenceLink = trim($data['referenceLink']);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Invalid email format.';
    echo json_encode($response);
    exit();
}

try {
    $stmt = $pdo->prepare("INSERT INTO contacts (full_name, email, phone_number, country, service_category, payment_method, payment_details, budget, message, reference_link) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $fullName,
        $email,
        $phone,
        $country,
        $category,
        $payment,
        $paymentDetails,
        $budget,
        $message,
        $referenceLink
    ]);

    $response['success'] = true;
    $response['message'] = 'Form submitted successfully.';

} catch (PDOException $e) {
    // Log the error to a file
    error_log("Database error: " . $e->getMessage(), 3, 'logs/php_error_log.txt'); // Log the error to a file

    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>