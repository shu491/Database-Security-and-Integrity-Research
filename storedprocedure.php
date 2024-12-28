<?php
$servername = "localhost";
$username = "root"; 
$password = ""; //Please use your password for MySQL
$dbname = "dbms_sqlia";

// Connect to MySQL database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Print the MySQL error execution
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Get user input
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Capture start time
    $start_time = microtime(true);

    // Vulnerable query with potential SQL Injection 
    $sql = "SELECT * FROM users WHERE Username = '$user' AND Password = '$pass'";
    $storedprocedure = "CALL isAuthenticated('$user', '$pass')";

    echo "Query: " . $sql . "<br><br>";
    echo "Stored Procedure: " . $storedprocedure . "<br><br>";

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
        } while ($conn->next_result()); 
    } else {
        // Display error if the query fails
        echo "Error in query execution: " . $conn->error . "<br>";
    }

    // Capture end time
    $end_time = microtime(true);
    $execution_time = $end_time - $start_time;

    echo "<br>Execution Time: " . number_format($execution_time, 6) . " seconds";

} catch (mysqli_sql_exception $e) {
    echo "MySQL Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
    echo "General Error: " . $e->getMessage() . "<br>";
} finally {
    $conn->close();
}
?>
