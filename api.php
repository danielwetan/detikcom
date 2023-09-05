<?php

// Database configuration
$dbConfig = [
    'host' => 'mysql',       
    'username' => 'root',   
    'password' => 'detikcom_123!@#',
    'database' => 'detikcom',      
    'charset' => 'utf8mb4',       
];

try {
    // Create a PDO database connection
    $conn = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}",
        $dbConfig['username'],
        $dbConfig['password']
    );

    // Set PDO attributes (optional)
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Transaction entity
class Transaction
{
    public $invoice_id;
    public $item_name;
    public $amount;
    public $payment_type;
    public $customer_name;
    public $merchant_id;
}

// Transaction controller
class TransactionController
{
    private $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function getTransaction()
    {
        // Get references_id and merchant_id from request parameters
        $referencesId = $_GET['references_id'] ?? '';
        $merchantId = $_GET['merchant_id'] ?? '';

        // Validate the input parameters (e.g., check for non-empty values)

        if (empty($referencesId) || empty($merchantId)) {
            http_response_code(400);
            echo "Missing or invalid parameters.";
            return;
        }

        // Query the database to retrieve the transaction
        try {
            $query = "SELECT references_id, invoice_id, status FROM transactions WHERE references_id = ? AND merchant_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $referencesId);
            $stmt->bindParam(2, $merchantId);
            $stmt->execute();

            echo "----\n";
            echo $referencesId . "\n";
            echo $merchantId . "\n";

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // Prepare the response data
                $responseData = [
                    'references_id' => $row['references_id'],
                    'invoice_id' => $row['invoice_id'],
                    'status' => $row['status'],
                ];

                // Return the response as JSON
                header("Content-Type: application/json");
                echo json_encode($responseData);
            } else {
                // Transaction not found
                http_response_code(404);
                echo "Transaction not found.";
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo "Error retrieving transaction: " . $e->getMessage();
        }
    }

    public function createTransaction()
    {
        // Get JSON raw input from the request
        $jsonInput = file_get_contents("php://input");

        // Parse the JSON input into an associative array
        $requestData = json_decode($jsonInput, true);

        // Validate and sanitize input data
        // Implement validation and sanitization logic here

        // Check if JSON decoding was successful
        if ($requestData === null) {
            http_response_code(400);
            echo "Invalid JSON data.";
            return;
        }

        // Generate references_id and va_number if payment_type is "virtual_account"
        $referencesId = uniqid(); // You can use your logic to generate this ID
        $vaNumber = null;

        if ($requestData['payment_type'] === 'virtual_account') {
            // Generate va_number if payment_type is "virtual_account"
            $vaNumber = rand(100000, 999999); // Replace with your logic for generating va_number
        }

        // Insert a new transaction into the database
        try {
            $query = "INSERT INTO transactions (invoice_id, item_name, amount, payment_type, customer_name, merchant_id, references_id, va_number, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);

            $default_status = "active";

            $stmt->bindParam(1, $requestData['invoice_id']);
            $stmt->bindParam(2, $requestData['item_name']);
            $stmt->bindParam(3, $requestData['amount']);
            $stmt->bindParam(4, $requestData['payment_type']);
            $stmt->bindParam(5, $requestData['customer_name']);
            $stmt->bindParam(6, $requestData['merchant_id']);
            $stmt->bindParam(7, $referencesId);
            $stmt->bindParam(8, $vaNumber);
            $stmt->bindParam(9, $default_status);

            $stmt->execute();

            http_response_code(201);
            
            // Prepare the response data
            $responseData = [
                'references_id' => $referencesId,
                'va_number' => $vaNumber
            ];

            // Return the response as JSON
            header("Content-Type: application/json");
            echo json_encode($responseData);
        } catch (PDOException $e) {
            http_response_code(500);
            echo "Error creating transaction: " . $e->getMessage();
        }
    }

    public function updateTransactionStatus($referencesId)
    {
        try {
            $query = "UPDATE transactions SET status = ? WHERE references_id = ?";
            $stmt = $this->conn->prepare($query);
            $newStatus = 'updated_status'; // Replace with the new status value
            $stmt->bindParam(1, $newStatus);
            $stmt->bindParam(2, $referencesId);
            $stmt->execute();

            http_response_code(200);
            echo "Transaction status updated successfully!";
        } catch (PDOException $e) {
            http_response_code(500);
            echo "Error updating transaction status: " . $e->getMessage();
        }
    }
}

$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

$controller = new TransactionController($conn);

if ($requestMethod === 'GET' && $requestUri === '/transactions') {
    $controller->getTransaction();
} elseif ($requestMethod === 'POST' && $requestUri === '/transactions') {
    $controller->createTransaction();
} elseif ($requestMethod === 'POST' && $requestUri === '/transactions') {
    $controller->createTransaction();
} else {
    http_response_code(404);
    echo "Not Found";
}
