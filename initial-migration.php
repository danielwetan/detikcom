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
    // Connect to MySQL server
    $conn = new PDO(
        "mysql:host={$dbConfig['host']};charset=utf8mb4",
        $dbConfig['username'],
        $dbConfig['password']
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Read and execute the SQL commands from the SQL file
    $sqlCommands = file_get_contents('initial_db.sql');
    $conn->exec($sqlCommands);

    echo "Database and table created successfully.\n";
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Close the database connection
$conn = null;
