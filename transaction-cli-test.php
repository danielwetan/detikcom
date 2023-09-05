<?php
// Define a function to run the CLI script and capture its output
function runCliScript($arguments) {
    $command = "php transaction-cli.php " . implode(" ", $arguments);
    return shell_exec($command);
}

// Test Case 1: Test with valid references_id and status
$arguments1 = ['valid_references_id', 'completed'];
$output1 = runCliScript($arguments1);

// Test Case 2: Test with missing arguments
$arguments2 = [];
$output2 = runCliScript($arguments2);

// Test Case 3: Test with invalid number of arguments
$arguments3 = ['references_id'];
$output3 = runCliScript($arguments3);

// Assert the output of each test case
assert($output1 === "Response:\n{\"references_id\":\"valid_references_id\",\"status\":\"completed\"}\n");
assert($output2 === "Usage: php transaction-cli.php {references_id} {status}\n");
assert($output3 === "Usage: php transaction-cli.php {references_id} {status}\n");

echo "All tests passed.\n";
