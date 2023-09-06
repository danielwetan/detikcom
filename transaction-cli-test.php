<?php

function testTransactionCli($referencesId, $status)
{
    $command = "php transaction-cli.php $referencesId $status";
    exec($command, $output, $returnCode);

    if ($returnCode === 0) {
        if (in_array("Response:", $output)) {
            echo "Test Passed\n";
        } else {
            echo "Test Failed: Output does not contain 'Response:'\n";
        }
    } else {
        echo "Test Failed: Non-zero return code ($returnCode)\n";
    }
}

testTransactionCli('12345', 'paid');
