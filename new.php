<?php

// Database connection
$servername = "localhost";
$username = "root";
$password = "Bohe1209!";
$dbname = "test_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to log queries and execution times
function logQuery($query, $params, $executionTime, $message = "Normal Query") {
    $logfile = 'query_log.txt';
    $logEntry = "[" . date("Y-m-d H:i:s") . "] " . $message . " - Query: $query - Params: " . json_encode($params) . 
                " - Execution Time: " . number_format($executionTime, 6) . " seconds\n";
    file_put_contents($logfile, $logEntry, FILE_APPEND);
}

//Function to detect malicious input for stored procedure case
function detectMaliciousInput($input) {
    $malicious = ['/;\s*DROP\s+TABLE\s+/i',   // Detects the pattern '; DROP TABLE ...'
                '/;\s*--/i',                // Detects the pattern '; --' (SQL comment after semicolon)
    ];

    // Check input against each malicious pattern
    foreach ($malicious as $pattern) {
        if (preg_match($pattern, $input)) {
            return true; // Malicious input detected
        }
    }
    return false; // No malicious input detected
}

// Function to authenticate user 
function authenticateUser($username, $password) {
    global $conn;
    
    //start to record time
    $startTime = microtime(true);

    // This is a stored procedure call
    $storedprocedure = "CALL isAuthenticated('$username', '$password');";
    //sql query
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password';";

    echo "\nExecuting Stored Procedure Call: " . $storedprocedure . "\n";
    echo "Executing SQL Query Inside Stored Procedure: " . $query . "\n";

    $result = $conn->query($query);
    $executionTime = microtime(true) - $startTime;
    // Log the query
    logQuery($query, ['username' => $username, 'password' => $password], $executionTime);

    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        return $user; // Return user data if authenticated
    } else {
        return null; // No user found
    }
}

// Get username and password from the user
echo "Username: ";
$usernameInput = trim(fgets(STDIN));

echo "Password: ";
$passwordInput = trim(fgets(STDIN));

// Check for malicious input in username and password
if (detectMaliciousInput($usernameInput) || detectMaliciousInput($passwordInput)) {
    echo "\nSQL Injection Attack Detected. Malicious input blocked.\n";
    echo "Stored Procedure: CALL isAuthenticated('$usernameInput', '$passwordInput');\n";
    $detectedquery = "SELECT * FROM users WHERE username = '$usernameInput' AND password = '$passwordInput'";
    echo "Query: $detectedquery\n";
    
    // Log the malicious attempt
    logQuery(
        $detectedquery,
        ['username' => $usernameInput, 'password' => $passwordInput],
        0,
        "Malicious Query Detected"
    );
    exit; // Stop further processing if malicious input is detected
}

// Authenticate user without using parameterized queries (for demonstration)
$user = authenticateUser($usernameInput, $passwordInput);

if ($user) {
    echo "Login successful. Welcome, " . $user['username'] . ".\n";
} else {
    echo "Invalid username or password.\n";
}

$conn->close();
?>
