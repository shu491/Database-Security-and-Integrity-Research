<?php
$servername = "localhost";
$username = "root"; // Replace with your MySQL username
$password = "shuann@2003"; // Replace with your MySQL password
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

// Vulnerable SQL query with adjusted parentheses to fix the injection
$sql = "SELECT * FROM Users WHERE (Username = '$user' OR '$user' = '') AND (Password = '$pass' OR '$pass' = 'anything')";

// Print the query to debug
echo "Query: " . $sql . "<br><br>";  // Debugging line

$result = $conn->query($sql);

// Display results
if ($result->num_rows > 0) {
    echo "Login successful! Welcome, " . htmlspecialchars($user);
} else {
    echo "Invalid credentials!";
}

$conn->close();
?>
