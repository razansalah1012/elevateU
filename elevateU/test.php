<?php
require_once 'DBController.php';
$db = new DBController();

echo "Attempting to open database connection...\n";
if ($db->openConnection()) {
    echo "Database connection opened successfully.\n";

    $query = "SELECT 'test_data' AS my_test;";
    echo "Executing query: " . $query . "\n";
    $result = $db->select($query);

    if ($result) {
        echo "Query executed successfully.\n";
        while ($row = $result->fetch_assoc()) {
            print_r($row);
        }
        $result->free_result();
    } else {
        echo "Query failed.\n";
    }
    echo "Closing database connection...\n";
    $db->closeConnection();
    echo "Database connection closed.\n";

} else {
    echo "Failed to open database connection.\n";
}
?> 