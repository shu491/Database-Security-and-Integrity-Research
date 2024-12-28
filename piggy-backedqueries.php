<?php
$servername = "localhost";
$username = "root"; // Replace with own MySQL username
$password = "shuann@2003"; // Replace with own MySQL password
$dbname = "dbms_sqlia";

// Connect to MySQL database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user input
$user = $_POST['username'];
$pass = $_POST['password'];

// Capture start time
$start_time = microtime(true);

// Vulnerable query (with piggybacked query)
$sql = "SELECT * FROM Usersforpiggytest WHERE Username = '$user' AND Password = '$pass'; DROP TABLE Usersforpiggytest; --";

// Print the query to debug
echo "Query: " . $sql . "<br><br>";

// Execute the query using multi_query
if ($conn->multi_query($sql)) {
    do {
        // Store result
        if ($result = $conn->store_result()) {
            // Display results
            while ($row = $result->fetch_assoc()) {
                foreach ($row as $column => $value) {
                    echo $column . ": " . $value . "<br>";
                }
                echo "<br>";
            }
            $result->free();
        }
    } while ($conn->next_result()); // Move to the next query result (if any)
} else {
    echo "Error in query execution: " . $conn->error . "<br>";
}

// Capture end time
$end_time = microtime(true);
$execution_time = $end_time - $start_time;

// Display execution time
echo "<br>Execution Time: " . number_format($execution_time, 6) . " seconds";

$conn->close();
?>
