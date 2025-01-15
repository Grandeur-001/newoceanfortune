<?php
session_start();
include 'connection.php';
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log function to write messages to log.txt
function logMessage($message) {
    $logFile = 'log.txt';
    $currentDateTime = date('Y-m-d H:i:s');
    $logMessage = "[{$currentDateTime}] {$message}\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Read and decode the raw JSON input
$requestBody = file_get_contents('php://input');
$data = json_decode($requestBody, true);

// Log the raw input for debugging
logMessage("Raw input: " . $requestBody);

// Validate and retrieve parameters from the request body
if (isset($data['crypto_id'], $data['plan_id'], $data['investment_amount'])) {
    $crypto_id = intval($data['crypto_id']);
    $plan_id = intval($data['plan_id']);
    $investment_amount = floatval($data['investment_amount']);
} else {
    logMessage("Missing required parameters in request body.");
    echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
    exit();
}

// Retrieve user_id from session
$user_id = $_SESSION['user_id'] ?? null;

if ($user_id === null) {
    logMessage("User is not logged in.");
    echo json_encode(['success' => false, 'message' => 'User is not logged in.']);
    exit();
}

logMessage("Received investment request. User ID: {$user_id}, Crypto ID: {$crypto_id}, Plan ID: {$plan_id}, Investment Amount: {$investment_amount}");

// Fetch the investment plan details
$plan_query = "SELECT * FROM investment_plan WHERE id = ?";
if ($stmt = $conn->prepare($plan_query)) {
    $stmt->bind_param("i", $plan_id);
    $stmt->execute();
    $plan_result = $stmt->get_result();

    if ($plan_result->num_rows > 0) {
        $plan = $plan_result->fetch_assoc();
        $min_investment = floatval($plan['minimum']);
        $max_investment = floatval($plan['maximum']);
        $roi = floatval($plan['roi']);

        // Validate investment amount
        if ($investment_amount < $min_investment || $investment_amount > $max_investment) {
            logMessage("Investment validation failed. Amount not within range.");
            echo json_encode(['success' => false, 'message' => "Investment amount must be between {$min_investment} and {$max_investment}."]);
            exit();
        }

        // Insert investment into the database
        $insert_query = "INSERT INTO investments (user_id, plan_id, crypto_id, roi, amount, status, earnings, total_earnings) 
                         VALUES (?, ?, ?, ?, ?, 'pending', 0.00, 0.00)";
        if ($stmt_insert = $conn->prepare($insert_query)) {
            $stmt_insert->bind_param("iiidd", $user_id, $plan_id, $crypto_id, $roi, $investment_amount);
            if ($stmt_insert->execute()) {
                logMessage("Investment added successfully for User ID: {$user_id}.");
                echo json_encode(['success' => true, 'message' => 'Investment added successfully.']);
            } else {
                logMessage("Failed to add investment: " . $stmt_insert->error);
                echo json_encode(['success' => false, 'message' => 'Failed to add investment.']);
            }
            $stmt_insert->close();
        } else {
            logMessage("Error preparing investment query: " . $conn->error);
            echo json_encode(['success' => false, 'message' => 'Error preparing investment query.']);
        }
    } else {
        logMessage("Investment plan not found for Plan ID: {$plan_id}");
        echo json_encode(['success' => false, 'message' => 'Investment plan not found.']);
    }
    $stmt->close();
} else {
    logMessage("Error preparing plan query: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Error preparing plan query.']);
}
?>
