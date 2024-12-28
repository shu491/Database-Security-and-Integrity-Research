<?php
$servername = "localhost";
$username = "root"; 
$password = "";   //Please use your password for MySQL
$dbname = "dbms_sqlia";

// Connect to MySQL database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user input and decode URL-encoded input
$user = urldecode($_POST['username']);
$pass = $_POST['password'];

// Capture start time
$start_time = microtime(true);

// Vulnerable query with potential SQL Injection (tautology-based attack)
$sql = "SELECT * FROM Users WHERE Username = '$user' AND Password = '$pass'";
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
        echo "Login successful! Welcome, " . htmlspecialchars($user) . "<br><br>";
    } else {
        echo "Invalid credentials!";
    }
}
echo "<br>Execution Time: " . number_format($execution_time, 6) . " seconds";
$conn->close();
?>
