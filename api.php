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
    $conn = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}",
        $dbConfig['username'],
        $dbConfig['password']
    );
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
        header("Content-Type: application/json");
        $this->conn = $conn;
    }

    public function getTransaction()
    {
        $referencesId = $_GET['references_id'] ?? '';
        $merchantId = $_GET['merchant_id'] ?? '';

        if (empty($referencesId) || empty($merchantId)) {
            http_response_code(400);
            $responseMessage = ['msg' => 'Missing or innvalid parameters.'];
            echo json_encode($responseMessage);            
            return;        
        }

        try {
            $query = "SELECT references_id, invoice_id, status FROM transactions WHERE references_id = ? AND merchant_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $referencesId);
            $stmt->bindParam(2, $merchantId);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                http_response_code(404);
                $responseMessage = ['msg' => 'Transaction not found.'];
                echo json_encode($responseMessage);   
                return;        
            }

            $responseData = [
                'references_id' => $row['references_id'],
                'invoice_id' => $row['invoice_id'],
                'status' => $row['status'],
            ];

            echo json_encode($responseData);
        } catch (PDOException $e) {
            http_response_code(500);
            $responseMessage = ['msg' => 'Error retrieving transaction: ' . $e->getMessage()];
            echo json_encode($responseMessage);            
        }
    }

    public function createTransaction()
    {
        $jsonInput = file_get_contents("php://input");
        $requestData = json_decode($jsonInput, true);

        if ($requestData === null) {
            http_response_code(400);
            echo "Invalid JSON data.";
            return;        
        }

        $referencesId = uniqid();
        $vaNumber = null;

        if ($requestData['payment_type'] === 'virtual_account') {
            $vaNumber = rand(100000, 999999);
        }

        try {
            $query = "INSERT INTO transactions (invoice_id, item_name, amount, payment_type, customer_name, merchant_id, references_id, va_number, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);

            $default_status = "pending";

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
            
            $responseData = [
                'references_id' => $referencesId,
                'va_number' => $vaNumber
            ];

            echo json_encode($responseData);
        } catch (PDOException $e) {
            http_response_code(500);
            $responseMessage = ['msg' => 'Error creating transaction: ' . $e->getMessage()];
            echo json_encode($responseMessage);            
        }
    }

    public function updateTransactionStatus()
    {
        $jsonInput = file_get_contents("php://input");
        $requestData = json_decode($jsonInput, true);

        if (empty($requestData['status']) || empty($requestData['references_id'])) {
            http_response_code(400);
            $responseMessage = ['msg' => 'Missing or innvalid parameters.'];
            echo json_encode($responseMessage);    
            return;        
        }

        if (!in_array($requestData['status'], ['pending', 'paid', 'failed'])) {
            http_response_code(400);
            echo "Invalid status. Allowed values are: pending, paid, failed.";
            return;
        }

        try {
            $query = "UPDATE transactions SET status = ? WHERE references_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $requestData['status']);
            $stmt->bindParam(2, $requestData['references_id']);
            $stmt->execute();

            http_response_code(200);
            $responseMessage = ['msg' => 'Transaction status updated successfully!'];
            echo json_encode($responseMessage);            
        } catch (PDOException $e) {
            http_response_code(500);
            $responseMessage = ['msg' => 'Error updatinng transaction status: ' . $e->getMessage()];
            echo json_encode($responseMessage);            

        }
    }
}


// Routing
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

$controller = new TransactionController($conn);

$routes = [
    [
        'method' => 'GET',
        'path' => '/transactions',
        'handler' => function () use ($controller) {
            $queryParams = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);

            if ($queryParams) {
                parse_str($queryParams, $queryData);
                $_GET = array_merge($_GET, $queryData);
                $controller->getTransaction();
            } else {
                http_response_code(400);
                $responseMessage = ['msg' => 'Missing or invalid parameters.'];
                echo json_encode($responseMessage);
            }
        },
    ],
    [
        'method' => 'POST',
        'path' => '/transactions',
        'handler' => function () use ($controller) {
            $controller->createTransaction();
        },
    ],
    [
        'method' => 'PATCH',
        'path' => '/transactions',
        'handler' => function () use ($controller) {
            $controller->updateTransactionStatus();
        },
    ],
];

// Find a matching route and execute its handler
$matched = false;

foreach ($routes as $route) {
    if ($requestMethod === $route['method'] && strpos($requestUri, $route['path']) === 0) {
        $route['handler']();
        $matched = true;
        break;
    }
}

if (!$matched) {
    http_response_code(404);
    $responseMessage = ['msg' => 'Not Found'];
    echo json_encode($responseMessage);
}