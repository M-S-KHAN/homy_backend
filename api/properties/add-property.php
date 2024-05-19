<?php

include_once '../../config/database.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->title) &&
    !empty($data->description) &&
    !empty($data->address) &&
    isset($data->lat) &&
    isset($data->lng) &&
    !empty($data->owner_id) &&
    !empty($data->price)
) {
    $query = "INSERT INTO properties (title, description, address, lat, lng, owner_id, price) VALUES (:title, :description, :address, :lat, :lng, :owner_id, :price)";
    $stmt = $pdo->prepare($query);

    $stmt->bindParam(':title', $data->title);
    $stmt->bindParam(':description', $data->description);
    $stmt->bindParam(':address', $data->address);
    $stmt->bindParam(':lat', $data->lat);
    $stmt->bindParam(':lng', $data->lng);
    $stmt->bindParam(':owner_id', $data->owner_id);
    $stmt->bindParam(':price', $data->price);

    try {
        if ($stmt->execute()) {
            $property_id = $pdo->lastInsertId(); // Get the ID of the newly created property
            http_response_code(201);
            echo json_encode(array(
                "message" => "Property was successfully added.",
                "property" => array(
                    "id" => $property_id,
                    "title" => $data->title
                )
            ));
        } else {
            throw new Exception("Unable to add the property due to an unknown error.");
        }
    } catch (PDOException $e) {
        http_response_code(503);
        if ($e->errorInfo[1] == 1062) {
            echo json_encode(array("message" => "Error: Duplicate entry for the property."));
        } else {
            echo json_encode(array("message" => "Server error occurred.", "error" => $e->getMessage()));
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => $e->getMessage()));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to add the property. Missing required data."));
}