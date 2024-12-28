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

// Vulnerable query
$sql = "SELECT * FROM Users WHERE Username = '$user' AND Password = '$pass'";

// Print the query to debug
echo "Query: " . $sql . "<br><br>";

$result = $conn->query($sql);

// Capture end time
$end_time = microtime(true);
$execution_time = $end_time - $start_time;

// Check if the query executed successfully
if ($result === false) {
    echo "Error in query execution: " . $conn->error . "<br>";
} else {
    // Display results
    if ($result->num_rows > 0) {
        // Print success message
        echo "Login successful! Welcome, " . htmlspecialchars($user) . "<br><br>";
    
         // Iterate over all rows and display each column
        while ($row = $result->fetch_assoc()) {
            foreach ($row as $column => $value) {
                echo $column . ": " . $value . "<br>";
            }
            echo "<br>";
        }
    } else {
        echo "Invalid credentials!";
    }
    
    
}

// Display execution time
echo "<br>Execution Time: " . number_format($execution_time, 6) . " seconds";

$conn->close();
?>
