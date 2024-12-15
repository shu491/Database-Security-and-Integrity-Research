<?php

// Database connection
$servername = "localhost";
$username = "root";
$password = ""; // YOUR PASSWORD
$dbname = "test_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to log queries and execution times
function logQuery($query, $params, $executionTime, $message = "Normal Query") {
    $logfile = 'query_log.txt';
    // Log the query along with the execution time and params
    $logEntry = "[" . date("Y-m-d H:i:s") . "] " . $message . " - Query: $query - Params: " . json_encode($params) . 
                " - Execution Time: " . number_format($executionTime, 6) . " seconds\n";
    file_put_contents($logfile, $logEntry, FILE_APPEND);
}

// Function to detect and handle time-based delay from 'WAITFOR DELAY' in the input
function detectinjection($input) {
    // Check if input contains WAITFOR DELAY pattern
    if (preg_match('/WAITFOR DELAY/i', $input)) {
        // Log the actual query with a message indicating the detection of an attack
        logQuery("Potential SQL Injection detected (WAITFOR DELAY) in input", ['input' => $input], 0, 'Malicious Input Detected');
        
        // Simulate the delay caused by the attacker
        sleep(5);  // Simulate the 5 seconds delay to mimic the attack

        return true;  // Return true to indicate a malicious input
    }
    return false;  // Return false if no attack is detected
}

// Function to authenticate user
function getUser($username, $password) {
    global $conn;

    // Start timing
    $startTime = microtime(true);

    // SQL query
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password';";

    // Log the actual query, regardless of whether it's malicious
    logQuery($query, ['username' => $username, 'password' => $password], 0);

    // Execute the query
    $result = $conn->query($query);
    $executionTime = microtime(true) - $startTime;

    // Log the query execution time
    logQuery($query, ['username' => $username, 'password' => $password], $executionTime);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        return $user; // Return user data if authenticated
    } else {
        return null; // No user found
    }
}

// Get user input
echo "Enter username: ";
$username = trim(fgets(STDIN));

echo "Enter password: ";
$password = trim(fgets(STDIN));

// Check for time-based attack using 'WAITFOR DELAY'
$isMalicious = detectinjection($username);

// Authenticate the user if no attack is detected
if ($isMalicious) {
    echo "Time-based SQL injection detected. Query delayed by 5 seconds.\n";
    exit;  // Stop further processing since this is an attack
}

$user = getUser($username, $password);
if ($user) {
    echo "Login successful. Welcome, " . $user['username'] . ".\n";
} else {
    echo "Invalid username or password.\n";
}

$conn->close();
?>
