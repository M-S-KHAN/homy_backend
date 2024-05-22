<?php

include_once '../../config/database.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Get JSON data from the request body
$data = json_decode(file_get_contents("php://input"));

// Validate the received data
if (empty($data->user_id) || empty($data->property_id) || empty($data->bid_amount)) {
    http_response_code(400);
    echo json_encode(array("message" => "Missing required bid details."));
    exit();
}

// Prepare a query to insert the bid into the database
$query = "INSERT INTO bids (property_id, user_id, bid_amount, message) VALUES (:property_id, :user_id, :bid_amount, :message)";
$stmt = $pdo->prepare($query);

// Bind parameters to the prepared statement
$stmt->bindParam(':property_id', $data->property_id, PDO::PARAM_INT);
$stmt->bindParam(':user_id', $data->user_id, PDO::PARAM_INT);
$stmt->bindParam(':bid_amount', $data->bid_amount);
$stmt->bindParam(':message', $data->message);

try {
    // Execute the statement and check if the insert was successful
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(array("message" => "Bid successfully created."));
    } else {
        throw new Exception("Unable to create bid.");
    }
} catch (PDOException $e) {
    http_response_code(503); // Service unavailable
    echo json_encode(array("message" => "Failed to create bid due to a database error.", "error" => $e->getMessage()));
} catch (Exception $e) {
    http_response_code(500); // Internal server error
    echo json_encode(array("message" => $e->getMessage()));
}
