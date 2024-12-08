<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "shuann@2003";
$dbname = "dbms_sqlia";

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

// Securely fetch user details with execution time
function getUserByUsername($username) {
    global $conn;

    $startTime = microtime(true); // Start timer

    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        logQuery($query, [$username], 0, "Failed to prepare statement");
        die("Database error: " . $conn->error);
    }

    $stmt->bind_param("s", $username);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $rows = $result->num_rows;

        $executionTime = microtime(true) - $startTime; // End timer

        // Detect Logical Incorrect Queries and log with execution time
        if ($rows > 1) {
            logQuery($query, [$username], $executionTime, "Logical Error: Too many rows returned");
            die("Logical Incorrect Query detected: Too many rows returned.");
        } elseif ($rows === 0) {
            logQuery($query, [$username], $executionTime, "Logical Error: No rows found");
            return null;
        }

        $user = $result->fetch_assoc();
        logQuery($query, [$username], $executionTime, "Normal Query");
        $stmt->close();
        return $user;
    } else {
        $executionTime = microtime(true) - $startTime; // End timer on error
        logQuery($query, [$username], $executionTime, "Execution Error");
        die("Database error: " . $stmt->error);
    }
}

// Interactive prompt to get username from the user
echo "Please enter a username to search: ";
$userInput = trim(fgets(STDIN)); // Read input from the user (stdin)

if (empty($userInput)) {
    echo "Username cannot be empty.\n";
    exit;
}

// Fetch user data based on input
$user = getUserByUsername($userInput);

if ($user) {
    echo "User found: " . json_encode($user) . "\n";
} else {
    echo "No user found with this username.\n";
}

$conn->close();
?>
