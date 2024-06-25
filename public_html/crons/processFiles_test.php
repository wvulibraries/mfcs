<?php
include 'databaseconnection.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Set up error logging
ini_set('log_errors', '1');
ini_set('error_log', '/tmp/log/php_errors.log');

// Log start of script
file_put_contents('/tmp/log/processFiles.log', "Script started at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

try {
    // Log database connection attempt
    file_put_contents('/tmp/log/processFiles.log', "Attempting to connect to database...\n", FILE_APPEND);

    // Assuming you have a database connection class
    $db = new DatabaseConnection(); // Replace with your actual database connection code

    // Check if the database connection is null
    if ($db === null) {
        file_put_contents('/tmp/log/processFiles.log', "Database connection is null\n", FILE_APPEND);
    } else {
        file_put_contents('/tmp/log/processFiles.log', "Database connection established\n", FILE_APPEND);
    }

    // Example of using the escape function
    $result = $db->escape("test");
    file_put_contents('/tmp/log/processFiles.log', "Database escape result: " . $result . "\n", FILE_APPEND);

} catch (Exception $e) {
    // Log any exceptions
    file_put_contents('/tmp/log/processFiles.log', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
}

// Log end of script
file_put_contents('/tmp/log/processFiles.log', "Script ended at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
?>