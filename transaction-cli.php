<?php
// Check if the correct number of command-line arguments is provided
if ($argc !== 3) {
    echo "Usage: php transaction-cli.php {references_id} {status}\n";
    exit(1);
}

$referencesId = $argv[1];
$status = $argv[2];

// Prepare the data to send in JSON format
$data = json_encode([
    'references_id' => $referencesId,
    'status' => $status,
]);

// Define the URL to send the POST request to
$url = 'http://localhost/transactions';

// Initialize cURL session
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

// Execute cURL session and get the response
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch) . "\n";
} else {
    // Display the response from the server
    echo "Response:\n";
    echo $response . "\n";
}

// Close cURL session
curl_close($ch);
