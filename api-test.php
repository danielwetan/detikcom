<?php

// Define a function to perform the test for a specific endpoint and method
function testEndpoint($method, $endpoint, $requestData = [], $expectedResponse = '', $description = '')
{
    $_SERVER['REQUEST_METHOD'] = $method;
    $_SERVER['REQUEST_URI'] = $endpoint;

    ob_start();

    require_once 'api.php';

    $output = ob_get_clean();

    assert(
        $output === $expectedResponse,
        "Test failed: $description (Expected: $expectedResponse, Actual: $output)"
    );

    echo "Test passed: $description" . PHP_EOL;
}

// Test GET /transactions
testEndpoint(
    'GET',
    '/transactions?references_id=123&merchant_id=456',
    [],
    '{"msg":"Transaction not found."}',
    'GET /transactions'
);

// Test POST /transactions
testEndpoint(
    'POST',
    '/transactions',
    '',
    '',
    'POST /transactions'
);

// Test PATCH /transactions
testEndpoint(
    'PATCH',
    '/transactions',
    '',
    '',
    'PATCH /transactions'
);
