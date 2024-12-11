<?php
$servername = "localhost";
$username = "root"; 
$password = "shuann@2003"; 
$dbname = "test_db";

// Connect to MySQL database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user input
$user = $_POST['username'];
$pass = $_POST['password'];

// Vulnerable query
$sql = "SELECT * FROM Users WHERE Username = '$user' AND Password = '$pass'";

// Print the query to debug
echo "Query: " . $sql . "<br><br>";

$result = $conn->query($sql);

// Check if the query executed successfully
if ($result === false) {
    echo "Error in query execution: " . $conn->error . "<br>";
} else {
    // Display results
    if ($result->num_rows > 0) {
        echo "Login successful! Welcome, " . htmlspecialchars($user);
    } else {
        echo "Invalid credentials!";
    }
}

$conn->close();
?>
