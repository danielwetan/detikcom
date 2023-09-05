<?php

require 'api.php'; // Include your application code

function assertEqual($actual, $expected, $message = '')
{
    if ($actual === $expected) {
        echo "PASS: $message\n";
    } else {
        echo "FAIL: $message\n";
        echo "Expected: $expected\n";
        echo "Actual: $actual\n";
    }
}

// Create a mock PDO instance for testing
$pdo = new class extends PDO {
    public function __construct() {}
    public function prepare($statement, $options = null) {}
    public function beginTransaction() {}
    public function commit() {}
    public function rollBack() {}
};

// Initialize the controller with the mock PDO
$controller = new TransactionController($pdo);

// Test the getTransaction method
$_GET['references_id'] = '12345';
$_GET['merchant_id'] = 'merchant123';

ob_start(); // Capture output to check response

$controller->getTransaction();

$output = ob_get_clean();

assertEqual(http_response_code(), 200, 'HTTP status code should be 200');
assertEqual(json_decode($output, true), ['references_id' => '12345', 'invoice_id' => '...', 'status' => '...'], 'Response should match expected data');

// Test the createTransaction method
$_POST = [
    'invoice_id' => 'INV123',
    'item_name' => 'Product A',
    'amount' => 100.0,
    'payment_type' => 'virtual_account',
    'customer_name' => 'John Doe',
    'merchant_id' => 'merchant123',
];

ob_start(); // Capture output to check response

$controller->createTransaction();

$output = ob_get_clean();

assertEqual(http_response_code(), 201, 'HTTP status code should be 201');
assertEqual(json_decode($output, true), ['references_id' => '...', 'va_number' => '...'], 'Response should match expected data');

echo "Tests completed.\n";
