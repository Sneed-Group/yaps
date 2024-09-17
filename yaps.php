<?php
// Get the target URL from the query parameters
if (!isset($_GET['targetUrl']) || empty($_GET['targetUrl'])) {
    http_response_code(400); // Bad Request
    echo 'Error: targetUrl parameter is required.';
    exit();
}

$targetUrl = filter_var($_GET['targetUrl'], FILTER_SANITIZE_URL);

// Validate the URL
if (!filter_var($targetUrl, FILTER_VALIDATE_URL)) {
    http_response_code(400); // Bad Request
    echo 'Error: Invalid targetUrl.';
    exit();
}

// Initialize cURL session
$ch = curl_init();

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

// Get the request data
$data = file_get_contents('php://input');

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $targetUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

if ($method === 'POST') {
    curl_setopt($ch, CURLOPT_POST, true); // Set the request method to POST
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // Set the POST fields
} elseif ($method === 'GET') {
    curl_setopt($ch, CURLOPT_HTTPGET, true); // Set the request method to GET
} else {
    http_response_code(405); // Method Not Allowed
    echo 'Error: Unsupported request method.';
    exit();
}

// Forward headers from the original request
$headers = [];
foreach (getallheaders() as $key => $value) {
    if (strtolower($key) != 'host' && strtolower($key) != 'content-length') {
        $headers[] = "$key: $value";
    }
}
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// Execute the request and get the response
$response = curl_exec($ch);

// Check for cURL errors
if ($response === false) {
    http_response_code(500); // Internal Server Error
    echo 'Error: ' . curl_error($ch);
    curl_close($ch);
    exit();
}

// Get the HTTP response code
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Close the cURL session
curl_close($ch);

// Set the response code
http_response_code($httpCode);

// Output the response
echo $response;
?>
